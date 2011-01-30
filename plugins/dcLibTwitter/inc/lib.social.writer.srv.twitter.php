<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibTwitter, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')){return;}# Add twitter to plugin soCialMe (writer part)class twitterSoCialMeWriterService extends soCialMeService{	protected $part = 'writer';		protected $define = array(		'id' => 'twitter',		'name' => 'Twitter',		'home' => 'http://twitter.com',		'icon' => 'pf=dcLibTwitter/icon.png'	);		protected $actions = array(		'playMessageContent' => true,		'playLinkContent' => true	);		private $oauth = false;		protected function init()	{		# Required plugin oAuthManager		# Used name of parent plugin		if (soCialMeUtils::checkPlugin('oAuthManager','0.1'))		{			$this->oauth = oAuthClient::load($this->core,'twitter',				array(					'user_id' => null,					'plugin_id' => 'soCialMeWriter',					'plugin_name' => __('SoCialMe Writer'),					'token' => 'fsd1RtLzixYqNyrW2lpZOg',					'secret' => 'D3StXIaXq2bjPwvZA1tIrgnGnNnSvVkQwxvOFWRi2k'				)			);		}				if (false === $this->oauth)		{			$this->available = false;			return false;		}				$this->available = true;		return true;	}		public function adminSave($service_id,$admin_url)	{		if (!$this->available || $service_id != $this->id) return;				$request_step = !empty($_REQUEST['step']) ? $_REQUEST['step'] : null;				if (!$request_step)		{			return;		}		elseif ($request_step == 'request')		{			$this->oauth->getRequestToken($admin_url.'&step=callback');		}		elseif ($request_step == 'callback')		{			$this->oauth->getAccessToken();		}		elseif ($request_step == 'clean')		{			$this->oauth->removeToken();		}	}		public function adminForm($service_id,$admin_url)	{		if (!$this->available) return;		$admin_url = str_replace('&','&amp;',$admin_url);				$res = '<p>';		if ($this->oauth->state() == 1)		{			$res .= '<a class="button" href="'.$admin_url.'&amp;step=clean">'.sprintf(__('Something went wrong, clean acces of %s from %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';		}		elseif ($this->oauth->state() == 2)		{			$user = $this->oauth->getScreenName();			if ($user)			{				$res .= '<p>'.sprintf(__('Your are connected as "%s"'),$user).'</p>';			}			$res .= '<a class="button" href="'.$admin_url.'&amp;step=clean">'.sprintf(__('Disconnet %s from %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';		}		elseif ($this->oauth->state() == 0)		{			$res .= '<a class="button" href="'.$admin_url.'&amp;step=request">'.sprintf(__('Connect %s to %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';		}		$res .= '</p>';				return $res;	}		public function playMessageContent($msg,$has_url=false)	{		$this->send($msg);	}		public function playLinkContent($title,$url,$type='link')	{		$this->send($title.' '.$url.' ('.$type.')');	}		private function send($msg)	{		if (!$this->available || $this->oauth->state() != 2) return;				# Split into smaller messages		$parts = soCialMeUtils::splitString($msg,140);		$count = count($parts);		# Loop throught lines of message		foreach($parts as $k => $line)		{			# Add line number at the end of message			if ($count > 1) {				$line .= ' '.($k+1).'/'.$count;			}			# Sleep script to prevent flood			if ($k > 0) {				sleep(2);			}			$params = array('status' => (string) $line);						$this->oauth->post('statuses/update',$params);		}	}}?>