<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of TaC, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------


class tac
{
	/* Vars */
	/* Contains the last HTTP status code returned. */
	public $http_code;
	/* Contains the last API call. */
	public $url;
	/* Set up the API root URL. */
	public $host = "https://api.twitter.com/1/";
	/* Set timeout default. */
	public $timeout = 30;
	/* Set connect timeout. */
	public $connecttimeout = 30; 
	/* Verify SSL Cert. */
	public $ssl_verifypeer = false;
	/* Respons format. */
	public $format = 'json';
	/* Decode returned json data. */
	public $decode_json = true;
	/* Contains the last HTTP headers returned. */
	public $http_info;
	/* Set the useragnet. */
	public $useragent = 'Dotclear-TaC v0.1-alpha1';
	
	
	/* Core object */
	private $core;
	/* DB object */
	private $con;
	/* table prefix */
	private $prefix;
	/* oAuth method object */
	private $sha1_method;
	
	/* consumer table name */
	private $table_registry;
	/* plugin_id */
	private $plugin_id = null;
	/* Array of plugin info from DB */
	private $registry = null;
	/* Array of user info from DB */
	private $consumer = null;
	
	/* token table name */
	private $table_access;
	/* user_id */
	private $user_id = null;
	/* oAuth token (user) object */
	private $access = null;
	/* oAuth consumer (plugin) object */
	private $token = null;
	
	
	/* Constructeur 
	 *
	 * $core : core object
	 * $cr_id : id du plugin
	 * $user_id : id de l'utilisateur sur le blog ou null pour un blog complet
	 */
	public function __construct($core,$plugin_id='TaC',$user_id=null)
	{
		$this->core = $core;
		$this->con = $this->core->con;
		$this->prefix = $this->core->prefix;
		
		$this->table_registry = $this->prefix.'tac_registry';
		$this->table_access = $this->prefix.'tac_access';
		
		$this->plugin_id = $plugin_id; //todo: test if plugin exists
		$this->user_id = !$user_id ? null : $user_id; //todo: test if exists
		
		// TIC - Twitter Inline Content
		// helper to easily acces user twitter account
		$this->tic = new tacQuick($this);
	}
	
	/* Test si un plugin est connu et si oui le charge */
	public function checkRegistry()
	{
		// Il est déjà chargé
		if ($this->registry) {
			return true;
		}
		// Recherche en base
		$rs = $this->getRegistry($this->plugin_id);
		
		// Il n'existe pas
		if ($rs->isEmpty()) {
			return false;
		}
		
		// Il existe
		
		// On fait le ménage
		$this->consumer = 
		$this->registry = 
		$this->token = 
		$this->access = null;

		// On charge la methode oAuth
		$method = 'OAuthSignatureMethod_'.str_replace('-','_',$rs->cr_sig_method);
		$this->sha1_method = new $method();
		
		// On charge l'object oAuth du consumer
		$this->consumer = new OAuthConsumer($rs->cr_key,$rs->cr_secret);
		
		// On charge les infos dans la classe
		$this->registry = $rs;
		
		return true;
	}
	
	/* Get consumer (plugin) info according to its id (plugin id) */
	public function getRegistry($cr_id)
	{
		return $this->con->select(
			'SELECT * FROM '.$this->table_registry.' '.
			"WHERE cr_id = '".$this->con->escape($cr_id)."' ".
			$this->con->limit(1)
		);
	}
	
	/* Add a registry (from cursor) */
	public function addRegistry($cur)
	{
		$this->con->writeLock($this->table_registry);
		
		try {
			$rs = $this->con->select(
				'SELECT MAX(registry_id) FROM '.$this->table_registry
			);
			
			$cur->registry_id = (integer) $rs->f(0) + 1;
			$cur->registry_dt = date('Y-m-d H:i:s');
			
			//todo: $this->getRegistryCursor($cur);
			
			$cur->insert();
			$this->con->unlock();
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}
		
		return $cur->registry_id;
	}
	
