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
 * Class that define how to access to Identi.ca
 * 
 * @author jeremie Patonnier
 * @package microBlog
 * @subpackage microBlogService
 */
class mbIdentica extends microBlogService
{
	/**
	 * HTTP client required to acces the service
	 *
	 * @var netHttp
	 */
	private $HTTP;
	
	/**
	 * Object that drive the cache system
	 * 
	 * @var microBlogCache
	 */
	private $cache;
	
	/**
	 * HTTP status return by Identi.ca
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
		500 => 'Identi.ca is broken !',
		502 => 'Identi.ca is down',
		503 => 'To much request'
	);
	
	public function __construct($user, $pwd)
	{
		parent::__construct($user, $pwd);
		
		$this->cache = new microBlogCache(120);
		
		$this->HTTP = new netHttp('identi.ca');
		$this->HTTP->useSSL(false);
		$this->HTTP->useGzip(false);
		$this->HTTP->setUserAgent('DotClear');
		$this->HTTP->setAuthorization($this->user, $this->pwd);
		
		$this->serviceId = md5("identica".$this->user);
	}
	
	public static function getServiceName() {return "Identi.ca";}
	public static function requireKey() {return false;}

	public function sendNote($txt)
	{
		$out = $this->HTTP->post("/api/statuses/update.xml", array(
			'status' => microBlogService::sanitize($txt)
		));
		
		$status = (int)$this->HTTP->getStatus();
		
		if ($out == false || $status != 200) {
			$stat = self::$status;
			throw new microBlogException($stat[$status], $status);
		}
		
		return $out;
	}
	
	public function getUserTimeline($limit=20,$page=1,$since=NULL,$user=NULL)
	{
		$request = "/api/statuses/user_timeline.xml?"
			. "id=" . (empty($user)?$this->user:$user) . "&"
			. "count=" . (int)$limit . "&"
			. "page=" . ($page < 0 ? 1 : (int)$page);
		
		$out = $this->performRequest($request,$since);
		
		return $out;
	}
	
	public function getFriendsTimeline($limit=20,$page=1,$since=NULL)
	{
		$request = "/api/statuses/friends_timeline.xml?"
			. "count=" . (int)$limit . "&"
			. "page=" . ($page < 0 ? 1 : (int)$page);
		
		$out = $this->performRequest($request,$since);
		
		return $out;
	}
	
	public function search($query,$limit=20,$page=1,$since=NULL)
	{
		$request = "/api/statuses/search.xml?"
			. "rpp=" . (int)$limit . "&"
			. "page=" . ($page < 0 ? 1 : (int)$page);
		
		$out = $this->performRequest($request,$since);
		
		return $out;
	}
	
	private function performRequest($request,$since=NULL)
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
			
			$out[$date] = html_entity_decode(utf8-decode((string)$s->text));
		}
		
		$this->cache->set($request, $out);
		
		return $out;
	}
}