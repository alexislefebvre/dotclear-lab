<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLibFacebook, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# fblike
class fblikeSoCialMeSharerService extends soCialMeService
{
	protected $setting_ns = 'dcLibFacebook';
	protected $setting_id = 'soCialMe_sharer';
	protected $config = array('colorscheme' => 'light');
	
	public function init()
	{
		$this->part = 'sharer';
		
		$this->define = array(
			'id' => 'facebook',
			'name' => 'Facebook like',
			'home' => 'http://facebook.com',
			'icon' => 'pf=dcLibFacebook/icon.png'
		);
		$this->actions = array(
			'playIconContent' => true,
			'playSmallContent' => true,
			'playBigContent' => true
		);
		
		$this->readSettings();
		$this->available = true;
		return true;
	}
	
	public function adminSave($service_id,$admin_url)
	{
		if ($service_id != $this->id || empty($_REQUEST['save'])) return;
		
		$this->config = array(
			'colorscheme' => !empty($_POST['dcLibFacebook_colorscheme']) ? $_POST['dcLibFacebook_colorscheme'] : ''
		);
		$this->writeSettings();
	}
	
	public function adminForm($service_id,$admin_url)
	{
		$admin_url = str_replace('&','&amp;',$admin_url);
		$combo_color = array(
			__('Light') => 'light',
			__('Dark') => 'dark'
		);
		$colorscheme = isset($this->config['colorscheme']) ? $this->config['colorscheme'] : 'light';
		
		return  
		'<form id="dcLibFacebook-form" method="post" action="'.$admin_url.'">'.
	    '<p><label>'.__('Colors scheme:').'<br />'.
	    form::combo(array('dcLibFacebook_colorscheme'),$combo_color,$colorscheme).
		'</label></p>'.
		'<p class="form-note">'.__('This is the color scheme for the small and big buttons.').'</p>'.
		'<p><input type="submit" name="save" value="'.__('save').'" />'.
		$this->core->formNonce().'</p>'.
		'</form>';
	}
	
	public function playIconContent($record)
	{
		if (!$record) return;
		$url = !empty($record['shorturl']) ? $record['shorturl'] : $record['url'];
		
		return soCialMeUtils::preloadBox(soCialMeUtils::easyLink('http://www.facebook.com/share.php?u='.urlencode($url),$this->name,$this->icon));
	}
	
	public function playBigContent($record)
	{
		return $this->parseContent('box_count',$record);
	}
	
	public function playSmallContent($record)
	{
		return $this->parseContent('button_count',$record);
	}
	
	private function parseContent($type,$record)
	{
		if (!$record) return;
		$url = !empty($record['shorturl']) ? $record['shorturl'] : $record['url'];
		
		if ($type == 'button_count') {
			$w = 90; $h = 20;
		}
		else {
			$w = 60; $h = 62;
		}
		
		return soCialMeUtils::preloadBox(
		'<iframe src="http://www.facebook.com/plugins/like.php?'.
		'href='.urlencode($url).
		'&amp;layout='.$type.
		'&amp;show_faces=false'.
		'&amp;width='.$w.
		'&amp;action=like'.
		'&amp;colorscheme='.(!empty($this->config['colorscheme']) ? $this->config['colorscheme'] : 'light').
		'&amp;height='.$h.'" '.
		'style="border:none; overflow:hidden; width:'.$w.'px; height:'.$h.'px;" '.
		'scrolling="no" frameborder="0" allowTransparency="true"></iframe>');
	}
}
?>