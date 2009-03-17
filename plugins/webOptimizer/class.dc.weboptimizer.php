<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of webOptimizer,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Peck and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------

require_once(dirname(__FILE__).'/minify/JSMin.php');
require_once(dirname(__FILE__).'/minify/CommentPreserver.php');
require_once(dirname(__FILE__).'/minify/CSS.php');
require_once dirname(__FILE__).'/../../inc/clearbricks/common/lib.l10n.php';


/**
 * This class holds one file that is (or can be) optimized
 *
 * @package    webOptimizer
 * @author     Peck
 * @version    SVN: $Id: $
 */
class dcWebOptimizer
{
	/**
	 * dcCore reference
	 *
	 * @var dcCore
	 * @access private
	 */
	var $filename;
	var $filetype;

	/**
	 * Constructor
	 *
	 * @param dcCore $core
	 */
	public function __construct($file)
	{
		$this->filename = $file;
		$this->filetype = self::getFileType($file);
		if($this->filetype == "other") {
			throw new Exception(sprintf(__('webOptimizer BUG : %s is not an optimizable file'),$file));
		}
	}

	/**
	 * Check that we have the right to read, write and delete current file and its backup
	 * Throws an exception otherwise
	 */
	public function canChange()
	{
		$dirname = dirname($this->filename);
		if (!is_writable($dirname))
		{
			throw new Exception(sprintf(__('%s is not writable'),$dirname));
		}
		$file = $this->getOriginalName();
		if ( file_exists($file) )
		{
			if(!is_readable($file))
			{
				throw new Exception(sprintf(__('%s is not readable'), $file));
			}
			if(!is_writable($file))
			{
				 throw new Exception(sprintf(__('%s is not writablee'), $file));
			}
		}
		$file = $this->getBackupName();
		if ( file_exists($file) )
		{
			if(!is_readable($file))
			{
				 throw new Exception(sprintf(__('%s is not readable'), $file));
			}
			if(!is_writable($file))
			{
				throw new Exception(sprintf(__('%s is not writablee'), $file));
			}
		}
	}

	/**
	 * Retrieve given file extension
	 *
	 * @param file file name
	 * @return css|js|lang|other
	 */
	public static function getFileType($file)
	{
		$file = strtolower($file);
		if(!preg_match("/^.*?(\\.\\w+)?\\.(\\w+)\$/",$file,$m)) {
			return "other";
		}
		switch($m[2]) {
			case "js":
			case "css":
			case "po":
				return $m[2];
			case "php":
				if($m[1] == ".lang")
					return "po";
		}
		return "other";
	}

	/**
	 * Return truc if this file is a backup
	 */
	public function isBackup()
	{
		if($this->filetype == "po") {
			return(preg_match("/\\.po\$/i", $this->filename));
		}
		// filetype js or css
		return(preg_match("/\\.bak.\w+\$/i", $this->filename));
	}

	/**
	 * Get the backup name of this file (if this is a backup, keep it as is)
	 *
	 */
	public function getBackupName()
	{
		if($this->isBackup()) {
			return $this->filename;
		}
		if($this->filetype == "po") {
			 return(preg_replace("/\\.lang\\.php\$/", ".po", $this->filename));
		}
		// filetype js or css
		return(preg_replace("/\\.(\\w+)\$/", ".bak.\$1", $this->filename));
	}

	/**
	 * Get the original name of this file (if this is an original, keep it as is)
	 *
	 */
	public function getOriginalName()
	{
		if(!$this->isBackup()) {
			return $this->filename;
		}
		if($this->filetype == "po") {
			return(preg_replace("/\\.po\$/i", ".lang.php", $this->filename));
		}
		// filetype js or css
		return(preg_replace("/\\.bak.(\\w+)\$/i", ".\$1", $this->filename));
	}

	/**
	 * Return true if the backup file exists
	 */
	public function isBackupOk()
	{
		return(file_exists($this->getBackupName()));
	}

	/**
	 * Calculate the gain obtained by this compression
	 *
	 * @return gain in percent
	 */
	public function percent()
	{
		if($this->filetype == "po") {
			if ($this->isBackupOk()) {
				return "compiled";
			}
			return "original";
		}
		if ($this->isBackup()) {
			return( round( filesize($this->getOriginalName()) / 
				filesize($this->filename) * 100, 2 ) . "%" );
		}
		elseif ($this->isBackupOk()) {
			return(	round( filesize($this->filename) / 
				filesize($this->getBackupName()) * 100 , 2 ) . "%" );
		}
		# else
		return "original";
	}

	/**
	 * Try to optimize the file and estimate the gain
	 *
	 * @return gain in percent
	 */
	public function estimate()
	{
		$data = $this->optimizeData($this->filename);
		return( round( strlen($data) /
			filesize($this->filename) * 100 , 2 ) );
	}

	/**
	 * Optimize the content of a file and return optimized content
	 * @parameter file the file to optimize
	 *
	 * @return string : optimized content
	 */
	public static function optimizeData($file)
	{
		switch(self::getFileType($file))
		{
			case "css" :
				$data = file_get_contents($file);
				$data = Minify_CSS::minify($data);
				break;
			case "js" :
				$data = file_get_contents($file);
				$data = JSMin::minify($data);
				break;
			case "po" :
				$data = "<?php\n".
					$license_block.
					"#\n#\n#\n".
					"#        DOT NOT MODIFY THIS FILE !\n\n\n\n\n";
				foreach (l10n::getPoFile($file) as $vo => $tr) {
					$vo = str_replace("'","\\'",$vo);
					$tr = str_replace("'","\\'",$tr);
					$podata .= '$GLOBALS[\'__l10n\'][\''.$vo.'\'] = \''.$tr.'\';'."\n";
				}
				$podata .= "?>";
				break;	
			default :
				throw new Exception(sprintf(__('webOptimizer BUG : Can\'t get type of %s'), $file));
		}
		return $data;
	}

