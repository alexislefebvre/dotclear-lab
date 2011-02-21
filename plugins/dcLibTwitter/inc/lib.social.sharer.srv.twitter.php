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

# Add twitter to plugin soCialMe (sharer part)
class twitterSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	protected $setting_ns = 'dcLibTwitter';
	protected $setting_id = 'soCialMe_sharer';
	
	protected $define = array(
		'id' => 'twitter',
		'name' => 'Twitter',
		'home' => 'http://twitter.com',
		'icon' => 'pf=dcLibTwitter/icon.png'
	);
	
	protected $actions = array(
		'playPublicScript' => true,
		'playIconContent' => true,
		'playSmallContent' => true,
		'playBigContent' => true
	);
	
	protected $config = array(
		'via' => ''
	);
	
	protected function init()
	{
		$this->readSettings();
		$this->available = true;
		return true;
	}
	
	public function adminSave($service_id,$admin_url)
	{
		if ($service_id != $this->id || empty($_REQUEST['save'])) return;
		
		$this->config = array(
			'via' => !empty($_POST['dcLibTwitter_soCialMe_via']) ? $_POST['dcLibTwitter_soCialMe_via'] : ''
		);
		$this->writeSettings();
	}
	
	public function adminForm($service_id,$admin_url)
	{
		$admin_url = str_replace('&','&amp;',$admin_url);
		$via = isset($this->config['via']) ? $this->config['via'] : '';
		
		return  
		'<form id="dcLibTwitter-form" method="post" action="'.$admin_url.'">'.
		'<p><label class="classic">'.__('Your screen name:').'<br />'.
		form::field(array('dcLibTwitter_soCialMe_via'),50,255,$via).
		'</label></p>'.
		'<p class="form-note">'.__('This attributes the shared tweet to you. Do not use @.').'</p>'.
		'<p><input type="submit" name="save" value="'.__('save').'" />'.
		$this->core->formNonce().'</p>'.
		'</form>';
	}
	
	public function playPublicScript($available)
	{
		if (isset($available['Small']) && in_array($this->id,$available['Small']) 
		 || isset($available['Big']) && in_array($this->id,$available['Big']))
		{
			return '<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>';
		}
	}
	
	private function parseContent($type,$post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$url2 = $url != $post['url'] ? $post['url'] : '';
		$title = html::clean($post['title']);
		
		$content =  
		'<a href="http://twitter.com/share" class="twitter-share-button" '.
		'data-url="'.$url.'" '.
		(!empty($this->config['via']) ? 'data-via="'.$this->config['via'].'" ' : '').
		'data-text="'.$title.'" '.
		'data-related="dcSoCialMe" '.
		($url2 ? 'data-counturl="'.$url2.'" ' : '').
		'data-count="'.$type.'">'.sprintf(__('Share on %s'),$this->name).'</a>';
		
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
	
	public function playIconContent($post)
	{
		if (!$post || empty($post['title'])) return;
		
		$url = !empty($post['shorturl']) ? $post['shorturl'] : $post['url'];
		$title = html::clean($post['title']);
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('Share on %s'),$this->name),
			'avatar' => $this->icon,
			'url' => 'http://twitthis.com/twit?url='.urlencode($url).'&amp;title='.urlencode($title)
		);
		return $record;
	}
	
	public function playSmallContent($post) { return $this->parseContent('horizontal',$post); }
	public function playBigContent($post) { return $this->parseContent('vertical',$post); }
}

?>