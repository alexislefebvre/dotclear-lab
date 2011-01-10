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

$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');

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
				
				$_ctx->subscription['name'] = preg_replace('/[\n\r]/','',$_POST['s_name']);
				$_ctx->subscription['mail'] = preg_replace('/[\n\r]/','',$_POST['s_mail']);
				$_ctx->subscription['login'] = preg_replace('/[\n\r]/','',$_POST['s_login']);
				$_ctx->subscription['password'] = preg_replace('/[\n\r]/','',$_POST['s_password']);
				$_ctx->subscription['password_confirm'] = preg_replace('/[\n\r]/','',$_POST['s_password_confirm']);
				$_ctx->subscription['blog_name'] = preg_replace('/[\n\r]/','',$_POST['s_blog_name']);
				$_ctx->subscription['blog_url'] = preg_replace('/[\n\r]/','',$_POST['s_blog_url']);
				
				Subscription::subscribe($_ctx->subscription);
				
				http::redirect($core->blog->url.$core->url->getBase('subscription2').'/created');
			}
			catch (Exception $e)
			{
				$_ctx->subscription['error'] = true;
				$_ctx->subscription['error_msg'] = $e->getMessage();
			}
			
		}
			
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
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("subscription")').'; ?>';
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
		return "<?php echo '$domain_name'; ?>";
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