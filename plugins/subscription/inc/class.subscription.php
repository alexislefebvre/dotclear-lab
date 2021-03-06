<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Subscription, a plugin for Dotclear.
# 
# Copyright (c) 2010 Marc Vachette
# marc.vachette@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------


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
		
		
		//augmentation des droits pour pouvoir ajouter le user
		self::_setSuperAdminRights();
		
		self::_createUser($subscription);
		self::_createBlog($subscription);
	}
	
	protected function _setSuperAdminRights()
	{
		global $core;
		
		$users = $core->getBlogPermissions($core->blog->id,true);		
		
		foreach($users as $id => $user) {
			if(true === $user['super']) {
				break;
			}
		}
		
		$core->auth->checkUser($id);
	}
	
	protected static function _createUser(ArrayObject $subscription)
	{
		global $core;
		
		$cur = $core->con->openCursor($core->prefix.'user');
		
		$cur->user_id		= $subscription['login'];
		$cur->user_super	= 0;  
		$cur->user_email	= $subscription['mail'];
		$cur->user_pwd		= $subscription['password'];
		
		if ($cur->user_creadt === null) {
			$cur->user_creadt = array('NOW()');
		}
		
		$core->addUser($cur);
	}
	
	protected static function _createBlog(ArrayObject $subscription)
	{
		global $core;
		
		//settings
		$settings = new dcSettings($core, null);
		$blogs_folder_path		= $settings->subscription->blogs_folder_path;	
		$dotclear_folder_path	= $settings->subscription->dotclear_folder_path;	
		
		//blog
		$root_url = 'http://'.$subscription['blog_url'].'.'.urlSubscription::getDomainName().'/';
		
		$cur = $core->con->openCursor($core->prefix.'blog');
	
		$cur->blog_id	= $subscription['blog_url'];
		
		$cur->blog_url	= $root_url.'index.php';
		//$cur->blog_url.= ($core->url->mode == 'query_string') ? '?' : '/';
		
		$cur->blog_name = $subscription['blog_name'];
		
		$core->addBlog($cur);

		//permissions du blog
		$core->setUserBlogPermissions($subscription['login'], $subscription['blog_url'],  array('admin' => 1, 'blogroll' => 1), true);

		//settings
		$core->blogDefaults($cur->blog_id);

		$blog_settings = new dcSettings($core,$subscription['blog_url']);
		$blog_settings->setNameSpace('system');
		$blog_settings->put('lang',http::getAcceptLanguage());
		//themes settings should be defined as global parameters
		
		
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