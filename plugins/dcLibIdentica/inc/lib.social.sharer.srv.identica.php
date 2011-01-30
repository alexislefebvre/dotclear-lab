<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibIdentica, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class identicaSoCialMeSharerService extends soCialMeService
{
	protected $part = 'sharer';
	
	protected $define = array(
		'id' => 'identica',
		'name' => 'Identica',
		'home' => 'http://identi.ca',
		'icon' => '/index.php?pf=dcLibIdentica/icon.png'
	);
	
	protected $actions = array(
		'playIconContent' => true,
		'playSmallContent' => true
	);
	
	protected $config = array('via' => '');
	
	protected function init()
	{
		$config = $this->core->blog->settings->dcLibIdentica->soCialMe_sharer;
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
			'via' => !empty($_POST['dcLibIdentica_soCialMe_via']) ? $_POST['dcLibIdentica_soCialMe_via'] : ''
		);
		$config = base64_encode(serialize($this->config));
		
		$this->core->blog->settings->dcLibIdentica->put('soCialMe_sharer',$config);
	}
	
	public function adminForm($service_id,$admin_url)
	{
		$admin_url = str_replace('&','&amp;',$admin_url);
		$via = isset($this->config['via']) ? $this->config['via'] : '';
		
		return  
		'<form id="dcLibIdentica-form" method="post" action="'.$admin_url.'">'.
		'<p><label class="classic">'.__('Your screen name:').'<br />'.
		form::field(array('dcLibIdentica_soCialMe_via'),50,255,$via).
		'</label></p>'.
		'<p class="form-note">'.__('This attributes the shared message to you. Do not use @.').'</p>'.
		'<p><input type="submit" name="save" value="'.__('save').'" />'.
		$this->core->formNonce().'</p>'.
		'</form>';
	}
	
	private function parseContent($type,$record)
	{
		if (!$record || empty($record['title'])) return;
		
		$url = !empty($record['shorturl']) ? $record['shorturl'] : $record['url'];
		$title = html::clean($record['title']);
		
		return soCialMeUtils::preloadBox(soCialMeUtils::easyLink(
			'http://identi.ca//index.php?action=newnotice&amp;status_textarea='.
			urlencode($title).' '.urlencode($url).
			(!empty($this->config['via']) ? ' '.$this->config['via'] : ''),
			$this->name,
			'/index.php?pf=dcLibIdentica/inc/icons/identica-'.$type.'.png'
		));
	}
	
	public function playIconContent($record)
	{
		return $this->parseContent('icon',$record);
	}
	
	public function playSmallContent($record)
	{
		return $this->parseContent('small',$record);
	}
}
?>