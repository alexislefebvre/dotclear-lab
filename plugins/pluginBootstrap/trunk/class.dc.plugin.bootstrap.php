<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of pluginBootstrap,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Vincent Garnier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class pluginBootstrap
{
	public $id;
	public $name;
	public $description;
	public $author;
	public $version;
	public $plugins_root;

	public $has_admin;
	public $has_public;

	public $templates_dir;
	public $templates = array(
		'admin'				=> '_admin.tpl',
		'changelog'			=> 'changelog.tpl',
		'class' 			=> 'class.plugin.tpl',
		'default_template' 	=> 'default_template.tpl',
		'define' 			=> '_define.tpl',
		'index'				=> 'index.tpl',
		'licence_block' 	=> 'licence_block.tpl',
		'locales_fr'		=> 'locales_fr.tpl',
		'prepend'			=> '_prepend.tpl',
		'public'			=> '_public.tpl'
	);

	private $dir;

	private $class_name;
	private $class_file;

	private $common_replacements = array();

	private $licence_block;
	static private $licencesList = array(
//		'asf20' 	=> 'Apache License 2.0',
//		'art' 		=> 'Artistic License/GPL',
//	 	'epl' 		=> 'Eclipse Public License 1.0',
		'gpl2' 		=> 'GNU General Public License v2',
		'gpl3' 		=> 'GNU General Public License v3',
		'lgpl' 		=> 'GNU Lesser General Public License v3',
	 	'mit' 		=> 'MIT License',
//	 	'mpl11' 	=> 'Mozilla Public License 1.1',
//	 	'bsd' 		=> 'New BSD License',
	 	'other' 	=> 'Other...'
	);

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param string $description
	 * @param string $author
	 * @param string $version
	 * @param string $licence
	 * @param boolean $has_admin
	 * @param boolean $has_public
	 */
	public function __construct($name,$description='',$author='',$version='0.1',$licence='other',$has_admin=false,$has_public=false)
	{
		$this->name = $name;
		$this->description = $description;
		$this->author = $author;
		$this->version = $version;

		if (array_key_exists($licence,self::$licencesList)) {
			$this->licence = $licence;
		}
		else {
			$this->licence = 'other';
		}

		$this->has_admin = (boolean)$has_admin;
		$this->has_public = (boolean)$has_public;
	}


	/* Building methods
	----------------------------------------------------------*/

	/**
	 * Bootstrap the plugin
	 *
	 */
	public function build()
	{
		$this->initBuilding();

		$this->makeDirs();

		$this->makeLicenceFile();

		$this->makeChangelogFile();

		$this->makeDefineFile();

		$this->makeClassFile();

		if ($this->has_admin && $this->has_public) {
			$this->makePrependFile();
		}

		if ($this->has_admin)
		{
			$this->makeAdminFile();
			$this->makeIndexFile();
			$this->makeLocales();
		}

		if ($this->has_public)
		{
			$this->makePublicFile();
			$this->makeDefaultTemplateFile();
		}
	}

	private function initBuilding()
	{
		$this->id = bsText::strToCamelCase($this->name);

		$this->dir = $this->plugins_root.'/'.$this->id;

		$this->class_name = 'dc'.ucfirst($this->id);
		$this->class_file = 'class.dc.'.strtolower($this->id).'.php';

		$this->makeLicenceBlock();

		$this->common_replacements = array(
			'##licence_block##'			=> $this->licence_block,
			'##plugin_id##' 			=> $this->id,
			'##plugin_name##' 			=> $this->name,
			'##plugin_description##' 	=> $this->description,
			'##plugin_author##' 		=> $this->author,
			'##plugin_version##' 		=> $this->version,
			'##class_name##'			=> $this->class_name,
			'##class_file##'			=> $this->class_file
		);
	}

	/**
	 * Build the Licence Block of the plugin and store it
	 *
	 */
	private function makeLicenceBlock()
	{
		$replacements = $this->getReplacements(array(
			'##licence##' => $this->getLicenceBlock()
		));

		$this->licence_block = $this->replace($this->getTpl('licence_block'),$replacements);
	}

	/**
	 * Make basis directories
	 *
	 */
	private function makeDirs()
	{
		if (file_exists($this->dir)) {
			throw new Exception(sprintf(__('It seems that a %s plugin allready exists'),$this->id));
		}

		files::makeDir($this->dir);
		files::makeDir($this->dir.'/locales/fr',true);
	}

	/**
	 * Make the CHANGELOG file
	 *
	 */
	private function makeChangelogFile()
	{
		$replacements = $this->getReplacements(array(
			'##date##' => date('Y-m-d')
		));

		$this->makeFile('changelog', $this->dir.'/CHANGELOG', $replacements);
	}

	/**
	 * Make the _define.php file
	 *
	 */
	private function makeDefineFile()
	{
		$replacements = $this->getReplacements(array(
			'##plugin_name##' => str_replace('"','\"',$this->name),
			'##plugin_description##' => str_replace('"','\"',$this->description),
			'##plugin_author##' => str_replace('"','\"',$this->author),
		));

		$this->makeFile('define', $this->dir.'/_define.php', $replacements);
	}

	/**
	 * Make the class file
	 *
	 */
	private function makeClassFile()
	{
		$replacements = $this->getReplacements();

		$this->makeFile('class', $this->dir.'/'.$this->class_file, $replacements);
	}

	/**
	 * Make the _prepend.php file
	 *
	 */
	private function makePrependFile()
	{
		$replacements = $this->getReplacements();

		if ($this->has_public) {
			$replacements['##url_register##'] = '$GLOBALS[\'core\']->url->register(\''.$this->id.'\',\''.$this->id.'\',\'^'.$this->id.'$\',array(\''.$this->class_name.'URL\',\''.$this->id.'\'));';
		}
		else {
			$replacements['##url_register##'] = '';
		}

		$this->makeFile('prepend', $this->dir.'/_prepend.php', $replacements);
	}

	/**
	 * Make the _admin.php file
	 *
	 */
	private function makeAdminFile()
	{
		$replacements = $this->getReplacements();

		if (!$this->has_public) {
			$replacements['##autoload##'] = '$GLOBALS[\'__autoload\'][\''.$this->class_name.'\'] = dirname(__FILE__).\'/'.$this->class_file.'\';';
		}
		else {
			$replacements['##autoload##'] = '';
		}

		$this->makeFile('admin', $this->dir.'/_admin.php', $replacements);
	}

	/**
	 * Make the index.php file
	 *
	 */
	private function makeIndexFile()
	{
		$replacements = $this->getReplacements();

		$this->makeFile('index', $this->dir.'/index.php', $replacements);
	}

	/**
	 * Make /locales/fr/main.po file
	 *
	 */
	private function makeLocales()
	{
		$replacements = $this->getReplacements();

		$this->makeFile('locales_fr', $this->dir.'/locales/fr/main.po', $replacements);
	}

	/**
	 * Make the _public.php file
	 *
	 */
	private function makePublicFile()
	{
		$replacements = $this->getReplacements();

		if (!$this->has_admin) {
			$replacements['##autoload##'] = '$GLOBALS[\'__autoload\'][\''.$this->class_name.'\'] = dirname(__FILE__).\'/'.$this->class_file.'\';';
		}
		else {
			$replacements['##autoload##'] = '';
		}

		$this->makeFile('public', $this->dir.'/_public.php', $replacements);
	}

	/**
	 * Make the default template file
	 *
	 */
	private function makeDefaultTemplateFile()
	{
		files::makeDir($this->dir.'/default-templates/');

		$replacements = $this->getReplacements();

		$this->makeFile('default_template', $this->dir.'/default-templates/'.$this->id.'.html', $replacements);
	}


	/* Licences methods
	----------------------------------------------------------*/

	static public function getLicencesList($reverse=false)
	{
		return ($reverse ? array_flip(self::$licencesList) : self::$licencesList);
	}

	public function getLicenceBlock()
	{
		if ($this->licence == 'other') {
			return '#';
		}

		$block_replacements = array(
	//		'asf20' 	=> array(),
	//		'art' 		=> array(),
	//	 	'epl' 		=> array(),
			'gpl2' 		=> array(),
			'gpl3' 		=> array(),
			'lgpl' 		=> array(),
		 	'mit' 		=> array(),
	//	 	'mpl11' 	=> array(),
	//	 	'bsd' 		=> array()
		);

		$this->templates[$this->licence.'_block'] = 'licences/'.$this->licence.'/block.tpl';
		return $this->tplReplace($this->licence.'_block', $block_replacements[$this->licence]);
	}

	public function makeLicenceFile()
	{
		if ($this->licence == 'other') {
			return null;
		}

		$licence_replacements = array(
	//		'asf20' 	=> array(),
	//		'art' 		=> array(),
	//	 	'epl' 		=> array(),
			'gpl2' 		=> array(),
			'gpl3' 		=> array(),
			'lgpl' 		=> array(),
		 	'mit' 		=> array(
				'#year#' 	=> date('Y'),
				'#author#' 	=> $this->author
			),
	//	 	'mpl11' 	=> array(),
	//	 	'bsd' 		=> array(),
		 	'other' 	=> array()
		);

		$this->templates[$this->licence] = 'licences/'.$this->licence.'/licence.tpl';
		$this->makeFile($this->licence, $this->dir.'/LICENCE', $licence_replacements[$this->licence]);
	}


	/* Templates and replacement methods
	----------------------------------------------------------*/

	/**
	 * Return content of a template file
	 *
	 * @param string $tpl	Template ID
	 * @return string
	 */
	protected function getTpl($tpl)
	{
		if (!file_exists($this->templates_dir.'/'.$this->templates[$tpl])) {
			throw new Exception(sprintf(__('%s template file doesn\'t exists in %s directory'),$this->templates[$tpl],$this->templates_dir));
		}

		return file_get_contents($this->templates_dir.'/'.$this->templates[$tpl]);
	}

	/**
	 * Make replacements
	 *
	 * @param string $str
	 * @param array $replacements
	 * @return string
	 */
	protected function replace($str, array $replacements)
	{
		return str_replace(array_keys($replacements),array_values($replacements),$str);
	}

	/**
	 * Make replacement in a template faile
	 *
	 * @param string $template_name	Template ID
	 * @param array $replacements
	 * @return string
	 */
	protected function tplReplace($template_name, array $replacements=array())
	{
		return $this->replace($this->getTpl($template_name),$replacements);
	}

	/**
	 * Make a file base on a template
	 *
	 * @param string $template_name
	 * @param string $destination
	 * @param array $replacements
	 * @return integer
	 */
	protected function makeFile($template_name,$destination,array $replacements=array())
	{
		return file_put_contents($destination,$this->tplReplace($template_name,$replacements));
	}

	/**
	 * Merge common replacement with other and return it
	 *
	 * @param array $other_replacements
	 * @return array
	 */
	protected function getReplacements(array $other_replacements=array())
	{
		return array_merge($this->common_replacements,$other_replacements);
	}

}
