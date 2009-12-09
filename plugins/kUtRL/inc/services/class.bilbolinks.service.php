<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class bilbolinksKutrlService extends kutrlServices
{
	public $id = 'bilbolinks';
	public $name = 'BilboLinks';
	public $home = 'http://www.tux-planet.fr/bilbobox/';

	private $url_api = '';
	private $url_test = 'http://dotclear.jcdenis.com/go/kUtRL';

	public function __construct($core,$limit_to_blog=true)
	{
		parent::__construct($core,$limit_to_blog);

		$base = (string) $this->s->kutrl_srv_bilbolinks_base;
		if (!empty($base) && substr($base,-1,1) != '/') $base .= '/';

		$this->url_api = $base.'api.php';
		$this->url_base = $base;
		$this->url_min_length = 25;
	}

	public function saveSettings()
	{
		$base = '';
		if (!empty($_POST['kutrl_srv_bilbolinks_base']))
		{
			$base = $_POST['kutrl_srv_bilbolinks_base'];
			if (substr($base,-1,1) != '/') $base .= '/';
		}

		$this->s->setNameSpace('kUtRL');
		$this->s->put('kutrl_srv_bilbolinks_base',$base);
		$this->s->setNameSpace('system');
	}

	public function settingsForm()
	{
		echo 
	    '<p><label class="classic">'.
		__('Url of the service:').'<br />'.
	    form::field(array('kutrl_srv_bilbolinks_base'),50,255,$this->s->kutrl_srv_bilbolinks_base).
		'</label></p>'.
	    '<p class="form-note">'.
	    __('This is the root URL of the "bilbolinks" service you want to use. Ex: "http://tux-pla.net/".').
	    '</p>';
	}

	public function testService()
	{
		if (empty($this->url_base)) return false;

		$arg = array('longurl' => urlencode($this->url_test));
		if (!self::post($this->url_api,$arg,true,true))
		{
			return false;
		}
		return true;
	}

	public function createHash($url,$hash=null)
	{
		$arg = array('longurl' => $url);

		if (!($response = self::post($this->url_api,$arg,true,true)))
		{
			return false;
		}
		if ($response == 'You are too speed!')
		{
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