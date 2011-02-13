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
	
	protected $define = array(
		'id' => 'twitter',
		'name' => 'Twitter',
		'home' => 'http://twitter.com',
		'icon' => 'pf=dcLibTwitter/icon.png'
	);
	
	protected $actions = array(
		'playIconContent' => true,
		'playSmallContent' => true,
		'playBigContent' => true,
		'playSmallExtraContent' => true,
		'playMediumExtraContent' => true,
		'playBigExtraContent' => true
	);
	
	protected $available = true;
	
	protected $config = array(
		'badge_color' => 'a',
		'screen_name'=>'',
		'extra_height' =>  '300',
		'extra_width' => '250',
		'extra_small_bgcolor' => '#1985B5',
		'extra_small_color' => '#FFFFFF',
		'extra_shell_bgcolor' => '#8EC1DA',
		'extra_shell_color' => '#FFFFFF',
		'extra_tweets_bgcolor' => '#FFFFFF',
		'extra_tweets_color' => '#000000',
		'extra_tweets_lncolor' => '#1985B5',
		'extra_avatars' => false
	);
	private $JS_loaded = false; //prevent from loading JS twice
	
	public function init()
	{
		$this->core->blog->settings->addNamespace('dcLibTwitter');
		$config = $this->core->blog->settings->dcLibTwitter->soCialMe_profil;
		$config = soCialMeUtils::decode($config);
		
		$this->config = array_merge($this->config,$config);
		
		return true;
	}
	
	public function adminSave($service_id,$admin_url)
	{
		if ($service_id != $this->id || empty($_REQUEST['save'])) return;
		
		$this->config = array(
			'screen_name' => !empty($_POST['dcLibTwitter_soCialMe_screen_name']) ? $_POST['dcLibTwitter_soCialMe_screen_name'] : '',
			
			'badge_color' => !empty($_POST['dcLibTwitter_soCialMe_badge_color']) ? $_POST['dcLibTwitter_soCialMe_badge_color'] : 'a',
			
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
		$config = soCialMeUtils::encode($this->config);
		
		$this->core->blog->settings->dcLibTwitter->put('soCialMe_profil',$config);
	}
	
	public function adminForm($service_id,$admin_url)
	{
		$admin_url = str_replace('&','&amp;',$admin_url);
		
		$combo_badge_color = array(
			__('Normal') => 'a',
			__('Grey') => 'b',
			__('Dark') => 'c'
		);
		
		return  
		'<form id="soCialMeLibTwitter-form" method="post" action="'.$admin_url.'">'.
		'<p><label class="classic">'.__('Your screen name:').'<br />'.
		form::field(array('dcLibTwitter_soCialMe_screen_name'),50,255,$this->config['screen_name']).
		'</label></p>'.
		'<p class="form-note">'.__('This is your screen name like it appears on your twitter home page URL.').'</p>'.
		'<h4>'.__('Static badges').'</h4>'.
		'<p><label class="classic">'.__('Static badges color:').'<br />'.
		form::combo(array('dcLibTwitter_soCialMe_badge_color'),$combo_badge_color,$this->config['badge_color']).
		'</label></p>'.
		'<h4>'.__('Small  extra badge').'</h4>'.
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
	
	private function parseStaticContent($type)
	{
		if (empty($this->config['screen_name'])) return '';
		
		$color = !empty($this->config['badge_color']) ? html::escapeHTML($this->config['badge_color']) : 'a';
		$name = $this->config['screen_name'];
		$text = sprintf(__('Follow %s on Twitter'),html::escapeHTML($this->config['screen_name']));
		
		return 
		'<a href="http://www.twitter.com/'.$name.'" '.'title="'.$text.'">'.
		'<img src="http://twitter-badges.s3.amazonaws.com/'.
		$type.'-'.$color.'.png" alt="'.$text.'"/></a>';
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
	
	public function playIconContent()
	{
		return $this->parseStaticContent('t_mini');
	}
	
	public function playSmallContent()
	{
		return $this->parseStaticContent('twitter');
	}
	
	public function playBigContent()
	{
		return $this->parseStaticContent('t_logo');
	}
	
	public function playSmallExtraContent()
	{
		if (!$this->config['screen_name']) return;
		
		return '<script type="text/javascript" src="http://twittercounter.com/embed/'.$this->config['screen_name'].'/'.self::color($this->config['extra_small_color'],false).'/'.self::color($this->config['extra_small_bgcolor'],false).'"></script>';
	}
	
	public function playMediumExtraContent()
	{
		if (!$this->config['screen_name']) return;
		
		return '<script type="text/javascript" src="http://twittercounter.com/embed/?username='.$this->config['screen_name'].'&amp;style=bird"></script>';
	}
	
	public function playBigExtraContent()
	{
		if (!$this->config['screen_name']) return;
		
		return "
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
}).render().setUser('".html::escapeJS($this->config['screen_name'])."').start();
</script>
		";
	}
}
?>