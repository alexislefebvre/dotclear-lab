<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Micro-Blogging, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jeremie Patonnier
# jeremie.patonnier@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

/**
 * Class that define how to access to Twitter
 * 
 * @author jeremie Patonnier
 * @package microBlog
 * @subpackage microBlogService
 */
class mbTwitter extends microBlogService
{
	/**
	 * HTTP client required to acces the service
	 *
	 * @var XnetHttp
	 */
	private $HTTP;
	
	/**
	 * Object that drive the cache system
	 * 
	 * @var microBlogCache
	 */
	private $cache;
	
	/**
	 * HTTP status return by Twitter
	 *
	 * @var array
	 */
	private static $status = array(
		200 => 'OK',
		304 => 'Not Modified: There was no new data to return',
		400 => 'Invalid Request. You maybe send to much notes',
		401 => 'Authentication failed',
		403 => 'Request denied. You maybe reach your update limits',
		404 => 'Not Found',
		406 => 'Invalid search format',
		500 => 'Twitter is broken !',
		502 => 'Twitter is down',
		503 => 'To much request'
	);
	
	public function __construct($user,$pwd)
	{
		$this->cache = new microBlogCache(120);
		
		$this->HTTP = new XnetHttp('twitter.com',80,5);
		$this->HTTP->useSSL(false);
		$this->HTTP->useGzip(false);
		$this->HTTP->setUserAgent('Dotclear');
		$this->HTTP->setAuthorization($this->user, $this->pwd);
		
		parent::__construct($user,$pwd);
		
		$this->serviceId = md5("twitter".$this->user);
	}
	
	public static function getServiceName() {return "Twitter";}
	public static function requireKey()     {return false;}

	public function sendNote($txt)
	{
		$out = $this->HTTP->post("/statuses/update.xml", array(
			'status' => microBlogService::sanitize($txt)
		));
		
		$status = (int)$this->HTTP->getStatus();
		
		if ($out == false || $status != 200) {
			$stat = self::status;
			throw new microBlogException($stat[$status], $status);
		}
		
		return $out;
	}
	
	public function getUserTimeline($limit=20,$page=1,$since=NULL,$user=NULL)
	{
		$request = "/statuses/user_timeline.xml?"
			. "id=" . (empty($user)?$this->user:$user) . "&"
			. "count=" . (int)$limit . "&"
			. "page=" . ($page < 0 ? 1 : (int)$page);
		
		$out = $this->performRequest($request);
		
		return $out;
	}
	
	public function getFriendsTimeline($limit = 20, $page = 1, $since = NULL)
	{
		$request = "/statuses/friends_timeline.xml?"
			. "count=" . (int)$limit . "&"
			. "page=" . ($page < 0 ? 1 : (int)$page);
		
		$out = $this->performRequest($request);
		
		return $out;
	}
	
	public function search($query, $limit = 20, $page = 1, $since = NULL)
	{
		$request = "/statuses/search.xml?"
			. "rpp=" . (int)$limit . "&"
			. "page=" . ($page < 0 ? 1 : (int)$page);
		
		$out = $this->performRequest($request);
		
		return $out;
	}
	
	private function performRequest($request)
	{
		$out = $this->cache->get($request);
		
		if (!is_null($out)) {
			return $out;
		}
		
		$out    =      $this->HTTP->get($request);
		$status = (int)$this->HTTP->getStatus();
		
		if ($out == false || $status != 200){
			$s = self::$status;
			throw new microBlogException($stat[$status], $status);
		}
		
		$xml = simplexml_load_string($this->HTTP->getContent());
		
		$out = array();
		
		foreach ($xml->status as $s){
			$date = strtotime($s->created_at) - (2*60*60);
			
			if ($date < $since) break;
			
			$out[$date] = (string)$s->text;
		}
		
		$this->cache->set($request, $out);
		
		return $out;
	}
}

/**
 * Class that extend netHttp to perform some workaround
 * 
 * Twitter do not accept X-Forward-For HTTP header so it's necessary
 * to remove it before sending the request to the Twitter API.
 * 
 * @author jeremie
 */
class XnetHttp extends netHttp
{
	protected function buildRequest()
	{
		$a = parent::buildRequest();
		
		$b = array();
		foreach ($a as $v)
		{
			if (substr($v,0,16) === "X-Forwarded-For:")
				continue;
			
			$b[] = $v; 
		}
		
		return $b;
	}
}