	/* Test si l'utilisateur est connu et si oui on le charge */
	public function checkAccess()
	{
		if (!$this->registry) {
			throw new Exception(__('Consumer not loaded'));
		}
		// Il est déjà chargé
		if ($this->access) {
			return true;
		}
		// Recherche en base
		$rs = $this->getAccess($this->registry->registry_id,$this->user_id);
		
		// Il n'existe pas
		if ($rs->isEmpty()) {
			return false;
		}
		
		// Il existe
		
		// On fait le ménage
		$this->token = 
		$this->access = null;
		
		// On charge l'objet oAuth de l'utilisateur
		$this->token = new OAuthConsumer($rs->ct_token,$rs->ct_token_secret);
		
		// On charge les infos dans la classe
		$this->access = $rs;
		
		return true;
	}
	
	/* Get user  info according to there user_id, plugin id on current blog */
	public function getAccess($registry_id,$user_id)
	{
		$registry_id = abs((integer) $registry_id);
		
		$req = 
		'SELECT * FROM '.$this->table_access.' '.
		"WHERE blog_id = '".$this->con->escape($this->core->blog->id)."' ".
		"AND registry_id = '".$registry_id."' ";
		
		if ($user_id) {
			$req .= "AND user_id = '".$this->con->escape($user_id)."' ";
		}
		else {
			$req .= "AND user_id IS NULL ";
		}
		
		return $this->con->select($req.$this->con->limit(1));
	}
	
	/* Ajoute un utilistaeur en base */
	public function addAccess($cur)
	{
		if (!$cur->registry_id) {
			throw new Exception (__('No registry'));
		}
		if (!$cur->ct_id) {
			throw new Exception (__('No id'));
		}
		if (!$cur->ct_token) {
			throw new Exception (__('No token'));
		}
		if (!$cur->ct_token_secret) {
			throw new Exception (__('No secret'));
		}
		
		$this->con->writeLock($this->table_access);
		
		try {
			$rs = $this->con->select(
				'SELECT MAX(access_id) FROM '.$this->table_access
			);
			
			$cur->access_id = (integer) $rs->f(0) + 1;
			$cur->access_dt = date('Y-m-d H:i:s');
			$cur->blog_id = $this->core->blog->id;
			
			$cur->insert();
			$this->con->unlock();
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}
		
		return $cur->access_id;
	}
	
	/* Efface un utilisteur dans la base */
	public function delAccess($registry_id,$user_id)
	{
		$registry_id = abs((integer) $registry_id);
		
		$req = 
		'DELETE FROM '.$this->table_access.' '.
		"WHERE blog_id = '".$this->con->escape($this->core->blog->id)."' ".
		"AND registry_id = '".$registry_id."' ";
		
		if ($user_id) {
			$req .= "AND user_id = '".$this->con->escape($user_id)."' ";
		}
		else {
			$req .= "AND user_id IS NULL ";
		}
		
		$this->con->execute($req);
	}
	
	/* Clean current user and session */
	public function cleanAccess()
	{
		if (!$this->registry || !$this->access) {
			return null;
		}
		$this->delAccess($this->registry->registry_id,$this->access->user_id);
		unset($_SESSION['oauth_token']);
		unset($_SESSION['oauth_token_secret']);
		
		return true;
	}
	
	/* Get temporary token */
	public function requestAccess($callback_url='',$sign_in=true)
	{
		if (!$this->registry) {
			throw new Exception(__('Consumer not loaded'));
		}
		
		$params = array();
		if (!empty($callback_url)) {
			$params['oauth_callback'] = $callback_url;
		}
		
		// On effectue la requete auprès du server oAuth
		$request = $this->query($this->registry->cr_url_request,'GET',$params);
		
		// Aille probleme de requete
		if ($this->http_code != 200) {
			throw new Exception(__('Failed to request access'));
		}
		
		// On transforme la réponse pour la rendre lisible
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		
		// On met en session les tokens temporaires
		$_SESSION['oauth_token'] = $token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $token['oauth_token_secret'];
		
		// On retourne l'url qui permettra d'avoie les tokens définitifs
		return $sign_in ?
			$this->registry->cr_url_autorize.'?oauth_token='.$token['oauth_token'] : 
			$this->registry->cr_url_authenticate.'?oauth_token='.$token['oauth_token'];
	}
	
