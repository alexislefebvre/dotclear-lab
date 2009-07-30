<?php
class mbMock extends microBlogService
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
		//$this->cache = new microBlogCache(300);
		
		//$this->HTTP = new netHttp('twitter.com');
		//$this->HTTP->useSSL(true);
		//$this->HTTP->setUserAgent('DotClear');
		//$this->HTTP->setAuthorization($this->user, $this->pwd);
		
		parent::__construct($user, $pwd);
		
		$this->serviceId = md5("mock".$this->user);
	}
	
	public static function getServiceName(){return "Mock";}
	public static function requireKey(){return false;}

	public function sendNote($txt)
	{
		return false;
	}
	
	public function getUserTimeline($limit = 20, $page = 1, $since = NULL, $user = NULL)
	{
		$out = array(
			'USER MOCK 1',
			'USER MOCK 2',
			'USER MOCK 3',
			'USER MOCK 4',
			'USER MOCK 5',
			'USER MOCK 6',
			'USER MOCK 7',
			'USER MOCK 8',
			'USER MOCK 9',
			'USER MOCK 10',
			'USER MOCK 11',
			'USER MOCK 12',
			'USER MOCK 13',	
		);
		
		return $out;
	}
	
	public function getFriendsTimeline($limit = 20, $page = 1, $since = NULL)
	{
		$out = array(
			'FRIEND MOCK 1',
			'FRIEND MOCK 2',
			'FRIEND MOCK 3',
			'FRIEND MOCK 4',
			'FRIEND MOCK 5',
			'FRIEND MOCK 6',
			'FRIEND MOCK 7',
			'FRIEND MOCK 8',
			'FRIEND MOCK 9',
			'FRIEND MOCK 10',
			'FRIEND MOCK 11',
			'FRIEND MOCK 12',
			'FRIEND MOCK 13',	
		);
		
		return $out;
	}
	
	public function search($query, $limit = 20, $page = 1, $since = NULL)
	{
		$out = array(
			'SEARCH MOCK 1',
			'SEARCH MOCK 2',
			'SEARCH MOCK 3',
			'SEARCH MOCK 4',
			'SEARCH MOCK 5',
			'SEARCH MOCK 6',
			'SEARCH MOCK 7',
			'SEARCH MOCK 8',
			'SEARCH MOCK 9',
			'SEARCH MOCK 10',
			'SEARCH MOCK 11',
			'SEARCH MOCK 12',
			'SEARCH MOCK 13',	
		);
		
		return $out;
	}
}