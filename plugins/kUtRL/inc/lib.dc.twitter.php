<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of libDcTwitter, 
# a library for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

/*
 * Release
 * -------
 * 0.1 - 2010.04.13 01:20 - JC Denis
 * First release
 *
 * How to use your a plugin
 * ------------------------
 * 1) On your prepend.php file put:
 *    (This code check if other version exists. Thks to Tomtom)
 *
 * if (array_key_exists('libDcTwitter',$__autoload))  {
 *	$r = new ReflectionClass(dirname(__FILE__).'/inc/lib.dc.twitter.php');
 *	if (version_compare(libDcTwitter::VERSION, $r->VERSION,'<')) {
 *		$__autoload['libDcTwitter'] = dirname(__FILE__).'/inc/lib.dc.twitter.php';
 *	}
 *	unset($r);
 *} else {
 *	$__autoload['libDcTwitter'] = dirname(__FILE__).'/inc/lib.dc.twitter.php';
 *}
 * 
 * 2) On your index.php or plugin configuration, 
 *    call inside a form:
 *
 * libDcTwitter::adminForm("ID OF YOUR PLUGIN");
 *
 * 3) On your index.php or plugin configuration, 
 *    call inside validation part:
 *
 * libDcTwitter::adminAction("ID OF YOUR PLUGIN");
 *
 * 4) When you want to send message to timeline,
 *  a) optionaly get your default message with:
 *
 * $str = libDcTwitter::getMessage("ID OF YOUR PLUGIN");
 *
 *  b) send message with:
 *
 * libDcTwitter::sendMessage("ID OF YOUR PLUGIN",$str) ;
 *
 *
 * Message longueur than 140 chars will be cut into smaller part
 * then send successively
 */

# Configuration and usage
class libDcTwitter
{
	const VESRION = '0.1';

	public static function getVersion()
	{
		return self::VERSION;
	}

	# DC 2.1.6 vs 2.2
	private static function settings($ns)
	{
		global $core;
		# New DC settings system
		if (!version_compare(DC_VERSION,'2.1.6','<=')) { 
			$core->blog->settings->addNamespace($ns); 
			return $core->blog->settings->{$ns}; 
		}
		# Old DC settings system
		else { 
			$core->blog->settings->setNamespace($ns); 
			return $core->blog->settings; 
		}
	}

	# Read settings
	private static function getSettings($ns)
	{
		# Set settings namespace
		$s = self::settings($ns);
		# Get saved settings
		$current = @unserialize($s->get($ns.'_libdctwitter'));
		# Clean settings
		return self::cleanSettings($current);
	}

	# Write settings
	private static function setSettings($ns,$current)
	{
		# Set settings namespace
		$s = self::settings($ns);
		# Clean settings
		$settings = self::cleanSettings($current);
		# Saved settings
		$s->put(
			$ns.'_libdctwitter',
			serialize($settings),
			'string',
			'libdctwitter settings'
		);
	}

	# Mix default settings
	private static function cleanSettings($current)
	{
		# Default settings values
		$default = array(
			'default_message' => '',
			'twitter_login' => '',
			'twitter_pass' => '',
			'identica_login' => '',
			'identica_pass' => ''
		);
		# Compare settings
		if (is_array($current) && !empty($current))
		{
			foreach($default as $key => $val)
			{
				if (isset($current[$key])) {
					$default[$key] = $current[$key];
				}
			}
		}
		return $default;
	}

	# Get default message
	public static function getMessage($ns='libdctwitter')
	{
		if (empty($ns))	return '';

		$s = self::getSettings($ns);

		return $s['default_message'];
	}

	# Administration form content (without <form> tag)
	public static function adminForm($ns='libdctwitter')
	{
		if (empty($ns))
		{
			return '';
		}

		$s = self::getSettings($ns);

		$twitter_login = isset($_POST[$ns.'_libdctwitter_twitter_login']) ?
			(string) $_POST[$ns.'_libdctwitter_twitter_login'] :
			$s['twitter_login'];

		$identica_login = isset($_POST[$ns.'_libdctwitter_identica_login']) ?
			(string) $_POST[$ns.'_libdctwitter_identica_login'] :
			$s['identica_login'];

		$default_message = isset($_POST[$ns.'_libdctwitter_default_message']) ?
			(string) $_POST[$ns.'_libdctwitter_default_message'] :
			$s['default_message'];

		echo  
		'<h3>'.__('Twitter account').'</h3>'.
		'<p><label class="classic">'.__('Login:').'<br />'.
		form::field($ns.'_libdctwitter_twitter_login',50,255,$twitter_login,'',2).
		'</label></p>'.
		'<p><label class="classic">'.__('Password:').'<br />'.
		form::password($ns.'_libdctwitter_twitter_pass',50,255,'','',2).
		'</label></p>'.
		'<p class="form-note">'.__('Type a password only to change old one.').'</p>'.
		'<h3>'.__('Identi.ca account').'</h3>'.
		'<p><label class="classic">'.__('Login:').'<br />'.
		form::field($ns.'_libdctwitter_identica_login',50,255,$identica_login,'',2).
		'</label></p>'.
		'<p><label class="classic">'.__('Password:').'<br />'.
		form::password($ns.'_libdctwitter_identica_pass',50,255,'','',2).
		'</label></p>'.
		'<p class="form-note">'.__('Type a password only to change old one.').'</p>'.
		'<h3>'.__('Message').'</h3>'.
		'<p><label class="classic">'.__('Text:').'<br />'.
		form::field($ns.'_libdctwitter_default_message',50,255,$default_message,'',2).
		'</label></p>';
	}

