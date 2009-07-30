<?php
class mbTwitter extends microBlogService
{
	/**
	 * Client HTTP pour faire les requêtes au service
	 *
	 * @var netHttp
	 */
	private $HTTP;
	
	/**
	 * Objet de gestion de cache
	 * 
	 * @var microBlogCache
	 */
	private $cache;
	
	/**
	 * Liste des code status HTTP que peut retourner Twitter
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
	
	public function __construct($user, $pwd)
	{
		$this->cache = new microBlogCache(120);
		
		$this->HTTP = new XnetHttp('twitter.com',80,5);
		$this->HTTP->useSSL(false);
		$this->HTTP->useGzip(false);
		$this->HTTP->setUserAgent('Dotclear');
		$this->HTTP->setAuthorization($this->user, $this->pwd);
		
		parent::__construct($user, $pwd);
		
		$this->serviceId = md5("twitter".$this->user);
	}
	
	public static function getServiceName(){return "Twitter";}
	public static function requireKey(){return false;}

	public function sendNote($txt)
	{
		$out = $this->HTTP->post("/statuses/update.xml", array(
			'status' => microBlogService::sanitize($txt)
		));
		
		$s = (int)$this->HTTP->getStatus();
		
		if($out == false || ($s != 200 && $s != 304)){
			$out  = false;
			$stat = self::status;
			throw new microBlogException($stat[$s], $s);
		}
		
		return $out;
	}
	
	public function getUserTimeline($limit = 20, $page = 1, $since = NULL, $user = NULL)
	{
		$request = "/statuses/user_timeline.xml?"
			. "id=" . (empty($user)?$this->user:$user) . "&"
			. "count=" . (int)$limit . "&"
			. "page=" . ($page < 0 ? 1 : (int)$page);
		
		$out = $this->cache->get($request);
		
		if(!is_null($out))
			return $out;
		
		$out = $this->HTTP->get($request);
		
		$s = (int)$this->HTTP->getStatus();
		
		if($out == false || $s != 200){
			$stat = self::$status;
			throw new microBlogException($stat[$s], $s);
		}
		
		$xml = simplexml_load_string($this->HTTP->getContent());
		
		$out = array();
		
		foreach($xml->status as $s){
			$date = strtotime($s->created_at) - (2*60*60);
			if($date < $since)
				break;
			
			$out[] = utf8_decode((string)$s->text);
		}
		
		$this->cache->set($request, $out);
		
		return $out;
	}
	
	public function getFriendsTimeline($limit = 20, $page = 1, $since = NULL)
	{
		$request = "/statuses/friends_timeline.xml?"
			. "count=" . (int)$limit . "&"
			. "page=" . ($page < 0 ? 1 : (int)$page);
		
		$out = $this->cache->get($request);
		
		if(!is_null($out))
			return $out;
		
		$out = $this->HTTP->get($request);
		
		$s = (int)$this->HTTP->getStatus();
		
		if($out == false || $s != 200){
			$stat = self::$status;
			throw new microBlogException($stat[$s], $s);
		}
		
		$xml = simplexml_load_string($this->HTTP->getContent());
		
		$out = array();
		
		foreach($xml->status as $s){
			$date = strtotime($s->created_at) - (2*60*60);
			if($date < $since)
				break;
			
			$out[] = utf8_decode((string)$s->text);
		}
		
		$this->cache->set($request, $out);
		
		return $out;
	}
	
	public function search($query, $limit = 20, $page = 1, $since = NULL)
	{
		$request = "/statuses/search.xml?"
			. "rpp=" . (int)$limit . "&"
			. "page=" . ($page < 0 ? 1 : (int)$page);
		
		$out = $this->cache->get($request);
		
		if(!is_null($out))
			return $out;
		
		$out = $this->HTTP->get($request);
		
		$s = (int)$this->HTTP->getStatus();
		
		if($out == false || $s != 200){
			$stat = self::$status;
			throw new microBlogException($stat[$s], $s);
		}
		
		$xml = simplexml_load_string($this->HTTP->getContent());
		
		$out = array();
		
		foreach($xml->status as $s){
			$date = strtotime($s->created_at) - (2*60*60);
			if($date < $since)
				break;
			
			$out[] = utf8_decode((string)$s->text);
		}
		
		$this->cache->set($request, $out);
		
		return $out;
	}
}

///// Work Around for netHttp ////
class XnetHttp extends netHttp
{
	protected function buildRequest()
	{
		$a = parent::buildRequest();
		$b = array();
		
		foreach($a as $v)
		{
			if(substr($v,0,16) === "X-Forwarded-For:")
				continue;
			
			$b[] = $v; 
		}
		
		return $b;
	}
}