	/**
	 * Create the backup file from original content
	 */
	public function createBackup()
	{
		// po files stay as is
		if($this->filetype == "po") {
			return;
		}
		// can't create a backup of a backup
		if($this->isBackup()) {
			throw new Exception(sprintf(__('Can\'t create a backup of %s'), $file));
		}
		$this->canChange();
		copy($this->filename, $this->getBackupName());
		return(true);
	}

	/**
	 * Restore the backup to the original file
	 */	
	public function restoreBackup()
	{
		$this->canChange();
		if($this->filetype == "po") {
			// special case, po stay as is, .lang.php are removed
			unlink($this->getOriginalName());
		}
		else
		{
			if(!$this->isBackupOk()) {
				throw new Exception(sprintf(__('%s has no valid backup'), $this->filename));
			}
			copy($this->getBackupName(), $this->getOriginalName());
			unlink($this->getBackupName());
		}
	}

	/**
	 * Create a new optimized file (and create backup if it doesn't exist)
	 */
	public function optimizeFile()
	{
		$this->canChange();

		if (!$this->isBackupOk()) 
		{
			$this->createBackup();
		}

		$data = $this->optimizeData($this->getBackupName());
		file_put_contents($this->getOriginalName(), $data);
	}

	/**
	 * Private funciton to recursively scan a directory for one type of file
	 *
	 * @param basedir the dirname to scan
	 * @param basename the public dirname to store relative path
	 * @param the file type to select
	 *
	 * @return a table of elements the form "$basename/<path>/<filename>.$type" 
	 */
	static function scanDir($basedir, $basename, $type)
	{
		$result = array();
		$content = scandir($basedir);
		foreach($content as $file) {
			if(preg_match("/^\\./", $file)) {
				continue;
			}
			if(is_dir("$basedir/$file")) {
				$array = self::scanDir("$basedir/$file", "$basename/$file", $type);
				$result = array_merge($result, $array);
				continue;
			}
			if(self::getFileType($file) == $type) {
				array_push($result, "$basename/$file");
				continue;
			}
		}
		return $result;
	}

	/**
	 * Return file size formated for display
	 *
	 * @param file the file name
	 * 
	 * @return file size as a string
	 */
	static function fileSize($file)
	{
		$size = filesize($file);
		if($size > 2048) {
			$size = round( $size / 1024, 0) . " KiB";
		} else {
			$size = "$size B";
		}
		return $size;
	}

	/**
	 * Display a list of file of the given type in a form
	 * 
	 * @param base_dir the directory to scan
	 * @param type the file type to scan for
	 * @param estimate list of files to actualy calculate gain estimation when scaning
	 *
	 */
	public static function fileTable($base_dir, $type, $estimate)
	{
		$files = self::scanDir($base_dir, ".", $type);
		foreach($files as $file) {
			$fobject = new dcWebOptimizer("$base_dir/$file");

			// only display necessary file
			if($fobject->isBackup() && $fobject->filetype != "po") {
				continue;
			}
			if(!$fobject->isBackup() && $fobject->filetype == "po") {
				continue;
			}

			echo "<input type=\"checkbox\" name=\"check".$type."[]\" value=\"$base_dir/$file\"  onclick=\"checkAll(2,'check".$type."[]','checkAll".$type."');\" /> <span id='filename'>$file</span> <span id='size'>";
			echo "<span id='size'>".self::fileSize("$base_dir/$file")." ";
			$pc = $fobject->percent();
			echo "($pc)";
			if(in_array("$base_dir/$file", $estimate)) {
				$estimation = $fobject->estimate();
				echo " (est $estimation%)";
			}
			echo "</span> <br />\n";
		}
	}

	/**
	 * Displays a tab giving acces to fils optimizations
	 *
	 * @param dir directory to scan
	 * @param type file type to scan
	 * @param title tab title
	 * @param estimate list of file to estimate
	 */
	public static function fileTab($core, $dir, $type, $title, $estimate)
	{
		echo "<div class=\"multi-part\" id=\"$type\" title=\"$title\">\n";
		echo "<form action=\"$p_url\" method=\"post\">\n";
		echo "<fieldset>";
		echo "<legend>".__('Files')."</legend>\n";
		echo "<p>".$core->formNonce()."</p>\n";
		echo "<p>";
		self::fileTable($dir, $type, $estimate);
		echo "<br />";
	 	echo "<input type=\"checkbox\" id=\"checkAll".$type."\" onclick=\"checkAll(1, 'check".$type."[]','checkAll".$type."');\" />".__('check all');
		echo "<input type=\"hidden\" name=\"type\" value=\"check".$type."\" />\n";
		echo "<input type=\"submit\" name=\"optimize\" value=\"".__('optimize')."\" />\n";
		echo "<input type=\"submit\" name=\"restore\" value=\"".__('restore')."\" />\n";
		echo "<input type=\"submit\" name=\"estimate\" value=\"".__('estimate')."\" />\n";
		echo "</p></fieldset>\n";
		echo "</form></div>\n";
	}
}
?>
