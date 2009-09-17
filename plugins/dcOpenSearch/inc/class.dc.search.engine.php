<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcSearchEngine
{
	public $type;
	public $name;
	public $label;
	public $description;
	public $active = true;
	public $order = 100;
	
	protected $core;
	protected $has_gui = false;
	protected $gui_url = null;
	
	public function __construct($core)
	{
		$this->core =& $core;
		$this->setInfo();
		
		if (!$this->name) {
			$this->name = get_class($this);
		}
		
		$this->gui_url = 'plugin.php?p=openSearch&e='.get_class($this);
	}
	
	protected function setInfo()
	{
		$this->type = __('No type');
		$this->label = __('No label');
		$this->description = __('No description');
	}
	
	public function getResults($q = '')
	{		
		return array();	
	}
	
	public function gui($url)
	{
	}
	
	public function hasGUI()
	{
		if (!$this->core->auth->check('admin',$this->core->blog->id)) {
			return false;
		}
		
		if (!$this->has_gui) {
			return false;
		}
		
		return true;
	}
	
	public function guiURL()
	{
		if (!$this->hasGui()) {
			return false;
		}
		
		return $this->gui_url;
	}

	public function guiLink()
	{
		if (($url = $this->guiURL()) !== false) {
			$url = html::escapeHTML($url);
			$link = '<a href="%2$s">%1$s</a>';
		} else {
			$link = '%1$s';
		}
		
		return sprintf($link,$this->name,$url);
	}
	
	public static function getItemAdminURL($rs)
	{
		return call_user_func(array($rs->search_engine,'getItemAdminURL'),&$rs);
	}
	
	public static function getItemPublicURL($rs)
	{
		return call_user_func(array($rs->search_engine,'getItemPublicURL'),&$rs);
	}
	
	public static function getItemContent($rs)
	{
		return call_user_func(array($rs->search_engine,'getItemContent'),&$rs);
	}
}

?>