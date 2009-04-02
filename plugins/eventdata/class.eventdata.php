<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class eventdata Extends dcEvent
{
	protected $core;
	public $url = '';
	public $path = '';

	protected $settings_available = array(
		'event_option_active' => 'boolean',
		'event_option_menu' => 'boolean',
		'event_option_public' => 'boolean',
		'event_perm_pst' => 'boolean',
		'event_perm_cat' => 'boolean',
		'event_perm_tpl' => 'boolean',
		'event_perm_adm' => 'boolean',
		'event_tpl_title' => 'string',
		'event_tpl_desc' => 'string',
		'event_tpl_url' => 'string',
		'event_tpl_dis_bhv' => 'boolean',
		'event_tpl_theme' => 'string',
		'event_tpl_cats' => 'string',
		'event_no_cats' => 'string'
	);
	protected $permissions_available = array(
		'pst' => array('admin','admin,usage,contentadmin,eventdata'),
		'cat' => array('admin','admin,categories,eventdata'),
		'tpl' => array('admin','admin,eventdata'),
		'adm' => array('admin','admin,eventdata')
	);

	public function __construct(&$core)
	{
		$this->core =& $core;
		parent::__construct($core);

		$this->url = 'plugin.php?p=eventdata';
		$this->path = array_pop(explode(PATH_SEPARATOR, DC_PLUGINS_ROOT.'/eventdata'));

		self::getSettings();
	}

	public function getSettings()
	{
		$this->S = new arrayObject();
		foreach($this->settings_available AS $s => $t) {
			$this->S->$s = $this->core->blog->settings->{$s};
		}
		return $this->S;
	}

	public function setSettings($args)
	{
		$done = 0;
		$this->core->blog->settings->setNameSpace('eventdata');
		foreach($args AS $k => $v) {
			if (array_key_exists($k,$this->settings_available)) {
				$this->core->blog->settings->put($k,$v,$this->settings_available[$k]);
				$done = 1;
			}
		}
		if ($done) {
			$this->core->blog->triggerBlog();
			self::getSettings();
		}
	}

	public function checkPerm($name)
	{
		return isset($this->S->{'event_perm_'.$name}) && ($this->core->auth->check($this->permissions_available[$name][$this->S->{'event_perm_'.$name}],$this->core->blog->id)
			|| $this->core->auth->isSuperAdmin()) ? true : false;
	}

	public function getThemes($type='all')
	{
		$tpl = $thm = $tpl_dirs = array();

		# Template
		if ($type !='themes') {
			$dir = $this->path.'/default-templates/';
			if ($dir && is_dir($dir) && is_readable($dir)) {			
				$d = dir($dir);
				while (($f = $d->read()) !== false) {
					if (is_dir($dir.'/'.$f) && !preg_match('/^\./',$f)) {
						$tpl_dirs[] = $f;
					}
				}
			}
			foreach($tpl_dirs AS $v) {
				$k = str_replace('eventdata-','',$v);
				$tpl[$k] = array(
					'name' => $k,
					'template_exists' => true,
					'template_file' => (file_exists($dir.$v.'/events.html') ? 
						$dir.$v.'/events.html' : ''),
					'theme_exists' => false,
					'theme_file' => '',
					'selected' => false
				);
			}
			if ($type == 'templates') return $tpl;
		}
		# Theme
		if ($type !='templates') {
			$themes = new dcThemes($this->core);
			$themes->loadModules($this->core->blog->themes_path,null);
			$tpl_thm = $themes->getModules();
			foreach($tpl_thm AS $v => $p) {
				$thm[$v] = array(
					'name' => $p['name'],
					'template_exists' => false,
					'template_file' => '',
					'theme_exists' => true,
					'theme_file' => (file_exists($p['root'].'/tpl/events.html') ? 
						$p['root'].'/tpl/events.html' : ''),
					'selected' => $this->core->blog->settings->theme == $v ? true : false
				);
			}
			if ($type == 'themes') return $thm;
		}
		# All
		if ($type !='templates' && $type != 'themes') {
			foreach($thm AS $k => $v) {
				$tpl[$k] = array(
					'name' => $v['name'],
					'template_exists' => isset($tpl[$k]['template_exists']) ? $tpl[$k]['template_exists'] : '',
					'template_file' => isset($tpl[$k]['template_file']) ? $tpl[$k]['template_file'] : '',
					'theme_exists' => $v['theme_exists'],
					'theme_file' => $v['theme_file'],
					'selected' => $v['selected']);
			}
			return $tpl;
		}
		return null;
	}
}

?>