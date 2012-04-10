<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012 Osku contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class urlAgora extends dcUrlHandlers
{
	public static function p401()
	{
		throw new Exception ("Unauthorized",401);
	}
	
	public static function error401($args,$type,$e)
	{
		if ($e->getCode() == 401) 
		{
			$_ctx =& $GLOBALS['_ctx'];
			$core =& $GLOBALS['core'];

			header('Content-Type: text/html; charset=UTF-8');
			http::head(401,'Service Unavailable');
			$core->url->type = '401';
			$_ctx->current_tpl = 'agora_401.html';
			$_ctx->content_type = 'text/html';

			echo $core->tpl->getData($_ctx->current_tpl);

			# --BEHAVIOR-- publicAfterDocument
			$core->callBehavior('publicAfterDocument',$core);
			exit;
		}
	}
	
	public static function checkAuth()
	{
		$core =& $GLOBALS['core'];
		if ($core->auth->userID() == false)
		{
			self::p401();
		}
	}
	
     public static function callbackFoo($args)
     {
          #Woohoo :)
          return;
     }

	public static function recover($args)
	{
		// recover/ : set a recovery key 
		// recover/blabla : create a newpassword and send it to user mailbox
		
		global $core, $_ctx;
		
		$allow = $core->auth->allowPassChange();
		$akey = ($allow && !empty($args)) ? $args : null;
		
		$user_id = $user_pwd = $user_key = $user_email = null;
		
		$core->agora_recover = new ArrayObject();
		$core->agora_recover['login'] = '';
		$core->agora_recover['email'] = '';
		
		# Recover password
		if ($allow && !empty($_POST['ru_login']) && !empty($_POST['ru_email']))
		{
			$user_id = !empty($_POST['ru_login']) ? $_POST['ru_login'] : null;
			$user_email = !empty($_POST['ru_email']) ? $_POST['ru_email'] : '';
			$core->agora_recover['login'] = $user_id;
			$core->agora_recover['email'] = $user_email;
			try
			{
				$core->agora_recover['key'] = $core->auth->setRecoverKey($user_id,$user_email);
				mailAgora::sendRecoveryEmail($core->agora_recover);
				http::head(200,'OK');
				$_ctx->agora_message = sprintf(__('The e-mail was sent successfully to %s.'),'<strong>'.$user_email.'</strong>');
			}
			
			catch (Exception $e)
			{
				$_ctx->form_error = $e->getMessage();
			}
		}
		elseif ($akey)
		{
			try
			{
				$recover_res = $core->auth->recoverUserPassword($akey);
				mailAgora::sendNewPasswordEmail($recover_res);
				http::head(200,'OK');
				$_ctx->agora_message = __('Your new password is in your mailbox.');
			}
			
			catch (Exception $e)
			{
				$_ctx->form_error = $e->getMessage();
			}
		
		}
		
	self::serveDocument('agora_recover.html');
	return;
	}

	public static function newaccount($args)
	{
		// Ajouter un test sur les conditions générales ... TODO behavior ?
		// URL register : create the user but without any perm
		// register/?key=12345678 : end of registration : add perm 'member'
		global $core, $_ctx;
		
		$core->agora_register = new ArrayObject();
		$core->agora_register['login'] = '';
		$core->agora_register['email'] = '';
		$core->agora_register['preview'] = false;
		$core->agora_register['key'] = false;
		$core->agora_register['pwd'] = '';
		
		//$url = $core->blog->url.$core->url->getBase("agora");
		
		$register = isset($_POST['ru_login']) && isset($_POST['ru_email']);
		$key =  !empty($_GET['key']) ? $_GET['key'] : null;
		
		if ($register)
		{
			// Spam trap
			if (!isset($_POST['email2']) || $_POST['email2'] !== '') {
				http::head('412');
				header('Content-Type: text/plain');
				echo "So Long, and Thanks For All the Fish";
				return;
			}
			
			// new password from clearbricks/common/lib.crypt.php
			$pwd = crypt::createPassword();
			$login = trim($_POST['ru_login']);
			$mail = trim($_POST['ru_email']); 
			$register_preview = !empty($_POST['preview']);
			
			$core->agora_register['login'] = $login;
			$core->agora_register['email'] = $mail;
			$core->agora_register['pwd'] = $pwd;
			
			if ($register_preview)
			{
				# --BEHAVIOR-- publicBeforeSignUp
				$core->callBehavior('publicBeforeSignUp',$core->agora_register);
				$core->agora_register['preview'] = true;
			}
			else
			{
				$cur = $core->con->openCursor($core->prefix.'user');
				$cur->user_id = $login;
				$cur->user_status = -2; /*User if not yet verified*/
				$cur->user_email = html::clean($mail);
				$cur->user_pwd = $pwd;
				$cur->user_lang = $core->blog->settings->system->lang;
				$cur->user_tz = $core->blog->settings->system->blog_timezone;
				$cur->user_default_blog = $core->blog->id;
				$cur->user_options = $core->userDefaults();
				// New post et message default status : -2 pending, 0 unpublished, 1 published.
				$cur->user_post_status = (integer) $core->blog->settings->agora->content_status;
				
				try
				{
					if (!text::isEmail($cur->user_email))
					{
						throw new Exception(__('You must provide a valid email'));
					}
					
					if ($core->getUsers(array('user_id' => $cur->user_id),true)->f(0) > 0) 
					{
						throw new Exception(sprintf(__('User "%s" already exists.'),html::escapeHTML($cur->user_id)));
					}
					
					if (!preg_match('/^[A-Za-z0-9@._-]{2,}$/',$cur->user_id))
					{
						throw new Exception(__('User ID must contain at least 2 characters using letters, numbers or symbols.'));
					}
					
					# --BEHAVIOR-- publicBeforeUserCreate
					$core->callBehavior('publicBeforeUserCreate',$cur);
					
					$user_id = $core->auth->sudo(array($core,'addUser'),$cur);
					mailAgora::sendActivationEmail($core->agora_register);
					# --BEHAVIOR-- publicAfterUserCreate
					$core->callBehavior('publicAfterUserCreate',$cur,$user_id);
					
					//header('Content-Type: text/html; charset=UTF-8');
					http::head(201,'Created');
					$_ctx->agora_message = sprintf(__('Your account %s is almost ready. You will receive an email with further information.'),'<strong>'.$user_id.'</strong>');
				}
			
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}

			}
		}
		
		if($key)
		{
			if (preg_match('/^[a-fA-F\d]{32}$/',$key))
			{
				try
				{
					$_ctx->user = $core->agora->getUnregistredUser($key);
					// throw Exception if invalid key ...
					
					$user_id = $_ctx->user['user_id'];
					$user_status = $_ctx->user['user_status'];
					$login ='<a href="'.$core->blog->url.$core->url->getURLFor('login').'">'.__('log in').'</a>';
					// http://dev.dotclear.org/2.0/browser/trunk/inc/core/class.dc.core.php#L684 

					// newaccount : permission is already added
					//if ($core->agora->isMember($user_id))
					if ($user_status == 1)
					{
						throw new Exception(sprintf(__('User %s is already registred. You can %s.'),'<strong>'.$user_id.'</strong>',$login));
					}
					else
					{
						if ($core->blog->settings->agora->register_modo) {
							$core->auth->sudo(array($core->agora,'moderateUser'),$user_id,-1,'');
							$_ctx->agora_message = sprintf(__('Your account (%s) is now awaiting validation. Be patient.'),'<strong>'.$user_id.'</strong>');
						} else {
							// No moderation : users are granted "active" and can connect. Status of new content is defined by a setting.
							$core->auth->sudo(array($core->agora,'moderateUser'),$user_id,1,'');
							$_ctx->agora_message = sprintf(__('Your account (%s) is now registred. You can %s.'),'<strong>'.$user_id.'</strong>',$login);
						}
					}
				}
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}
			}
			else
			{
				$_ctx->form_error = __('This is a wrong registration URL. Registration failed.');
			}
		}
		
		self::serveDocument('agora_register.html','text/html',false);
		return;
	}
	
	public static function login($args)
	{
		// module de password recovery : envoi d'un email avec mot de passe généré auto.. OK
		// URL login : login user 
		
		global $core, $_ctx;
		$url = $core->blog->url;
		
		if (!isset($_SESSION['sess_user_id']))
		{
			$login = isset($_POST['li_login']) && isset($_POST['li_pwd']);
			
			if ($login)
			{
				$login = trim($_POST['li_login']);
				$pwd = trim($_POST['li_pwd']);
				
				try
				{
					$user_id = $core->agora->userLogin($login,$pwd);
					$redir = $url;
					http::redirect($redir);
					return;
				}
				
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage(); 
				}
			}
			self::serveDocument('agora_login.html');
			return;
		}
		else
		{
			header('Location: '.$url);
			return;
		}
	}

	public static function logout($args)
	{
		// URL logout : logout user without template
		
		global $core;

		$cookie_name = $core->blog->settings->agora->global_auth ? 
			'dc_agora_sess' : 
			'dc_agora_'.$core->blog->id;

		$cookie_auto_name = 'dc_agora_auto_'.$core->blog->id;
		
		if (isset($_SESSION['sess_user_id']))
		{
			//$_SESSION['sess_user_id'] = null;
			$core->session->destroy();
			
			if (isset($_COOKIE[$cookie_name]))
			{
				unset($_COOKIE[$cookie_name]);
				setcookie($cookie_name,false,-600);
			}
			if (isset($_COOKIE[$cookie_auto_name]))
			{
				unset($_COOKIE[$cookie_auto_name]);
				setcookie($cookie_auto_name,false,-600);
			}
			//what about comment_info cookie ?
			setcookie('comment_info',false,-600);
		}
		$redir = $core->blog->url;
		http::redirect($redir);
		return;
	}

	public static function people($args)
	{
		global $core, $_ctx;

		//if ($core->blog->settings->agora->private_flag) {
			self::checkAuth();
		//}

		$_ctx->users = $core->agora->getUsers();
		self::serveDocument('agora_people.html','text/html');
	}

	public static function old_people($args)
	{
		global $core, $_ctx;

		//if ($core->blog->settings->agora->private_flag) {
			self::checkAuth();
		//}

		$n = self::getPageNumber($args);

		if ($args && !$n)
		{
			# "Then specified URL went unrecognized by all URL handlers and 
			# defaults to the home page, but is not a page number.
			self::p404();
		}
		else
		{
			if ($n) {
				$GLOBALS['_page_number'] = $n;
				//$core->url->type = $n > 1 ? 'default-page' : 'default';
			}
			
			if (empty($_GET['q'])) {
				$_ctx->users = $core->agora->getUsers();
				self::serveDocument('agora_people.html','text/html');
				return;
			} else {
				self::user_search();
			}
		}
	}

	public static function user_search()
	{
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		$core->url->type='user_search';
		
		$GLOBALS['_user_search'] = !empty($_GET['q']) ? rawurldecode($_GET['q']) : '';
		if ($GLOBALS['_user_search']) {
			$params = new ArrayObject(array('q' => $GLOBALS['_user_search']));
			$core->callBehavior('publicBeforeUserSearchCount',$params);
			$GLOBALS['_user_search_count'] = $core->agora->getUsers($params,true)->f(0);
		}
		
		self::serveDocument('agora_search.html');
	}
	
	public static function preferences($args)
	{
		global $core, $_ctx;

		/*if ($core->blog->settings->agora->private_flag) {
			self::checkAuth();
		}*/
	
		if (($args != ''))
		{
			self::p404();
		}	

		$user_id = ($core->auth->userID() != false && isset($_SESSION['sess_user_id'])) ? $core->auth->userID() : '';

		$_ctx->users = $core->agora->getUser($user_id);

		if ($_ctx->users->isEmpty()) {
			self::p404();
		}
		
		$_ctx->profile_user = new ArrayObject();
		
		$_ctx->profile_user['email'] = $_ctx->users->user_email;
		$_ctx->profile_user['url'] = $_ctx->users->user_url;
		$_ctx->profile_user['status'] = $_ctx->users->user_status;
		$_ctx->profile_user['pwd'] = '';
		$_ctx->profile_user['msg'] = '';
		
		if (!empty($_POST['submit']))
		{
			//$_ctx->profile_user['pseudo'] = trim($_POST['li_pseudo']);
			$_ctx->profile_user['email']  = trim($_POST['li_email']);
			$_ctx->profile_user['url']  = trim($_POST['li_url']);
			$_ctx->profile_user['pwd'] = trim($_POST['li_pwd']);
			$_ctx->profile_user['pwd2'] = trim($_POST['li_pwd2']);
			$redir = $core->blog->url.$core->url->getURLFor("preferences");
			$redir .= $core->blog->settings->system->url_scan == "query_string" ? '&' : '?';

			 if (empty($_ctx->form_error))
			{
				try
				{
				
					if (!empty($_POST['li_pwd']))
					{
						if (empty($_POST['li_pwd2']))
						{
							 throw new Exception(__('You must confirm your password'));
						}
						elseif ($_POST['li_pwd'] != $_POST['li_pwd2'])
						{
							 throw new Exception(__('Please, check your password. Passwords don\'t match'));
							}
						else {
							$cur->user_pwd = $_ctx->profile_user['pwd'];
						}
					}
			
					if (empty($_ctx->profile_user['email']) ||
					!text::isEmail($_ctx->profile_user['email']))
					{
						throw new Exception(__('You must provide a valid email'));
					}
				
					$cur = $core->con->openCursor($core->prefix.'user');
					$cur->user_id = $user_id;
					$cur->user_email = $_ctx->profile_user['email'];
					$cur->user_url = $_ctx->profile_user['url'];
					if (!empty($_ctx->profile_user['pwd']))
					{
						$cur->user_pwd =  $_ctx->profile_user['pwd'];
					}
					
					# --BEHAVIOR-- publicBeforeUserCreate
					$core->callBehavior('publicBeforeUserUpdate',$cur,$user_id);
					
					$id = $core->auth->sudo(array($core,'updUser'),$user_id,$cur);
					
					//$_ctx->agora_message = sprintf(__('User %s successfully updated.'),'<strong>'.$args.'</strong>');
					# --BEHAVIOR-- publicAfterUserCreate
					$core->callBehavior('publicAfterUserUpdate',$cur,$id);
					
					if (!empty($id))
					{
						$redir_arg = 'upd=2#pr';
					}
					else
					{
						//$redir_arg = 'error=1';
					}
					
					header('Location: '.$redir.$redir_arg);
				}
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}
				
			}		
		}
		
		self::serveDocument('agora_preferences.html','text/html');
		return;
	}

	public static function profile($args)
	{
		// URL forum/profile/batman : edit/view profile ..
		// URL forum/profile/batman/ban : ban user  ..
		
		global $core, $_ctx;
		
		//if ($core->blog->settings->agora->private_flag) {
			self::checkAuth();
		//}
		
		if (($args == '') || (!is_string($args)))
		{
			self::p404();
		}
		else 
		{
			$_ctx->users = $core->agora->getUser($args);

			if ($_ctx->users->isEmpty()) {
				self::p404();
			}
		}

		self::serveDocument('agora_profile.html','text/html');
		return;
	}

	public static function newpost($args)
	{
		global $core, $_ctx;
		$user_id = ($core->auth->userID() != false && isset($_SESSION['sess_user_id'])) ? $core->auth->userID() : '';
		
		if ($args) {$args =  substr($args,1);}

		//$core->addBehavior('coreInitWikiPost',array('agoraBehaviors','coreInitWikiPost'));
		
		if (/*$args == '' ||*/ !$core->auth->userID()) {
			self::p401();
		}

		$params['cat_url'] = $args;
         
		$_ctx->categories = $core->blog->getCategories($params);
		
		$_ctx->post_preview = new ArrayObject();
		$_ctx->post_preview['title'] = '';
		$_ctx->post_preview['content'] = '';
		$_ctx->post_preview['rawcontent'] = '';
		$_ctx->post_preview['excerpt'] = '';
		$_ctx->post_preview['rawexcerpt'] = '';
		$_ctx->post_preview['preview'] = false;
		
		$post_new = isset($_POST['c_content']) && isset($_POST['c_title']);
		$post_excerpt = $post_excerpt_xhtml = $post_content_xhtml = '';
		//new post to change
		if ($post_new)// && ($core->agora->isMember($user_id)))
		{
			$title = $_POST['c_title'];
			$content = $_POST['c_content'];
			$preview = !empty($_POST['preview']);
			$format = $core->blog->settings->agora->content_syntax;
			$status = $core->auth->getInfo('user_post_status');
			$content_xhtml = '';
			
			if ($content != '')
			{
				$core->initWikiPost();
				$content_xhtml = $core->callFormater($format,$content);
				$content_xhtml = $core->HTMLfilter($content_xhtml);
			}

			$_ctx->post_preview['title'] = $title ;
			$_ctx->post_preview['content'] = $content_xhtml;
			$_ctx->post_preview['rawcontent'] = $content;
			
			if ($preview)
			{
				# --BEHAVIOR-- publicBeforePostPreview
				$core->callBehavior('publicBeforePostPreview',$_ctx->post_preview);
				
				$_ctx->post_preview['preview'] = true;
			}
			
			else
			{
				$core->blog->setPostContent(
					'',$format,$core->auth->getInfo('user_lang'),
					$_ctx->post_preview['rawexcerpt'],$_ctx->post_preview['excerpt'],
					$_ctx->post_preview['rawcontent'],$_ctx->post_preview['content']
				);
				$cur = $core->con->openCursor($core->prefix.'post');
				
				$cur->user_id = $user_id;
				$cur->post_title = $title;
				$offset = dt::getTimeOffset($core->blog->settings->system->blog_timezone);
				$cur->post_dt = date('Y-m-d H:i:s',time() + $offset);
				$cur->post_format = $format;// setting
				$cur->post_status = $status; // setting
				$cur->post_lang = $core->auth->getInfo('user_lang');
				$cur->post_content = $content;
				//$cur->post_content_xhtml = $content_xhtml;
				//$cur->post_type = 'post'; // may change with following behavior publicBeforePostCreate
				$cur->post_open_comment = 1;
				$redir = $core->blog->url;//.$core->url->getBase("post").'/';
			
				try
				{
					if (!$cur->post_title) {
						throw new Exception(__('No title.'));
					}
					
					if (!$cur->post_content) {
						throw new Exception(__('No content.'));
					}
					
					# --BEHAVIOR-- publicBeforePostCreate
					$core->callBehavior('publicBeforePostCreate',$cur);
				
					$post_id = $core->auth->sudo(array($core->blog,'addPost'),$cur);
				
					# --BEHAVIOR-- publicAfterPostCreate
					$core->callBehavior('publicAfterPostCreate',$cur,$post_id);
				
					$redir .= $core->url->getURLFor('post',$cur->post_url);
					$redir .= $core->blog->settings->system->url_scan == "query_string" ? '&' : '?';
					$redir_arg = ($status == 1) ? 'post=1' : 'post=0';
					$redir_arg .= '#pr';
				
					header('Location: '.$redir.$redir_arg);
					return;
				}
		
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}
			}
		}
		self::serveDocument('agora_newpost.html','text/html');
		return;
	}

	public static function thread($args)
	{
		global $core, $_ctx;
		
		// to be defined
		//$core->addBehavior('coreInitWikiPost',array('agoraBehaviors','coreInitWikiPost'));
		
		if ($args == ''){
			self::p404();
		}
		$core->blog->withoutPassword(false);
		
		$user_id = ($core->auth->userID() != false && isset($_SESSION['sess_user_id'])) ? $core->auth->userID() : '';
		
		$params = new ArrayObject();
		$params['post_url'] = $args;
		
		$_ctx->posts = $core->blog->getPosts($params);
		
		if ($_ctx->posts->isEmpty())
		{
			self::p404();
		}
		
		$post_id = $_ctx->posts->post_id;
		$post_password = $_ctx->posts->post_password;
		
		# Password protected entry
		if ($post_password != '' && !$_ctx->preview)
		{
			# Get passwords cookie
			if (isset($_COOKIE['dc_passwd'])) {
				$pwd_cookie = unserialize($_COOKIE['dc_passwd']);
			} else {
				$pwd_cookie = array();
			}
	
			# Check for match
			if ((!empty($_POST['password']) && $_POST['password'] == $post_password)
			|| (isset($pwd_cookie[$post_id]) && $pwd_cookie[$post_id] == $post_password))
			{
				$pwd_cookie[$post_id] = $post_password;
				setcookie('dc_passwd',serialize($pwd_cookie),0,'/');
			}
			else
			{
				self::serveDocument('password-form.html','text/html');
				return;
			}
		}

		$_ctx->message_preview = new ArrayObject();
		$_ctx->message_preview['content'] = '';
		$_ctx->message_preview['title'] = '';
		$_ctx->message_preview['rawcontent'] = '';
		$_ctx->message_preview['preview'] = false;
		
		$post_message = (isset($_POST['c_content']) && $_ctx->posts->commentsActive());
		$format = $core->blog->settings->agora->content_syntax;
		$status = $core->auth->getInfo('user_post_status');
		
		if ($post_message)
		{
			$content = $_POST['c_content'];
			$preview = !empty($_POST['preview']);
			
			if ($content != '')
			{
				$core->initWikiComment();
				$content_xhtml = $core->callFormater($format,$content);
				$content_xhtml = $core->HTMLfilter($content_xhtml);
			}
			
			$_ctx->message_preview['content'] = $content_xhtml;
			$_ctx->message_preview['rawcontent'] = $content;
			
			if ($preview)
			{
				# --BEHAVIOR-- publicBeforePostPreview
				$core->callBehavior('publicBeforeMessagePreview',$_ctx->message_preview);
				$_ctx->message_preview['preview'] = true;
			}
			
			else
			{
				# Check message form spam
				if (class_exists('dcAntispam') && isset($core->spamfilters))
				{
					# Fake cursor to check spam
					$cur = $core->con->openCursor('foo');
					$cur->comment_trackback = 0;
					$cur->comment_author = $user_id;
					$cur->comment_email = $core->auth->getInfo('user_email');
					$cur->comment_site = $core->auth->getInfo('user_url');
					$cur->comment_ip = http::realIP();
					$cur->comment_content = $content;
					$cur->post_id = 0; // That could break things...
					$cur->comment_status = 1;
					
					@dcAntispam::isSpam($cur);
					
					if ($cur->comment_status == -2) {
						$status = -3;
					}
					unset($cur);
				}
				
				
				$cur = $core->con->openCursor($core->prefix.'message');
				$cur->user_id = $user_id;
				$cur->message_format = $format;
				$cur->message_content = $content;
				//$cur->message_content_xhtml = $content_xhtml;
				$offset = dt::getTimeOffset($core->blog->settings->system->blog_timezone);
				$cur->message_dt = date('Y-m-d H:i:s',time() + $offset);
				$cur->post_id = $_ctx->posts->post_id;
				$cur->message_status =  $status ;
				
				$redir = $_ctx->posts->getURL();
				$redir .= $core->blog->settings->system->url_scan == "query_string" ? '&' : '?';
				
				try
				{
					# --BEHAVIOR-- publicBeforePostCreate
					$core->callBehavior('publicBeforeMessageCreate',$cur);
					
					$message_id = $core->auth->sudo(array($core->agora,'addMessage'),$cur);
					//$message_id = $core->agora->addMessage($cur);
					
					# --BEHAVIOR-- publicAfterPostCreate
					$core->callBehavior('publicAfterMessageCreate',$cur,$message_id);
					
					if ($cur->message_status == 1) {
						$redir_arg = 'msg=1';
					} else {
						$redir_arg = 'msg=0';
					}
					
					header('Location: '.$redir.$redir_arg);
					return;
				}
				
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}
			}
		}
		
		self::serveDocument('agora_post.html','text/html');
		return;
	}

	
	public static function publishpost($args)
	{
		global $core, $_ctx;
		$user_id = $core->auth->userID();
		
		if ($core->agora->isModerator($user_id) === false)
		{
			self::p404();
		}

		$post_id = $args;
		
		if (!is_numeric($post_id))
		{
			self::p404();
		}
		
		$params['post_id'] = $args;
		$params['no_content'] = true;
		$params['post_type'] = '';

		$_ctx->posts = $core->auth->sudo(array($core->blog,'getPosts'),$params);

		if ($_ctx->posts->isEmpty() )
		{
			self::p404();
		}
		
		$redir = $_ctx->posts->getURL();

		$redir .= $core->blog->settings->system->url_scan == "query_string" ? '&' : '?';
		
		try
		{
			# --BEHAVIOR-- publicBeforePostPublish
			$core->callBehavior('publicBeforePostPublish',$post_id);
			
			$core->auth->sudo(array($core->blog,'updPostStatus'),$_ctx->posts->post_id,1);
			
			# --BEHAVIOR-- publicAfterPostPublish
			$core->callBehavior('publicAfterPostPublish',$post_id);
			
			$redir_arg = 'post=2#pr';
			
			header('Location: '.$redir.$redir_arg);
			return;
		}
		
		catch (Exception $e)
		{
			$_ctx->form_error = $e->getMessage();
		}
			
	}

	public static function unpublishpost($args)
	{
		global $core, $_ctx;
		$user_id = $core->auth->userID();
		
		if ($core->agora->isModerator($user_id) === false)
		{
			self::p404();
		}

		$post_id = $args;
		
		if (!is_numeric($post_id))
		{
			self::p404();
		}
		
		$params['post_id'] = $args;
		$params['no_content'] = true;
		$params['post_type'] = '';

		$_ctx->posts = $core->auth->sudo(array($core->blog,'getPosts'),$params);
		
		if ($_ctx->posts->isEmpty() )
		{
			self::p404();
		}
		
		$redir = $_ctx->posts->getURL();

		$redir .= $core->blog->settings->system->url_scan == "query_string" ? '&' : '?';
		
		try
		{
			# --BEHAVIOR-- publicBeforePostUnpublish
			$core->callBehavior('publicBeforePostUnpublish',$post_id);
			
			$core->auth->sudo(array($core->blog,'updPostStatus'),$_ctx->posts->post_id,0);
			
			# --BEHAVIOR-- publicAfterPostUnpublish
			$core->callBehavior('publicAfterPostUnpublish',$post_id);
			
			$redir_arg = 'post=2#pr';
			
			header('Location: '.$redir.$redir_arg);
			return;
		}
		
		catch (Exception $e)
		{
			$_ctx->form_error = $e->getMessage();
		}
			
	}

	public static function editpost($args)
	{
		global $core, $_ctx;
		
		// $core->addBehavior('coreInitWikiPost',array('agoraBehaviors','coreInitWikiPost'));
		$user_id = $core->auth->userID();
		
		if ($core->auth->userID() == false)
		{
			self::p404();
		}
		
		$params['post_id'] = $args ;
		$params['post_type'] = '';
		if (!$core->blog->settings->agora->wiki_flag) {
			$_ctx->posts = $core->blog->getPosts($params);
		} else {
			$_ctx->posts = $core->auth->sudo(array($core->blog,'getPosts'),$params);
		}

		if ($_ctx->posts->isEmpty())
		{
			self::p404();
		}
		
		if ((!$_ctx->posts->isEditable() || !$_ctx->posts->isStillinTime()) && !$core->blog->settings->agora->wiki_flag)
		{
			self::p404();
		}

		$_ctx->post_preview = new ArrayObject();
		$_ctx->post_preview['content'] = $_ctx->posts->post_content_xhtml;
		$_ctx->post_preview['rawcontent'] = $_ctx->posts->post_content;
		$_ctx->post_preview['excerpt'] = $_ctx->posts->post_excerpt_xhtml;
		$_ctx->post_preview['rawexcerpt'] = $_ctx->posts->post_excerpt;
		$_ctx->post_preview['title'] = $_ctx->posts->post_title;
		$_ctx->post_preview['preview'] = false;
		$_ctx->post_preview['cat'] = $_ctx->posts->cat_id;
		$_ctx->post_preview['not_empty'] = ( $args == '' ) ? false : true;
		
		$edit_post = isset($_POST['c_content']) &&  isset($_POST['c_title']);
		$format = $core->blog->settings->agora->content_syntax;
		
		if ($edit_post)
		{
			$content = isset($_POST['c_content'])? $_POST['c_content'] : '';
			$title = isset($_POST['c_title'])? $_POST['c_title'] : '';
			$preview = !empty($_POST['preview']);
		
			if ($content != '')
			{
				$core->initWikiPost();
				$content_xhtml = $core->callFormater($format,$content);
				$content_xhtml = $core->HTMLfilter($content_xhtml);
			}
			
			$_ctx->post_preview['content'] = $content_xhtml;
			$_ctx->post_preview['rawcontent'] =  $content;
			$_ctx->post_preview['title'] = $_POST['c_title'];
			$_ctx->post_preview['not_empty'] = true;
			
			if ($preview)
			{
				# --BEHAVIOR-- publicBeforePostReview 
				$core->callBehavior('publicBeforePostPreview',$_ctx->post_preview);
				$_ctx->post_preview['preview'] = true;
			}
			else
			{
				$post_id = $args;
				$post_format = $format;
				$post_lang = $_ctx->posts->post_lang;
				
				$cur = $core->con->openCursor($core->prefix.'post');
				$cur->post_id = $post_id;
				// on ne change pas la date quand on édite..
				$cur->post_dt = $_ctx->posts->post_dt;
				$cur->post_title = isset($_POST['c_title'])? $_POST['c_title'] : $_ctx->posts->post_title;
				//$cur->post_excerpt = '';
				$cur->post_content = isset($_POST['c_content'])? $content : $_ctx->posts->post_content;
				//$cur->post_content_xhtml = $post_content_xhtml;
				$cur->post_format =  $post_format;
				
				$redir = $_ctx->posts->getURL();
				$redir .= $core->blog->settings->system->url_scan == "query_string" ? '&' : '?';
				
				try
				{
					# --BEHAVIOR-- publicBeforePostUpdate
					$core->callBehavior('publicBeforePostUpdate',$cur,$post_id);
				
					$core->auth->sudo(array($core->blog,'updPost'),$post_id,$cur);
					//$core->blog->updPost($post_id,$cur);
				
					# --BEHAVIOR-- publicAfterPostUpdate
					$core->callBehavior('publicAfterPostUpdate',$cur,$post_id);
				
					$redir_arg = 'post=2#pr';
				
					header('Location: '.$redir.$redir_arg);
					return;
				}
			
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}
			
			}
		}
		# The entry
		self::serveDocument('agora_editpost.html','text/html');
		return;
	}

	public static function publishmessage($args)
	{
		global $core, $_ctx;
		$user_id = $core->auth->userID();
		
		if ($core->agora->isModerator($user_id) === false)
		{
			self::p404();
		}

		$message_id = $args;
		
		if (!is_numeric($message_id))
		{
			self::p404();
		}
		
		$params['message_id'] = $args;
		$params['no_content'] = true;

		$_ctx->messages = $core->auth->sudo(array($core->agora,'getMessages'),$params);

		if ($_ctx->messages->isEmpty() )
		{
			self::p404();
		}
		$redir = $_ctx->messages->getPostURL();

		$redir .= $core->blog->settings->system->url_scan == "query_string" ? '&' : '?';
		
		try
		{
			# --BEHAVIOR-- publicBeforeMessagePublish
			$core->callBehavior('publicBeforeMessagePublish',$message_id);

			$core->auth->sudo(array($core->agora,'updMessageStatus'),$message_id,1);
			//$core->agora->updMessageStatus($message_id,1);
			
			# --BEHAVIOR-- publicAfterMessagePublish
			$core->callBehavior('publicAfterMessagePublish',$message_id);
			
			$redir_arg = 'msg=2#pr';
			
			header('Location: '.$redir.$redir_arg);

			return;
		}
		
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}
			
	}

	public static function unpublishmessage($args)
	{
		global $core, $_ctx;
		$user_id = $core->auth->userID();
		
		if ($core->agora->isModerator($user_id) === false)
		{
			self::p404();
		}

		$message_id = $args;
		
		if (!is_numeric($message_id))
		{
			self::p404();
		}
		
		$params['message_id'] = $args;
		$params['no_content'] = true;

		$_ctx->messages = $core->agora->getMessages($params);

		if ($_ctx->messages->isEmpty() )
		{
			self::p404();
		}

		$redir = $_ctx->messages->getPostURL();

		$redir .= $core->blog->settings->system->url_scan == "query_string" ? '&' : '?';
		
		try
		{
			# --BEHAVIOR-- publicBeforeMessageUnpublish
			$core->callBehavior('publicBeforeMessageUnpublish',$message_id);

			$core->auth->sudo(array($core->agora,'updMessageStatus'),$message_id,0);
			
			# --BEHAVIOR-- publicAfterMessageUnpublish
			$core->callBehavior('publicAfterMessageUnpublish',$message_id);
			
			$redir_arg = 'msg=2#pr';
			
			header('Location: '.$redir.$redir_arg);

			return;
		}
		
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}
			
	}
	
	public static function editmessage($args)
	{
		global $core, $_ctx;
		
		//$core->addBehavior('coreInitWikiPost',array('agoraBehaviors','coreInitWikiPost'));
		$user_id = $core->auth->userID();
		
		if ($core->auth->userID() == false)
		{
			self::p404();
		}
		
		$params['message_id'] = $args ;
		
		$_ctx->messages = $core->auth->sudo(array($core->agora,'getMessages'),$params);

		if ($_ctx->messages->isEmpty() )
		{
			self::p404();
		}
		
		if (!$_ctx->messages->isEditable() && !$_ctx->messages->isStillinTime())
		{
			self::p404();
		}

		$_ctx->message_preview = new ArrayObject();
		$_ctx->message_preview['content'] = $_ctx->messages->message_content_xhtml;
		$_ctx->message_preview['rawcontent'] = $_ctx->messages->message_content;
		$_ctx->message_preview['preview'] = false;

		$edit_message = isset($_POST['c_content']);
		$format = $core->blog->settings->agora->content_syntax;
		
		if ($edit_message)
		{
			$content = isset($_POST['c_content'])? $_POST['c_content'] : '';

			$preview = !empty($_POST['preview']);
		
			if ($content != '')
			{
				$core->initWikiComment();
				$content_xhtml = $core->callFormater($format,$content);
				$content_xhtml = $core->HTMLfilter($content_xhtml);
			}
			
			$_ctx->message_preview['content'] = $content_xhtml;
			$_ctx->message_preview['rawcontent'] =  $content;
			
			if ($preview)
			{
				# --BEHAVIOR-- publicBeforeMessagePreview
				$core->callBehavior('publicBeforeMessagePreview',$_ctx->message_preview);
				$_ctx->message_preview['preview'] = true;
			}
			else
			{
				$message_id = $args;
				$cur = $core->con->openCursor($core->prefix.'message');
				$cur->message_id = $message_id;
				$cur->message_content = $content;
				$cur->message_content_xhtml = $content_xhtml;
				$cur->message_format =  $format;//'wiki';
				$cur->message_dt = $_ctx->messages->message_dt;
				
				$redir = $_ctx->messages->getPostURL();
				$redir .= $core->blog->settings->system->url_scan == "query_string" ? '&' : '?';
				
				try
				{
					# --BEHAVIOR-- publicBeforeMessageUpdate
					$core->callBehavior('publicBeforeMessageUpdate',$cur,$message_id );
				
					$core->auth->sudo(array($core->agora,'updMessage'),$message_id,$cur);
				
					# --BEHAVIOR-- publicAfterMessageUpdate
					$core->callBehavior('publicAfterMessageUpdate',$cur,$message_id);
				
					$redir_arg = 'msg=2#pr';
				
					header('Location: '.$redir.$redir_arg);
					return;
				}
			
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}
			
			}
		}
		# The message
		self::serveDocument('agora_editmessage.html','text/html');
		return;
	}

	public static function feed($args)
	{
		$type = null;
		$comments = false;
		$cat_url = false;
		$post_id = null;
		$subtitle = '';
		
		$mime = 'application/xml';
		
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		if (preg_match('!^([a-z]{2}(-[a-z]{2})?)/(.*)$!',$args,$m)) {
			$params = new ArrayObject(array('lang' => $m[1]));
			
			$args = $m[3];
			
			$core->callBehavior('publicFeedBeforeGetLangs',$params,$args);
			
			$_ctx->langs = $core->blog->getLangs($params);
			
			if ($_ctx->langs->isEmpty()) {
				# The specified language does not exist.
				self::p404();
				return;
			} else {
				$_ctx->cur_lang = $m[1];
			}
		}
		
		if (preg_match('#^rss2/xslt$#',$args,$m))
		{
			# RSS XSLT stylesheet
			self::serveDocument('rss2.xsl','text/xml');
			return;
		}
		elseif (preg_match('#^(atom|rss2)/comments/([0-9]+)$#',$args,$m))
		{
			# Post comments feed
			$type = $m[1];
			$comments = true;
			$post_id = (integer) $m[2];
		}
		elseif (preg_match('#^(?:category/(.+)/)?(atom|rss2)(/comments)?$#',$args,$m))
		{
			# All posts or comments feed
			$type = $m[2];
			$comments = !empty($m[3]);
			if (!empty($m[1])) {
				$cat_url = $m[1];
			}
		}
		else
		{
			# The specified Feed URL is malformed.
			self::p404();
			return;
		}
		
		if ($cat_url)
		{
			$params = new ArrayObject(array(
				'cat_url' => $cat_url,
				'post_type' => 'post'));
			
			$core->callBehavior('publicFeedBeforeGetCategories',$params,$args);
			
			$_ctx->categories = $core->blog->getCategories($params);
			
			if ($_ctx->categories->isEmpty()) {
				# The specified category does no exist.
				self::p404();
				return;
			}
			
			$subtitle = ' - '.$_ctx->categories->cat_title;
		}
		elseif ($post_id)
		{
			$params = new ArrayObject(array(
				'post_id' => $post_id,
				'post_type' => ''));
				
			$core->callBehavior('publicFeedBeforeGetPosts',$params,$args);
			
			$_ctx->posts = $core->blog->getPosts($params);
			
			if ($_ctx->posts->isEmpty()) {
				# The specified post does not exist.
				self::p404();
				return;
			}
			
			$subtitle = ' - '.$_ctx->posts->post_title;
		}
		
		$tpl = $type;
		if ($comments) {
			if ($core->blog->settings->agora->full_flag) {
				$tpl = 'agora-'.$type;
				$tpl .= '-messages';
				$_ctx->nb_message_per_page = $core->blog->settings->agora->nb_message_per_feed;
			} else {
				$tpl .= '-comments';
				$_ctx->nb_comment_per_page = $core->blog->settings->system->nb_comment_per_feed;
			}
		} else {
			$_ctx->nb_entry_per_page = $core->blog->settings->system->nb_post_per_feed;
			$_ctx->short_feed_items = $core->blog->settings->system->short_feed_items;
		}
		$tpl .= '.xml';
		
		if ($type == 'atom') {
			$mime = 'application/atom+xml';
		}
		
		$_ctx->feed_subtitle = $subtitle;
		
		header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->system->robots_policy,''));
		self::serveDocument($tpl,$mime);
		if (!$comments && !$cat_url) {
			$core->blog->publishScheduledEntries();
		}
	}
		
	/**
	*
	*/
	public static function feedXslt($args)
	{
		self::serveDocument('rss2.xsl','text/xml');
	}	
	
	/**
	*
	*/
	public static function publicFeed($args)
	{
		#Don't reinvent the wheel - take a look to dcUrlHandlers/feed
		global $core,$_ctx;

		$type = null;
		$params = array();
		$mime = 'application/xml';
		
		if (preg_match('#^(atom|rss2)$#',$args,$m)) {
			# Atom or RSS2 ?
			$type = $m[0];
		}
		
		$tpl = 'agora-';
		$tpl .=  $type == '' ? 'atom' : $type;
		$tpl .= '-pv.xml';
		
		if ($type == 'atom') {
			$mime = 'application/atom+xml';
		}
		
		header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->system->robots_policy,''));
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument($tpl,$mime);
		
		return;
	}
	
	public static function checkAuthHandler($args)
	{
		global $core,$_ctx;
		
		#New temporary urlHandlers 
		$urla = new urlHandler();
		$urla->mode = $core->url->mode;
		$urla->registerDefault(array('urlAgora','callbackfoo'));
		foreach ($core->url->getTypes() as $k => $v) {
			$urla->register($k,$v['url'],$v['representation'],array('urlAgora','callbackfoo'));
		}
		
		#Find type
		$urla->getDocument();
		$type = $urla->type;
		unset($urla);
		
		#Define allowed url->type 
		$allowed_types = new ArrayObject(array('feed','xslt','tag_feed','agora_feed','fakefeed','spamfeed','hamfeed','trackback','login','logout','register','recover'));
		$core->callBehavior('initAgoraPrivateMode',$allowed_types);

		#Generic behavior
		$core->callBehavior('initAgoraPrivateHandler',$core);
		
		$login = isset($_POST['li_login']) && isset($_POST['li_pwd']);
		
		if ($login)
		{
			$login = trim($_POST['li_login']);
			$pwd = trim($_POST['li_pwd']);
			
			try
			{
				$core->agora->userLogin($login,$pwd);
			}
			
			catch (Exception $e)
			{
				$_ctx->form_error = $e->getMessage(); 
			}
		}
		
		if ((in_array($type,(array)$allowed_types)) || ($core->auth->userID() == true)) 
		{
			return;
		} 
		else 
		{
			$_ctx =& $GLOBALS['_ctx'];
			$core =& $GLOBALS['core'];

			header('Content-Type: text/html; charset=UTF-8');
			http::head(401,'Service Unavailable');
			$core->url->type = '401';
			$_ctx->current_tpl = 'agora_login.html';
			$_ctx->content_type = 'text/html';
			//$_ctx->agora_message = __('The document you are looking for need an authentication.');

			echo $core->tpl->getData($_ctx->current_tpl);

			# --BEHAVIOR-- publicAfterDocument
			$core->callBehavior('publicAfterDocument',$core);
			exit;
		}
	}
}
?>
