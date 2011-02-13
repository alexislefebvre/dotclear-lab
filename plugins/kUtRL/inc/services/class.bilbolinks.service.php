<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class bilbolinksKutrlService extends kutrlService
{
	protected $config = array(
		'id' => 'bilbolinks',
		'name' => 'BilboLinks',
		'home' => 'http://www.tux-planet.fr/bilbobox/'
	);
	
	protected function init()
	{
		$base = (string) $this->settings->kutrl_srv_bilbolinks_base;
		if (!empty($base) && substr($base,-1,1) != '/') $base .= '/';
		
		$this->config['url_api'] = $base.'api.php';
		$this->config['url_base'] = $base;
		$this->config['url_min_len'] = 25;
	}
	
	public function saveSettings()
	{
		$base = '';
		if (!empty($_POST['kutrl_srv_bilbolinks_base']))
		{
			$base = $_POST['kutrl_srv_bilbolinks_base'];
			if (substr($base,-1,1) != '/') $base .= '/';
		}
		
		$this->settings->put('kutrl_srv_bilbolinks_base',$base);
	}
	
	public function settingsForm()
	{
		echo 
	    '<p><label class="classic">'.
		__('Url of the service:').'<br />'.
	    form::field(array('kutrl_srv_bilbolinks_base'),50,255,$this->settings->kutrl_srv_bilbolinks_base).
		'</label></p>'.
	    '<p class="form-note">'.
	    __('This is the root URL of the "bilbolinks" service you want to use. Ex: "http://tux-pla.net/".').
	    '</p>';
	}
	
	public function testService()
	{
		if (empty($this->url_base))
		{
			$this->error->add(__('Service is not well configured.'));
			return false;
		}
		
		$arg = array('longurl' => urlencode($this->url_test));
		if (!self::post($this->url_api,$arg,true,true))
		{
			$this->error->add(__('Service is unavailable.'));
			return false;
		}
		return true;
	}
	
	public function createHash($url,$hash=null)
	{
		$arg = array('longurl' => $url);
		
		if (!($response = self::post($this->url_api,$arg,true,true)))
		{
			$this->error->add(__('Service is unavailable.'));
			return false;
		}
		if ($response == 'You are too speed!')
		{
			$this->error->add(__('Service rate limit exceeded.'));
			return false;
		}
		
		$rs = new ArrayObject();
		$rs->hash = str_replace($this->url_base,'',$response);
		$rs->url = $url;
		$rs->type = $this->id;
		
		return $rs;
	}
}
?>