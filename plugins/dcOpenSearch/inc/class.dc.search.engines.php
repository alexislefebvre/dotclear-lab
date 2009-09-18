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

class dcSearchEngines
{
	private $engines = array();
	private $engines_opt = array();
	private $search = array();
	private $core;
	
	public function __construct($core)
	{
		$this->core = $core;
	}
	
	public function init($engines)
	{
		$this->engines = array();
		
		foreach ($engines as $e)
		{ 
			if (!class_exists($e)) {
				continue;
			}
			
			$r = new ReflectionClass($e);
			$p = $r->getParentClass();
			
			if (!$p || $p->name != 'dcSearchEngine') {
				continue;
			}
			
			$this->engines[$e] = new $e($this->core);
		}
		
		$this->setEngineOpts();
		if (!empty($this->engines_opt)) {
			uasort($this->engines,array($this,'orderCallBack'));
		}
	}
	 	 	
	public function search($q,$count_only)
	{	
		$search = array();
		
		foreach ($this->engines as $eid => $e)
		{	
			if (!$e->active) {
				continue;
			}
			
			$GLOBALS['_searchcountbytype'][$e->type] = count($e->getResults($q));
			
			$search = array_merge($search,$e->getResults($q));
		}
		
		$this->search = $count_only ? array(array(count($search))) : $search;

		return $this->search;
	}
	
	public function getEngines()
	{
		return $this->engines;
	}
	
	public function saveEngineOpts($opts,$global=false)
	{
		$this->core->blog->settings->setNameSpace('dcopensearch');
		if ($global) {
			$this->core->blog->settings->drop('dcopensearch_engines');
		}
		$this->core->blog->settings->put('dcopensearch_engines',serialize($opts),'string','Search engines',true,$global);
	}
	
	private function setEngineOpts()
	{
		if ($this->core->blog->settings->dcopensearch_engines !== null) {
			$this->engines_opt = @unserialize($this->core->blog->settings->dcopensearch_engines);
		}
		
		# Create default options if needed
		if (!is_array($this->engines_opt)) {
			$this->saveEngineOpts(array(),true);
			$this->engines_opt = array();
		}
		
		foreach ($this->engines_opt as $k => $o)
		{
			if (isset($this->engines[$k]) && is_array($o)) {		
				$this->engines[$k]->active = isset($o[0]) ? $o[0] : false;
				$this->engines[$k]->order = isset($o[1]) ? $o[1] : 0;
			}
		}
	}
	
	private function orderCallBack($a,$b)
	{
		if ($a->order == $b->order) {
			return 0;
		}
		
		return $a->order > $b->order ? 1 : -1;
	}
}

?>