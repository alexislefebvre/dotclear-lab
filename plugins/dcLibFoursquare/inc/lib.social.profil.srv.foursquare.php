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
		'playCardContent' => true
	);
	
	private $oauth = false;
	
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
		if (!$this->oauth || !$this->oauth->info('id')) return;
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('View my profil on %s'),$this->name),
			'avatar' => $this->url.$img,
			'url' => 'http://foursquare.com/user/'.$this->oauth->info('id')
		);
		return $record;
	}
	public function playIconContent() { return $this->parseContent('pf=dcLibFoursquare/inc/icons/icon-small.png'); }
	public function playSmallContent() { return $this->parseContent('pf=dcLibFoursquare/inc/icons/icon-medium.png'); }
	public function playBigContent() { return $this->parseContent('pf=dcLibFoursquare/inc/icons/icon-big.png'); }
	
// exemple for user unlock bagdes
	
	# Put user badges into cache file
	public function playServerScript($available)
	{
		if (!$this->available || $this->oauth->state() != 2) return;
		
		# cache filename
		$file = $this->core->blog->id.$this->id.'user_profil_and_badges';
		
		# check cache expiry
		if(isset($available['Card']) && in_array($this->id,$available['Card']) 
		 && soCialMeCacheFile::expired($file,'enc',$this->cache_timeout))
		{
			$i = 0;
			$records = null;
			$this->log('Get','playServerScript','user_profil_and_badges');
			
			// profil
			$rsp = foursquareUtils::api($this->oauth,'users/self');
			
			if ($rsp && $rsp->user)
			{
				$rs = $rsp->user;
				
				$records[$i]['service'] = $this->id;
				$records[$i]['author'] = $this->oauth->info('name');
				$records[$i]['source_name'] = $this->name;
				$records[$i]['source_url'] = $this->home;
				$records[$i]['source_icon'] = $this->icon;
				
				$records[$i]['me'] = true;
				$records[$i]['url'] = 'http://foursquare.com/user/'.$rs->id;
				$records[$i]['title'] = $rs->firstName.' '.$rs->lastName;
				$records[$i]['excerpt'] = sprintf(__('View my profil on %s'),$this->name);
				$records[$i]['content'] = sprintf(__('%s checkins, %s badges'),$rs->checkins->count,$rs->badges->count);
				$records[$i]['avatar'] = 'http://playfoursquare.s3.amazonaws.com/userpix_thumbs/QENPJKQ33TFQLNRS.png';
				$records[$i]['icon'] = 'http://playfoursquare.s3.amazonaws.com/userpix_thumbs/QENPJKQ33TFQLNRS.png';
				
				$i++;
			}
			
			// badges
			$rsp = foursquareUtils::api($this->oauth,'users/self/badges');
			
			if ($rsp && $rsp->badges)
			{
				$rs = $rsp->badges;
				
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
					$records[$i]['excerpt'] = sprintf(__('View this badge on %s'),$this->name);
					$records[$i]['content'] = $record->description;
					$records[$i]['avatar'] = $record->image->prefix.$recorf->image->size[1].$record->image->name;
					$records[$i]['icon'] = $record->image->prefix.$recorf->image->size[0].$record->image->name;
					
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
	
	# List from cache file user unlock badges on soCialMe "card content"
	public function playCardContent()
	{
		if (!$this->available) return;
		
		$file = $this->core->blog->id.$this->id.'user_profil_and_badges';
		$content = soCialMeCacheFile::read($file,'enc');
		if (empty($content)) return;
		
		return soCialMeUtils::decode($content);
	}
}
?>