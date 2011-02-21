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

if (!defined('DC_RC_PATH')){return;}

# Add twitter to plugin soCialMe (service part)
class twitterSoCialMeProfilService extends soCialMeService
{
	protected $part = 'profil';
	protected $setting_ns = 'dcLibTwitter';
	protected $setting_id = 'soCialMe_profil';
	
	protected $define = array(
		'id' => 'twitter',
		'name' => 'Twitter',
		'home' => 'http://twitter.com',
		'icon' => 'pf=dcLibTwitter/icon.png'
	);
	
	protected $actions = array(
		'playServerScript' => true,
		'playIconContent' => true,
		'playSmallContent' => true,
		'playBigContent' => true,
		'playCardContent' => true,
		'playSmallExtraContent' => true,
		'playBigExtraContent' => true
	);
	
	protected $config = array(
		'badge_color' => 'a',
		//'screen_name' => '',
		'extra_height' =>  '300',
		'extra_width' => '250',
		'extra_small_type' => 0,
		'extra_small_bgcolor' => '#1985B5',
		'extra_small_color' => '#FFFFFF',
		'extra_shell_bgcolor' => '#8EC1DA',
		'extra_shell_color' => '#FFFFFF',
		'extra_tweets_bgcolor' => '#FFFFFF',
		'extra_tweets_color' => '#000000',
		'extra_tweets_lncolor' => '#1985B5',
		'extra_avatars' => false
	);
	
	private $oauth = false;
	
	public function init()
	{
		# Required plugin oAuthManager
		# Used name of parent plugin
		if (soCialMeUtils::checkPlugin('oAuthManager','0.1'))
		{
			$this->oauth = oAuthClient::load($this->core,'twitter',
				array(
					'user_id' => null,
					'plugin_id' => 'soCialMeProfil',
					'plugin_name' => __('SoCialMe Profil'),
					'token' => 'fjWBGGA5qkR009ZikITvQ',
					'secret' => '1Une37GYVs3Xn0zMHAcX5kq1KFfos2uMrwXd5aJ9U'
				)
			);
		}
		
		if (false === $this->oauth)
		{
			$this->available = false;
			return false;
		}
		
		$this->readSettings();
		$this->available = true;
		return true;
	}
	
	public function adminSave($service_id,$admin_url)
	{
		if (!$this->available || $service_id != $this->id) return;
		
		$request_step = !empty($_REQUEST['step']) ? $_REQUEST['step'] : null;
		
		if ($request_step == 'request')
		{
			$this->oauth->getRequestToken($admin_url.'&step=callback');
		}
		elseif ($request_step == 'callback')
		{
			$this->oauth->getAccessToken();
		}
		elseif ($request_step == 'clean')
		{
			$this->oauth->removeToken();
		}
		
		if (!empty($_REQUEST['save']))
		{
			$this->config = array(
				//'screen_name' => !empty($_POST['dcLibTwitter_soCialMe_screen_name']) ? $_POST['dcLibTwitter_soCialMe_screen_name'] : '',
				
				'badge_color' => !empty($_POST['dcLibTwitter_soCialMe_badge_color']) ? $_POST['dcLibTwitter_soCialMe_badge_color'] : 'a',
				
				'extra_small_type' => !empty($_POST['dcLibTwitter_soCialMe_extra_small_type']),
				'extra_small_bgcolor' => !empty($_POST['dcLibTwitter_soCialMe_extra_small_bgcolor']) ? self::color($_POST['dcLibTwitter_soCialMe_extra_small_bgcolor']) : '#8EC1DA',
				'extra_small_color' => !empty($_POST['dcLibTwitter_soCialMe_extra_small_color']) ? self::color($_POST['dcLibTwitter_soCialMe_extra_small_color']) : '#FFFFFF',
				
				'extra_height' => !empty($_POST['dcLibTwitter_soCialMe_extra_height']) ? $_POST['dcLibTwitter_soCialMe_extra_height'] : '300',
				'extra_width' => !empty($_POST['dcLibTwitter_soCialMe_extra_width']) ? $_POST['dcLibTwitter_soCialMe_extra_width'] : '250',
				'extra_shell_bgcolor' => !empty($_POST['dcLibTwitter_soCialMe_extra_shell_bgcolor']) ? self::color($_POST['dcLibTwitter_soCialMe_extra_shell_bgcolor']) : '#8EC1DA',
				'extra_shell_color' => !empty($_POST['dcLibTwitter_soCialMe_extra_shell_color']) ? self::color($_POST['dcLibTwitter_soCialMe_extra_shell_color']) : '#FFFFFF',
				'extra_tweets_bgcolor' => !empty($_POST['dcLibTwitter_soCialMe_extra_tweets_bgcolor']) ? self::color($_POST['dcLibTwitter_soCialMe_extra_tweets_bgcolor']) : '#FFFFFF',
				'extra_tweets_color' => !empty($_POST['dcLibTwitter_soCialMe_extra_tweets_color']) ? self::color($_POST['dcLibTwitter_soCialMe_extra_tweets_color']) : '#000000',
				'extra_tweets_lncolor' => !empty($_POST['dcLibTwitter_soCialMe_extra_tweets_lncolor']) ? self::color($_POST['dcLibTwitter_soCialMe_extra_tweets_lncolor']) : '#1985B5',
				'extra_avatars' => !empty($_POST['dcLibTwitter_soCialMe_extra_avatars']),
			);
			$this->writeSettings();
		}
	}
	
