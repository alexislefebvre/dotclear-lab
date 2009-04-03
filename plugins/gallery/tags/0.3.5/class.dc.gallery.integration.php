<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class dcGalleryIntegration
{
	protected $core;

	public static $default_supported_modes = array(
		"home" => array("default","default-page"),
		"category" => array("category"),
		"tag" => array("tag","tags"),
		"archive" => array("archive"),
		"search" => array("search"));

	protected $type_mode_bind;

	protected $integrations;
	protected $supported_modes;

	
	public function __construct(&$core)
	{
		$this->core =& $core; 
		$this->integrations = $this->load();
		$this->type_mode_bind=array();
		if (function_exists('json_decode') &&
			$this->core->blog->settings->gallery_supported_modes) {
				$this->supported_modes = json_decode($this->core->blog->settings->gallery_supported_modes);
		} else {
			$this->supported_modes = self::$default_supported_modes;
		}
		foreach ($this->supported_modes as $mode => $v) {
			foreach ($v as $type)
				$this->type_mode_bind[$type]=$mode;
		}
	}

	public function load() {
		if ($this->core->blog->settings->gallery_integrations != "") {
			$integ = @unserialize(base64_decode($this->core->blog->settings->gallery_integrations));
			if ($integ === false)	
				$integ = array();
		} else {
			$integ = array();
		}
		return $integ;
	}

	public function save() {
		$this->core->blog->settings->setNamespace('gallery');
		$this->core->blog->settings->put('gallery_integrations',
			@base64_encode(serialize($this->integrations)),'string',
			'Gallery integrations');

	}

	public function getModes() {
		$integ = $this->integrations;
		foreach ($this->supported_modes as $k=>$v) {
			if (!array_key_exists($k,$integ)) {
				$integ[$k]=array('gal' => 'none','img' => 'none');
			}
		}
		return $integ;
	}

	public function setMode($mode,$img,$gal) {
		$this->integrations[$mode]=array('gal' => $gal,'img' => $img);
	}

	public function isEnabledForType($type,$check_gal=true,$check_img=true) {
		if (!array_key_exists($type,$this->type_mode_bind))
			return false;
		$mode = $this->type_mode_bind[$type];

		if (!array_key_exists($mode,$this->integrations)) {
			return false;
		} else {
			return ($check_gal && $this->integrations[$mode]['gal'] != 'none')
				|| ($check_img && $this->integrations[$mode]['img'] != 'none'); 
		}

	}

	public function updateGetPostParams($type,&$params) {
		if (!array_key_exists($type,$this->type_mode_bind))
			return false;
		$mode = $this->type_mode_bind[$type];
		if (!array_key_exists($mode,$this->integrations))
			return false;
		$cond=array();
		$post_types = array();
		switch ($this->integrations[$mode]['img']) {
		case 'selected':
			$cond[]="'galitem'";
			/* No break here, and it is wanted so! */
		case 'all':
			$post_types[]="galitem";
		}
		switch ($this->integrations[$mode]['gal']) {
		case 'selected':
			$cond[]="'gal'";
			/* No break here, and it is wanted so! */
		case 'all':
			$post_types[]="gal";
		}
		$p="";
		if (!empty($post_types)) {
			$params['post_type'] = $post_types;
			$params['post_type'][] = 'post';
		}
		if (!empty($cond)) {
			if (!isset($params['sql']))
				$params['sql']="";
			$params['sql'] .= "AND not (P.post_type in (".join(",",$cond).") AND P.post_selected='0') ";
		}
	}

}

