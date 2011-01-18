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

class bitlyKutrlService extends kutrlServices
{
	public $id = 'bitly';
	public $name = 'bit.ly';
	public $home = 'http://bit.ly';

	private $url_api = 'http://api.bit.ly/v3/';
	private $args = array(
		'format' => 'xml',
		'login' => '',
		'apiKey' => '',
		'history' => 0
	);

	public function __construct($core,$limit_to_blog=true)
	{
		parent::__construct($core,$limit_to_blog);

		$this->args['login'] = $this->s->kutrl_srv_bitly_login;
		$this->args['apiKey'] = $this->s->kutrl_srv_bitly_apikey;
		$this->args['history'] = $this->s->kutrl_srv_bitly_history ? 1 : 0;

		$this->url_base = 'http://bit.ly/';
		$this->url_min_length = 25;
	}

	public function saveSettings()
	{
		$this->s->put('kutrl_srv_bitly_login',$_POST['kutrl_srv_bitly_login']);
		$this->s->put('kutrl_srv_bitly_apikey',$_POST['kutrl_srv_bitly_apikey']);
		$this->s->put('kutrl_srv_bitly_history',isset($_POST['kutrl_srv_bitly_history']));
	}

	public function settingsForm()
	{
		echo 
		'<p><label class="classic">'.__('Login:').'<br />'.
		form::field(array('kutrl_srv_bitly_login'),50,255,$this->s->kutrl_srv_bitly_login).
		'</label></p>'.
		'<p class="form-note">'.
		__('This is your login to sign up to bit.ly.').
		'</p>'.
		'<p><label class="classic">'.__('API Key:').'<br />'.
		form::field(array('kutrl_srv_bitly_apikey'),50,255,$this->s->kutrl_srv_bitly_apikey).
		'</label></p>'.
		'<p class="form-note">'.
		__('This is your personnal bit.ly API key. You can find it on your account page.').
		'</p>'.
		'<p><label class="classic">'.
		form::checkbox(array('kutrl_srv_bitly_history'),'1',$this->s->kutrl_srv_bitly_history).' '.
		__('Publish history').
		'</label></p>'.
		'<p class="form-note">'.
		__('This publish all short links on your bit.ly public page.').
		'</p>';
	}

	public function testService()
	{
		if (empty($this->args['login']) || empty($this->args['apiKey']))
		{
			$this->error->add(__('Service is not well configured.'));
			return false;
		}

		$args = $this->args;
		$args['hash'] = 'WP9vc';
		if (!($response = self::post($this->url_api.'expand',$args,true)))
		{
			$this->error->add(__('Failed to call service.'));
			return false;
		}

		$rsp = simplexml_load_string($response);

		$err_msg = (string) $rsp->status_txt;
		if ($err_msg != 'OK') {
			$err_no = (integer) $rsp->status_code;
			$this->error->add(sprintf(__('An error occured with code %s and message "%s"'),$err_no,$err_msg));
			return false;
		}
		return true;
	}

	public function createHash($url,$hash=null)
	{
		$args = $this->args;
		$args['longUrl'] = $url;

		if (!($response = self::post($this->url_api.'shorten',$args,true)))
		{
			$this->error->add(__('Failed to call service.'));
			return false;
		}

		$rsp = simplexml_load_string($response);

		$err_msg = (string) $rsp->status_txt;
		if ($err_msg != 'OK') {
			$err_no = (integer) $rsp->status_code;
			$this->error->add(sprintf(__('An error occured with code %s and message "%s"'),$err_no,$err_msg));
			return false;
		}

		$rs = new ArrayObject();
		$rs->hash = (string) $rsp->data[0]->hash;
		$rs->url = (string) $rsp->data[0]->long_url;
		$rs->type = $this->id;

		return $rs;
	}
}
?>