	public function adminForm($service_id,$admin_url)
	{
		if (!$this->available) return;
		
		$admin_url = str_replace('&','&amp;',$admin_url);
		
		$combo_badge_color = array(
			__('Normal') => 'a',
			__('Grey') => 'b',
			__('Dark') => 'c'
		);
		
		$res = '<p>';
		if ($this->oauth->state() == 1)
		{
			$res .= '<a class="button" href="'.$admin_url.'&amp;step=clean">'.sprintf(__('Something went wrong, clean acces of %s from %s'),$this->oauth->config('plugin_name'),$this->oauth->config('client_name')).'</a>';
		}
		elseif ($this->oauth->state() == 2)
		{
			$user = $this->oauth->getScreenName();
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
		
		return  $res.
		'<form id="soCialMeLibTwitter-form" method="post" action="'.$admin_url.'">'.
		//'<p><label class="classic">'.__('Your screen name:').'<br />'.
		//form::field(array('dcLibTwitter_soCialMe_screen_name'),50,255,$this->config['screen_name']).
		//'</label></p>'.
		//'<p class="form-note">'.__('This is your screen name like it appears on your twitter home page URL.').'</p>'.
		'<h4>'.__('Static badges').'</h4>'.
		'<p><label class="classic">'.__('Static badges color:').'<br />'.
		form::combo(array('dcLibTwitter_soCialMe_badge_color'),$combo_badge_color,$this->config['badge_color']).
		'</label></p>'.
		'<h4>'.__('Small  extra badge').'</h4>'.
		'<p><label class="classic">'.
		form::checkbox(array('dcLibTwitter_soCialMe_extra_small_type'),'1',$this->config['extra_small_type']).
		__('Use medium extra badge instead').'</label></p>'.
		'<p><label class="classic">'.__('Background color:').'<br />'.
		form::field(array('dcLibTwitter_soCialMe_extra_small_bgcolor'),6,7,self::color($this->config['extra_small_bgcolor'])).
		'</label></p>'.
		'<p><label class="classic">'.__('Text color:').'<br />'.
		form::field(array('dcLibTwitter_soCialMe_extra_small_color'),6,7,self::color($this->config['extra_small_color'])).
		'</label></p>'.
		'<h4>'.__('Big extra badge').'</h4>'.
		'<p><label class="classic">'.__('Height:').'<br />'.
		form::field(array('dcLibTwitter_soCialMe_extra_height'),5,3,$this->config['extra_height']).
		'</label></p>'.
		'<p><label class="classic">'.__('Width:').'<br />'.
		form::field(array('dcLibTwitter_soCialMe_extra_width'),5,3,$this->config['extra_width']).
		'</label></p>'.
		'<p><label class="classic">'.__('Shell background color:').'<br />'.
		form::field(array('dcLibTwitter_soCialMe_extra_shell_bgcolor'),6,7,self::color($this->config['extra_shell_bgcolor'])).
		'</label></p>'.
		'<p><label class="classic">'.__('Shell text color:').'<br />'.
		form::field(array('dcLibTwitter_soCialMe_extra_shell_color'),6,7,self::color($this->config['extra_shell_color'])).
		'</label></p>'.
		'<p><label class="classic">'.__('Tweets background color:').'<br />'.
		form::field(array('dcLibTwitter_soCialMe_extra_tweets_bgcolor'),6,7,self::color($this->config['extra_tweets_bgcolor'])).
		'</label></p>'.
		'<p><label class="classic">'.__('Tweets text color:').'<br />'.
		form::field(array('dcLibTwitter_soCialMe_extra_tweets_color'),6,7,self::color($this->config['extra_tweets_color'])).
		'</label></p>'.
		'<p><label class="classic">'.__('Tweets link color:').'<br />'.
		form::field(array('dcLibTwitter_soCialMe_extra_tweets_lncolor'),6,7,self::color($this->config['extra_tweets_lncolor'])).
		'</label></p>'.
		'<p><label class="classic">'.
		form::checkbox(array('dcLibTwitter_soCialMe_extra_avatars'),'1',$this->config['extra_avatars']).
		__('Show avatars').'</label></p>'.
		
		'<p><input type="submit" name="save" value="'.__('save').'" />'.
		$this->core->formNonce().'</p>'.
		'</form>';
	}
	
	private function parseContent($type)
	{
		if (!$this->available || $this->oauth->state() != 2) return;
		
		$color = !empty($this->config['badge_color']) ? html::escapeHTML($this->config['badge_color']) : 'a';
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('View my profil on %s'),$this->name),
			'avatar' => 'http://twitter-badges.s3.amazonaws.com/'.$type.'-'.$color.'.png',
			'url' => 'http://www.twitter.com/'.$this->oauth->getScreenName()
		);
		return $record;
	}
	
	public function playIconContent()
	{
		return $this->parseContent('t_mini');
	}
	
	public function playSmallContent()
	{
		return $this->parseContent('twitter');
	}
	
	public function playBigContent()
	{
		return $this->parseContent('t_logo');
	}
	
	public function playSmallExtraContent()
	{
		if (!$this->available || $this->oauth->state() != 2) return;
		
		$content = !empty($this->config['extra_small_type']) ? 
			'<script type="text/javascript" src="http://twittercounter.com/embed/'.$this->oauth->getScreenName().'/'.self::color($this->config['extra_small_color'],false).'/'.self::color($this->config['extra_small_bgcolor'],false).'"></script>' :
			'<script type="text/javascript" src="http://twittercounter.com/embed/?username='.$this->oauth->getScreenName().'&amp;style=bird"></script>';
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => false,
			'content' => $content
		);
		return $record;
		
	}
	
	public function playBigExtraContent()
	{
		if (!$this->available || $this->oauth->state() != 2) return;
		
		$content = "
	<script type=\"text/javascript\" src=\"http://widgets.twimg.com/j/2/widget.js\"></script>
<script type=\"text/javascript\">
new TWTR.Widget({
  version: 2,
  type: 'profile',
  rpp: 4,
  interval: 6000,
  width: ".html::escapeJS($this->config['extra_width']).",
  height: ".html::escapeJS($this->config['extra_height']).",
  theme: {
    shell: {
      background: '".self::color($this->config['extra_shell_bgcolor'])."',
      color: '".self::color($this->config['extra_shell_color'])."'
    },
    tweets: {
      background: '".self::color($this->config['extra_tweets_bgcolor'])."',
      color: '".self::color($this->config['extra_tweets_color'])."',
      links: '".self::color($this->config['extra_tweets_lncolor'])."'
    }
  },
  features: {
    scrollbar: false,
    loop: false,
    live: false,
    hashtags: true,
    timestamp: true,
    avatars: ".($this->config['extra_avatars'] ? 'true' : 'false').",
    behavior: 'all'
  }
}).render().setUser('".html::escapeJS($this->oauth->getScreenName())."').start();
</script>
		";
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => false,
			'content' => $content
		);
		return $record;
	}
	
	# Put last user profil into cache file
	public function playServerScript($available)
	{
		if (!$this->available || $this->oauth->state() != 2) return;
		
		# cache filename
		$file = $this->core->blog->id.$this->id.'user_profil';
		
		# check cache expiry
		if (!isset($available['Card']) || !in_array($this->id,$available['Card']) 
		 || !soCialMeCacheFile::expired($file,'enc',$this->cache_timeout))
		{
			return;
		}

		# Query
		$records = null;
		$this->log('Get','playServerScript','user_profil');
		$params = array(
			'screen_name' => $this->oauth->getScreenName()
		);
		$record = $this->oauth->get('users/show',$params);
		
		# Parse
		if ($record)
		{
			$records[0]['service'] = $this->id;
			$records[0]['author'] = (string) $record->screen_name;
			$records[0]['source_name'] = $this->name;
			$records[0]['source_url'] = $this->home;
			$records[0]['source_icon'] = $this->icon;
			
			$records[0]['me'] = true;
			$records[0]['title'] = (string) $record->screen_name;
			$records[0]['excerpt'] = sprintf(__('View my profil on %s'),$this->name);
			$records[0]['content'] = sprintf(__('%s posts'),(string) $record->statuses_count).', '.sprintf(__('%s friends'),(string) $record->friends_count).', '.sprintf(__('%s followers'),(string) $record->followers_count);
			$records[0]['url'] = 'http://twitter.com/'.((string) $record->screen_name);
			$records[0]['avatar'] = (string) $record->profile_image_url;
			$records[0]['icon'] = (string) $record->profile_image_url;
		}
		
		# Set cache file
		if (empty($records)) {
			soCialMeCacheFile::touch($file,'enc');
		}
		else {
			soCialMeCacheFile::write($file,'enc',soCialMeUtils::encode($records));
		}
	}
	
	public function playCardContent()
	{
		if (!$this->available || $this->oauth->state() != 2) return;
		
		$file = $this->core->blog->id.$this->id.'user_profil';
		$content = soCialMeCacheFile::read($file,'enc');
		if (empty($content)) return;
		
		return soCialMeUtils::decode($content);
	}
	
	private static function color($c,$with=true)
	{
		if ($c == '') {
			$c = '#CCCCCC';
		}
		else
		{
			$c = strtoupper($c);
			
			if (preg_match('/^[A-F0-9]{3,6}$/',$c)) {
				$c = '#'.$c;
			}
			
			if (!preg_match('/^#[A-F0-9]{6}$/',$c) && preg_match('/^#[A-F0-9]{3,}$/',$c)) {
				$c = '#'.substr($c,1,1).substr($c,1,1).substr($c,2,1).substr($c,2,1).substr($c,3,1).substr($c,3,1);
			}
		}
		return $with ? $c : substr($c,1);
	}
}
?>