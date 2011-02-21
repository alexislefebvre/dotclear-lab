<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFoursquare, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}# Add foursquare to plugin soCialMe (reader part)class foursquareSoCialMeReaderService extends soCialMeService{	protected $part = 'reader';		protected $define = array(		'id' => 'foursquare',		'name' => 'Foursquare',		'home' => 'http://foursquare.com',		'icon' => 'pf=dcLibFoursquare/icon.png'	);		protected $actions = array(
		'playServerScript' => true,
		'playWidgetContent' => true,
		'playPageContent' => true	);	
	private $oauth = false;
	private $chekins_returned = 20;
	
	protected function init()
	{
		# read facebook app settings for admin side
		$oauth_settings = foursquareUtils::decodeApp('admin');
		
		# Required plugin oAuthManager
		# Used name of parent plugin
		if (!empty($oauth_settings['client_id']) && soCialMeUtils::checkPlugin('oAuthManager','0.3'))
		{
			$this->oauth = oAuthClient::load($this->core,'foursquare',
				array(
					'user_id' => null,
					'plugin_id' => 'soCialMeReader',
					'plugin_name' => __('SoCialMe Reader'),
					'token' => $oauth_settings['client_id'], //app_id
					'secret' => $oauth_settings['client_secret'] //app_secret
				)
			);
		}
		
		if (false === $this->oauth)
		{
			$this->available = false;
			return false;
		}
		
		$this->available = true;
		return true;
	}
	
	public function adminSave($service_id,$admin_url)
	{
		if (!$this->available || $service_id != $this->id) return;
		
		$request_step = !empty($_REQUEST['step']) ? $_REQUEST['step'] : null;
		
		if (!$request_step)
		{
			return;
		}
		elseif ($request_step == 'request')
		{
			$this->oauth->getRequestToken($admin_url.'&step=callback');
		}
		elseif ($request_step == 'callback' && !empty($_REQUEST['code']))
		{
			$this->oauth->getAccessToken($admin_url.'&step=callback','json');
		}
		elseif ($request_step == 'clean')
		{
			$this->oauth->removeToken();
		}
		return;
	}
	
	public function adminForm($service_id,$admin_url)
	{
		if (!$this->available)
		{
			$res = '<p>'.sprintf(__('In order to use %s on your blog, a super admin must register an %s app.'),$this->oauth->config('client_name')).'</p>';
		}
		else
		{
			$admin_url = str_replace('&','&amp;',$admin_url);
			
			$res = '<p>';
			if ($this->oauth->state() == 1)
			{
				$res .= '<a class="button" href="'.$admin_url.'&amp;step=clean">'.sprintf(__('Something went wrong, clean acces of %s from %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';
			}
			elseif ($this->oauth->state() == 2)
			{
				$user = $this->oauth->info('name');
				if ($user)
				{
					$res .= '<p>'.sprintf(__('Your are connected as "%s"'),$user).'</p>';
				}
				$res .= '<a class="button" href="'.$admin_url.'&amp;step=clean">'.sprintf(__('Disconnet %s from %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';
			}
			elseif ($this->oauth->state() == 0)
			{
				$res .= '<a class="button" href="'.$admin_url.'&amp;step=request">'.sprintf(__('Connect %s to %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';
			}
			$res .= '</p>';
		}
		return $res;
	}
	
	# Put last user checkins into cache file
	public function playServerScript($available)
	{
		if (!$this->available || $this->oauth->state() != 2) return;
		
		# cache filename
		$file = $this->core->blog->id.$this->id.'user_checkins';
		
		# check cache expiry
		if((isset($available['Widget']) && in_array($this->id,$available['Widget']) 
		 || isset($available['Page']) && in_array($this->id,$available['Page'])) 
		&& soCialMeCacheFile::expired($file,'enc',$this->cache_timeout))
		{
			$records = null;
			$this->log('Get','playServerScript','user_checkins');
			# call API
			$params = array(
				'limit' => (integer) $this->chekins_returned
			);
			$rsp = foursquareUtils::api($this->oauth,'users/self/checkins',$params);
			
			if ($rsp && $rsp->checkins->count)
			{
				$rs = $rsp->checkins->items;
				
				# Parse response
				$i = 0;
				foreach($rs as $record)
				{
					$records[$i]['service'] = $this->id;
					$records[$i]['author'] = $this->oauth->info('name');
					$records[$i]['source_name'] = $this->name;
					$records[$i]['source_url'] = $this->home;
					$records[$i]['source_icon'] = $this->icon;
					
					$records[$i]['me'] = true;
					$records[$i]['date'] = $record->createdAt;
					$records[$i]['url'] = 'http://foursquare.com/user/'.$this->oauth->info('id').'/checkin/'.$record->id;
					
					if ($record->venue)
					{
						$records[$i]['title'] = sprintf(__('%s went through %s'),$this->oauth->info('name'),$record->venue->name);
					}
					if ($record->shout)
					{
						$records[$i]['content'] = $record->shout;
					}
					elseif ($record->venue && $record->venue->location)
					{
						$content = array();
						if ($record->venue->location->address) $content[] = $record->venue->location->address;
						if ($record->venue->location->address) $content[] = $record->venue->location->city;
						
						$records[$i]['content'] = implode(', ',$content);
					}
					if ($record->venue && $record->venue->categories)
					{
						$records[$i]['avatar'] = $record->venue->categories[0]->icon;
						$records[$i]['icon'] = $record->venue->categories[0]->icon;
					}
					
					$i++;
				}
			}
			
			# Set cache file
			if (empty($records)) {
				soCialMeCacheFile::touch($file,'enc');
			}
			else {
				soCialMeCacheFile::write($file,'enc',soCialMeUtils::encode($records));
			}
		}
	}
	
	public function playWidgetContent()
	{
		return self::parseContent();
	}
	
	public function playPageContent()
	{
		return self::parseContent();
	}
	
	private function parseContent()
	{
		if (!$this->available) return;
		# cache filename
		$file = $this->core->blog->id.$this->id.'user_checkins';
		# Read cache content
		$content = soCialMeCacheFile::read($file,'enc');
		if (empty($content)) return;
		# Parse content
		return soCialMeUtils::decode($content);
	}
}?>