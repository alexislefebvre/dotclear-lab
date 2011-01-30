<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class soCialMeProfil extends soCialMe
{
	protected $ns = 'soCialMeProfil';
	protected $things = array(
		'Icon' => 'Icon badge',
		'Small' => 'Small badge',
		'Big' => 'Big badge',
		'SmallExtra' => 'Small extra badge',
		'MediumExtra' => 'Medium extra bagde',
		'BigExtra' => 'Big extra badge'
	);
	protected $markers = null;
	
	# Load markers (all actions things)
	public function init()
	{
		# before post
		$markers['ontop'] = array(
			'name' => __('Page header'),
			'description' => __('Place a group of bagdes just after page title'),
			'action' => array('Icon','Small','Big','SmallExtra','MediumExtra','BigExtra'),
			'title' => true,
			'homeonly' => true
		);
		
		# after post
		$markers['onfooter'] = array(
			'name' => __('Footer'),
			'description' => __('Place a group of badges on footer'),
			'action' => array('Icon','Small','Big','SmallExtra','MediumExtra','BigExtra'),
			'title' => true,
			'homeonly' => true
		);
		
		# on widget
		$markers['onwidget'] = array(
			'name' => __('Widget'),
			'description' => __('Place group of bagdes on widget. You must set up widget.'),
			'action' => array('Icon','Small','Big','SmallExtra','MediumExtra','BigExtra'),
			'title' => true,
			'homeonly' => true
		);
		
		$this->markers = $markers;
	}
	
	# Public content (on page and widget)
	public static function publicContent($place,$core)
	{
		# Active
		if (!$core->blog->settings->soCialMeProfil->active) 
		{
			return;
		}
		
		# main class
		$class = new soCialMeProfil($core);
		
		# Only on home page
		$s_homeonly = $class->getMarker('homeonly',false);
		if (!empty($s_homeonly) && is_array($s_homeonly) && isset($s_homeonly[$place]) 
		&& !empty($s_homeonly[$place]) && $core->url->type != 'default')
		{
			return;
		}
		
		# Services
		$s_action = $class->getMarker('action',array());
		if (empty($s_action) || !is_array($s_action) || !isset($s_action[$place]))
		{
			return;
		}
		
		# Title
		$s_title = $class->getMarker('title','');
		
		# Get services codes
		foreach($class->things() as $thing => $plop)
		{
			if (!empty($thing_limit) && $thing != $thing_limit) continue;
			
			$usable[$thing] = $class->can($thing.'Content');
			$rs[$thing] = '';
		}
		
		# Reorder
		$s_order = $class->fillOrder($usable);
		
		# Get actions
		foreach($s_order as $thing => $services)
		{
			if (!isset($s_action[$place][$thing]) || empty($s_action[$place][$thing])) continue;
			
			foreach($services as $id)
			{
				if (!in_array($id,$s_action[$place][$thing])) continue;
				
				$rs[$thing] .= '<li class="social-profil social-id-'.$id.'">'.$class->play($id,$thing,'Content',null).'</li>';
			}
		}
		
		# Combine
		$res = '';
		foreach($rs as $thing => $content)
		{
			if (empty($content)) continue;
			
			$res .= '<div class="social-profils '.$place.'-'.$thing.'">';
			if (isset($s_title[$place]) && !empty($s_title[$place]))
			{
				$res .= $place == 'onwidget' ?
					'<h2>'.$s_title[$place].'</h2>' :
					'<h3>'.$s_title[$place].'</h3>';
			}
			$res .= '<ul>'.$content.'</ul></div>';
		}
		return $res;
	}
}
?>