	# Administration form content validation
	public static function adminAction($ns='libdctwitter')
	{
		if (empty($ns) || empty($_POST))
		{
			return false;
		}

		$s = self::getSettings($ns);

		$rs = array();

		$rs['twitter_login'] = isset($_POST[$ns.'_libdctwitter_twitter_login']) ?
			(string) $_POST[$ns.'_libdctwitter_twitter_login'] :
			$s['twitter_login'];

		$rs['twitter_pass'] = !empty($_POST[$ns.'_libdctwitter_twitter_pass']) ?
			(string) $_POST[$ns.'_libdctwitter_twitter_pass'] :
			$s['twitter_pass'];

		$rs['identica_login'] = isset($_POST[$ns.'_libdctwitter_identica_login']) ?
			(string) $_POST[$ns.'_libdctwitter_identica_login'] :
			$s['identica_login'];

		$rs['identica_pass'] = !empty($_POST[$ns.'_libdctwitter_identica_pass']) ?
			(string) $_POST[$ns.'_libdctwitter_identica_pass'] :
			$s['identica_pass'];

		$rs['default_message'] = isset($_POST[$ns.'_libdctwitter_default_message']) ?
			(string) $_POST[$ns.'_libdctwitter_default_message'] :
			$s['default_message'];

		self::setSettings($ns,$rs);
		return true;
	}

	# Send message using $ns settings
	public static function sendMessage($ns,$str)
	{
		if (empty($ns) || empty($str))
		{
			return false;
		}
		# Messenger
		$obj = new libDcTwitterSender();
		# Settings
		$s = self::getSettings($ns);
		# Send on titter if account is set
		if (!empty($s['twitter_login']) && !empty($s['twitter_pass']))
		{
			$obj->setAPI('twitter');
			$obj->setUser($s['twitter_login'],$s['twitter_pass']);
			$obj->send($str);
		}
		# Send to identica if account is set
		if (!empty($s['identica_login']) && !empty($s['identica_pass']))
		{
			$obj->setAPI('identica');
			$obj->setUser($s['identica_login'],$s['identica_pass']);
			$obj->send($str);
		}
		return true;
	}
}

# Messenger
class libDcTwitterSender
{
	const VESRION = '0.1';
	private $api_id; // api to use ie: twitter or identica
	private $user_id; // user login
	private $user_pass; // user password

	public static function getVersion()
	{
		return self::VERSION;
	}

	# All parameters are optionals
	public function __construct($api_id='twitter',$user_id='',$user_pass='')
	{
		$this->setAPI($api_id);
		$this->setUser($user_id,$user_pass);
	}

	# Set API to use eg: 'twitter' or 'identica'
	public function setAPI($api)
	{
		$this->api_id = $api == 'identica' ? 'identica' : 'twitter';
		$this->user_id = '';
		$this->user_pass = '';
	}

	# Set API user login and password
	public function setUser($login,$password)
	{
		$this->user_id = (string) $login;
		$this->user_pass = (string) $password;
	}

	# Send message $str to API user status (timeline)
	public function send($str)
	{
		# User not set
		if (!$this->user_id || !$this->user_pass) {
			throw New Exception(__('User is not set.'));
		}
		# Clean message
		$str = (string) $str;
		$str = trim($str);
		//$str = urlencode($str);
		# Empty message
		if (!$str) {
			throw New Exception(__('Nothing to send.'));
		}
		# Split into smaller messages
		$msg = $this->splitStr($str,140);
		# Loop throught lines of messages
		foreach($msg as $k => $line)
		{
			# Identica specs
			if ($this->api_id == 'identica') {
				$url = 'identi.ca';
				$path = '/api/statuses/update.xml';
				$args = array('status'=>$line);
			}
			# Twitter specs
			else {
				$url = 'twitter.com';
				$path = '/statuses/update.xml';
				$args = array('status'=>$line);
			}
			# Open connection
			$client = new netHttp($url);
			$client->setAuthorization($this->user_id,$this->user_pass);
			# Send Message
			if (!$client->post($path,$args)) {
				throw new Exception(sprintf(__('Failed to send message (%s)'),$k+1));
				return false;
			}
		}
		return true;
	}

	# Cut on word message $str into sub message less than $len chars long
	public function splitStr($str,$len=140)
	{
		$split = array(0=>'');
		$j = 0;
		if (strlen($str) < $len)
		{
			$words = explode(' ',$str);
			for($i = 0; $i < count($words); $i++)
			{
				$s = empty($split[$j]) ? '' : ' ';

				$next_len = $split[$j].$s.$words[$i];
				if (strlen($next_len) < $len)
				{
					$split[$j] .= $s.$words[$i];
				}
				else
				{
					$j++;
					$split[$j] = $words[$i];
				}
			}
		}
		else
		{
			$split[0] = $str;
		}
		return $split;
	}

	# Trim a long url using http://is.gd API
	# Usefull for short message
	public static function shorten($url,$verbose=false)
	{
		$error = '';
		$api = 'http://is.dg/api.php?';
		$path = '';
		$data = array('longUrl'=>urlencode($url));

		# Send request
		$client = netHttp::initClient($api,$path);
		$client->setUserAgent('libDcTwitterSender - '.self::$version);
		$client->setPersistReferers(false);
		$client->get($path,$data);

		# Recieve short url
		if ($client->getStatus() == 200) {
			return (string) $client->getContent();
		}
		# Error during shorten link
		elseif ($client->getStatus() == 500) {
			$str = html::escapeHTML((string) $client->getContent());
			$error = sprintf(__('Failed to get short url (%s)'),$str);
		}

		# Throw error
		if ($verbose) {
			if (empty($error)) {
				throw New Exception(__('Failed to get short url'));
			}
			else {
				throw New Exception($error);
			}
		}
		return false;
	}
}
?>