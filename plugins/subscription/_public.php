<?php
# ***** BEGIN LICENSE BLOCK *****
# This file a plugin of DotClear.
# Copyright (c) Marc Vachette. All rights
# reserved.
#
#Subscription2 is free software; you can redistribute it and/or modify
# it under the terms of the Creative Commons License "Attribution"
# see the page http://creativecommons.org/licenses/by/2.0/ for more information
# 
# Subscription is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# Creative Commons License for more details.
#
# ***** END LICENSE BLOCK *****

$core->url->register('subscription','subscribe','^subscribe(/(.*))?$',array('urlSubscription','create'));

$core->tpl->addBlock('SubscriptionIf',array('tplSubscription','SubscriptionIf'));
$core->tpl->addValue('SubscriptionURL',array('tplSubscription','SubscriptionURL'));
$core->tpl->addValue('SubscriptionMsgSuccess',array('tplSubscription','SubscriptionMsgSuccess'));
$core->tpl->addValue('SubscriptionMsgError',array('tplSubscription','SubscriptionMsgError'));
$core->tpl->addValue('SubscriptionDomainName',array('tplSubscription','SubscriptionDomainName'));
$core->tpl->addValue('SubscriptionPageTitle',array('tplSubscription','SubscriptionPageTitle'));
$core->tpl->addValue('SubscriptionFormCaption',array('tplSubscription','SubscriptionFormCaption'));
$core->tpl->addValue('SubscriptionName',array('tplSubscription','SubscriptionName'));
$core->tpl->addValue('SubscriptionEmail',array('tplSubscription','SubscriptionEmail'));
$core->tpl->addValue('SubscriptionLogin',array('tplSubscription','SubscriptionLogin'));
$core->tpl->addValue('SubscriptionBlogName',array('tplSubscription','SubscriptionBlogName'));
$core->tpl->addValue('SubscriptionBlogUrl',array('tplSubscription','SubscriptionBlogUrl'));


class urlSubscription extends dcUrlHandlers {

	public static function getDomainName() {
			return $_SERVER['HTTP_HOST'];
	}
	
	public static function create($args)
	{
		global $core, $_ctx;
		
		if (!$core->blog->settings->blogs_folder_path) {
			self::p404();
			exit;
		}
		
		$_ctx->subscription = new ArrayObject(array(
			'name' => '',
			'mail' => '',
			'login' => '',
			'password' => '',
			'password_confirm' => '',
			'blog_name' => '',
			'blog_url' => '',
			'created' => false,
			'error' => false,
			'error_msg' => ''
		));
		
		$create_msg = 
			isset($_POST['s_name']) && isset($_POST['s_mail']) && isset($_POST['s_login']) &&
			isset($_POST['s_password']) && isset($_POST['s_password_confirm']) &&
			isset($_POST['s_blog_name']) && isset($_POST['s_blog_url']);
		
		if ($args == 'created')
		{
			$_ctx->subscription['created'] = true;
		}
		elseif ($create_msg)
		{
			# Spam trap
			if (!empty($_POST['f_mail'])) {
				http::head(412,'Precondition Failed');
				header('Content-Type: text/plain');
				echo "So Long, and Thanks For All the Fish";
				exit;
			}
			
		
			try
			{
				$_ctx->subscription['name'] = preg_replace('/[\n\r]/','',$_POST['s_name']);
				$_ctx->subscription['mail'] = preg_replace('/[\n\r]/','',$_POST['s_mail']);
				$_ctx->subscription['login'] = preg_replace('/[\n\r]/','',$_POST['s_login']);
				$_ctx->subscription['password'] = preg_replace('/[\n\r]/','',$_POST['s_password']);
				$_ctx->subscription['password_confirm'] = preg_replace('/[\n\r]/','',$_POST['s_password_confirm']);
				$_ctx->subscription['blog_name'] = preg_replace('/[\n\r]/','',$_POST['s_blog_name']);
				$_ctx->subscription['blog_url'] = preg_replace('/[\n\r]/','',$_POST['s_blog_url']);
							
				# Checks provided fields
				if (empty($_POST['s_name'])) {
					throw new Exception(__('You must provide a name.'));
				}
				
				if (!text::isEmail($_POST['s_mail'])) {
					throw new Exception(__('You must provide a valid email address.'));
				}
				
				if (empty($_POST['s_login'])) {
					throw new Exception(__('You must provide a login.'));
				}
				
				if (empty($_POST['s_password'])) {
					throw new Exception(__('You must provide a password.'));
				}
				
				if ($_POST['s_password_confirm'] !== $_POST['s_password']) {
					throw new Exception(__("Passwords don't match"));
				}
				
				if (empty($_POST['s_blog_name'])) {
					throw new Exception(__('You must provide a name for you blog.'));
				}
				
				if (empty($_POST['s_blog_url'])) {
					throw new Exception(__('You must provide an url for your blog.'));
				}
				
				//creating user and blog
				$core->blog->settings->setNamespace('subscription2');
				$blogs_folder_path = $core->blog->settings->get('blogs_folder_path');	
				$dotclear_folder_path = $core->blog->settings->get('dotclear_folder_path');	
				
				
				if($core->userExists($_ctx->subscription['login'])) {
					throw new Exception(__('User already exists'));
				}
				
				if($core->blogExists($_ctx->subscription['blog_url'])) {
					throw new Exception(__('A blog already exists at this URL'));
				}
				
				
				//augmentation des droits TODO : changer admin par un recuperation de login
				$core->auth->checkUser('marc');
				
				//user
				$cur = $core->con->openCursor($core->prefix.'user');

				$cur->user_id = $_ctx->subscription['login'];
				$cur->user_super = 0;  
				$cur->user_email = $_ctx->subscription['mail'];
				$cur->user_pwd = $_ctx->subscription['password'];
				
				if (!preg_match('/^[A-Za-z0-9._-]{2,}$/',$cur->user_id)) {
					throw new Exception(__('User ID must contain at least 2 characters using letters, numbers or symbols.'));
				}
				if ($cur->user_creadt === null) {
					$cur->user_creadt = array('NOW()');
				}
			
				//$cur->insert();
				$core->addUser($cur);
				
				
				//blog
				
				$root_url = 'http://'.$_ctx->subscription['blog_url'].'.'.self::getDomainName().'/';
				
				$cur = $core->con->openCursor($core->prefix.'blog');
			
				$cur->blog_id = $_ctx->subscription['blog_url'];
				$cur->blog_url = $root_url.'index.php/';
				$cur->blog_name = $_ctx->subscription['blog_name'];
				
				$core->addBlog($cur);
	
				//permissions du blog
				
				$core->setUserBlogPermissions($_ctx->subscription['login'], $_ctx->subscription['blog_url'],  array('admin'=>1, 'blogroll'=>1), true);
	
				$core->blogDefaults($cur->blog_id);

				$blog_settings = new dcSettings($core,$_ctx->subscription['blog_url']);
				$blog_settings->setNameSpace('system');
				$blog_settings->put('lang',http::getAcceptLanguage());
				
				$blog_settings->put('themes_path',$core->blog->themes_path);
				$blog_settings->put('themes_url',$core->blog->themes_path); //TODO : injection via la config
				
				//creating blog folder and index.php file
				$path = $blogs_folder_path.$_ctx->subscription['blog_url'];
				if(!file_exists($path)){
					mkdir ($path);
					chmod ($path, 0755);
					mkdir ($path."/public");
					chmod ($path."/public", 0755);
				}
				
				file_put_contents($path.'/index.php',"<?php\n\n".
					"define('DC_BLOG_ID','".$_ctx->subscription['blog_url']."'); # identifiant du blog\n".
					"require '".realpath($dotclear_folder_path.'/inc/public/prepend.php')."';\n\n?>");	//TODO : param dans la conf pour le path de dotclear
				
				
								
				http::redirect($core->blog->url.$core->url->getBase('subscription2').'/created');
			}
			catch (Exception $e)
			{
				$_ctx->subscription['error'] = true;
				$_ctx->subscription['error_msg'] = $e->getMessage();
			}
			
		}
			
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('new_blog.html');
		exit;
	}

}

