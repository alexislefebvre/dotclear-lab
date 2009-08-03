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
 * Interface that define the static API of the micro-bloghing services
 * 
 * @author jeremie
 * @package microBlog
 * @subpackage microBlogService
 */
interface iMicroBlogService{
	
	/**
	 * Return the human readeable name of the service
	 * 
	 * @return string
	 */
	public static function getServiceName();
	
	/**
	 * Indicate if the service require an API_KEY
	 * 
	 * @return bool
	 */
	public static function requireKey();
}


/**
 * Abstract class that define the public API to access a service
 * 
 * Instade of the API, the class provide a basic global implementation
 * of commons features
 * 
 * @author jeremie
 * @package microBlog
 * @subpackage microBlogService
 */
abstract class microBlogService implements iMicroBlogService
{
	/**
	 * The username require to access the service
	 * 
	 * @var string
	 */
	protected $user;

	/**
	 * The password require to access the service
	 * 
	 * @var string
	 */
	protected $pwd;
	
	/**
	 * A unique ID to identify this connexion to the service
	 * 
	 * Every class that extend microBlogService must be named by the 
	 * service global id prefix with the string "mb". The service 
	 * global id must start with a uper case letter.
	 * 
	 * For exemple, to access to twitter.com, you could use "Twitter"
	 * as the service global id and the class that will extend the
	 * microBlogService will be named "mbTwitter"
	 * 
	 * knowing that, the $serviceId is compute as follow :
	 * 
	 * $serviceId = md5(strtolower(service global id) + username);
	 * 
	 * @var string
	 */
	protected $serviceId;

	
	/**
	 * Class constructor
	 * 
	 * @param $user string;
	 * @param $pwd string;
	 */
	public function __construct($user,$pwd)
	{
		if(empty($user)||empty($pwd))
			throw new microBlogException('Unable to authenticate service', __LINE__);
		
		$this->user = (string)$user;
		$this->pwd  = (string)$pwd;
	}
	
	
	/**
	 * Return the service unique ID;
	 * 
	 * @return string
	 */
	public function getServiceId()
	{
		return $this->serviceId;
	}
	
	
	/**
	 * Method that perform some commons sanitizations tasks
	 * 
	 * - Remove all HTML tags
	 * 
	 * @param $txt
	 * @return string
	 */
	static public function sanitize($txt)
	{
		$txt = strip_tags($txt);
		
		return $txt;
	}
	
	
	/**
	 * Method that perform some commons output formating
	 * 
	 * - Convert prevent from forbiden characters such as > < and &
	 * - Convert URL into HTML links
	 * 
	 * @param $txt
	 * @return string
	 */
	static public function formatOutput($txt)
	{
		$txt = htmlentities($txt);
		$txt = preg_replace('#(https?://[^\s]+)#', '<a href="\1">\1</a>', $txt);
		
		return $txt;
	}
	

# ABSTRACT METHODS ////////////////////////////
	
	/**
	 * Send a note to the service
	 *
	 * @param $text string
	 * @return bool
	 */
	abstract public function sendNote($text);
	
	/**
	 * Method that get the user timeline.
	 *
	 * @param $limit int Number of notes per pages
	 * @param $page int Current page to get
	 * @param $since int Timestamp of the oldest note to get
	 * @param $user string explicite username if required by the service
	 * @return array
	 */
	abstract public function getUserTimeline($limit=20,$page=1,$since=NULL,$user=NULL);
	
	/**
	 * Method that get user's friends timeline
	 *
	 * @param $limit int Number of notes per pages
	 * @param $page int Current page to get
	 * @param $since int Timestamp of the oldest note to get
	 * @return array
	 */
	abstract public function getFriendsTimeline($limit=20,$page=1,$since=NULL);
	
	/**
	 * Method that perform a search on the service
	 *
	 * @param $query string The search query
	 * @param $limit int Number of notes per pages
	 * @param $page int Current page to get
	 * @param $since int Timestamp of the oldest note to get
	 * @return array
	 */
	abstract public function search($query,$limit=20,$page=1,$since=NULL);
}