<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMeLibMore, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')){return;}# More services// some services that not have full soCialMe librairies yet// temporaly include in "more" soCialMe plugin# deliciousclass deliciousMoreSoCialMeProfilService extends soCialMeService{	protected $part = 'profil';	protected $setting_ns = 'soCialMeLibMore';	protected $setting_id = 'soCialMe_profil_delicious';	protected $available = true;	protected $config = array('uri' => '');	protected $define = array(		'id' => 'delicious',		'name' => 'Delicious',		'home' => 'http://delicious.com',		'icon' => 'pf=soCialMeLibMore/inc/icons/delicious.png'	);	protected $actions = array(		'playIconContent'=>true,		'playSmallContent'=>false,//have it!		'playBigContent'=>true	);		public function adminSave($service_id,$admin_url)	{		if ($service_id != $this->id || empty($_REQUEST['save'])) return;				$this->config = array(			'uri' => !empty($_POST['soCialMe_profil_delicious_uri']) ? $_POST['soCialMe_profil_delicious_uri'] : ''		);		$this->writeSettings();	}		public function adminForm($service_id,$admin_url)	{		$admin_url = str_replace('&','&amp;',$admin_url);				return  		'<form id="soCialMe_profil_delicious-form" method="post" action="'.$admin_url.'">'.		'<p><label class="classic">'.__('Your profil shortname:').'<br />'.		form::field(array('soCialMe_profil_delicious_uri'),50,255,$this->config['uri']).		'</label></p>'.		'<p class="form-note">'.sprintf(__('It appears on %s URL like this: %s'),$this->define['name'],'hhttp://delicious.com/yourname/').'</p>'.		'<p><input type="submit" name="save" value="'.__('save').'" />'.		$this->core->formNonce().'</p>'.		'</form>';	}		public function init() { $this->readSettings(); return true; }	public function parseContent($img) { return empty($this->config['uri']) ? null : soCialMeUtils::preloadBox(soCialMeUtils::easyLink('http://delicious.com/'.$this->config['uri'],$this->name,$this->url.$img,'profil')); }	public function playIconContent() { return $this->parseContent('pf=soCialMeLibMore/inc/icons/delicious.png'); }	public function playSmallContent() { return $this->parseContent('pf=soCialMeLibMore/inc/icons/delicious-small.png'); }	public function playBigContent() { return $this->parseContent('pf=soCialMeLibMore/inc/icons/delicious-big.png'); }}# feedburnerclass feedburnerMoreSoCialMeProfilService extends soCialMeService{	protected $config = array('uri' => '');	protected $part = 'profil';	protected $setting_ns = 'soCialMeLibMore';	protected $setting_id = 'soCialMe_profil_feedburner';	protected $available = true;	protected $define = array(		'id' => 'feedburner',		'name' => 'Feedburner',		'home' => 'http://www.feedburner.com/',		'icon' => 'pf=soCialMeLibMore/inc/icons/feedburner.png'	);	protected $actions = array(		'playIconContent'=>true,		'playBigContent'=>true	);		public function adminSave($service_id,$admin_url)	{		if ($service_id != $this->id || empty($_REQUEST['save'])) return;				$this->config = array(			'uri' => !empty($_POST['soCialMe_profil_feedburner_uri']) ? $_POST['soCialMe_profil_feedburner_uri'] : ''		);		$this->writeSettings();	}		public function adminForm($service_id,$admin_url)	{		$admin_url = str_replace('&','&amp;',$admin_url);				return  		'<form id="soCialMe_profil_feedburner-form" method="post" action="'.$admin_url.'">'.		'<p><label class="classic">'.__('Your profil shortname:').'<br />'.		form::field(array('soCialMe_profil_feedburner_uri'),50,255,$this->config['uri']).		'</label></p>'.		'<p class="form-note">'.sprintf(__('It appears on %s URL like this: %s'),$this->define['name'],'http://feeds.feedburner.com/yourname/').'</p>'.		'<p><input type="submit" name="save" value="'.__('save').'" />'.		$this->core->formNonce().'</p>'.		'</form>';	}		public function init() { $this->readSettings(); return true; }	public function parseContent($img) { return empty($this->config['uri']) ? null : soCialMeUtils::preloadBox(soCialMeUtils::easyLink('http://feeds.feedburner.com/'.$this->config['uri'],$this->name,$this->url.$img,'profil')); }	public function playIconContent() { return $this->parseContent('pf=soCialMeLibMore/inc/icons/feedburner.png'); }	public function playSmallContent() { return $this->parseContent('pf=soCialMeLibMore/inc/icons/feedburner-small.png'); }	public function playBigContent() { return $this->parseContent('pf=soCialMeLibMore/inc/icons/feedburner-big.png'); }}# flickrclass flickrMoreSoCialMeProfilService extends soCialMeService{	protected $config = array('uri' => '');	protected $part = 'profil';	protected $setting_ns = 'soCialMeLibMore';	protected $setting_id = 'soCialMe_profil_flickr';	protected $available = true;	protected $define = array(		'id' => 'flickr',		'name' => 'Flickr',		'home' => 'http://www.flickr.com',		'icon' => 'pf=soCialMeLibMore/inc/icons/flickr.png'	);	protected $actions = array(		'playIconContent'=>true,		'playSmallContent'=>false,//have it!		'playBigContent'=>true	);		public function adminSave($service_id,$admin_url)	{		if ($service_id != $this->id || empty($_REQUEST['save'])) return;				$this->config = array(			'uri' => !empty($_POST['soCialMe_profil_flickr_uri']) ? $_POST['soCialMe_profil_flickr_uri'] : ''		);		$this->writeSettings();	}		public function adminForm($service_id,$admin_url)	{		$admin_url = str_replace('&','&amp;',$admin_url);				return  		'<form id="soCialMe_profil_flickr-form" method="post" action="'.$admin_url.'">'.		'<p><label class="classic">'.__('Your profil shortname:').'<br />'.		form::field(array('soCialMe_profil_flickr_uri'),50,255,$this->config['uri']).		'</label></p>'.		'<p class="form-note">'.sprintf(__('It appears on %s URL like this: %s'),$this->define['name'],'http://www.flickr.com/photos/yourname/').'</p>'.		'<p><input type="submit" name="save" value="'.__('save').'" />'.		$this->core->formNonce().'</p>'.		'</form>';	}		public function init() { $this->readSettings(); return true; }	public function parseContent($img) { return empty($this->config['uri']) ? null : soCialMeUtils::preloadBox(soCialMeUtils::easyLink('http://www.flickr.com/photos/'.$this->config['uri'],$this->name,$this->url.$img,'profil')); }	public function playIconContent() { return $this->parseContent('pf=soCialMeLibMore/inc/icons/flickr.png'); }	public function playSmallContent() { return $this->parseContent('pf=soCialMeLibMore/inc/icons/flickr-small.png'); }	public function playBigContent() { return $this->parseContent('pf=soCialMeLibMore/inc/icons/flickr-big.png'); }}# netvibesclass netvibesMoreSoCialMeProfilService extends soCialMeService{	protected $config = array('uri' => '');	protected $part = 'profil';	protected $setting_ns = 'soCialMeLibMore';	protected $setting_id = 'soCialMe_profil_netvibes';	protected $available = true;	protected $define = array(		'id' => 'netvibes',		'name' => 'Netvibes',		'home' => 'http://www.netvibes.com',		'icon' => 'pf=soCialMeLibMore/inc/icons/netvibes.png'	);	protected $actions = array(		'playIconContent'=>true,		'playSmallContent'=>false,//have it!		'playBigContent'=>true	);		public function adminSave($service_id,$admin_url)	{		if ($service_id != $this->id || empty($_REQUEST['save'])) return;				$this->config = array(			'uri' => !empty($_POST['soCialMe_profil_netvibes_uri']) ? $_POST['soCialMe_profil_netvibes_uri'] : ''		);		$this->writeSettings();	}		public function adminForm($service_id,$admin_url)	{		$admin_url = str_replace('&','&amp;',$admin_url);				return  		'<form id="soCialMe_profil_netvibes-form" method="post" action="'.$admin_url.'">'.		'<p><label class="classic">'.__('Your profil shortname:').'<br />'.		form::field(array('soCialMe_profil_netvibes_uri'),50,255,$this->config['uri']).		'</label></p>'.		'<p class="form-note">'.sprintf(__('It appears on %s URL like this: %s'),$this->define['name'],'http://netvibes.com/yourname').'</p>'.		'<p><input type="submit" name="save" value="'.__('save').'" />'.		$this->core->formNonce().'</p>'.		'</form>';	}		public function init() { $this->readSettings(); return true; }	public function parseContent($img) { return empty($this->config['uri']) ? null : soCialMeUtils::preloadBox(soCialMeUtils::easyLink('http://netvibes.com/'.$this->config['uri'],$this->name,$this->url.$img,'profil')); }	public function playIconContent() { return $this->parseContent('pf=soCialMeLibMore/inc/icons/netvibes.png'); }	public function playSmallContent() { return $this->parseContent('pf=soCialMeLibMore/inc/icons/netvibes-small.png'); }	public function playBigContent() { return $this->parseContent('pf=soCialMeLibMore/inc/icons/netvibes-big.png'); }}?>