	/* Get final token */
	public function grantAccess()
	{
		if (!$this->registry) {
			throw new Exception(__('Consumer not loaded'));
		}
		
		// On récupère les tokens temporaire depuis la session
		$oauth_token = isset($_SESSION['oauth_token']) ? $_SESSION['oauth_token'] : '';
		$oauth_token_secret = isset($_SESSION['oauth_token_secret']) ? $_SESSION['oauth_token_secret'] : '';
		$request_token = isset($_REQUEST['oauth_token']) ? $_REQUEST['oauth_token'] : '';
		
		// On nettoye les infos temporaire de la session
		unset($_SESSION['oauth_token']);
		unset($_SESSION['oauth_token_secret']);
		
		// Si il sont différent de ceux retourné par l'url 
		// la session a expiré et donc on efface tout
		if (!$oauth_token_secret || !$oauth_token 
		 || $request_token && $oauth_token !== $request_token)
		{
			$this->delAccess($this->registry->registry_id,$this->user_id);
			
			throw new Exception (__('Expired access'));
		}
		
		// on charge l'objet de l'utilisateur
		$this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
		
		// on demande un vérification
		$params = array();
		if (!empty($_REQUEST['oauth_verifier'])) {
			$params['oauth_verifier'] = $_REQUEST['oauth_verifier'];
		}
		$request = $this->query($this->registry->cr_url_access,'GET',$params);
		
		if ($this->http_code != 200) {
			throw new Exception(__('Failed to grant access'));
		}
		
		// On transforme la réponse pour la rendre lisible
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		
		// On sauve les tokens defintif en base
		$cur = $this->con->openCursor($this->prefix.'tac_access');
		
		try {
			$cur->registry_id = $this->registry->registry_id;
			$cur->ct_id = $token['user_id'];
			$cur->ct_token = $token['oauth_token'];
			$cur->ct_token_secret = $token['oauth_token_secret'];
			
			if ($user_id) {
				$cure->user_id = $user_id;
			}
			
			$this->addAccess($cur);
		}
		catch (Exception $e) {
			throw New Exception(__('Failed to add access').$e->getMessage());
		}
		
		return true;
	}
	
	/* GET wrapper for query */
	public function get($url,$parameters=array())
	{
		$response = $this->query($url,'GET',$parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}
	
	/* POST wrapper for query */
	public function post($url,$parameters=array())
	{
		$response = $this->query($url,'POST',$parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}
	
	/* DELETE wrapper for query */
	public function delete($url,$parameters=array())
	{
		$response = $this->query($url,'DELETE',$parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}
	
	/* Query with oAuth signature */
	private function query($url,$method,$parameters)
	{
		if (strrpos($url,'https://') !== 0 && strrpos($url,'http://') !== 0) {
			$url = $this->host.$url.'.'.$this->format;
		}
		$request = OAuthRequest::from_consumer_and_token($this->consumer,$this->token,$method,$url,$parameters);
		$request->sign_request($this->sha1_method,$this->consumer,$this->token);
		
		switch ($method) {
			case 'GET':
				return $this->http($request->to_url(),'GET');
			default:
				return $this->http($request->get_normalized_http_url(),$method,$request->to_postdata());
		}
	}
	
	//!!! rewrite it with clearbricks net htttp !!!
	private function http($url, $method, $postfields = NULL)
	{
		$this->http_info = array();
		$ci = curl_init();
		
		/* Curl settings */
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this,'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields)) {
					$url = "{$url}?{$postfields}";
				}
		}

		curl_setopt($ci,CURLOPT_URL,$url);
		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci,CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;
		curl_close ($ci);
		
		return $response;
	}
	
	private function getHeader($ch, $header)
	{
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
}
?>