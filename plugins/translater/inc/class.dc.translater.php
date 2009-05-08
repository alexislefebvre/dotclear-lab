<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of "translater" a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcTranslater
{
	protected static $dcTranslaterVersion = '0.2.4';
	private $core;

	# List of l10n code/name allowed from clearbricks l10n class
	protected static $iso = array();
	# List of type of folder where backups could be saved
	public static $allowed_backup_folders = array(
		'public',
		'module',
		'plugin',
		'cache',
		'translater'
	);
	# List of l10n groups (subname) of file allowed
	public static $allowed_l10n_groups = array(
		'main',
		'public',
		'theme',
		'admin',
		'date',
		'error'
	);
	# List of informations about author allowed
	public static $allowed_user_informations = array(
		'firstname',
		'name',
		'displayname',
		'email',
		'url'
	);
	# List of settings and infos
	private $settings = array(
		'light_face' => array(
			'translater_light_face',
			0,
			'boolean',
			'Use easy and light interface',
			true,
			false
		),
		'two_cols' => array(
			'translater_two_cols',
			0,
			'boolean',
			'Use two columns in admin options',
			true,
			false
		),
		'plugin_menu' => array(
			'translater_plugin_menu',
			0,
			'boolean',
			'Put an link in plugins page',
			true,
			false
		),
		'theme_menu' => array(
			'translater_theme_menu',
			0,
			'boolean',
			'Put a link in themes page',
			true,
			false
		),
		'backup_auto' => array(
			'translater_backup_auto',
			1,
			'boolean',
			'Make a backup of languages old files when there are modified',
			true,
			false
		),
		'backup_limit' => array(
			'translater_backup_limit',
			20,
			'string',
			'Maximum backups per module',
			true,
			false
		),
		'backup_folder' => array(
			'translater_backup_folder',
			'module',
			'string',
			'In which folder to store backups',
			true,
			false
		),
		'write_po' => array(
			'translater_write_po',
			1,
			'boolean',
			'Write .po languages files',
			true,
			false
		),
		'write_langphp' => array(
			'translater_write_langphp',
			1,
			'boolean',
			'Write .lang.php languages files',
			true,
			false
		),
		'parse_nodc' => array(
			'translater_parse_nodc',
			1,
			'boolean',
			'Translate only untranslated strings of Dotclear',
			true,
			false
		),
		'parse_comment' => array(
			'translater_parse_comment',
			1,
			'boolean',
			'Write comments and strings informations in lang files',
			true,
			false
		),
		'parse_user' => array(
			'translater_parse_user',
			1,
			'boolean',
			'Write inforamtions about author in lang files',
			true,
			false
		),
		'parse_userinfo' => array(
			'translater_parse_userinfo',
			'displayname, email',
			'string',
			'Type of informations about user to write',
			true,
			false
		),
		'import_overwrite' => array(
			'translater_import_overwrite',
			0,
			'boolean',
			'Overwrite existing languages when import packages',
			true,
			false
		),
		'export_filename' => array(
			'translater_export_filename',
			'type-module-l10n-timestamp',
			'string',
			'Name of files of exported package',
			true,
			false
		)
	);
	# Settings default values
	private $S = array();
	# List of modules (from plugins,thems, by dcModule::getModules)
	private $modules = array();
	# Particular module
	private $module = array();

	function __construct($core)
	{
		$this->core =& $core;

		self::loadSettings();
		self::loadModules();
	}

	private function loadSettings()
	{
		$this->S = array();
		$this->core->blog->settings->setNamespace('translater');
		foreach($this->settings AS $k => $v) {
			$cur = $this->core->blog->settings->{$v[0]};
			if (null === $cur)
				self::setSettings($k,$v[1]);
			else
				$this->S[$k] = $cur;
		}
		# Set default settings values
		if ($this->S['backup_limit'] > 100 || $this->S['backup_limit'] < 2)
			self::setSettings('backup_limit',20);

		if (!in_array($this->S['backup_folder'],self::$allowed_backup_folders))
			self::setSettings('backup_folder','module');

		if (empty($this->S['export_filename']))
			self::setSettings('export_filename','type-module-l10n-timestamp');

		if (empty($this->S['parse_userinfo']))
			self::setSettings('parse_userinfo','firstname name, email');
	}

	public function setSettings($settings='',$v=null)
	{
		if (!is_array($settings))
			$settings = array($settings=>$v);

		foreach($settings as $k => $v) {
			if (!isset($this->settings[$k])) continue;

			$v = $this->settings[$k][2] == 'boolean' ? 
				(string) (integer) $v : (string) $v;

			$this->core->blog->settings->setNamespace('translater');
			$this->core->blog->settings->put(
				$this->settings[$k][0],
				$v,
				$this->settings[$k][2],
				$this->settings[$k][3],
				$this->settings[$k][4],
				$this->settings[$k][5]
			);

			$this->S[$k] = $v;
		}
	}

	public function getSettings($k='')
	{
		if (empty($k))
			return $this->S;

		if (!empty($k) && isset($this->S[$k]))
			return $this->S[$k];

		return null;
	}

	public function __get($k)
	{
		return self::getSettings($k);
	}

	public function moduleInfo($id,$info)
	{
		if (isset($this->modules['plugin'][$id]))
			$type = 'plugin';
		elseif (isset($this->modules['theme'][$id]))
			$type = 'theme';
		else
			return null;

		if ($info == 'type')
			return $type;

		return isset($this->modules[$type][$id][$info]) ? 
			$this->modules[$type][$id][$info] : null;
	}

	private function loadModules()
	{
		$themes = new dcThemes($this->core);
		$themes->loadModules($this->core->blog->themes_path,null);
		$this->modules['theme'] = $this->modules['plugin'] = array();

		$m = $themes->getModules();
		foreach($m AS $k => $v) {
			if (!$v['root_writable']) continue;
			$this->modules['theme'][$k] = $v;
		}
		$m = $this->core->plugins->getModules();
		foreach($m AS $k => $v) {
			if (!$v['root_writable']) continue;
			$this->modules['plugin'][$k] = $v;
		}
	}

	public function listModules($type='')
	{
		if (in_array($type,array('plugin','theme')))
			return $this->modules[$type];

		return array_merge($this->modules['theme'],$this->modules['plugin']);
	}

	public function getModule($module='',$type='')
	{
		$o = new ArrayObject();
		# Load nothing?
		if (empty($type) && empty($module)) {
			return false;
		# Unknow type?
		} elseif (!in_array($type,array('plugin','theme'))) {
				throw new Exception(sprintf(
					__('Cannot work with module of type %s'),$type));
			return false;
		# Unknow module?
		} elseif (!isset($this->modules[$type][$module])) {
				throw new Exception(sprintf(
					__('Cannot find module %s of type %s'),$module,$type));
			return false;
		}
		# Module info
		foreach($this->modules[$type][$module] as $a => $b) {
			$o->{$a} = $b;
		}
		$o->root = path::real($o->root);
		# Locales path
		$o->locales = $o->root.'/locales';
		# Module exists
		$o->exists = true;
		# Module type
		$o->type = $type;
		# Module Basename
		$i = path::info($o->root);
		$o->basename = $i['basename'];

		return $o;
	}

	public function getBackupFolder($module,$throw=false)
	{
		$dir = false;
		switch($this->S['backup_folder']) {

			case 'module':
			# plugin
			if (isset($this->modules['plugin'][$module]) && 
			 $this->modules['plugin'][$module]['root_writable']) {
				$dir = path::real($this->modules['plugin'][$module]['root']).'/locales';
			# theme
			} elseif (isset($this->modules['theme'][$module]) && 
			 $this->modules['theme'][$module]['root_writable']) {
				$dir = path::real($this->modules['theme'][$module]['root']).'/locales';
			}
			break;

			case 'plugin':
			$tmp = path::real(array_pop(explode(PATH_SEPARATOR, DC_PLUGINS_ROOT)));
			if ($tmp && is_writable($tmp))
				$dir = $tmp;
			break;

			case 'public':
			$tmp = path::real($this->core->blog->public_path);
			if ($tmp && is_writable($tmp))
				$dir = $tmp;
			break;

			case 'cache':
			$tmp = path::real(DC_TPL_CACHE);
			if ($tmp && is_writable($tmp)) {
				@mkDir($tmp.'/l10n');
				$dir = $tmp.'/l10n';
			}
			break;

			case 'translater':
			$tmp = path::real($this->modules['plugin']['translater']['root']);
			if ($tmp && is_writable($tmp)) {
				@mkDir($tmp.'/locales');
				$dir = $tmp.'/locales';
			}
			break;
		}
		if (!$dir && $throw)
			throw new Exception(sprintf(
				__('Cannot find backups folder for module %s'),$module));

		return $dir;
	}

	public function getLangsFolder($module='',$throw=false)
	{
		$dir = $module == 'dotclear' ?
			DC_ROOT :
			self::getModuleFolder($module,false);

		if (!$dir && $throw) 
			throw new Exception(sprintf(
				__('Cannot find languages folder for module %s'),$module));

		return !$dir ? false : $dir.'/locales';
	}

	public function getModuleFolder($module='',$throw=false)
	{
		$dir = false;
		if ((isset($this->modules['plugin'][$module]['root'])
		 && ($tmp = path::real($this->modules['plugin'][$module]['root']))) ||
		 (isset($this->modules['theme'][$module]['root'])
		 && ($tmp = path::real($this->modules['theme'][$module]['root'])))) {
			$dir = $tmp;
		}
		if (!$dir && $throw)
			throw new Exception(sprintf(
				__('Cannot find root folder for module %s'),$module));
		
		return $dir;
	}

	public function isBackupLimit($module,$throw=false)
	{
		# Find folder of backup
		$backup = self::getBackupFolder($module,true);

		# Count backup file
		$count = 0;
		foreach(self::scandir($backup) AS $file) {
			if (!is_dir($backup.'/'.$file) 
			 && preg_match('/^(l10n-'.$module.'(.*?).bck.zip)$/',$backup))
				$count++;
		}

		# Lmite exceed
		if ($count >= $this->S['backup_limit']) {
			if ($throw)
				throw new Exception(sprintf(
					__('Limit of %s backups for module %s exceed'),
					$this->S['backup_limit'],$module));
			return true;
		} else {
			return false;
		}
	}

	public function listBackups($module,$return_filename=false)
	{
		# Not a module installed
		self::getLangsFolder($module,true);

		# No backup folder for this module
		$backup = self::getBackupFolder($module,false);
		if (!$backup) return array();

		# Scan files for backups
		$res = $sort = array();
		$files = self::scandir($backup);
		foreach($files AS $file) {

			# Not a bakcup file
			$is_backup = preg_match(
				'/^(l10n-('.$module.')-(.*?)-([0-9]*?).bck.zip)$/',$file,$m);

			if (is_dir($backup.'/'.$file) 
			 || !$is_backup 
			 || !self::isIsoCode($m[3])) continue;

			# Backup infos
			if ($return_filename) {
				$res[] = $file;
			} else {
				$res[$m[3]][$file] = path::info($backup.'/'.$file);
				$res[$m[3]][$file]['time']= filemtime($backup.'/'.$file);
				$res[$m[3]][$file]['size'] = filesize($backup.'/'.$file);
				$res[$m[3]][$file]['module'] = $module;
			}
		}
		return $res;
	}

	public function createBackup($module,$lang)
	{
		# Not a module installed
		$locales = self::getLangsFolder($module,true);

		# No backup folder for this module
		$backup = self::getBackupFolder($module,true);

		# Not an existing lang
		if (!is_dir($locales.'/'.$lang))
			throw new Exception(sprintf(
				__('Cannot find language folder %s for module %s'),$lang,$module));

		# Scan files for a lang
		$res = array();
		$files = self::scandir($locales.'/'.$lang);
		foreach($files as $file) {

			# Only lang file
			if (!is_dir($locales.'/'.$lang.'/'.$file) 
			&& (self::isLangphpFile($file) 
			 || self::isPoFile($file))) {

				$res[$locales.'/'.$lang.'/'.$file] = 
					$module.'/locales/'.$lang.'/'.$file;
			}
		}

		# Do Zip 
		if (!empty($res)) {
			self::isBackupLimit($module,true);

			@set_time_limit(300);
			$fp = fopen($backup.'/l10n-'.$module.'-'.$lang.'-'.time().'.bck.zip','wb');
			$zip = new fileZip($fp);
			foreach($res AS $src => $dest) {
				$zip->addFile($src,$dest);
			}
			$zip->write();
			$zip->close();
			unset($zip);
		}
	}

	public function deleteBackup($module,$file)
	{
		# Not a module installed
		self::getLangsFolder($module,true);

		# No backup folder for this module
		$backup = self::getBackupFolder($module,true);

		# Not a bakcup file
		$is_backup = preg_match(
			'/^(l10n-('.$module.')-(.*?)-([0-9]*?).bck.zip)$/',$file,$m);

		if (is_dir($backup.'/'.$file) 
		 || !$is_backup 
		 || !self::isIsoCode($m[3])) continue;

		if (!files::isDeletable($backup.'/'.$file))
			throw new Exception(sprintf(
				__('Cannot delete backup file %s'),$file));

		unlink($backup.'/'.$file);
	}

	public function restoreBackup($module,$file)
	{
		# Not a module installed
		$locales = self::getModuleFolder($module,true);

		# No backup folder for this module
		$backup = self::getBackupFolder($module,true);

		if (!file_exists($backup.'/'.$file))
			throw new Exception(sprintf(
				__('Cannot find backup file %s'),$file));

		$zip = new fileUnzip($backup.'/'.$file);
		$zip_files = $zip->getFilesList();

		foreach($zip_files AS $zip_file) {
			$f = self::explodeZipFilename($zip_file,true);
			if ($module != $f['module']) continue;

			$zip->unzip($zip_file,
				$locales.'/locales/'.$f['lang'].'/'.$f['group'].$f['ext']);
			$done = true;
		}
		$zip->close();
		unset($zip);
	}

	public function exportPack($modules,$langs)
	{
		# Not a query good formed
		if (!is_array($modules) || 1 > count($modules)
		 || !is_array($langs) || 1 > count($langs))
			throw new Exception(
				__('Wrong export query'));

		# Filter default filename
		$filename = files::tidyFileName($this->S['export_filename']);

		# Not a filename good formed
		if (empty($filename))
			throw new Exception(sprintf(
				__('Cannot use export mask %s'),$this->S['export_filename']));

		# Modules folders
		$res = array();
		$count = array();
		foreach($modules AS $module) {

			# Not a module installed
			$locales = self::getLangsFolder($module,false);
			if (!$locales) continue;

			# Langs folders
			foreach($langs AS $lang) {
				# Not a lang folder
				if (!is_dir($locales.'/'.$lang)) continue;

				# Scan files for a lang
				$files = self::scandir($locales.'/'.$lang);
				foreach($files as $file) {

					# Not a lang file
					if (is_dir($locales.'/'.$lang.'/'.$file) 
					 || !self::isLangphpFile($file) 
					 && !self::isPoFile($file)) continue;

					# Add file to zip in format "module/locales/lang/filename"
					$res[$locales.'/'.$lang.'/'.$file] = 
						$module.'/locales/'.$lang.'/'.$file;

					$count[$module] = 1;
				}
			}
		}

		# Nothing to do
		if (empty($res))
			throw new Exception('Nothing to export');

		# Prepare files to zip
		@set_time_limit(300);
		$fp = fopen('php://output','wb');
		$zip = new fileZip($fp);
		foreach($res as $from => $to) {
			$zip->addFile($from,$to);
		}

		# Set filename
		$file_infos = 1 < count($count) ? 
			array(time(),'modules','multi',self::$dcTranslaterVersion) : 
			array(
				time(),
				$modules[0],
				self::moduleInfo($modules[0],'type'),
				self::moduleInfo($modules[0],'version')
			);
		$filename = 
			files::tidyFileName(
				dt::str(
					str_replace(
						array('timestamp','module','type','version'),
						$file_infos,
						$this->S['export_filename']
					)
				)
			);

		# Send Zip
		header('Content-Disposition: attachment;filename='.$filename.'.zip');
		header('Content-Type: application/x-zip');
		$zip->write();
		unset($zip);
		exit;
	}

	public function importPack($modules,$zip_file)
	{
		# Not a file uploaded
		files::uploadStatus($zip_file);

		# No modules to update
		if (!is_array($modules) || 1 > count($modules))
			throw new Exception(
				__('Wrong import query'));

		$done = false;
		$res = array();

		# Load Unzip object
		$zip = new fileUnzip($zip_file['tmp_name']);
		$files = $zip->getFilesList();

		# Scan zip
		foreach($files AS $file) {

			$f = self::explodeZipFilename($file,false);

			# Not a requested module
			if (!is_array($f) 
			 || !in_array($f['module'],$modules)) continue;

			# Get locales folder (even if "locales" is not set)
			if (!$dir = self::getModuleFolder($f['module'],false)) continue;
			$locales = $dir.'/locales';

			# Not allow overwrite
			if (!$this->S['import_overwrite'] 
			 && file_exists($locales.'/'.$f['lang'].'/'.$f['group'].$f['ext'])) continue;

			$res[] = array(
				'from' => $file, 
				'root' => $locales.'/'.$f['lang'], 
				'to' => $locales.'/'.$f['lang'].'/'.$f['group'].$f['ext']
			);
		}
		# Unzip files
		foreach ($res AS $rs) {
			if (!is_dir($rs['root']))
				files::makeDir($rs['root'],true);

			$zip->unzip($rs['from'],$rs['to']);
			$done = true;
		}
		$zip->close();
		unlink($zip_file['tmp_name']);

		# No file unzip
		if (!$done)
			throw new Exception(sprintf(
				__('Nothing to import for these modules in pack %s'),
				$zip_file['name']));
	}

	public function explodeZipFilename($file='',$throw=false)
	{
		# module/locales/lang/group.ext
		$is_file = preg_match(
			'/^(.*?)\/locales\/(.*?)\/(.*?)(.po|.lang.php)$/',$file,$f);

		# Explode file to infos
		if ($is_file) {
			$module = null !== self::moduleInfo($f[1],'name') ? 
				$f[1] : false;
			$lang = self::isIsoCode($f[2]) ? 
				$f[2] : false;
			$group = in_array($f[3],self::$allowed_l10n_groups) ? 
				$f[3] : false;
			$ext = self::isLangphpFile($f[4]) || self::isPoFile($f[4]) ? 
				$f[4] : false;
		}
		# Not good formed
		if (!$is_file || !$module || !$lang || !$group || !$ext) {
			if ($throw)
				throw new Exception(sprintf(
					__('Zip file %s is not in translater format'),$file));
			return false;
		}
		return array(
			'module' => $module,
			'lang' => $lang,
			'group' => $group,
			'ext' => $ext
		);
	}

	public function listLangs($module,$return_path=false)
	{
		$res = array();

		# Not a module installed
		$locales = self::getLangsFolder($module,true);

		# Add prefix "locales" as scandir remove it
		$prefix = preg_match('/(locales(.*))$/',$locales) ? 'locales' : '';

		# Retrieve langs folders
		$files = self::scandir($locales);
		foreach($files as $file) {
			if (!preg_match(
				'/(.*?(locales\/)([^\/]*?)\/([^\/]*?)(.lang.php|.po))$/',
				$prefix.$file,$m)) continue;

			if (!self::isIsoCode($m[3])) continue;

			if ($return_path) {
				$res[$m[3]][] = $file; # Path
			} else {
				$res[$m[3]] = self::$iso[$m[3]]; # Lang name
			}
		}
		return $res;
	}

	public function addLang($module,$lang,$from_lang='')
	{
		# Not a module installed
		$locales = self::getLangsFolder($module,true);

		# Path is right formed
		self::isIsoCode($lang,true);

		# Retrieve langs folders
		$langs = self::listLangs($module);

		# Lang folder is not present
		if (isset($langs[$lang]))
			throw new Exception(sprintf(
				__('Language %s already exists for module %s'),$lang,$module));

		# Create new lang directory
		files::makeDir($locales.'/'.$lang,true);

		# Verify folder of other lang
		if (!empty($from_lang) 
		 && !isset($langs[$from_lang]))
			throw new Exception(sprintf(
				__('Cannot copy file from language %s for module %s'),
				$from_lang,$module));

		# Copy files from other lang
		if (!empty($from_lang) 
		 && isset($langs[$from_lang])) {
			$files = self::scandir($locales.'/'.$from_lang);
			foreach($files as $file) {
				if (is_dir($locales.'/'.$from_lang.'/'.$file) 
				 || !self::isLangphpFile($file) 
				 && !self::isPoFile($file)) continue;

				file_put_contents($locales.'/'.$lang.'/'.$file,
					file_get_contents($locales.'/'.$from_lang.'/'.$file));
			}
		} else {
			# Create basic empty lang file as translater need these files to be present
			self::setLangphpFile($module,$lang,'main',array());
			self::setPoFile($module,$lang,'main',array());
		}
	}

	public function updLang($module,$lang,$msgs)
	{
		# Not a module installed
		$locales = self::getLangsFolder($module,true);

		# Path is right formed
		self::isIsoCode($lang,true);

		# Retrieve langs folders
		$langs = self::listLangs($module);

		# Lang folder is not present
		if (!isset($langs[$lang]))
			throw new Exception(sprintf(
				__('Cannot find language folder %s for module %s'),$lang,$module));

		# Sort msgids by groups
		$rs = array();
		foreach($msgs as $msg) {
			$msg['group'] = isset($msg['group']) ? $msg['group'] : '';
			$msg['msgid'] = isset($msg['msgid']) ? $msg['msgid'] : '';
			$msg['msgstr'] = isset($msg['msgstr']) ? trim($msg['msgstr']) : '';
/*
			if (get_magic_quotes_gpc()) {
				$msg['msgid'] = stripcslashes($msg['msgid']);
				$msg['msgstr'] = stripcslashes($msg['msgstr']);
			}
*/			if ($msg['msgstr'] == '') continue;

			$rs[$msg['group']][$msg['msgid']] = $msg['msgstr'];
		}

		# Backup files if auto-backup is on
		if ($this->S['backup_auto'])
			self::createBackup($module,$lang);

		# Delete empty files (files with no group)
		foreach(self::$allowed_l10n_groups AS $group) {
			if (isset($rs[$group])) continue;

			$po_file = $locales.'/'.$lang.'/'.$group.'.po';
			$langphp_file = $locales.'/'.$lang.'/'.$group.'.lang.php';

			if (file_exists($po_file))
				unlink($po_file);

			if (file_exists($langphp_file))
				unlink($langphp_file);
		}

		# No msgstr to write
		if (empty($rs))
			throw new Exception(sprintf(
				__('No string to write, language %s deleted for module %s'),
				$lang,$module));

		# Write .po and .lang.php files
		foreach($rs AS $group => $msgs) {
			self::setLangphpFile($module,$lang,$group,$msgs);
			self::setPoFile($module,$lang,$group,$msgs);
		}
	}

	public function delLang($module,$lang,$del_empty_dir=true)
	{
		# Not a module installed
		$locales = self::getLangsFolder($module,true);

		# Path is right formed
		self::isIsoCode($lang,true);

		# Retrieve langs folders
		$files = self::listLangs($module,true);

		# Lang folder is not present
		if (!isset($files[$lang]))
			throw new Exception(sprintf(
				__('Cannot find language folder %s for module %s'),$lang,$module));

		# Delete .po and .lang.php files
		foreach($files[$lang] as $file) {
			unlink($locales.'/'.$file);
		}

		# Delete lang folder if empty
		$dir = self::scandir($locales.'/'.$lang);
		if (empty($dir))
			rmdir($locales.'/'.$lang);

		# Delete locales folder if empty
		$loc = self::scandir($locales);
		if (empty($loc))
			rmdir($locales);
	}
	public static function encodeMsg($str)
	{
		return text::toUTF8($str);
	}

	/* Scan a module folder to find all l10n strings in .php files */
	public function getMsgIds($module)
	{
		$res = array();

		# Not a module installed
		$dir = self::getModuleFolder($module,true);

		$files = self::scandir($dir);

		foreach($files AS $file) {
			if (is_dir($dir.'/'.$file) 
			 || files::getExtension($file) != 'php') continue;

			$contents = file($dir.'/'.$file);
			foreach($contents AS $line => $content) {
				if (!preg_match_all(
					"|__\((['\"]{1})(.*)([\"']{1})\)|U",$content,$matches)) continue;

				foreach($matches[2] as $id) {
					$res[] = array(
						'msgid' => self::encodeMsg($id),
						'file' => $file,
						'line' => $line + 1
					);
				}
			}
			unset($contents);
		}
		return $res;
	}
	/* Scan a lang folder to find l10n translations in files */
	public function getMsgStrs($module,$requested_lang='')
	{
		$res = array();

		# Not a module installed
		$locales = self::getLangsFolder($module,true);

		$langs = self::listLangs($module,true);

		# Langs folders
		foreach($langs as $lang => $files) {
			if ('' != $requested_lang && $lang != $requested_lang) continue;

			foreach($files as $file) {

				# .lang.php files
				if (self::isLangphpFile($file)) {
					$php = self::getLangphpFile($locales.'/'.$file);
					foreach($php AS $id => $str) {
						$is_php[$lang][$id] = 1;
						$res[] = array(
							'msgid' => self::encodeMsg($id),
							'msgstr' => self::encodeMsg($str), 
							'lang' => $lang,
							'type' => 'php',
							'path' => $locales.'/'.$file,
							'file' => basename($file),
							'group'=> str_replace('.lang.php','',basename($file))
						);
					}
				# .po files
				} elseif (self::isPoFile($file)) {
					$po = self::getPoFile($locales.'/'.$file);
					if (!is_array($po)) continue;

					foreach($po as $id => $str) {

						# Don't overwrite .lang.php
						if (isset($is_php[$lang][$id])) continue;

						$res[] = array(
							'msgid' => self::encodeMsg($id),
							'msgstr' => self::encodeMsg($str),
							'lang' => $lang,
							'type' => 'po',
							'path' => $locales.'/'.$file,
							'file' => basename($file),
							'group'=> str_replace('.po','',basename($file))
						);
					}
				}
			}
		}
		return $res;
	}

	public function getMsgs($module,$requested_lang='')
	{
		# Get messages ids of a module
		$m_msgids = self::getMsgIds($module);

		# Get messages translations for a module
		$m_msgstrs = self::getMsgStrs($module,$requested_lang);

		# Get messages translations for others modules
		foreach(self::listModules() AS $o_module => $o_infos) {
			if ($o_module == $module) continue;
			$m_o_msgstrs[$o_module] = self::getMsgStrs($o_module,$requested_lang);
		}
		$m_o_msgstrs['dotclear'] = self::getMsgStrs('dotclear',$requested_lang);

		# Only one lang or all
		$langs = '' == $requested_lang ? 
			self::listLangs($module) :
			array($requested_lang => self::isIsoCode($requested_lang));

		# Let's go reorder the mixture
		$res = array();
		foreach($langs AS $lang => $iso) {

			$res[$lang] = array();

			# From id list
			foreach($m_msgids AS $rs) {
				$res[$lang][$rs['msgid']]['files'][] = array(trim($rs['file'],'/'),$rs['line']);
				$res[$lang][$rs['msgid']]['group'] = 'main';
				$res[$lang][$rs['msgid']]['msgstr'] = '';
				$res[$lang][$rs['msgid']]['in_dc'] = false;
				$res[$lang][$rs['msgid']]['o_msgstrs'] = array();
			}

			# From str list
			foreach($m_msgstrs AS $rs) {
				if ($rs['lang'] != $lang) continue;

				if (!isset($res[$lang][$rs['msgid']])) {
					$res[$lang][$rs['msgid']]['files'][] = array();
					$res[$lang][$rs['msgid']]['in_dc'] = false;
					$res[$lang][$rs['msgid']]['o_msgstrs'] = array();
				}
				$res[$lang][$rs['msgid']]['group'] = $rs['group'];
				$res[$lang][$rs['msgid']]['msgstr'] = $rs['msgstr'];
				$res[$lang][$rs['msgid']]['in_dc'] = false;
			}

			# From others str list
			foreach($m_o_msgstrs AS $o_module => $o_msgstrs) {
				foreach($o_msgstrs AS $rs) {
					if ($rs['lang'] != $lang) continue;

					if (!isset($res[$lang][$rs['msgid']])) continue;

					$res[$lang][$rs['msgid']]['o_msgstrs'][] = array(
						'msgstr' => $rs['msgstr'],
						'module' => $o_module,
						'file' => $rs['file']
					);
					if ($o_module == 'dotclear')
						$res[$lang][$rs['msgid']]['in_dc'] = true;
				}
			}
		}
		return '' == $requested_lang ? $res : $res[$requested_lang];
	}
	
	/* Write a lang file */
	private function writeLangFile($dir,$content,$throw)
	{
		$path = path::info($dir);
		if (is_dir($path['dirname']) && !is_writable($path['dirname']) 
		 || file_exists($dir) && !is_writable($dir))
			throw new Exception(sprintf(
				__('Cannot grant write acces on lang file %s'),$dir));

		$f = @file_put_contents($dir,$content);
		
		if (!$f && $throw)
			throw new Exception(sprintf(
				__('Cannot write lang file %s'),$dir));

		return $f;
	}
	/* Try if a file is a .lang.php file */
	public static function isLangphpFile($file)
	{
		return files::getExtension($file) == 'php' && stristr($file,'.lang.php');
	}
	/* Get and parse a .lang.php file */
	public static function getLangphpFile($file)
	{
		if (!file_exists($file)) return array();

		$res = array();
		$content = implode('',file($file));
		$count = preg_match_all(
			'/(\$GLOBALS\[\'__l10n\'\]\[\'(.*?)\'\]\s*\x3D\s*\'(.*?)\';)/',$content, $m);

		if (!$count) return array();

		for ($i=0; $i<$count; $i++) {
			$id = $m[2][$i];
			$str = self::langphpString($m[3][$i]);

			if ($str)
				$res[self::langphpString($id)] = $str;
		}
		if (!empty($res['']))
			$res = array_diff_key($res,array(''=>1));

		return $res;
	}
	/* Construct and write a .lang.php file */
	private function setLangphpFile($module,$lang,$group,$fields)
	{
		if (!$this->S['write_langphp']) return;

		# Not a module installed
		$locales = self::getLangsFolder($module,true);

		# Path is right formed
		$lang_name = self::isIsoCode($lang,true);

		$l = "<?php\n";
		if ($this->S['parse_comment']) {
			$l .= 
				'// Language: '.$lang_name." \n".
				'// Module: '.$module." - ".self::moduleInfo($module,'version')."\n".
				'// Date: '.dt::str('%Y-%m-%d %H:%M:%S')." \n";

			if ($this->S['parse_user'] && !empty($this->S['parse_userinfo'])) {
				$search = self::$allowed_user_informations;
				foreach($search AS $n) {
					$replace[] = $this->core->auth->getInfo('user_'.$n);
				}				
				$info = trim(str_replace($search,$replace,$this->S['parse_userinfo']));
				if (!empty($info))
					$l .= '// Author: '.html::escapeHTML($info)."\n";
			}
			$l .= 
				'// Translated with dcTranslater - '.self::$dcTranslaterVersion." \n\n";
		}
		if ($this->S['parse_comment']) {
			$infos = self::getMsgids($module);
			foreach($infos AS $info) {
				if (isset($fields[$info['msgid']])) {

					$comments[$info['msgid']] = (!isset($comments[$info['msgid']]) ?
						$comments[$info['msgid']] : '').
						'#'.trim($info['file'],'/').':'.$info['line']."\n";
				}
			}
		}

		foreach($fields as $id => $str) {

			if ($this->S['parse_comment'] && isset($comments[$id]))
				$l .= $comments[$id];

			$l .= 
				'$GLOBALS[\'__l10n\'][\''.addcslashes($id,"'").'\'] = '.
				'\''.self::langphpString($str,true)."';\n";
			if ($this->S['parse_comment'])
				$l .= "\n";
		}
		$l .= "?>";

		self::writeLangFile($locales.'/'.$lang.'/'.$group.'.lang.php',$l,true);
	}
	/* Parse a .lang.php string */
	private static function langphpString($string,$reverse=false)
	{
		if ($reverse) {
			$smap = array('\'', "\n", "\t", "\r");
			$rmap = array('\\\'', '\\n"' . "\n" . '"', '\\t', '\\r');
			return trim((string) str_replace($smap, $rmap, $string));
		} else {
			$smap = array('/\\\\n/', '/\\\\r/', '/\\\\t/', "/\\\'/");
			$rmap = array("\n", "\r", "\t", "'");
			return trim((string) preg_replace($smap, $rmap, $string));
		}
	}
	/* Try if a file is a .po file */
	public static function isPoFile($file)
	{
		return files::getExtension($file) == 'po';
	}
	/* Get and parse a .po file */
	public static function getPoFile($file)
	{
		if (!file_exists($file)) return array();

		$res = array();
		$content = implode('',file($file));
		$count = preg_match_all('/(msgid\s+("([^"]|\\\\")*?"\s*)+)\s+'.
			'(msgstr\s+("([^"]|\\\\")*?(?<!\\\)"\s*)+)/',$content,$m);

		if (!$count) return array();
		
		for ($i=0; $i<$count; $i++)
		{
			$id = preg_replace('/\s*msgid\s*"(.*)"\s*/s','\\1',$m[1][$i]);
			$str= preg_replace('/\s*msgstr\s*"(.*)"\s*/s','\\1',$m[4][$i]);
			$str = self::poString($str);

			if ($str)
				$res[self::poString($id)] = $str;
		}

		if (!empty($res['']))
			$res = array_diff_key($res,array(''=>1));		

		return $res;
	}
	/* Construct and parse a .po file */
	private function setPoFile($module,$lang,$group,$fields)
	{
		if (!$this->S['write_po']) return;

		# Not a module installed
		$locales = self::getLangsFolder($module,true);

		# Path is right formed
		self::isIsoCode($lang,true);

		$l = '';
		if ($this->S['parse_comment']) {
			$l .= 
				'# Language: '.self::$iso[$lang]."\n".
				'# Module: '.$module." - ".self::moduleInfo($module,'version')."\n".
				'# Date: '.dt::str('%Y-%m-%d %H:%M:%S')."\n";

			if ($this->S['parse_user'] && !empty($this->S['parse_userinfo'])) {
				$search = self::$allowed_user_informations;
				foreach($search AS $n) {
					$replace[] = $this->core->auth->getInfo('user_'.$n);
				}				
				$info = trim(str_replace($search,$replace,$this->S['parse_userinfo']));
				if (!empty($info))
					$l .= '# Author: '.html::escapeHTML($info)."\n";
			}
			$l .= 
				'# Translated with dcTranslater - '.self::$dcTranslaterVersion."\n";
		}
		$l .= 
		"\nmsgid \"\"\n".
		'msgstr "Content-Type: text/plain; charset=UTF-8\n"'."\n\n";

		if ($this->S['parse_comment']) {
			$infos = self::getMsgids($module);
			foreach($infos AS $info) {
				if (isset($fields[$info['msgid']])) {

					$comments[$info['msgid']] = (!isset($comments[$info['msgid']]) ?
						$comments[$info['msgid']] : '').
						'#: '.trim($info['file'],'/').':'.$info['line']."\n";
				}
			}
		}

		foreach($fields as $id => $str) {

			if ($this->S['parse_comment'] && isset($comments[$id]))
				$l .= $comments[$id];

			$l .= 
			'msgid "'.$id .'"'."\n".
			'msgstr "'.self::poString($str,true).'"'."\n\n";
		}

		self::writeLangFile($locales.'/'.$lang.'/'.$group.'.po',$l,true);
	}
	/* Parse .po string */
	private static function poString($string,$reverse=false)
	{
		if ($reverse) {
			$smap = array('"', "\n", "\t", "\r");
			$rmap = array('\\"', '\\n"' . "\n" . '"', '\\t', '\\r');
			return trim((string) str_replace($smap, $rmap, $string));
		} else {
			$smap = array('/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\"/');
			$rmap = array('', "\n", "\r", "\t", '"');
			return trim((string) preg_replace($smap, $rmap, $string));
		}
	}
	/* Scan recursively a folder and return files and folders names */
	public static function scandir($path,$dir='',$res=array())
	{
		$path = path::real($path);
		if (!is_dir($path) || !is_readable($path)) return array();

		$files = files::scandir($path);

		foreach($files AS $file) {
			if ($file == '.' || $file == '..') continue;

			if (is_dir($path.'/'.$file)) {
				$res[] = $file;
				$res = self::scanDir($path.'/'.$file,$dir.'/'.$file,$res);
			} else {
				$res[] = empty($dir) ? $file : $dir.'/'.$file;
			}
		}
		return $res;
	}
	/* Return array of langs like in clearbreaks l10n */
	public static function getIsoCodes($flip=false,$name_with_code=false)
	{
		if (empty(self::$iso))
			self::$iso = l10n::getISOcodes($flip,$name_with_code);

		return self::$iso;
	}
	/* Find if lang code exists or lang name */
	public static function isIsoCode($iso,$throw=false)
	{
		$codes = self::getIsoCodes();
		$code = isset($codes[$iso]) ? $codes[$iso] : false;
		if (!$code && $throw)
			throw new Exception(sprintf(
				__('Cannot find language for code %s'),$iso));

		return $code;
	}
}
?>