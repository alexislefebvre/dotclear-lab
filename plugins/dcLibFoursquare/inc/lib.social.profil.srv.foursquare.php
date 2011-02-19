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

if (!defined('DC_RC_PATH')){return;}

# Add Foursquare to plugin soCialMe (service part)
class foursquareSoCialMeProfilService extends soCialMeService
{
	protected $part = 'profil';
	
	protected $define = array(
		'id' => 'foursquare',
		'name' => 'Foursquare',
		'home' => 'http://foursquare.com',
		'icon' => 'pf=dcLibFoursquare/icon.png'
	);
	
	protected $actions = array(
		'playServerScript' => true,
		'playIconContent' => true,
		'playSmallContent' => true,
		'playBigContent' => true,
		'playMediumExtraContent' => true
	);
	
	protected $available = true;
	
	private $oauth = false;
	
	protected function init()
	{
		# read facebook app settings for admin side
		$oauth_settings = foursquareUtils::decodeApp('admin');
		
		# Required plugin oAuthManager
		# Used name of parent plugin
		if (!empty($oauth_settings['client_id']) && soCialMeUtils::checkPlugin('oAuthManager','0.2-alpha1'))
		{
			$this->oauth = oAuthClient::load($this->core,'foursquare',
				array(
					'user_id' => null,
					'plugin_id' => 'soCialMeWriter',
					'plugin_name' => __('SoCialMe Writer'),
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
	
	public function parseContent($img)
	{ 
		return !$this->oauth || !$this->oauth->info('id') ? 
			null : 
			soCialMeUtils::preloadBox(
				soCialMeUtils::easyLink(
					'http://foursquare.com/user/'.$this->oauth->info('id'),
					$this->name,
					$this->url.$img,
					'profil'
				)
			);
	}
	public function playIconContent() { return $this->parseContent('pf=dcLibFoursquare/inc/icons/icon-small.png'); }
	public function playSmallContent() { return $this->parseContent('pf=dcLibFoursquare/inc/icons/icon-medium.png'); }
	public function playBigContent() { return $this->parseContent('pf=dcLibFoursquare/inc/icons/icon-big.png'); }
	
// exemple for user unlock bagdes
	
	# Put user badges into cache file
	public function playServerScript($available)
	{
		if (!$this->available || $this->oauth->state() != 2) return;
		
		#
		# Cache for user badges
		#
		
		# cache filename
		$file_user_badges = $this->core->blog->id.$this->id.'user_badges';
		
		# check cache expiry
		if(isset($available['MediumExtra']) && in_array($this->id,$available['MediumExtra']) 
		&& soCialMeCacheFile::expired($file_user_badges,'enc',$this->cache_timeout))
		{
			# call API
			$rsp = foursquareUtils::api($this->oauth,'users/self/badges');
//echo '<p>rsp:</p><pre style="text-align:left;">'.print_r($rsp,true).'</pre>';exit(1);
			if ($rsp && $rsp->badges)
			{
				$rs = $rsp->badges;
				
				# Parse response
				$records = null;
				$i = 0;
				foreach($rs as $record)
				{
					$unlock = $record->unlocks;
					if (empty($unlock)) continue;
					
					$records[$i]['service'] = $this->id;
					$records[$i]['author'] = $this->oauth->info('name');
					$records[$i]['source_name'] = $this->name;
					$records[$i]['source_url'] = $this->home;
					$records[$i]['source_icon'] = $this->icon;
					
					$records[$i]['me'] = true;
					$records[$i]['date'] = $record->unlocks[0]->checkins[0]->createdAt;
					$records[$i]['url'] = 'http://foursquare.com/user/'.$this->oauth->info('id').'/bagdes/'.$record->id;
					$records[$i]['title'] = sprintf(__('%s has got the %s badge'),$this->oauth->info('name'),$record->name);
					$records[$i]['content'] = $record->description;
					$records[$i]['avatar'] = $record->image->prefix.$recorf->image->size[1].$record->image->name;
					$records[$i]['icon'] = $record->image->prefix.$recorf->image->size[0].$record->image->name;
					
					$i++;
				}
				# Create cache file
				if (!empty($records)) {
					soCialMeCacheFile::write($file_user_badges,'enc',soCialMeUtils::encode($records));
				}
			}
		}
	}
	
	# List from cache file user unlock badges on soCialMe "medium extra content"
	public function playMediumExtraContent()
	{
		if (!$this->available) return;
		# cache filename
		$file = $this->core->blog->id.$this->id.'user_badges';
		# Read cache content
		$content = soCialMeCacheFile::read($file,'enc');
		if (empty($content)) return;
		# Parse content
		$rs = soCialMeUtils::decode($content);
//echo '<p>rs:</p><pre style="text-align:left;">'.print_r($rs,true).'</pre>';exit(1);
		if (empty($rs)) return;
		
		$res = '';
		
		foreach($rs as $record)
		{
			$res .=
			'<div class="foursquare-badge">'.
			'<img src="'.$record['icon'].'" alt="'.$record['title'].'" /> '.
			'<strong>'.$record['title'].'</strong><br />'.
			$record['content'].'<br />'.
			'<em>'.dt::str($this->core->blog->settings->system->date_format.', '.$this->core->blog->settings->system->time_format,$record['date']).'</em>'.
			'</div>';
		}
		return $res;
	}
}
?>