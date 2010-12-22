<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcDevKit, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom, Dsls and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class moduleBootstrap
{
	const classServices		= '%sRestMethods';
	const classWidgets		= '%sWidgets';
	const classBehaviors	= '%sBehaviors';
	const classHandler		= '%sUrl';
	const classTemplate		= '%sTpl';
	const classRsExtend		= '%sRsExtension';
	
	protected $core;
	protected $root;
	protected $templates;
	protected $descriptor;
	
	public function __construct($core)
	{
		$this->core = $core;
		$this->root = dirname(__FILE__).'/../templates';
		$this->templates = array();
		$this->replacements = array();
		$this->descriptor = new descriptor();
		
		# Templates
		$this->addTemplate('define',array('_define.php'));
		$this->addTemplate('autoload',array('_prepend.php'));
		$this->addTemplate('public_restriction',array('*'));
		$this->addTemplate('admin_restriction',array('*'));
		$this->addTemplate('behavior',array('_prepend.php','_public.php','_admin.php','_widgets.php'));
		$this->addTemplate('admin_page',array('_admin.php'));
		$this->addTemplate('admin_service',array('_admin.php'));
		$this->addTemplate('index',array('index.php'));
		$this->addTemplate('class',array('*'));
		$this->addTemplate('function',array('*'));
		$this->addTemplate('handler',array('_prepend.php'));
		$this->addTemplate('template_tag_value',array('_public.php'));
		$this->addTemplate('template_tag_block',array('_public.php'));
		$this->addTemplate('template',array('*'));
		$this->addTemplate('require',array('_prepend.php'));
		$this->addTemplate('code_tag_value',array('_public.php'));
		$this->addTemplate('code_tag_block',array('_public.php'));
		$this->addTemplate('code_handler',array('_public.php'));
		$this->addTemplate('code_service',array('_services.php'));
		$this->addTemplate('code_widget_admin',array('_widgets.php'));
		$this->addTemplate('code_widget_public',array('_widgets.php'));
		
		# --BEHAVIOR-- dcDevKitModuleBootstrap
		$this->core->callBehavior('dcDevKitModuleBootstrap',$this);
	}
	
	public function getTemplates()
	{
		return $this->templates;
	}
	
	public function getTemplate($id)
	{
		return array_key_exists($id,$this->templates) ? $this->templates[$id] : null;
	}
	
	public function addTemplate($id,$target)
	{
		if (!is_string($id)) {
			return;
		}
		if (!is_array($target)) {
			return;
		}
		
		if (!file_exists($this->root.'/'.$id.'.template')) {
			return;
		}
		
		$this->templates[$id] = $target;
	}
	
	protected function computeFile($descriptor)
	{
		$content = array();
		$pattern = '';
		
		foreach ($descriptor->get() as $template)
		{
			$res = array();
			
			# Template checks
			if (!array_key_exists($template->getName(),$this->templates)) {
				throw new Exception(sprintf(__('Template %s does not exists'),$template->getName()));
			}
			if (
				!is_int(array_search($descriptor->getName(),$this->templates[$template->getName()])) &&
				!is_int(array_search('*',$this->templates[$template->getName()]))
			) {
				throw new Exception(sprintf(
					__('You are not allowed to write template %s in file %s'),
					$template->getName(),$descriptor->getName()
				));
			}
			
			$pattern = file_get_contents($this->root.'/'.$template->getName().'.template');
			
			if ($template->count() === 0) {
				$res[] = $pattern;
			}
			
			foreach ($template->get() as $replacement) { 
				
				$tmp = $pattern;
				foreach ($replacement->get() as $k => $v) {
					if ($v instanceof templateDescriptor) {
						$f = new fileDescriptor($descriptor->getName());
						$f->add($v);
						$tmp = str_replace($k,$this->computeFile($f),$tmp);
					}
					else {
						$tmp = str_replace($k,$v,$tmp);
					}
				}
				$res[] = $tmp;
			}
			
			$content[] = implode($template->getSeparator(),$res);
		}
		
		$content = implode("\n\n",$content);
		
		return $content;
	}
	
	protected function createFile($descriptor)
	{
		$root = dirname(__FILE__).'/../output';
		
		if ($descriptor->count() === 0) {
			return;
		}
		
		$content = $this->computeFile($descriptor);
		
		$ext = files::getExtension($descriptor->getName());
		if ($ext === 'php') {
			$content =
			'<?php'."\n\n".
			$content."\n\n".
			'?>';
		}
		
		# Create directory if does not exist
		$path = $root.'/'.$descriptor->getName();
		files::makeDir(dirname($path),true);
		
		$fp = @fopen($path, 'w');
		
		if ($fp === false) {
			throw new Exception(__('Unable to open file.'));
		}
		if (fwrite($fp,$content,strlen($content)) === false) {
			throw new Exception(sprintf(__('Impossible to write file %s'),$descriptor->getName()));
		}
		
		fclose($fp);
	}
	
	public function createModule($type)
	{
		$this->check();
		
		# Check if module exists
		/*$root = $type === 'plugins' ? DC_PLUGINS : '';	
		$dh = @opendir($root);
		if ($dh === true) {
			throw new Exception(sprintf(__('Module %s already exists'),$_POST['module_id']);
		}*/
		
		if ($type === 'plugins') {
			$this->createPlugin();
		}
		if ($type === 'themes') {
			$this->createTheme();
		}
		
		/*echo '<pre>';
		var_dump($this->descriptor);
		echo '</pre>';
		exit;*/
		
		foreach ($this->descriptor->get() as $descriptor)
		{
			$this->createFile($descriptor);
		}
		
		/*echo '<pre>';
		var_dump($this->descriptor);
		echo '</pre>';
		exit;*/
	}
	
	protected function createPlugin()
	{
		$files = array(
			'define' => array(
				'name' => '_define.php',
				'restriction' => null
			),
			'prepend' => array(
				'name' => '_prepend.php',
				'restriction' => 'public'
			),
			'admin' => array(
				'name' => '_admin.php',
				'restriction' => 'admin'
			),
			'index' => array(
				'name' => 'index.php',
				'restriction' => 'admin'
			),
			'services' => array(
				'name' => '_services.php',
				'restriction' => 'admin'
			),
			'widget' => array(
				'name' => '_widgets.php',
				'restriction' => 'public'
			),
			'public' => array(
				'name' => '_public.php',
				'restriction' => 'public'
			)
		);
		
		foreach ($files as $id => $file) {
			${$id} = new fileDescriptor($file['name']);
		}
				
		# _define.php
		$permissions = $_POST['module_permissions'] === '' ? "'usage,contentadmin'" : "'".$_POST['module_permission']."'";
		$priority = $_POST['module_priority'] === '' ? '100' : $_POST['module_priority'];		
		$t_define = new templateDescriptor('define');
		$t_define->add(array(
			'##MODULE_ID##' => $_POST['module_id'],
			'##MODULE_DESCRIPTION##' => $_POST['module_description'],
			'##MODULE_AUTHOR##' => $_POST['module_author'],
			'##MODULE_PERMISSIONS##' => $permissions,
			'##MODULE_PRIORITY##' => $priority
		));
		$define->add($t_define);
		unset($t_define);
		
		# _admin.php 
		if ($_POST['module_admin_page']) {
			$permissions = $_POST['module_admin_page_permissions'] === '' ? "'usage,contentadmin'" : "'".$_POST['module_admin_page_permissions']."'";
			$t_admin_page = new templateDescriptor('admin_page');
			$t_admin_page->add(array(
				'##MODULE_ID##' => $_POST['module_id'],
				'##MODULE_NAME##' => $_POST['module_name'],
				'##MODULE_PERMISSIONS##' => $permissions
			));	
			$admin->add($t_admin_page);
			unset($t_admin_page);
		}
		
		# _services.php
		if (trim($_POST['module_admin_services']) !== '') {
			$t_admin_services = new templateDescriptor('admin_service');
			$t_functions = new templateDescriptor('function',"\n\n");
			$t_code = new templateDescriptor('code_service');
			$class = sprintf(self::classServices,$_POST['module_id']);
			foreach (explode("\r\n",trim($_POST['module_admin_services'])) as $name) {
				$t_admin_services->add(array('##SERVICE_NAME##' => $name,'##SERVICE_CLASS##' => $class));
				$t_functions->add(array(
					'##FUNCTION_VISIBILITY##' => 'public static',
					'##FUNCTION_NAME##' => $name,
					'##FUNCTION_ATTR##' => '$core,$get',
					'##FUNCTION_CODE##' => $t_code
				));
			}
			$t_class = new templateDescriptor('class');
			$t_class->add(array('##CLASS_NAME##' => $class,'##CLASS_FUNCTIONS##' => $t_functions));
			$admin->add($t_admin_services);
			$services->add($t_class);
			unset($t_admin_services,$t_functions,$t_code,$t_class);
		}
		
		# Add widgets
		if ($_POST['module_admin_widget']) {
			$t_class = new templateDescriptor('class');
			$t_behaviors = new templateDescriptor('behavior');
			$t_functions = new templateDescriptor('function',"\n\n");
			$t_code_widget_admin = new templateDescriptor('code_widget_admin');
			$t_code_widget_public = new templateDescriptor('code_widget_public');
			$class = sprintf(self::classWidgets,$_POST['module_id']);
			$t_behaviors->add(array('##BEHAVIOR_NAME##' => 'initWidgets','##BEHAVIOR_CLASS##' => $class));
			$t_code_widget_admin->add(array(
				'##WIDGET_NAME##' => $_POST['module_id'],
				'##WIDGET_TITLE##' => $_POST['module_name'],
				'##WIDGET_CLASS##' => $class,
				'##WIDGET_FUNCTION##' => 'widget'
			));
			$t_code_widget_public->add(array('##WIDGET_NAME##' => $_POST['module_id']));
			$t_functions->add(array(
				'##FUNCTION_VISIBILITY##' => 'public static',
				'##FUNCTION_NAME##' => 'initWidgets',
				'##FUNCTION_ATTR##' => '$w',
				'##FUNCTION_CODE##' => $t_code_widget_admin
			));
			$t_functions->add(array(
				'##FUNCTION_VISIBILITY##' => 'public static',
				'##FUNCTION_NAME##' => 'widget',
				'##FUNCTION_ATTR##' => '$w',
				'##FUNCTION_CODE##' => $t_code_widget_public
			));
			$t_class->add(array('##CLASS_NAME##' => $class,'##CLASS_FUNCTIONS##' => $t_functions));
			$widget->add($t_behaviors);
			$widget->add($t_class);
			unset($t_class,$t_behaviors,$t_functions,$t_code_widget_admin,$t_code_widget_public);
		}
		
		# index.php
		if ($_POST['module_admin_page']) {
			$t_index = new templateDescriptor('index');
			$t_index->add(array('##MODULE_ID##' => $_POST['module_id'],'##MODULE_NAME##' => $_POST['module_name']));
			$index->add($t_index);
			unset($t_index);
		}
		
		# _public.php
		# Add template tags
		if ($_POST['module_public_page_tags'] !== '') {
			$t_tags_value = new templateDescriptor('template_tag_value');
			$t_tags_block = new templateDescriptor('template_tag_block');
			$t_functions = new templateDescriptor('function',"\n\n");
			$t_code_value = new templateDescriptor('code_tag_value');
			$t_code_block = new templateDescriptor('code_tag_block');
			$class = sprintf(self::classTemplate,$_POST['module_id']);
			foreach (explode("\r\n",trim($_POST['module_public_page_tags'])) as $tags) {
				$tag = explode(':',$tags);
				if (is_array($tag) && count($tag) === 2) {
					if ($tag[1] === 'value') {
						$t_tags_value->add(array('##TAG_NAME##' => $tag[0],'##TAG_CLASS##' => $class));
						$t_functions->add(array(
							'##FUNCTION_VISIBILITY##' => 'public static',
							'##FUNCTION_NAME##' => $tag[0],
							'##FUNCTION_ATTR##' => '$attr',
							'##FUNCTION_CODE##' => $t_code_value
						));
					}
					if ($tag[1] === 'block') {
						$t_tags_block->add(array('##TAG_NAME##' => $tag[0],'##TAG_CLASS##' => $class));
						$t_functions->add(array(
							'##FUNCTION_VISIBILITY##' => 'public static',
							'##FUNCTION_NAME##' => $tag[0],
							'##FUNCTION_ATTR##' => '$attr,$content',
							'##FUNCTION_CODE##' => $t_code_block
						));
					}
				}
			}
			if ($t_functions->count() > 0) {
				$t_class = new templateDescriptor('class');
				$t_class->add(array('##CLASS_NAME##' => $class,'##CLASS_FUNCTIONS##' => $t_functions));
				$public->add($t_tags_value);
				$public->add($t_tags_block);
				$public->add($t_class);
				unset($t_class);
			}
			unset($t_tags_value,$t_tags_block,$t_functions,$t_code_value,$t_code_block);
		}
		# Add handlers		
		if ($_POST['module_public_page_handlers'] !== '') {
			$t_handlers = new templateDescriptor('handler');
			$t_functions = new templateDescriptor('function',"\n\n");
			$class = sprintf(self::classHandler,$_POST['module_id']);
			foreach (explode(',',trim($_POST['module_public_page_handlers'])) as $name) {
				$t_handlers->add(array('##HANDLER_NAME##' => $name,'##HANDLER_CLASS##' => $class));
				$t_code = new templateDescriptor('code_handler');
				$t_code->add(array('##HANDLER_NAME##' => $name));
				$t_functions->add(array(
					'##FUNCTION_VISIBILITY##' => 'public static',
					'##FUNCTION_NAME##' => $name,
					'##FUNCTION_ATTR##' => '$args',
					'##FUNCTION_CODE##' => $t_code
				));
				$template = new fileDescriptor(sprintf('default-templates/%s.html',$name));
				$template->add(new templateDescriptor('template'));
				$this->descriptor->add($template);
			}
			$t_class = new templateDescriptor('class');
			$t_class->add(array('##CLASS_NAME##' => $class.' extends dcUrlHandlers','##CLASS_FUNCTIONS##' => $t_functions));
			$public->add($t_class);
			unset($t_functions,$t_code,$t_class);
		}
		
		# _prepend.php
		# Add autoload
		if ($services->count() > 0) {
			$t_autoload = new templateDescriptor('autoload');
			$class = sprintf(self::classServices,$_POST['module_id']);
			$t_autoload->add(array('##CLASS_PATH##' => '/'.$services->getName(),'##CLASS_NAME##' => $class));
			$prepend->add($t_autoload);
			unset($t_autoload);
		}
		# Add widgets
		if ($_POST['module_admin_widget']) {
			$t_require = new templateDescriptor('require');
			$t_require->add(array('##FILE_NAME##' => $widget->getName()));
			unset($t_require);
		}
		# Add handler
		if (isset($t_handlers) && $t_handlers->count() > 0) {
			$prepend->add($t_handlers);
			unset($t_handlers);
		}	
		# Add behaviors
		if (trim($_POST['module_behaviors']) !== '') {
			$t_behaviors = new templateDescriptor('behavior');
			$t_functions = new templateDescriptor('function',"\n\n");
			$class = sprintf(self::classBehaviors,$_POST['module_id']);
			foreach (explode("\r\n",trim($_POST['module_behaviors'])) as $name) {
				$t_behaviors->add(array('##BEHAVIOR_NAME##' => $name,'##BEHAVIOR_CLASS##' => $class));
				$t_functions->add(array(
					'##FUNCTION_VISIBILITY##' => 'public static',
					'##FUNCTION_NAME##' => $name,
					'##FUNCTION_ATTR##' => '',
					'##FUNCTION_CODE##' => ''
				));
			}
			$t_class = new templateDescriptor('class');
			$t_class->add(array('##CLASS_NAME##' => $class,'##CLASS_FUNCTIONS##' => $t_functions));
			$prepend->add($t_behaviors);
			$prepend->add($t_class);
			unset($t_behaviors,$t_functions,$t_class);
		}
		
		# Add restrictions
		$t_admin_restriction = new templateDescriptor('admin_restriction');
		$t_public_restriction = new templateDescriptor('public_restriction');
		foreach ($files as $id => $file) {
			if (${$id}->count() > 0) {
				if ($file['restriction'] === 'admin') {
					${$id}->add($t_admin_restriction,true);
				}
				if ($file['restriction'] === 'public') {
					${$id}->add($t_public_restriction,true);
				}
			}
			$this->descriptor->add(${$id});
		}
		
		unset($t_admin_restriction,$t_public_restriction);
	}
	
	protected function createTheme()
	{
		throw new Exception (__('Theme creation is not available yet'));
	}
	
	protected function check()
	{
		if ($_POST['module_id'] === '') {
			throw new Exception(__('ID is required'));
		}
		if ($_POST['module_name'] === '') {
			throw new Exception(__('Name is required'));
		}
		if ($_POST['module_description'] === '') {
			throw new Exception(__('Description is required'));
		}
		if ($_POST['module_author'] === '') {
			throw new Exception(__('Author is required'));
		}
	}
}

