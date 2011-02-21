<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}# All services that use soCialMe must extend this classclass soCialMeService{	public $core;
	
	# Enable log functions through core->log
	public $debug;
	
	# Timeout for keeping log
	protected $log_timeout = 604800; // 1 week
	
	# Timeout for cached file refresh
	protected $cache_timeout = 900; // 15 minutes		# Part of soCialMe plugin to work on (sharer,reader,profil,writer)	protected $part = null;		# Quick definition of this service (all these def are required)	protected $define = array(		'id' => '',		'name' => '',		'home' => '',		'icon' => ''	);		# Say if this service can be used (can be set in funtion init() )	protected $available = false;		# list of available	protected $actions = array();		# Definition of settings namespace et id	protected $setting_ns = null;	protected $setting_id = null;		# List of available actions for each part	private $default_actions = array(		'sharer' => array(			'parseIconContent' => false,			'parseIconScript' => false,			'parseSmallContent' => false,			'parseSmallScript' => false,			'parseBigContent' => false,			'parseBigScript' => false		),		'reader' => array(				),		'profil' => array(			'parseIconContent' => false,			'parseIconScript' => false,			'parseSmallContent' => false,			'parseSmallScript' => false,			'parseBigContent' => false,			'parseBigScript' => false,			'parseSmallExtraContent' => false,			'parseSmallExtraScript' => false,			'parseBigExtraContent' => false,			'parseBigExtraScript' => false		),		'writer' => array(			'sendMessage' => false,			'sendLink' => false,			'sendData' => false,			'sendArticle' => false		)	);
	
	# Local URL to get images
	protected $url = null;		public function __construct($core)	{		$this->core = $core;
		
		$debug = (string) $core->blog->settings->soCialMe->debug;
		switch ($debug) {
			case 'dc': $this->debug = DC_DEBUG; break;
			case 'on': $this->debug = true; break;
			default: $this->debug = false; break;
		}
		
		$this->setCacheTimeout($core->blog->settings->soCialMe->cache_timeout);
		$this->setLogTimeout($core->blog->settings->soCialMe->log_timeout);
		
		$this->url = $core->blog->getQmarkURL();				$this->init();				if (!$this->part || !isset($this->default_actions[$this->part])) {			throw new Exception('Part must be specified');		}		if (!is_array($this->define)) {			throw new Exception('Define must be specified');		}		if (!is_array($this->actions)) {			throw new Exception('Actions must be specified');		}		$this->actions = array_merge($this->default_actions[$this->part],$this->actions);	}		# Init class (check requirements here and set $available)	protected function init()	{		return null;	}
	
	# Log some actions on debug mode
	// For exemple, this is used to check number of cache file creation
	protected function log($action,$thing,$more='')
	{
		if (!$this->debug) return;
		
		# Add new log
		$msg = sprintf('%s %s from function %s of service %s on %s part',
			$this->core->con->escape($action),
			$this->core->con->escape($more),
			$this->core->con->escape($thing),
			$this->id,
			$this->part
		);
		try {
			$cur = $this->core->con->openCursor($this->core->prefix.'log');
			$cur->log_msg = $msg;
			$cur->log_table = 'soCialMe';
			$this->core->log->addLog($cur);
		} catch (Exception $e) {}
		
		# Delete old logs
		try {
			$time = time() - (integer) $this->log_timeout;
			$date = dt::str('%Y-%m-%d %H:%M:%S',$time);
			
			$this->core->con->execute(
				'DELETE FROM '.$this->prefix.'log '.
				"WHERE log_table='soCialMe' ".
				"AND log_dt < TIMESTAMP '".$date."' "
			);
		} catch (Exception $e) {}
		
	}
	
	# Changed cache timeout for cached file refresh
	public function setCacheTimeout($t=900)
	{
		$cache_timeout = abs((integer) $t);
		if ($cache_timeout > 60) {
			$this->cache_timeout = $cache_timeout;
		}
	}
	
	# Changed log timeout for debug mode
	public function setLogTimeout($t=604600)
	{
		$log_timeout = abs((integer) $t);
		if ($log_timeout > 60) {
			$this->log_timeout = $log_timeout;
		}
	}
	
	# Changed debug mode
	public function setDebugMode($d=true)
	{
		$this->debug = (boolean) $d;
	}	
	# Read settings according to part name	protected function readSettings()	{		if (!$this->setting_ns || !$this->setting_id) {			throw new Exception('Setting is not set');		}				$this->core->blog->settings->addNamespace($this->setting_ns);		$config = $this->core->blog->settings->{$this->setting_ns}->get($this->setting_id);		$config = soCialMeUtils::decode($config);				if (!is_array($this->config)) $this->config = array();				$this->config = array_merge($this->config,$config);	}	
	# Write settings according to part name	protected function writeSettings()	{		if (!$this->setting_ns || !$this->setting_id) {			throw new Exception('Setting is not set');		}				if (!is_array($this->config)) $this->config = array();				$this->core->blog->settings->addNamespace($this->setting_ns);		$config = soCialMeUtils::encode($this->config);		$this->core->blog->settings->{$this->setting_ns}->put($this->setting_id,$config);	}		# get denition of service	public function __get($n)	{
		if ($n == 'icon' && !empty($this->define['icon']) && substr($this->define['icon'],0,3) == 'pf=') {
			return $this->url.$this->define['icon'];
		}		return isset($this->define[$n]) ? $this->define[$n] : false;	}		# Return false to all unknow calls	public function __call($n,$v)	{		if (empty($this->acions[$n])) {			throw new Exception ('Failed to call "'.$n.'"');		}		return false;	}		# Say if this service is enabled	public function available()	{		return $this->available;	}		# Say if this service has a feature	public function actions()	{		return $this->actions;	}		# Save settings from admin page	public function adminSave($service_id,$admin_url)	{		return null;	}		# Settings form for admin page	public function adminForm($service_id,$admin_url)	{		return null;	}/*# Exemple for soCialMe Sharer		public function parseIconScript()	{		return '';	}		public function parseSmallScript()	{		return '';	}		public function parseBigScript()	{		return '';	}		public function parseIconContent($url,$text,$realurl)	{		return '';	}		public function parseBigContent($url,$text,$realurl)	{		return '';	}		public function parseSmallContent($url,$text,$realurl)	{		return '';	}# Exemple for soCialMe Reader# Exemple for soCialMe Profil		public function parseIconScript()	{		return '';	}		public function parseSmallScript()	{		return '';	}		public function parseBigScript()	{		return '';	}		public function parseSmallExtraScript()	{		return '';	}		public function parseBigExtraScript()	{		return '';	}		public function parseIconContent()	{		return '';	}		public function parseBigContent()	{		return '';	}		public function parseSmallContent()	{		return '';	}		public function parseBigExtraContent()	{		return '';	}		public function parseSmallExtraContent()	{		return '';	}# Exemple for soCialMe Writer	# Send short message to service API	public function sendMessage($msg,$has_url=false)	{		return false;	}		# Send url to service API.	public function sendLink($title,$url,$type='link')	{		return false;	}		# Send data to service API.	# type could be: link,photo,video,audio	# some API allowed file upload from url or multipart/form-data	public function sendData($title,$type,$url,$data=null)	{		return false;	}		# Send full article to service API	public function sendArticle($title,$content,$author=null,$tags=array())	{		return false;	}*/}?>