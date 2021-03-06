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

if (!defined('DC_RC_PATH')){return;}# More services// some services that not have full soCialMe librairies yet// temporaly include in "more" soCialMe plugin
# feedclass feedMoreSoCialMeReaderService extends soCialMeService{
	protected $part = 'reader';
	protected $setting_ns = 'soCialMeLibMore';
	protected $setting_id = 'soCialMe_reader_feed';
		protected $define = array(		'id' => 'feed',		'name' => 'Feed',		'home' => '',		'icon' => 'pf=soCialMeLibMore/inc/icons/feed.png'	);	
	protected $actions = array(		'playServerScript' => true,		'playWidgetContent' => true,		'playPageContent' => true	);
	
	protected $config = array(
		'feed' => ''
	);
		public function init() { $this->readSettings(); $this->available = true; return true; }	public function playWidgetContent() { return self::parseContent(); }	public function playPageContent() { return self::parseContent(); }		public function adminSave($service_id,$admin_url)	{		if ($service_id != $this->id || empty($_REQUEST['save'])) return;				$this->config = array(			'feed' => !empty($_POST['soCialMe_reader_feed_feed']) ? trim($_POST['soCialMe_reader_feed_feed']) : ''		);		$this->writeSettings();	}		public function adminForm($service_id,$admin_url)	{		$admin_url = str_replace('&','&amp;',$admin_url);				return 		'<form id="soCialMe_reader_feed-form" method="post" action="'.$admin_url.'">'.		'<p><label class="classic">'.__('URL of Atom or RSS feed to read:').'<br />'.		form::textarea(array('soCialMe_reader_feed_feed'),60,10,html::escapeHTML($this->config['feed'])).		'</label></p>'.		'<p class="form-note">'.__('Put one feed per line.').'</p>'.		'<p><input type="submit" name="save" value="'.__('save').'" />'.		$this->core->formNonce().'</p>'.		'</form>';	}		public function playServerScript($available)	{		if (empty($this->config['feed'])) return '';				$file = $this->core->blog->id.$this->id.'feed_content';				if(!isset($available['Widget']) || !in_array($this->id,$available['Widget'])  		 || soCialMeCacheFile::expired($file,'enc',$this->cache_timeout))		{
			return;
		}		
		$i = 0;
		$records = null;
		$this->log('Get','playServerScript','feed_content');				$feeds = explode("\n",trim($this->config['feed']));		foreach($feeds as $feed)		{			$feed = trim($feed);			if (empty($feed)) continue;						try			{				$feed_reader = new feedReader;				$feed_reader->setCacheDir(DC_TPL_CACHE);				$feed_reader->setTimeout(2);				$feed_reader->setUserAgent('soCialMeReader');				$f = $feed_reader->parse($feed);								if (!$f->items) continue;								foreach($f->items as $item)				{					$records[$i]['author'] = $item->creator;					$records[$i]['service'] = $this->id;					$records[$i]['date'] = $item->TS;					$records[$i]['title'] = $item->title;					$records[$i]['content'] = $item->content;					$records[$i]['url'] = $item->link;					$records[$i]['source_name'] = $f->title;					$records[$i]['source_url'] = $f->link;					$records[$i]['source_icon'] = $this->icon;										$i++;				}			}			catch (Exception $e) {}		}
		
		# Set cache file
		if (empty($records)) {
			soCialMeCacheFile::touch($file,'enc');
		}
		else {
			soCialMeCacheFile::write($file,'enc',soCialMeUtils::encode($records));
		}	}		private function parseContent()	{		if (empty($this->config['feed'])) return '';				$file = $this->core->blog->id.$this->id.'feed_content';		$content = soCialMeCacheFile::read($file,'enc');		if (empty($content)) return;				return soCialMeUtils::decode($content);	}}?>