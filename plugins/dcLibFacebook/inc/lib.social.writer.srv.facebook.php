<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFacebook, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}
		'playArticleContent' => true
		# read facebook app settings for admin side
		$oauth_settings = facebookUtils::decodeApp('admin');
		
					'options' => array(
						'scope' => 'offline_access,publish_stream'
					)
		return;
		{
			$res = '<p>'.sprintf(__('In order to use %s on your blog, a super admin must register an %s app.'),$this->oauth->config('client_name')).'</p>';
		}
		else
		{
		$params = array(
			'message' => $msg
		);
		$params = array(
			'link' => $url,
			'name' => $title
		);
		$this->send($params);
	
	public function playArticleContent($record)
	{
		if (!is_array($record) || empty($record['title']) || empty($record['content'])) return false;
		
		$params = array(
			'body' => html::clean($record['excerpt'].$record['content']),
			'link' => $record['url'],
			'name' => $record['title']
		);
		
		return $this->send($params);
	}
	
	private function send($params)
	{
		if (!$this->available || $this->oauth->state() != 2) return;
		
		$this->oauth->post('me/feed',$params);
	}