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

class dcDevKit
{
	protected $core;
	protected $modules;
	
	public function __construct($core)
	{
		$this->core = $core;
		$this->modules = array();
		
		# --BEHAVIOR-- dcDevKitConstructor
		$this->core->callBehavior('dcDevKitConstructor',$this);
	}
	
	public function addModule($id)
	{
		if (!class_exists($id)) {
			return;
		}
		
		$r = new ReflectionClass($id);
		$p = $r->getParentClass();

		if (!$p || $p->name != 'dcDevModule') {
			return;
		}
		
		# Register module
		$this->modules[$id] = new $id($this->core);
	}
	
	public function getModules()
	{
		return $this->modules;
	}
	
	public function getModule($id)
	{
		return array_key_exists($id,$this->modules) ? $this->modules[$id] : null;
	}
	
	public function hasModule($id)
	{
		return array_key_exists($id,$this->modules);
	}
}

?>