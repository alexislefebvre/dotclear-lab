<?php


class Subscription
{

	public static function subscribe(ArrayObject $subscription)
	{
		global $core;
		
		if($core->userExists($subscription['login'])) {
			throw new Exception(__('User already exists'));
		}
		
		if($core->blogExists($subscription['blog_url'])) {
			throw new Exception(__('A blog already exists at this URL'));
		}
		
		self::_createUser($subscription);
		self::_createBlog($subscription);
	}
	
	protected static function _createUser(ArrayObject $subscription)
	{
		global $core;
		
		//augmentation des droits pour pouvoir ajouter le user
		//@TODO : changer admin par un recuperation de login
		$core->auth->checkUser('mvachette');
		
		//user
		$cur = $core->con->openCursor($core->prefix.'user');
		
		$cur->user_id		= $subscription['login'];
		$cur->user_super	= 0;  
		$cur->user_email	= $subscription['mail'];
		$cur->user_pwd		= $subscription['password'];
		
		if (!preg_match('/^[A-Za-z0-9._-]{2,}$/',$cur->user_id)) {
			throw new Exception(__('User ID must contain at least 2 characters using letters, numbers or symbols.'));
		}
		
		if ($cur->user_creadt === null) {
			$cur->user_creadt = array('NOW()');
		}
		
		$core->addUser($cur);
	}
	
	protected static function _createBlog(ArrayObject $subscription)
	{
		global $core;
		
		//settings
		$core->blog->settings->setNamespace('subscription');
		$blogs_folder_path		= $core->blog->settings->blogs_folder_path;	
		$dotclear_folder_path	= $core->blog->settings->dotclear_folder_path;	
		
		//blog
		$root_url = 'http://'.$subscription['blog_url'].'.'.urlSubscription::getDomainName().'/';
		
		$cur = $core->con->openCursor($core->prefix.'blog');
	
		$cur->blog_id	= $subscription['blog_url'];
		$cur->blog_url	= $root_url.'index.php/';
		$cur->blog_name = $subscription['blog_name'];
		
		$core->addBlog($cur);

		//permissions du blog
		$core->setUserBlogPermissions($subscription['login'], $subscription['blog_url'],  array('admin' => 1, 'blogroll' => 1), true);

		$core->blogDefaults($cur->blog_id);

		$blog_settings = new dcSettings($core,$subscription['blog_url']);
		$blog_settings->setNameSpace('system');
		$blog_settings->put('lang',http::getAcceptLanguage());
		
		$blog_settings->put('themes_path',$core->blog->themes_path);
		$blog_settings->put('themes_url',$core->blog->themes_path); //TODO : injection via la config
		
		//creating blog folder and index.php file
		$path = $blogs_folder_path.$subscription['blog_url'];
		mkdir ($path);
		chmod ($path, 0755);
		mkdir ($path."/public");
		chmod ($path."/public", 0755);
				
		file_put_contents($path.'/index.php',"<?php\n\n".
			"define('DC_BLOG_ID','".$subscription['blog_url']."'); # identifiant du blog\n".
			"require '".realpath($dotclear_folder_path.'/inc/public/prepend.php')."';\n\n?>");	//TODO : param dans la conf pour le path de dotclear
		
	}



}