<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class yourlsKutrlService extends kutrlServices
{
	public $id = 'yourls';
	public $name = 'YOURLS';
	public $home = 'http://yourls.org';

	private $url_api = '';
	private $url_test = 'http://dotclear.jcdenis.com/go/kUtRL';
	private $args = array(
		'username' => '',
		'password' => '',
		'format' => 'xml',
		'action' => 'shorturl'
	);

	public function __construct($core,$limit_to_blog=true)
	{
		parent::__construct($core,$limit_to_blog);

		$this->args['username'] = $this->s->kutrl_srv_yourls_username;
		$this->args['password'] = $this->s->kutrl_srv_yourls_password;

		$base = (string) $this->s->kutrl_srv_yourls_base;
		//if (!empty($base) && substr($base,-1,1) != '/') $base .= '/';

		$this->url_api = $base;
		$this->url_base = $base;
		$this->url_min_length = strlen($base)+3;
	}

	public function saveSettings()
	{
		$this->s->put('kutrl_srv_yourls_username',$_POST['kutrl_srv_yourls_username']);
		$this->s->put('kutrl_srv_yourls_password',$_POST['kutrl_srv_yourls_password']);
		$this->s->put('kutrl_srv_yourls_base',$_POST['kutrl_srv_yourls_base']);
	}

	public function settingsForm()
	{
		echo 
	    '<p><label class="classic">'.
		__('Url of the service:').'<br />'.
	    form::field(array('kutrl_srv_yourls_base'),50,255,$this->s->kutrl_srv_yourls_base).
		'</label></p>'.
	    '<p class="form-note">'.
	    __('This is the URL of the YOURLS service you want to use. Ex: "http://www.smaller.org/api.php".').
	    '</p>'.
		'<p><label class="classic">'.__('Login:').'<br />'.
		form::field(array('kutrl_srv_yourls_username'),50,255,$this->s->kutrl_srv_yourls_username).
		'</label></p>'.
		'<p class="form-note">'.
		__('This is your user name to sign up to this YOURLS service.').
		'</p>'.
		'<p><label class="classic">'.__('Password:').'<br />'.
		form::field(array('kutrl_srv_yourls_password'),50,255,$this->s->kutrl_srv_yourls_password).
		'</label></p>'.
		'<p class="form-note">'.
		__('This is your password to sign up to this YOURLS service.').
		'</p>';
	}

	public function testService()
	{
		if (empty($this->url_api))
		{
			$this->error->add(__('Service is not well configured.'));
			return false;
		}

		$args = $this->args;
		$args['url'] = $this->url_test;

		if (!($response = self::post($this->url_api,$this->args,true)))
		{
			$this->error->add(__('Service is unavailable.'));
			return false;
		}
		$rsp = @simplexml_load_string($response);

		if ($rsp && $rsp->status == 'success')
		{
			return true;
		}
		$this->error->add(__('Authentication to service failed.'));
		return false;
	}

	public function createHash($url,$hash=null)
	{
		$args = $this->args;
		$args['url'] = $url;

		if (!($response = self::post($this->url_api,$args,true)))
		{
			$this->error->add(__('Service is unavailable.'));
			return false;
		}

		$rsp = @simplexml_load_string($response);

		if ($rsp && $rsp->status == 'success')
		{
			$rs = new ArrayObject();
			$rs->hash = $rsp->url[0]->keyword;
			$rs->url = $url;
			$rs->type = $this->id;
			return $rs;
		}
		$this->error->add(__('Unreadable service response.'));
		return false;
	}
}
?>