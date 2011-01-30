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
	
	protected $define = array(
		'id' => 'twitter',
		'name' => 'Twitter',
		'home' => 'http://twitter.com',
		'icon' => '/index.php?pf=dcLibTwitter/icon.png'
	);
	
	protected $actions = array(
		'playIconContent' => true,
		'playSmallContent' => true,
		'playSmallScript' => true,
		'playBigContent' => true,
		'playBigScript' => true
	);
	
	protected $config = array('via' => '');
	private $script_loaded = false; //prevent from loading JS twice
	
	protected function init()
	{
		$config = $this->core->blog->settings->dcLibTwitter->soCialMe_sharer;
		$config = @unserialize(base64_decode($config));
		if (!is_array($config)) $config = array();
		
		$this->config = array_merge($this->config,$config);
		
		$this->available = true;
		return true;
	}
	
	public function adminSave($service_id,$admin_url)
	{
		if ($service_id != $this->id || empty($_REQUEST['save'])) return;
		
		$this->config = array(
			'via' => !empty($_POST['dcLibTwitter_soCialMe_via']) ? $_POST['dcLibTwitter_soCialMe_via'] : ''
		);
		$config = base64_encode(serialize($this->config));
		
		$this->core->blog->settings->dcLibTwitter->put('soCialMe_sharer',$config);
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
	
	private function parseScript()
	{
		if ($this->script_loaded) return '';
		
		$this->script_loaded = true;
		return '<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>';
	}
	
	private function parseContent($type,$record)
	{
		if (!$record || empty($record['title'])) return;
		
		$url = !empty($record['shorturl']) ? $record['shorturl'] : $record['url'];
		$url2 = $url != $record['url'] ? $record['url'] : '';
		$title = html::clean($record['title']);
		
		return 
		'<a href="http://twitter.com/share" class="twitter-share-button" '.
		'data-url="'.$url.'" '.
		(!empty($this->config['via']) ? 'data-via="'.$this->config['via'].'" ' : '').
		'data-text="'.$title.'" '.
		'data-related="dcSoCialMe" '.
		($url2 ? 'data-counturl="'.$url2.'" ' : '').
		'data-count="'.$type.'">'.sprintf(__('Share on %s'),$this->name).'</a>';
	}
	
	public function playIconContent($record)
	{
		if (!$record || empty($record['title'])) return;
		
		$url = !empty($record['shorturl']) ? $record['shorturl'] : $record['url'];
		$title = html::clean($record['title']);
		
		return soCialMeUtils::preloadBox(soCialMeUtils::easyLink('http://twitthis.com/twit?url='.urlencode($url).'&amp;title='.urlencode($title),$this->name,$this->icon));
	}
	
	public function playSmallScript() { return $this->parseScript(); }
	public function playBigScript() { return $this->parseScript(); }
	public function playSmallContent($record) { return $this->parseContent('horizontal',$record); }
	public function playBigContent($record) { return $this->parseContent('vertical',$record); }
}

?>