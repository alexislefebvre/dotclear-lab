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

if (!defined('DC_RC_PATH')){return;}class soCialMeSharer extends soCialMe{
	protected $part = 'sharer';	protected $ns = 'soCialMeSharer';
	protected $markers = null;	protected $things = array(		'Icon' => 'Icon button',		'Small' => 'Small button',		'Big' => 'Big button'	);
	
	# Construct admin pages
	public static function adminNav()
	{
		if (!defined('DC_CONTEXT_ADMIN')) return null;
		
		return array(
			'title' => __('Sharer'),
			'description' => __('Help your visitors to share content of your blog on social networks.'),
			'ns' => 'soCialMeSharer',
			'parts' => array(
				'location' => __('Locations'),
				'order' => __('Orders'),
				'service' => __('Services'),
				'setting' => __('Settings')
			),
			'common' => array('order','service','setting')
		);
	}		# Load markers (all actions things)	public function init()	{		# before post		$markers['beforepost'] = array(			'name' => __('Before post content'),			'description' => __('Place a group of buttons just before post content'),			'action' => array('Icon','Small','Big'),			'title' => true,			'page' => true,
			'order' => true		);				# after post		$markers['afterpost'] = array(			'name' => __('After post content'),			'description' => __('Place group of buttons just before post content'),			'action' => array('Icon','Small','Big'),			'title' => true,			'page' => true,
			'order' => true		);				# on widget		$markers['onwidget'] = array(			'name' => __('On a widget'),			'description' => __('Place group of buttons on widget. You must set up widget.'),			'action' => array('Icon','Small','Big'),			'title' => true,			'page' => false,
			'order' => true		);				$this->markers = $markers;	}}?>