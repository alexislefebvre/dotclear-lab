<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of oAuthManager, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class oAuthClient10
{
	/*
	 * Client info
	 */
	
	protected $config;
	protected $store;
	protected $record;
	
	/*
	 * Queries
	 */
	
	#Contains the last HTTP status code returned
	public $http_code;
	#Contains the last HTTP headers returned
	public $http_info;
	# Contains the last API call
	public $http_url;
	# Set the useragnet
	public $useragent = 'Dotclear-oAuthClientManager';
	# Set timeout default
	public $timeout = 30;
	# Set connect timeout
	public $connecttimeout = 30;
	# Verify SSL Cert
	public $ssl_verifypeer = false;
	# Response format
	public $format = 'json';
	# Decode returned json data
	public $decode_json = true;
	
	/*
	 * oAuth
	 */
	protected $sha1_method = null;
	protected $consumer = null;
	protected $client = null;
	
	public function __construct($core,$config)
	{
		# config: default values
		$default = array(
			# dotclear info
			'plugin_id' => 'unknow',
			'plugin_name' => __('Unknow'),
			'client_id' => 'unknow',
			'client_name' => __('Unknow'),
			'user_id' => null,
			
			# consumer info
			'token' => null,
			'secret' => null,
			'api_url' => 'http://localhost/api',
			'request_token' => 'http://localhost/oauth/request_token',
			'authorize' => 'http://localhost/oauth/authenticate',
			'authenticate' => 'http://localhost/oauth/authenticate',
			'access_token' => 'http://localhost/oauth/access_token',
			'sig_method' => 'HMAC-SHA1',
			'expiry' => null
		);
		# config: current service
		$this->config = array_merge($default,$config);
		
		# storage
		$this->store = new oAuthClient10Store($core);
		
		# load start values
		$this->reset();
	}
	
	public function reset()
	{
		# load user info if exists
		$this->record = $this->store->get($this->config['plugin_id'],$this->config['client_id'],$this->config['user_id']);
		# else add him
		if (!$this->record) {
			$this->record = $this->store->add($this->config['plugin_id'],$this->config['client_id'],$this->config['user_id']);
		}
		
		# sig method oAuth
		$method = 'OAuthSignatureMethod_'.str_replace('-','_',$this->config['sig_method']);
		$this->sha1_method = new $method();
		
		# consumer object
		$this->consumer = new OAuthConsumer($this->config['token'],$this->config['secret']);
		
		# if user previously registered: load client object
		$this->client = 2 == $this->state() ? new OAuthConsumer($this->record->token,$this->record->secret) : null;
	}
	
	public function config($k)
	{
		return isset($this->config[$k]) ? $this->config[$k] : null;
	}
	
	public function state()
	{
		return !$this->record ? 0 : (integer) $this->record->state;
	}
	
	public function deleteRecord()
	{
		$this->store->del($this->record->uid);
		$this->reset();
	}
	
	public function removeToken()
	{
		# update storage
		$cur = $this->store->open();
		$cur->state = 0;
		$cur->token = null;
		$cur->secret = null;
		$this->store->upd($this->record->uid,$cur);
		
		$this->reset();
	}
	
	public function getRequestToken($callback_url,$sign_in=true)
	{
		# define return url
		$params = array();
		if (!empty($callback_url))
		{
			$params['oauth_callback'] = $callback_url;
		}
		
		# query server
		$request = $this->query($this->config['request_token'],'GET',$params);
		
		# failed to query
		if ($this->http_code != 200)
		{
			throw new Exception(__('Failed to request access: '.$this->http_info));
		}
		
		# parse response
		$client = OAuthUtil::parse_parameters($request);
		$this->client = new OAuthConsumer($client['oauth_token'],$client['oauth_token_secret']);
		
		# update storage
		$cur = $this->store->open();
		$this->record->state = $cur->state = 1;
		$this->record->token = $cur->token = $client['oauth_token'];
		$this->record->secret = $cur->secret = $client['oauth_token_secret'];
		$this->store->upd($this->record->uid,$cur);
		
		# redirect to next step
		$url = ($sign_in ? $this->config['authorize'] : $this->config['authenticate']).'?oauth_token='.$client['oauth_token'];
		http::redirect($url);
	}
	
	public function getAccessToken()
	{
		if (1 != $this->state())
		{
			throw new Exception('Failed to get access, make request first');
		}
		
		# get returned token
		$request_token = isset($_REQUEST['oauth_token']) ? $_REQUEST['oauth_token'] : '';
		$request_verifier = isset($_REQUEST['oauth_verifier']) ? $_REQUEST['oauth_verifier'] : '';
		
		# expired
		if ($request_token != $this->record->token)
		{
			throw new Exception('Expired token');
		}
		
		# client object
		$this->client = new OAuthConsumer($this->record->token,$this->record->secret);
		
		
		# check server token
		$params = array();
		if (!empty($request_verifier))
		{
			$params['oauth_verifier'] = $request_verifier;
		}
		$request = $this->query($this->config['access_token'],'GET',$params);
		
		if ($this->http_code != 200)
		{
			throw new Exception(__('Failed to grant access: '.$this->http_info));
		}
		
		# parse response
		$client = OAuthUtil::parse_parameters($request);
		$this->client = new OAuthConsumer($client['oauth_token'],$client['oauth_token_secret']);
		
		# update storage
		$cur = $this->store->open();
		$this->record->state = $cur->state = 2;
		$this->record->token = $cur->token = $client['oauth_token'];
		$this->record->secret = $cur->secret = $client['oauth_token_secret'];
		$this->store->upd($this->record->uid,$cur,$this->config['expiry']);
		
		# Execute a function after acces grant
		$this->onGrantAccess();
		return true;
	}
	
	protected function onGrantAccess()
	{
		// do what you want in your child class
	}
	
	/*********************************/
	
	
	/* GET wrapper for query */
	public function get($url,$parameters=array())
	{
		$response = $this->query($url,'GET',$parameters);
		if ($this->format === 'json' && $this->decode_json)
		{
			return json_decode($response);
		}
		return $response;
	}
	
	/* POST wrapper for query */
	public function post($url,$parameters=array())
	{
		$response = $this->query($url,'POST',$parameters);
		if ($this->format === 'json' && $this->decode_json)
		{
			return json_decode($response);
		}
		return $response;
	}
	
	/* DELETE wrapper for query */
	public function delete($url,$parameters=array())
	{
		$response = $this->query($url,'DELETE',$parameters);
		if ($this->format === 'json' && $this->decode_json)
		{
			return json_decode($response);
		}
		return $response;
	}
	
	/* Query with oAuth signature */
	private function query($url,$method,$parameters)
	{
		if (strrpos($url,'https://') !== 0 && strrpos($url,'http://') !== 0)
		{
			$url = $this->config['api_url'].$url.'.'.$this->format;
		}
		
		$request = OAuthRequest::from_consumer_and_token($this->consumer,$this->client,$method,$url,$parameters);
		$request->sign_request($this->sha1_method,$this->consumer,$this->client);
		
		switch ($method)
		{
			case 'GET':
			return $this->http($request->to_url(),'GET');
		
			default:
			return $this->http($request->get_normalized_http_url(),$method,$request->to_postdata());
		}
	}
	
	//!!! Can not use clearbricks netHttp as DELETE method is missing
	private function http($url,$method,$postfields=NULL)
	{
		$this->http_info = array();
		$ci = curl_init();
		
		/* Curl settings */
		curl_setopt($ci,CURLOPT_USERAGENT,$this->useragent);
		curl_setopt($ci,CURLOPT_CONNECTTIMEOUT,$this->connecttimeout);
		curl_setopt($ci,CURLOPT_TIMEOUT,$this->timeout);
		curl_setopt($ci,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ci,CURLOPT_HTTPHEADER,array('Expect:'));
		curl_setopt($ci,CURLOPT_SSL_VERIFYPEER,$this->ssl_verifypeer);
		curl_setopt($ci,CURLOPT_HEADERFUNCTION,array($this,'getHeader'));
		curl_setopt($ci,CURLOPT_HEADER,FALSE);
		
		switch ($method)
		{
			case 'POST':
			curl_setopt($ci,CURLOPT_POST,TRUE);
			if (!empty($postfields))
			{
				curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
			}
			break;
			
			case 'DELETE':
			curl_setopt($ci,CURLOPT_CUSTOMREQUEST,'DELETE');
			if (!empty($postfields))
			{
				$url = "{$url}?{$postfields}";
			}
		}
		
		curl_setopt($ci,CURLOPT_URL,$url);
		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci,CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info,curl_getinfo($ci));
		$this->http_url = $url;
		curl_close ($ci);
		
		return $response;
	}
	
	private function getHeader($ch,$header)
	{
		$i = strpos($header,':');
		if (!empty($i))
		{
			$key = str_replace('-', '_',strtolower(substr($header,0,$i)));
			$value = trim(substr($header,$i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
}

?>