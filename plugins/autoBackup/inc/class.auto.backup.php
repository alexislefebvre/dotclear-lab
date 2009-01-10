<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of autoBackup, a plugin for Dotclear.
# 
# Copyright (c) 2008 k-net, Franck, Tomtom
# http://www.k-netweb.net/
# http://www.franck-paul.fr/
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class autoBackup
{	
	public static function getConfig()
	{
		global $core;

		# Get current config from database
		if ($core->blog->settings->autobackup_config) {
			$config = unserialize($core->blog->settings->autobackup_config);
		}
		# If no config or badly formatted, set a fresh one
		if (!isset($config) || !is_array($config)) {
			$config = array();
		}

		# Set default options if undefined:

		# Filepath of import-export needed class
		# Franck Paul : Is there any solution to detect it automatically (in order to avoid to ask it to the final user)?
		if (!isset($config['importexportclasspath'])) $config['importexportclasspath'] = realpath($core->plugins->moduleRoot('importExport').'/inc/flat/class.db.export.php');

		# Backup on file group:
		if (!isset($config['backup_onfile'])) $config['backup_onfile'] = false;
		if (!isset($config['backup_onfile_repository'])) $config['backup_onfile_repository'] = $core->blog->public_path;
		if (!isset($config['backup_onfile_compress_gzip'])) $config['backup_onfile_compress_gzip'] = false;
		if (!isset($config['backup_onfile_deleteprev'])) $config['backup_onfile_deleteprev'] = false;

		# Backup sent by mail group:
		if (!isset($config['backup_onemail'])) $config['backup_onemail'] = false;
		if (!isset($config['backup_onemail_adress'])) $config['backup_onemail_adress'] = '';
		if (!isset($config['backup_onemail_compress_gzip'])) $config['backup_onemail_compress_gzip'] = true;
		if (!isset($config['backup_onemail_header_from'])) $config['backup_onemail_header_from'] = $core->blog->name.' <your@email.com>';

		# Backup characteristics:
		if (!isset($config['backuptype'])) $config['backuptype'] = 'full';
		if (!isset($config['backupblogid'])) $config['backupblogid'] = $core->blog->id;
		if (!isset($config['interval'])) $config['interval'] = 3600*24;

		# Last backups done:
		if (!isset($config['backup_onfile_last'])) $config['backup_onfile_last'] = array('date' => 0, 'file' => '');
		if (!isset($config['backup_onemail_last'])) $config['backup_onemail_last'] = array('date' => 0);
		
		# Running backup flag:
		if (!isset($config['backup_running'])) $config['backup_running'] = false;
		
		# Backup ASAP flag:
		if (!isset($config['backup_asap'])) $config['backup_asap'] = false;

		return $config;
	}

	public static function setConfig($config)
	{
		global $core;

		$core->blog->settings->setNamespace('autobackup');
		$core->blog->settings->put('autobackup_config',serialize($config),'string');
		$core->blog->triggerBlog();
	}

	public static function check()
	{
		global $core;

		# Get last or default config
		$config = self::getConfig();

		if ($config['interval'] > 0) {
			
			# Backup ASAP is resquested?
			if ($config['backup_asap']) {
				# Interval set as 1 second in order to force the backup to run as soon as possible
				$interval = 1;
			} else {
				$interval = $config['interval'];
			}

			# Should we start new backup?
			$offset = dt::getTimeOffset($core->blog->settings->blog_timezone);
			$time = time() + $offset;
			$backup_onfile = $config['backup_onfile'] && (($config['backup_onfile_last']['date'] + $interval) <= $time);
			$backup_onemail = $config['backup_onemail'] && (($config['backup_onemail_last']['date'] + $interval) <= $time);
			
			if ($backup_onfile || $backup_onemail) {
				# Is there already backup running?
				# We assume that the running backup must not take more than half of the interval
				if ($config['backup_running']) {
					if ($config['backup_onfile_last']['date']) {
						if ($backup_onfile && (($time - $config['backup_onfile_last']['date']) >= ($config['interval']/2))) {
							# Previous backup on file started more than half of interval ago, we cancel it
							$config['backup_running'] = false;
						}
					}
					if ($config['backup_onemail_last']['date']) {
						if ($backup_onemail && (($time - $config['backup_onemail_last']['date']) >= ($config['interval']/2))) {
							# Previous backup by email started more than half of interval ago, we cancel it
							$config['backup_running'] = false;
						}
					}
				}

				if (!$config['backup_running']) {

					# We must do the backup, register that it is running from now
					$config['backup_running'] = true;
					self::setConfig($config);

					# Set the according filename
					$backupname = ($config['backuptype'] != 'full' ? $config['backupblogid'] : 'blog').'-backup-'.date('Ymd-H\hi',$time).'.txt';
					$backupname .= ($config['backup_onfile_compress_gzip'] || $config['backup_onemail_compress_gzip']) ? '.gz' : '';

					if ($backup_onfile) {
						$file = $config['backup_onfile_repository'].'/'.$backupname;
					} else {
						$file = realpath(dirname(__FILE__).'/'.$backupname);
					}

					if ($config['backuptype'] == 'full') {
						$backup_content = self::backup_full($file);
					} elseif ($config['backupblogid'] == $core->blog->id) {
						$backup_content = self::backup_blog($file, $config['backupblogid']);
					}

					if (!empty($backup_content)) {

						# Create backup file
						if ($backup_onfile) {

							if (is_file($file)) {
								# Encode content with gzip if needed
								if ($config['backup_onfile_compress_gzip'] && is_writable($file)) {
									file_put_contents($file, gzencode(file_get_contents($file), 9));
								}

								if ($config['backup_onfile_deleteprev'] && is_file($config['backup_onfile_repository'].'/'.$config['backup_onfile_last']['file'])) {
									@unlink($config['backup_onfile_repository'].'/'.$config['backup_onfile_last']['file']);
								}
								$config['backup_onfile_last']['date'] = $time;
								$config['backup_onfile_last']['file'] = $file;
							}
						}

						# Send backup email
						if ($backup_onemail) {
							if ($config['backup_onemail_compress_gzip'] && is_writable($file)) {
								file_put_contents($file, gzencode(file_get_contents($file), 9));
							}
							elseif (is_writable($file)) {
								file_put_contents($file, file_get_contents($file));
							}

							$mail = new mail();
							$mail->to = $config['backup_onemail_adress'];
							$mail->from = $config['backup_onemail_header_from'];
							$mail->subject = sprintf(__('Auto Backup : %s'),$core->blog->name);
							$mail->message = 
								sprintf(__('This is an automatically sent message from your blog %s.'), $core->blog->name)."\n".
								sprintf(__('You will find attached the backup file created on %s.'), date('r', $time));
							$mail->date = dt::rfc822(time(),$core->blog->settings->blog_timezone);
							$mail->utf8 = true;
							$mail->attach($file);
							if ($mail->send()) {
								$config['backup_onemail_last']['date'] = $time;
							}
						}

						# Let's delete the temporary file
						if (!$backup_onfile) {
							@unlink($file);
						}

						# The backup is no more running
						$config['backup_running'] = false;
						
						# The next backup will run according to the current setting (interval)
						$config['backup_asap'] = false;
						
						# Register the new config
						self::setConfig($config);
					}
				}
			}
		}
	}
	                                                                                                            
	public static function backup_full($file) {
		
		global $core;
		 
		$config = self::getConfig();
		
		if (!is_file($config['importexportclasspath'])) {
			return false;
		}
		require_once $config['importexportclasspath'];
		
		if (((is_file($file) && is_writable($file)) || (!file_exists($file) && is_writable(dirname($file))))) {
			
			$exp = new dbExport($core->con,$file,$core->prefix);
			
			fwrite($exp->fp,'#/DOTCLEAR|'.DC_VERSION."|full\n");
			$exp->exportTable('blog');
			$exp->exportTable('category');
			$exp->exportTable('link');
			$exp->exportTable('setting');
			$exp->exportTable('user');
			$exp->exportTable('permissions');
			$exp->exportTable('post');
			$exp->exportTable('media');
			$exp->exportTable('post_media');
			$exp->exportTable('log');
			$exp->exportTable('ping');
			$exp->exportTable('comment');
			$exp->exportTable('spamrule');
			$exp->exportTable('version');
			
			# Export tags only if we're in public
			# In admin it's done by the metadata plugin by calling the behavior
			if (!defined('DC_CONTEXT_ADMIN')) {
				$exp->exportTable('meta');
			}
			
			# --BEHAVIOR-- exportFull
			$core->callBehavior('exportFull',$core,$exp);
			
			return true;
		}
		
		return false;
		/*
		rewind($exp->fp);
		return stream_get_contents($exp->fp);
		#*/
	}
	
	public static function backup_blog($file, $blog_id) {
		
		global $core;
		 
		$config = self::getConfig();
		
		if (!is_file($config['importexportclasspath'])) {
			return false;
		}
		require_once $config['importexportclasspath'];
		
		if ($blog_id != $core->blog->id) {
			return false;
		}
		
		if (((is_file($file) && is_writable($file)) || (!file_exists($file) && is_writable(dirname($file))))) {
			
			$exp = new dbExport($core->con,$file,$core->prefix);
			
			fwrite($exp->fp,'#/DOTCLEAR|'.DC_VERSION."|single\n");
			$exp->export('category',
				'SELECT * FROM '.$core->prefix.'category '.
				"WHERE blog_id = '".$blog_id."'"
			);
			$exp->export('link',
				'SELECT * FROM '.$core->prefix.'link '.
				"WHERE blog_id = '".$blog_id."'"
			);
			$exp->export('setting',
				'SELECT * FROM '.$core->prefix.'setting '.
				"WHERE blog_id = '".$blog_id."'"
			);
			$exp->export('post',
				'SELECT * FROM '.$core->prefix.'post '.
				"WHERE blog_id = '".$blog_id."'"
			);
			$exp->export('media',
				'SELECT * FROM '.$core->prefix."media WHERE media_path = '".
				$core->con->escape($core->blog->settings->public_path)."'"
			);
			$exp->export('post_media',
				'SELECT media_id, M.post_id '.
				'FROM '.$core->prefix.'post_media M, '.$core->prefix.'post P '.
				'WHERE P.post_id = M.post_id '.
				"AND P.blog_id = '".$blog_id."'"
			);
			$exp->export('ping',
				'SELECT ping.post_id, ping_url, ping_dt '.
				'FROM '.$core->prefix.'ping ping, '.$core->prefix.'post P '.
				'WHERE P.post_id = ping.post_id '.
				"AND P.blog_id = '".$blog_id."'"
			);
			$exp->export('comment',
				'SELECT C.* '.
				'FROM '.$core->prefix.'comment C, '.$core->prefix.'post P '.
				'WHERE P.post_id = C.post_id '.
				"AND P.blog_id = '".$blog_id."'"
			);
			
			# Export tags only if we're in public
			# In admin it's done by the metadata plugin by calling the behavior
			if (!defined('DC_CONTEXT_ADMIN')) {
				$exp->export('meta',
					'SELECT meta_id, meta_type, M.post_id '.
					'FROM '.$core->prefix.'meta M, '.$core->prefix.'post P '.
					'WHERE P.post_id = M.post_id '.
					"AND P.blog_id = '".$blog_id."'"
				);
			}
			
			# --BEHAVIOR-- exportSingle
			$core->callBehavior('exportSingle',$core,$exp,$blog_id);
			
			return true;
		}
		
		return false;
		/*
		rewind($exp->fp);
		return stream_get_contents($exp->fp);
		#*/
	}
}

?>