<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Upload Updater" plugin.
#
# Copyright (c) 2003-2010 DC Team
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class dcUpdateLocal extends dcUpdate
{
	const ERR_FILES_CHANGED = 101;
	const ERR_FILES_UNREADABLE = 102;
	const ERR_FILES_UNWRITALBE = 103;
	
	protected $archive;
	protected $cache_dir;
	
	/**
	Constructor
	
	@param url		string		Versions file URL
	@param subject		string		Subject to check
	@param version		string		Version type
	@param cache_dir	string		Directory cache path
	*/
	public function __construct($file,$cache,$cache_dir)
	{
		$this->url = '';
		$this->subject = '';
		$this->version = '';
		$this->archive = $cache_dir.'/'.$file;
		$this->cache_file = $cache_dir.'/uu_info';
	}
	
	
	public function do_upload($fileinfo) {
		files::uploadStatus($fileinfo);
		$new_v=true;
		if (@move_uploaded_file($fileinfo['tmp_name'],$this->archive) === false) {
			throw new Exception(__('An error occurred while fetching the file.'));
		}
		$zip = new fileUnzip($this->archive);
		if (!$zip->hasFile('dotclear/inc/digests') && !$zip->hasFile('dotclear/inc/prepend.php')) {
			@unlink($this->archive);
			throw new Exception(__('Uploaded file seems not to be a valid archive.'));
		}
		$zip->unzip('dotclear/inc/prepend.php', $this->cache_file);
		$count = preg_match("#define\\('DC_VERSION','([^']+)'\\)#msu",file_get_contents($this->cache_file),$matches);
		if ($count <1) {
			@unlink($this->archive);
			throw new Exception(__('Uploaded file seems not to be a valid archive.'));
		}
		$ver = $matches[1];
		if (version_compare(DC_VERSION,$ver,'>=')) {
			@unlink($this->archive);
			throw new Exception(__('Uploaded version is older or equal to current version.'));
		}
		if (version_compare(DC_VERSION,'2.1.7','<') && version_compare ($ver,'2.2.alpha','>=')) {
			@unlink($this->archive);
			throw new Exception(__('Please consider updating to 2.1.7 before any upgrade to 2.2 branch.'));
		}
	}
	

	
	/**
	Backups changed files before an update.
	*/
	public function backup($zip_digests,$root,$root_digests,$dest)
	{
		return parent::backup($this->archive,$zip_digests,$root,$root_digests,$dest);
	}

	/**
	Upgrade process.
	*/
	public function performUpgrade($zip_digests,$zip_root,$root,$root_digests)
	{
		return parent::performUpgrade($this->archive,$zip_digests,$zip_root,$root,$root_digests);
	}
}
?>