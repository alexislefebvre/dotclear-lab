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
	protected $config;
	protected $content;
	protected $offset;
	protected $time;
	protected $backup_file;
	protected $email_file;

	/**
	 * Construct new autoBackup object
	 *
	 * @param:	array	core
	 */
	public function __construct($core)
	{
		$this->core =& $core;
		$this->offset = dt::getTimeOffset($core->blog->settings->blog_timezone);
		$this->format = $core->blog->settings->date_format.' - '.$core->blog->settings->time_format;
		$this->content = null;
		$this->config = null;
		$this->time = null;
		$this->backup_file = null;
		$this->email_file = null;

		$this->getConfig();
	}

	/**
	 * Retrieves plugin configuration and sets $this->config attribut
	 *
	 * @return:	array
	 */
	public function getConfig()
	{
		# Get current config from database
		if ($this->core->blog->settings->autobackup_config) {
			$this->config = unserialize($this->core->blog->settings->autobackup_config);
		}
		# If no config or badly formatted, set a fresh one
		if (!isset($this->config) || !is_array($this->config)) {
			$this->config = array();
		}

		# Set default options if undefined:

		# Filepath of import-export needed class
		if (!isset($this->config['importexportclasspath'])) $this->config['importexportclasspath'] = realpath($this->core->plugins->moduleRoot('importExport').'/inc/flat/class.db.export.php');

		# Backup on file group:
		if (!isset($this->config['backup_onfile'])) $this->config['backup_onfile'] = false;
		if (!isset($this->config['backup_onfile_repository'])) $this->config['backup_onfile_repository'] = $this->core->blog->public_path;
		if (!isset($this->config['backup_onfile_compress_gzip'])) $this->config['backup_onfile_compress_gzip'] = false;
		if (!isset($this->config['backup_onfile_deleteprev'])) $this->config['backup_onfile_deleteprev'] = false;

		# Backup sent by mail group:
		if (!isset($this->config['backup_onemail'])) $this->config['backup_onemail'] = false;
		if (!isset($this->config['backup_onemail_adress'])) $this->config['backup_onemail_adress'] = '';
		if (!isset($this->config['backup_onemail_compress_gzip'])) $this->config['backup_onemail_compress_gzip'] = true;
		if (!isset($this->config['backup_onemail_header_from'])) $this->config['backup_onemail_header_from'] = $this->core->blog->name.' <your@email.com>';

		# Backup characteristics:
		if (!isset($this->config['backuptype'])) $this->config['backuptype'] = 'full';
		if (!isset($this->config['backupblogid'])) $this->config['backupblogid'] = $this->core->blog->id;
		if (!isset($this->config['interval'])) $this->config['interval'] = 3600*24;

		# Last backups done:
		if (!isset($this->config['backup_onfile_last'])) $this->config['backup_onfile_last'] = array('date' => 0, 'file' => '');
		if (!isset($this->config['backup_onemail_last'])) $this->config['backup_onemail_last'] = array('date' => 0);
		
		# Running backup flag:
		if (!isset($this->config['backup_running'])) $this->config['backup_running'] = false;
		
		# Backup ASAP flag:
		if (!isset($this->config['backup_asap'])) $this->config['backup_asap'] = false;

		# Backup errors
		if (!isset($this->config['errors'])) $this->config['errors'] = array('config' => '','file' => '','email' => '');

		return $this->config;
	}

	/**
	 * Saves and updates plgin configuration
	 *
	 * @param:	array	config
	 *
	 * @return:	boolean
	 */
	public function setConfig($config = null)
	{
		if ($config !== null && is_array($config)) {
			foreach ($config as $k => $v) {
				if (array_key_exists($k,$this->config)) {
					$this->config[$k] = $v;
				}
			}
		}

		try {
			$this->core->blog->settings->setNamespace('autobackup');
			$this->core->blog->settings->put('autobackup_config',serialize($this->config),'string');
			$this->core->blog->triggerBlog();
			return true;
		}
		catch (Exception $e) {
			$this->config['errors']['config'] = sprintf('%s : %s',dt::str($this->format,time() + $this->offset),$e->getMessage());
			return false;
		}
	}

	/**
	 * Checks to create a new backup or not
	 */
	public function check()
	{
		if ($this->config['interval'] > 0) {

			# Backup ASAP is resquested?
			$interval = $this->config['backup_asap'] ? 1 : $this->config['interval'];

			# Get time
			$this->time = time() + $this->offset;

			# Get file names
			$this->backup_file = $this->config['backup_onfile_repository'].'/';
			$this->backup_file .= ($this->config['backuptype'] != 'full' ? $this->config['backupblogid'] : 'blog').'-backup-'.date('Ymd-H\hi',$this->time);
			$this->backup_file .= $this->config['backup_onfile_compress_gzip'] ? '.gz' : '.txt';
			$this->email_file = realpath(dirname(__FILE__)).'/';
			$this->email_file .= ($this->config['backuptype'] != 'full' ? $this->config['backupblogid'] : 'blog').'-backup-'.date('Ymd-H\hi',$this->time);
			$this->email_file .= $this->config['backup_onemail_compress_gzip'] ? '.gz' : '.txt';

			# Should we start new backup?
			$backup_onfile = $this->config['backup_onfile'] && (($this->config['backup_onfile_last']['date'] + $interval) <= $this->time);
			$backup_onemail = $this->config['backup_onemail'] && (($this->config['backup_onemail_last']['date'] + $interval) <= $this->time);

			# If yes, get content
			if ($backup_onfile || $backup_onemail) {
				if ($this->config['backuptype'] == 'full') {
					$this->getFullContent();
				}
				elseif ($this->config['backupblogid'] == $this->core->blog->id) {
					$this->getBlogContent();
				}

				//echo $this->content; exit;
				# Is there already backup running?
				# We assume that the running backup must not take more than half of the interval
				if ($this->config['backup_running']) {
					if ($this->config['backup_onfile_last']['date']) {
						if ($backup_onfile && (($this->time - $this->config['backup_onfile_last']['date']) >= ($this->config['interval']/2))) {
							# Previous backup on file started more than half of interval ago, we cancel it
							$this->config['backup_running'] = false;
						}
					}
					if ($this->config['backup_onemail_last']['date']) {
						if ($backup_onemail && (($this->time - $this->config['backup_onemail_last']['date']) >= ($this->config['interval']/2))) {
							# Previous backup by email started more than half of interval ago, we cancel it
							$this->config['backup_running'] = false;
						}
					}
				}

				if (!$this->config['backup_running']) {

					# We must do the backup, register that it is running from now
					$this->config['backup_running'] = true;
					$this->setConfig();

					# Create backup file
					if ($backup_onfile) {
						if (is_writable($this->config['backup_onfile_repository'])) {
							try {
								file_put_contents(
									$this->backup_file,
									$this->config['backup_onfile_compress_gzip'] ? gzencode($this->content,9) : $this->content
								);
								$last = $this->config['backup_onfile_last']['file'];
								if ($this->config['backup_onfile_deleteprev'] && is_file($last)) {
									unlink($last);
								}
								$this->config['backup_onfile_last']['date'] = $this->time;
								$this->config['backup_onfile_last']['file'] = $this->backup_file;
								$this->config['errors']['file'] = '';	
							}
							catch (Exception $e) {
								$this->config['errors']['file'] = sprintf('%s : %s',dt::str($this->format,$this->time),$e->getMessage());
							}
						}
						else {
							$this->config['errors']['file'] = sprintf('%s : %s',dt::str($this->format,$this->time),__('Impossible to write backup file'));
						}
					}

					# Send backup email
					if ($backup_onemail) {
						if (is_writable(realpath(dirname(__FILE__)))) {
							try {
								file_put_contents(
									$this->email_file,
									$this->config['backup_onemail_compress_gzip'] ? gzencode($this->content,9) : $this->content
								);
								$mail = new mail();
								$mail->to = $this->config['backup_onemail_adress'];
								$mail->from = $this->config['backup_onemail_header_from'];
								$mail->subject = sprintf(__('Auto Backup : %s'),$this->core->blog->name);
								$mail->message = 
									sprintf(__('This is an automatically sent message from your blog %s.'), $this->core->blog->name)."\n".
									sprintf(__('You will find attached the backup file created on %s.'), date('r', $this->time));
								$mail->date = dt::rfc822($this->time,$this->core->blog->settings->blog_timezone);
								$mail->utf8 = true;
								$mail->attach($this->email_file);
								if ($mail->send()) {
									$this->config['backup_onemail_last']['date'] = $this->time;
									$this->config['errors']['email'] = '';
								}
								else {
									$this->config['errors']['file'] = sprintf('%s : %s',dt::str($this->format,$this->time),__('Impossible to send email'));
								}
							}
							catch (Exception $e) {
								$this->config['errors']['email'] = sprintf('%s : %s',dt::str($this->format,$this->time),$e->getMessage());
							}
						}
						else {
							$this->config['errors']['email'] = sprintf('%s : %s',dt::str($this->format,$this->time),__('Impossible to write email file'));
						}
					}

					# The backup is no more running
					$this->config['backup_running'] = false;
					# The next backup will run according to the current setting (interval)
					$this->config['backup_asap'] = false;
					# Register the new config
					$this->setConfig();
				}
			}
		}
	}

	/**
	 * Retrieves a full content of database
	 *
	 * @param:	string	file
	 *
	 * @return:	boolean
	 */
	protected function getFullContent($file = 'php://temp')
	{
		try {
			require_once $this->config['importexportclasspath'];
			$exp = new dbExport($this->core->con,$file,$this->core->prefix);
		}
		catch (Exception $e) {
			$this->config['errors']['config'] = $e->getMessage();
			return false;
		}

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
		$this->core->callBehavior('exportFull',$this->core,$exp);

		rewind($exp->fp);
		$this->content = stream_get_contents($exp->fp);

		return true;
	}

	/**
	 * Retrieves a blog content from database
	 *
	 * @param:	string	file
	 *
	 * @return:	boolean
	 */
	protected function getBlogContent($file = 'php://temp')
	{
		try {
			require_once $this->config['importexportclasspath'];
			$exp = new dbExport($this->core->con,$file,$this->core->prefix);
		}
		catch (Exception $e) {
			$this->config['errors']['config'] = $e->getMessage();
			return false;
		}
	
		$exp = new dbExport($this->core->con,$file,$this->core->prefix);

		fwrite($exp->fp,'#/DOTCLEAR|'.DC_VERSION."|single\n");
		$exp->export('category',
			'SELECT * FROM '.$this->core->prefix.'category '.
			"WHERE blog_id = '".$this->core->blog->id."'"
		);
		$exp->export('link',
			'SELECT * FROM '.$this->core->prefix.'link '.
			"WHERE blog_id = '".$this->core->blog->id."'"
		);
		$exp->export('setting',
			'SELECT * FROM '.$this->core->prefix.'setting '.
			"WHERE blog_id = '".$this->core->blog->id."'"
		);
		$exp->export('post',
			'SELECT * FROM '.$this->core->prefix.'post '.
			"WHERE blog_id = '".$this->core->blog->id."'"
		);
		$exp->export('media',
			'SELECT * FROM '.$this->core->prefix."media WHERE media_path = '".
			$this->core->con->escape($this->core->blog->settings->public_path)."'"
		);
		$exp->export('post_media',
			'SELECT media_id, M.post_id '.
			'FROM '.$this->core->prefix.'post_media M, '.$this->core->prefix.'post P '.
			'WHERE P.post_id = M.post_id '.
			"AND P.blog_id = '".$this->core->blog->id."'"
		);
		$exp->export('ping',
			'SELECT ping.post_id, ping_url, ping_dt '.
			'FROM '.$this->core->prefix.'ping ping, '.$this->core->prefix.'post P '.
			'WHERE P.post_id = ping.post_id '.
			"AND P.blog_id = '".$this->core->blog->id."'"
		);
		$exp->export('comment',
			'SELECT C.* '.
			'FROM '.$this->core->prefix.'comment C, '.$this->core->prefix.'post P '.
			'WHERE P.post_id = C.post_id '.
			"AND P.blog_id = '".$this->core->blog->id."'"
		);

		# Export tags only if we're in public
		# In admin it's done by the metadata plugin by calling the behavior
		if (!defined('DC_CONTEXT_ADMIN')) {
			$exp->export('meta',
				'SELECT meta_id, meta_type, M.post_id '.
				'FROM '.$this->core->prefix.'meta M, '.$this->core->prefix.'post P '.
				'WHERE P.post_id = M.post_id '.
				"AND P.blog_id = '".$this->core->blog->id."'"
			);
		}

		# --BEHAVIOR-- exportSingle
		$this->core->callBehavior('exportSingle',$this->core,$exp,$this->core->blog->id);

		rewind($exp->fp);
		$this->content = stream_get_contents($exp->fp);

		return true;
	}
}

?>