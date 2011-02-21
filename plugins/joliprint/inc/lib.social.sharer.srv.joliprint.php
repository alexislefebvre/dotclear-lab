<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of joliprint, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class joliprintSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $available = true;
	protected $define = array(
		'id' => 'joliprint',
		'name' => 'Joliprint',
		'home' => 'http://joliprint.com',
		'icon' => 'pf=joliprint/icon.png'
	);
	protected $actions = array(
		'playIconContent' => true,
		'playSmallContent' => true,
		'playBigContent' => true
	);
	
	public function playIconContent($post)
	{
		return $this->parseHTML('joliprint-icon.png',$post);
	}
	
	public function playBigContent($post)
	{
		return $this->parseHTML('joliprint-share-style.png',$post);
	}
	
	public function playSmallContent($post)
	{
		return $this->parseHTML('joliprint-share-button.png',$post);
	}
	
	private function parseHTML($type,$post)
	{
		if (!$post || empty($post['url'])) return;
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'content' => joliprint::toHTML(array('url'=>$post['url'],'button'=>$type))
		);
		return $record;
	}
}
?>