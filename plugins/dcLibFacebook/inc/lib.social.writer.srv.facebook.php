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

if (!defined('DC_RC_PATH')){return;}# Add facebook to plugin soCialMe (writer part)class facebookSoCialMeWriterService extends soCialMeService{	protected $part = 'writer';		protected $define = array(		'id' => 'facebook',		'name' => 'Facebook',		'home' => 'http://facebook.com',		'icon' => 'pf=dcLibFacebook/icon.png'	);		protected $actions = array(		'playMessageContent' => true,		'playLinkContent' => true,
		'playArticleContent' => true	);		private $oauth = false;		protected function init()	{
		# read facebook app settings for admin side
		$oauth_settings = facebookUtils::decodeApp('admin');
				# Required plugin oAuthManager		# Used name of parent plugin		if (!empty($oauth_settings['client_id']) && soCialMeUtils::checkPlugin('oAuthManager','0.2-alpha1'))		{			$this->oauth = oAuthClient::load($this->core,'facebook',				array(					'user_id' => null,					'plugin_id' => 'soCialMeWriter',					'plugin_name' => __('SoCialMe Writer'),					'token' => $oauth_settings['client_id'], //app_id					'secret' => $oauth_settings['client_secret'], //app_secret
					'options' => array(
						'scope' => 'offline_access,publish_stream'
					)				)			);		}				if (false === $this->oauth)		{			$this->available = false;			return false;		}				$this->available = true;		return true;	}		public function adminSave($service_id,$admin_url)	{		if (!$this->available || $service_id != $this->id) return;				$request_step = !empty($_REQUEST['step']) ? $_REQUEST['step'] : null;				if (!$request_step)		{			return;		}		elseif ($request_step == 'request')		{			$this->oauth->getRequestToken($admin_url.'&step=callback');		}		elseif ($request_step == 'callback' && !empty($_REQUEST['code']))		{			$this->oauth->getAccessToken($admin_url.'&step=callback');		}		elseif ($request_step == 'clean')		{			$this->oauth->removeToken();		}
		return;	}		public function adminForm($service_id,$admin_url)	{		if (!$this->available)
		{
			$res = '<p>'.sprintf(__('In order to use %s on your blog, a super admin must register an %s app.'),$this->oauth->config('client_name')).'</p>';
		}
		else
		{			$admin_url = str_replace('&','&amp;',$admin_url);						$res = '<p>';			if ($this->oauth->state() == 1)			{				$res .= '<a class="button" href="'.$admin_url.'&amp;step=clean">'.sprintf(__('Something went wrong, clean acces of %s from %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';			}			elseif ($this->oauth->state() == 2)			{				$user = $this->oauth->info('name');				if ($user)				{					$res .= '<p>'.sprintf(__('Your are connected as "%s"'),$user).'</p>';				}				$res .= '<a class="button" href="'.$admin_url.'&amp;step=clean">'.sprintf(__('Disconnet %s from %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';			}			elseif ($this->oauth->state() == 0)			{				$res .= '<a class="button" href="'.$admin_url.'&amp;step=request">'.sprintf(__('Connect %s to %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';			}			$res .= '</p>';		}		return $res;	}		public function playMessageContent($msg,$has_url=false)	{
		$params = array(
			'message' => $msg
		);		$this->send($params);	}		public function playLinkContent($title,$url,$type='link')	{
		$params = array(
			'link' => $url,
			'name' => $title
		);
		$this->send($params);	}
	
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
	}}?>