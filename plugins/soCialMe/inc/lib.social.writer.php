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

if (!defined('DC_RC_PATH')){return;}class soCialMeWriter extends soCialMe{
	protected $part = 'writer';	protected $ns = 'soCialMeWriter';
	protected $markers = null;	protected $things = array(		'Message' => 'Send a short message on:',		'Link' => 'Send the link on:',		'Article' => 'Send a full article on:',		'Data' => 'Send special data on:'	);
	
	# Construct admin pages
	public static function adminNav()
	{
		if (!defined('DC_CONTEXT_ADMIN')) return null;
		
		return array(
			'title' => __('Writer'),
			'description' => __('Send and share content from your blog to your social networks.'),
			'ns' => 'soCialMeWriter',
			'parts' => array(
				'message' => __('Message'),
				'event' => __('Events'),
				'service' => __('Services')
				//,'setting' => __('Settings')
			),
			'common' => array('service')
		);
	}		# Load markers (all actions things)	public function init()	{		# On post update		$markers['postupdate'] = array(			'name' => __('Post update'),			'description' => __('When a post is updated on your blog:'),			'action' => array('Message','Link','Article'),			'format' => array('Message'),			'wildcards' => array('Message' => array('%blog%','%title%','%url%','%author%','%category%','%tags%'))		);				# On post publish		$markers['postpublish'] = array(			'name' => __('Post publish'),			'description' => __('When a post is published on your blog:'),			'action' => array('Message','Link','Article'),			'format' => array('Message'),			'wildcards' => array('Message' => array('%blog%','%title%','%url%','%author%','%category%','%tags%'))		);				# On post update		$markers['usercreate'] = array(			'name' => __('Create user'),			'description' => __('When a user is added on your blog:'),			'action' => array('Message'),			'format' => array('Message'),			'wildcards' => array('Message' => array('%blog%','user%'))		);				$this->markers = $markers;	}}?>