class tplSubscription
{
	public static function SubscriptionIf($attr,$content)
	{
		$if = array();
		
		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';
		
		if (isset($attr['created'])) {
			$sign = (boolean) $attr['created'] ? '' : '!';
			$if[] = $sign."\$_ctx->subscription['created']";
		}
		
		if (isset($attr['error'])) {
			$sign = (boolean) $attr['error'] ? '' : '!';
			$if[] = $sign."\$_ctx->subscription['error']";
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	public static function SubscriptionURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("subscription2")').'; ?>';
	}
	
	public static function SubscriptionMsgSuccess($attr)
	{
		/*return '<?php echo $core->blog->settings->cm_msg_success; ?>';*/
		return '<?php echo "Yeah !"; ?>';
	}
	
	public static function SubscriptionMsgError($attr)
	{
		//TODO : message d'erreur depuis la conf
		return '<?php echo html::escapeHTML($_ctx->subscription["error_msg"]); ?>';
		/*return '<?php echo sprintf($core->blog->settings->subscription2_msg_error,html::escapeHTML($_ctx->subscription["error_msg"])); ?>';*/
	}
	
	public static function SubscriptionDomainName() {
		$domain_name = urlSubscription::getDomainName();	
		return "<?php echo $domain_name; ?>";
	}
	
	public static function SubscriptionPageTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->subscription_page_title').'; ?>';
	}
	
	public static function SubscriptionFormCaption($attr)
	{
		//TODO : titre du formulaire depuis la conf
		//$f = $GLOBALS['core']->tpl->getFilters($attr);
		/*return '<?php echo '.sprintf($f,'$core->blog->settings->subscription_page_title').'; ?>';*/
	}
	
	public static function SubscriptionName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->subscription["name"]').'; ?>';
	}
	
	public static function SubscriptionEmail($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->subscription["mail"]').'; ?>';
	}
	
	public static function SubscriptionLogin($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->subscription["login"]').'; ?>';
	}
	
	public static function SubscriptionBlogName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->subscription["blog_name"]').'; ?>';
	}
	
	public static function SubscriptionBlogUrl($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->subscription["blog_url"]').'; ?>';
	}


}



?>