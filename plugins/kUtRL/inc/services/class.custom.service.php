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

class customKutrlService extends kutrlServices
{
	public $id = 'custom';
	public $name = 'Custom';
	public $home = '';
	
	private $url_api = '';
	public $url_base = '';
	private $url_param = '';
	private $url_encode = true;
	
	private $url_test = 'http://dotclear.jcdenis.com/go/kUtRL';
	
	public function __construct($core,$limit_to_blog=true)
	{
		parent::__construct($core,$limit_to_blog);
		
		$config = unserialize(base64_decode($this->s->kutrl_srv_custom));
		if (!is_array($config))
		{
			$config = array();
		}
		
		$this->url_api = !empty($config['url_api']) ? $config['url_api'] : '';
		$this->url_base = !empty($config['url_base']) ? $config['url_base'] : '';
		$this->url_param = !empty($config['url_param']) ? $config['url_param'] : '';
		$this->url_encode = !empty($config['url_api']) ? true : false;
		
		$this->url_min_length = strlen($this->url_base) + 2;
	}
	
	public function saveSettings()
	{
		$config = array(
			'url_api' => $_POST['kutrl_srv_custom_url_api'],
			'url_base' => $_POST['kutrl_srv_custom_url_base'],
			'url_param' => $_POST['kutrl_srv_custom_url_param'],
			'url_encode' => !empty($_POST['kutrl_srv_custom_url_encode'])
		);
		$this->s->put('kutrl_srv_custom',base64_encode(serialize($config)));
	}
	
	public function settingsForm()
	{
		$default = array(
			'url_api' => '',
			'url_base' => '',
			'url_param' => '',
			'url_encode' => true
		);
		$config = unserialize(base64_decode($this->s->kutrl_srv_custom));
		if (!is_array($config))
		{
			$config = array();
		}
		$config = array_merge($default,$config);
		
		echo 
		'<p>'.__('You can set a configurable service.').'<br />'.
		__('It consists on a simple query to an URL with only one param.').'<br />'.
		__('It must respond with a http code 200 on success.').'<br />'.
		__('It must returned the short URL (or only hash) in clear text.').'</p>' .
		'<p><label class="classic">'.__('API URL:').'<br />'.
		form::field(array('kutrl_srv_custom_url_api'),50,255,$config['url_api']).
		'</label></p>'.
		'<p class="form-note">'.__('Full path to API of the URL shortener. ex: "http://is.gd/api.php"').'</p>'.
		'<p><label class="classic">'.__('Short URL domain:').'<br />'.
		form::field(array('kutrl_srv_custom_url_base'),50,255,$config['url_base']).
		'</label></p>'.
		'<p class="form-note">'.__('Common part of the short URL. ex: "http://is.gd/"').'</p>'.
		'<p><label class="classic">'.__('API URL param:').'<br />'.
		form::field(array('kutrl_srv_custom_url_param'),50,255,$config['url_param']).
		'</label></p>'.
		'<p class="form-note">'.__('Param of the query. ex: "longurl"').'</p>'.
		'<p><label class="classic">'.
		form::checkbox(array('kutrl_srv_custom_url_encode'),'1',$config['url_encode']).' '.
		__('Encode URL').
		'</label></p>';
	}
	
	public function testService()
	{
		if (empty($this->url_api)) return false;
		
		$url = $this->url_encode ? urlencode($this->url_test) : $this->url_test;
		$arg = array($this->url_param => $url);
		if (!self::post($this->url_api,$arg,true,true))
		{
			$this->error->add(__('Service is unavailable.'));
			return false;
		}
		return true;
	}
	
	public function createHash($url,$hash=null)
	{
		$enc = $this->url_encode ? urlencode($url) : $url;
		$arg = array($this->url_param => $enc);
		
		if (!($response = self::post($this->url_api,$arg,true,true)))
		{
			$this->error->add(__('Service is unavailable.'));
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