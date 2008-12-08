<?php
# ***** BEGIN LICENSE BLOCK *****
# This is Auto Backup, a plugin for DotClear. 
# Copyright (c) 2005 k-net. All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$this->registerModule(
	/* Name */			"Auto Backup",
	/* Description*/		"Make backups automatically",
	/* Author */			"k-net, brol",
	/* Version */			'1.1.5',
	/* Permissions */		'usage,contentadmin'
);


class autoBackup {
	
	public static function getConfig() {
		
		global $core;
		
		if ($core->blog->settings->autobackup_config) {
			$config = unserialize($core->blog->settings->autobackup_config);
		}
		if (!isset($config) || !is_array($config)) {
			$config = array();
		}
		
		if (!isset($config['importexportclasspath'])) $config['importexportclasspath'] = realpath($core->plugins->moduleRoot('importExport').'/inc/flat/class.db.export.php');
		if (!isset($config['backup_onfile'])) $config['backup_onfile'] = false;
		if (!isset($config['backup_onemail'])) $config['backup_onemail'] = false;
		if (!isset($config['backup_onfile_repository'])) $config['backup_onfile_repository'] = $core->blog->public_path;
		if (!isset($config['backup_onfile_compress_gzip'])) $config['backup_onfile_compress_gzip'] = false;
		if (!isset($config['backup_onfile_deleteprev'])) $config['backup_onfile_deleteprev'] = false;
		if (!isset($config['backup_onemail_adress'])) $config['backup_onemail_adress'] = '';
		if (!isset($config['backup_onemail_compress_gzip'])) $config['backup_onemail_compress_gzip'] = true;
		if (!isset($config['backup_onemail_header_from'])) $config['backup_onemail_header_from'] = $core->blog->name.' <your@email.com>';
		if (!isset($config['backuptype'])) $config['backuptype'] = 'full';
		if (!isset($config['backupblogid'])) $config['backupblogid'] = $core->blog->id;
		if (!isset($config['interval'])) $config['interval'] = 3600*24;
		if (!isset($config['backup_onfile_last'])) $config['backup_onfile_last'] = array('date' => 0, 'file' => '');
		if (!isset($config['backup_onemail_last'])) $config['backup_onemail_last'] = array('date' => 0);
		
		#self::setConfig($config);
		
		return $config;
	}
	
	public static function setConfig($config) {
		
		global $core;
		
		$core->blog->settings->setNamespace('autobackup');
		$core->blog->settings->put('autobackup_config',serialize($config),'string');
		$core->blog->triggerBlog();
	}
	
	public static function check() {
		
		global $core;
		 
		$config = self::getConfig();
		
		$time = time();
		
		$backup_onfile = $config['backup_onfile'] && $config['backup_onfile_last']['date'] + $config['interval'] <= $time;
		$backup_onemail = $config['backup_onemail'] && $config['backup_onemail_last']['date'] + $config['interval'] <= $time;
		
		if ($config['interval'] > 0 && ($backup_onfile || $backup_onemail)) {
			
			$backupname = ($config['backuptype'] != 'full' ? $config['backupblogid'] : 'blog').'-backup-'.date('Ymd-H\hi').'.txt';
			$backupname .= $config['backup_onfile_compress_gzip'] ? '.gz' : '';
			
			if ($backup_onfile) {
				$file = $config['backup_onfile_repository'].'/'.$backupname;
			} else {
				$file = dirname(__FILE__).'/tmp.txt';
			}
			
			if ($config['backuptype'] == 'full') {
				$backup_content = self::backup_full($file);
			} elseif ($config['backupblogid'] == $core->blog->id) {
				$backup_content = self::backup_blog($file, $config['backupblogid']);
			}
			
			if (!empty($backup_content)) {
				
				// Create backup file
				if ($backup_onfile) {
					
					if (is_file($file)) {
						// Encode content with gzip if needed
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
				
				// Send backup email
				if ($backup_onemail) {
					$backup_content = file_get_contents($file);
					
					if ($config['backup_onemail_compress_gzip']) {
						$backup_content = gzencode($backup_content, 9);
						// Add .gz if it ain't already done
						$backupname .= $config['backup_onfile_compress_gzip'] ? '' : '.gz';
					}
					
					require_once dirname(__FILE__).'/class.mime_mail.php';
					$email = new mime_mail(
						$config['backup_onemail_adress'],
						sprintf(__('Auto Backup : %s'),$core->blog->name),
						sprintf(__('This is an automatically sent message from your blog %s.'), $core->blog->name)."\n".
						sprintf(__('You will find attached the backup file created on %s.'), date('r', $time)),
						$config['backup_onemail_header_from']);
					$email->attach($backup_content, $backupname, 'application/octet-stream', $encoding='utf-8');
					if ($email->send()) {
						$config['backup_onemail_last']['date'] = $time;
					}
				}
				
				// Let's delete the temporary file
				if (!$backup_onfile) {
					unlink($file);
				}
				
				self::setConfig($config);
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
			
			fwrite($exp->fp,'///DOTCLEAR|'.DC_VERSION."|full\n");
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
			
			// Export tags only if we're in public
			// In admin it's done by the metadata plugin by calling the behavior
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
		//*/
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
			
			fwrite($exp->fp,'///DOTCLEAR|'.DC_VERSION."|single\n");
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
			
			// Export tags only if we're in public
			// In admin it's done by the metadata plugin by calling the behavior
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
		//*/
	}
	
}

autoBackup::check();

?>
