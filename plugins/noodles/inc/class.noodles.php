<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of noodles, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class noodles
{
	private $noodles = array();
	
	public static function decode($s)
	{
		$o = @unserialize(base64_decode($s));
		if ($o instanceof self)	return $o;
		return new self;
	}
	
	public function encode()
	{
		return base64_encode(serialize($this));
	}
	
	public function add($id,$name,$js_callback,$php_callback=null)
	{
		$this->noodles[$id] = new noodle($id,$name,$js_callback,$php_callback);
	}
	
	public function __get($id)
	{
		return isset($this->noodles[$id]) ? $this->noodles[$id] : null;
	}
	
	public function __set($id,$noodle)
	{
		return $this->noodles[$id] = $noodle;
	}
	
	public function exists($id)
	{
		return isset($this->noodles[$id]);
	}
	
	public function isEmpty()
	{
		return !count($this->noodles);
	}
	
	public function noodles()
	{
		return $this->noodles;
	}
}

class noodle
{
	private $id;
	private $name;
	private $js_callback;
	private $php_callback;
	private $settings = array(
		'active' => 0,
		'rating' => 'g',
		'size' => 16,
		'target' => '',
		'place' => 'prepend'
	);
	
	public function __construct($id,$name,$js_callback,$php_callback=null)
	{
		$this->id = $id;
		$this->name = $name;
		$this->js_callback = $js_callback;
		$this->php_callback = $php_callback;
	}
	
	public function id()
	{
		return $this->id;
	}
	
	public function name()
	{
		return $this->name;
	}
	
	public function jsCallback($g,$content='')
	{
		if (!is_callable($this->js_callback)) return null;
		return call_user_func($this->js_callback,$g,$content);
	}
	
	public function hasJsCallback()
	{
		return !empty($this->js_callback);
	}
	
	public function phpCallback($core)
	{
		if (!is_callable($this->php_callback)) return null;
		return call_user_func($this->php_callback,$core,$this);
	}
	
	public function hasPhpCallback()
	{
		return !empty($this->php_callback);
	}
	
	public function set($type,$value)
	{
		switch ($type)
		{
			case 'active':
			$this->settings['active'] = abs((integer) $value);
			break;
			
			case 'rating':
			$this->settings['rating'] = in_array($value,array('g','pg','r','x')) ?
				$value : 'g';
			break;
			
			case 'size':
			$this->settings['size'] = 
				in_array($value,array(16,24,32,48,56,64,92,128,256)) ?
				$value : 16;
			break;
			
			case 'css':
			$this->settings['css'] = (string) $value;
			break;
			
			case 'target':
			$this->settings['target'] = (string) $value;
			break;
			
			case 'place':
			$this->settings['place'] = 
				in_array($value,array('append','prepend','before','after')) ? 
				$value : 'prepend';
			break;
		}
	}
	
	public function __set($type,$value)
	{
		$this->set($type,$value);
	}
	
	public function get($type)
	{
		return isset($this->settings[$type]) ? $this->settings[$type] : null;
	}
	
	public function __get($type)
	{
		return $this->get($type);
	}
}
?>