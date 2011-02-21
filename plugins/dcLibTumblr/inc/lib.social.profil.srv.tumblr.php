<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibTumblr, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# Add Tumblr to plugin soCialMe (profil part)
class tumblrSoCialMeProfilService extends soCialMeService
{
	protected $part = 'profil';
	protected $setting_ns = 'dcLibTumblr';
	protected $setting_id = 'soCialMe_profil';
	
	protected $define = array(
		'id' => 'tumblr',
		'name' => 'Tumblr',
		'home' => 'http://tumblr.com',
		'icon' => 'pf=dcLibTumblr/icon.png'
	);
	
	protected $actions = array(
		'playServerScript' => true,
		'playIconContent' => true,
		'playSmallContent' => true,
		'playBigContent' => true,
		'playCardContent' => true
	);
	
	protected $config = array(
		'email'=>'',
		'password'=>'',
		'id'=>''
	);
	
	public function init()
	{
		$this->readSettings();
		$this->available = true;
		return true;
	}
	
	public function adminSave($service_id,$admin_url)
	{
		if ($service_id != $this->id || empty($_REQUEST['save'])) return;
		
		if (!empty($_POST['dcLibTumblr_mail']))
		{
			if (!empty($_POST['dcLibTumblr_pass']))
			{
				$this->config['password'] = $_POST['dcLibTumblr_pass'];
			}
			elseif ($_POST['dcLibTumblr_mail'] != $this->config['email'])
			{
				$this->config['password'] = '';
			}
			$this->config['email'] = $_POST['dcLibTumblr_mail'];
			$this->config['id'] = $_POST['dcLibTumblr_id'];
		}
		else
		{
			$this->config['email'] = $this->config['password'] = $this->config['id'] = '';
		}
		$this->writeSettings();
	}
	
	public function adminForm($service_id,$admin_url)
	{
		$admin_url = str_replace('&','&amp;',$admin_url);
		
		return  
		'<form id="dcLibTumblr-form" method="post" action="'.$admin_url.'">'.
		'<p><label class="classic">'.__('URL ID:').'<br />'.
		form::field('dcLibTumblr_id',50,255,$this->config['id']).
		'</label></p>'.
		'<p class="form-note">'.__('This is your name in the URL of your Tumblr blog. http://YOUR_ID.tumblr.com').'</p>'.
		'<p><label class="classic">'.__('Email:').'<br />'.
		form::field('dcLibTumblr_mail',50,255,$this->config['email']).
		'</label></p>'.
		'<p><label class="classic">'.__('Password:').'<br />'.
		form::password('dcLibTumblr_pass',50,255,'').
		'</label></p>'.
		'<p><input type="submit" name="save" value="'.__('save').'" />'.
		$this->core->formNonce().'</p>'.
		'</form>';
	}
	
	public function parseContent($img)
	{
		if (!$this->available || empty($this->config['email']) || empty($this->config['password']) || empty($this->config['id'])) return;
		
		$record[0] = array(
			'service' => $this->id,
			'source_name' => $this->name,
			'source_url' => $this->home,
			'source_icon' => $this->icon,
			'preload' => true,
			'title' => sprintf(__('View my profil on %s'),$this->name),
			'avatar' => $this->url.$img,
			'url' => 'http://'.$this->config['id'].'.tumblr.com'
		);
		return $record;
	}
	
	public function playIconContent() { return $this->parseContent('pf=dcLibTumblr/inc/icons/icon-small.png'); }
	public function playSmallContent() { return $this->parseContent('pf=dcLibTumblr/inc/icons/icon-medium.png'); }
	public function playBigContent() { return $this->parseContent('pf=dcLibTumblr/inc/icons/icon-big.png'); }
	
	# Put last user profil into cache file
	public function playServerScript($available)
	{
		if (!$this->available || empty($this->config['email']) || empty($this->config['password']) || empty($this->config['id'])) return;
		
		# cache filename
		$file = $this->core->blog->id.$this->id.'user_profil';
		
		# check cache expiry
		if (!isset($available['Card']) || !in_array($this->id,$available['Card']) 
		 || !soCialMeCacheFile::expired($file,'enc',$this->cache_timeout))
		{
			return;
		}
		
		$records = null;
		$this->log('Get','playServerScript','user_profil');
		$rs = $this->query('http://www.tumblr.com/api/authenticate');
		
		if ($rs && $rs->tumblelog)
		{
			$record = $rs->tumblelog;
			
			$records[0]['service'] = $this->id;
			$records[0]['author'] = (string) $record->name;
			$records[0]['source_name'] = $this->name;
			$records[0]['source_url'] = $this->home;
			$records[0]['source_icon'] = $this->icon;
			
			$records[0]['me'] = true;
			$records[0]['title'] = (string) $record['title'][0];
			$records[0]['excerpt'] = sprintf(__('View my profil on %s'),$this->name);
			$records[0]['content'] = sprintf(__('%s posts'),(string) $record['posts'][0]).', '.sprintf(__('%s followers'),(string) $record['followers'][0]);
			$records[0]['url'] = (string) $record['url'][0];
			$records[0]['avatar'] = preg_replace('/^(.*?)_([0-9]+)\.([a-z]{3})$/','\1_64.\3',(string) $record['avatar-url'][0]);
			$records[0]['icon'] = preg_replace('/^(.*?)_([0-9]+)\.([a-z]{3})$/','\1_16.\3',(string) $record['avatar-url'][0]);
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
		if (!$this->available) return;
		
		$file = $this->core->blog->id.$this->id.'user_profil';
		$content = soCialMeCacheFile::read($file,'enc');
		if (empty($content)) return;
		
		return soCialMeUtils::decode($content);
	}
	
	private function query($url,$data=array())
	{
		if (empty($this->config['email']) || empty($this->config['password']) || empty($this->config['id'])) return;
		
		$data = array_merge($data,array(
			'email'     => $this->config['email'],
			'password'  => $this->config['password'],
			'generator' => 'soCialMeWriter'
		));
		
		$client = netHttp::initClient($url,$url);
		$client->setUserAgent('soCialMeProfil');
		$client->setPersistReferers(false);
		
		$client->post($url,$data);
		if ($client->getStatus() != 200) return;
		
		$rsp = $client->getContent();
		$rs = @simplexml_load_string($rsp);
		
		return $rs ? $rs : null;
		
	}
}
?>