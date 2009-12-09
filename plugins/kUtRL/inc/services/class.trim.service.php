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

class trimKutrlService extends kutrlServices
{
	public $core;

	public $id = 'trim';
	public $name = 'tr.im';
	public $home = 'http://tr.im';

	private $url_api = 'http://api.tr.im/v1/';
	private $api_rate_time = 0;

	public function __construct($core,$limit_to_blog=true)
	{
		parent::__construct($core,$limit_to_blog);

		$this->args['username'] = $this->s->kutrl_srv_trim_username;
		$this->args['password'] = $this->s->kutrl_srv_trim_password;

		$this->url_base = 'http://tr.im/';
		$this->url_min_length = 25;
		$this->api_rate_time = (integer) $this->s->kutrl_srv_trim_apiratetime;
	}

	public function saveSettings()
	{
		$this->s->setNameSpace('kUtRL');
		$this->s->put('kutrl_srv_trim_username',$_POST['kutrl_srv_trim_username']);
		$this->s->put('kutrl_srv_trim_password',$_POST['kutrl_srv_trim_password']);
		$this->s->setNameSpace('system');
	}

	public function settingsForm()
	{
		echo 
		'<p><label class="classic">'.__('Login:').'<br />'.
		form::field(array('kutrl_srv_trim_username'),50,255,$this->s->kutrl_srv_trim_username).
		'</label></p>'.
		'<p class="form-note">'.
		__('This is your login to sign up to tr.im.').
		'</p>'.
		'<p><label class="classic">'.__('Password:').'<br />'.
		form::field(array('kutrl_srv_trim_password'),50,255,$this->s->kutrl_srv_trim_password).
		'</label></p>'.
		'<p class="form-note">'.
		__('This is your password to sign up to tr.im.').
		'</p>';
	}

	public function testService()
	{
		if (empty($this->args['username']) || empty($this->args['password'])) return false;
		if (time() < $this->api_rate_time + 300) return false; // bloc service within 5min on API rate limit

		if (!($rsp = self::post($this->url_api.'verify.xml',$this->args,true,true)))
		{
			return false;
		}
		$r = simplexml_load_string($rsp);

		if ($r['code'] == 200)
		{
			return true;
		}
		return false;
	}

	public function createHash($url,$hash=null)
	{
		$arg = $this->args;
		$arg['url'] = $url;

		if (!($rsp = self::post($this->url_api.'trim_url.xml',$arg,true,true)))
		{
			return false;
		}

		$r = simplexml_load_string($rsp);

		# API rate limit
		if ($r['code'] == 425)
		{
			$this->s->setNameSpace('kUtRL');
			$this->s->put('kutrl_srv_trim_apiratetime',time());
			$this->s->setNameSpace('system');
			return false;
		}

		if (isset($r->trimpath))
		{
			$rs = new ArrayObject();
			$rs->hash = $r->trimpath;
			$rs->url = $url;
			$rs->type = $this->id;
			return $rs;
		}
		return false;
	}
}
?>