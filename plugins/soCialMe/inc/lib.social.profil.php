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
	protected $part = 'profil';
	protected $ns = 'soCialMeProfil';
	protected $markers = null;
	protected $things = array(
		'Icon' => 'Icon badge',
		'Small' => 'Small badge',
		'Big' => 'Big badge',
		'Card' => 'Identity card',
		'SmallExtra' => 'Small extra badge',
		'BigExtra' => 'Big extra badge'
	);
	
	# Construct admin pages
	public static function adminNav()
	{
		if (!defined('DC_CONTEXT_ADMIN')) return null;
		
		return array(
			'title' => __('Profil'),
			'description' => __('Show your social profiles and counters.'),
			'ns' => 'soCialMeProfil',
			'parts' => array(
				'location' => __('Locations'),
				'order' => __('Orders'),
				'service' => __('Services'),
				'setting' => __('Settings')
			),
			'common' => array('order','service','setting')
		);
	}
	
	# Load markers (all actions things)
	public function init()
	{
		# before post
		$markers['ontop'] = array(
			'name' => __('Page header'),
			'description' => __('Place a group of bagdes just after page title'),
			'action' => array('Icon','Small','Big','Card','SmallExtra','BigExtra'),
			'title' => true,
			'homeonly' => true,
			'order' => true
		);
		
		# after post
		$markers['onfooter'] = array(
			'name' => __('Footer'),
			'description' => __('Place a group of badges on footer'),
			'action' => array('Icon','Small','Big','Card','SmallExtra','BigExtra'),
			'title' => true,
			'homeonly' => true,
			'order' => true
		);
		
		# on widget
		$markers['onwidget'] = array(
			'name' => __('Widget'),
			'description' => __('Place group of bagdes on widget. You must set up widget.'),
			'action' => array('Icon','Small','Big','Card','SmallExtra','BigExtra'),
			'title' => true,
			'homeonly' => true,
			'order' => true
		);
		
		$this->markers = $markers;
	}
}
?>