abstract class commonDescriptor
{
	protected $name;
	protected $data;
	
	abstract public function add($add);
	
	public function __construct($name = null)
	{
		$this->name = $name;
		$this->data = array();
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function count()
	{
		return count($this->data);
	}
	
	public function get()
	{
		return $this->data;
	}
	
	public function __get($k = null)
	{
		return array_key_exists($k,$this->data) ? $this->data[$k] : null;
	}
}

class descriptor extends commonDescriptor
{
	public function add($add)
	{
		if (!($add instanceof fileDescriptor)) {
			throw new Exception (__('Wrong file descriptor'));
		}
		
		$this->data[] = $add;
	}
}

class fileDescriptor extends commonDescriptor
{
	public function add($add,$unshift = false)
	{
		if (!($add instanceof templateDescriptor)) {
			throw new Exception (__('Wrong template descriptor'));
		}
		
		if ($unshift) {
			array_unshift($this->data,$add);
		} else {
			array_push($this->data,$add);
		}
	}
}

class templateDescriptor extends commonDescriptor
{
	protected $separator = null;
	
	public function __construct($name = null,$separator = null)
	{
		if (!is_null($separator)) {
			$this->separator = $separator;
		}
		
		parent::__construct($name);
	}
	
	public function add($add,$unshift = false)
	{
		if (!is_array($add)) {
			throw new Exception (__('Invalid replacement format'));
		}
		
		$r = new replacementDescriptor($this->name);
		$r->add($add);
		
		if ($unshift) {
			array_unshift($this->data,$r);
		} else {
			array_push($this->data,$r);
		}
	}
	
	public function getSeparator()
	{
		return is_null($this->separator) ? "\n" : $this->separator;
	}
}

class replacementDescriptor extends commonDescriptor
{
	public function add($add)
	{
		foreach ($add as $k => $v) {
			$this->data[$k] = $v;
		}
	}
}

?>