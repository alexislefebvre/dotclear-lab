<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku ,Tomtom and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class urlAgora extends dcUrlHandlers
{
	public static function recover($args)
	{
		// recover/ : set a recovery key 
		// recover/blabla : create a newpassword and send it to user mailbox
		
		global $core, $_ctx;
		
		$allow = $core->auth->allowPassChange();
		$akey = ($allow && !empty($args)) ? $args : null;
		
		$user_id = $user_pwd = $user_key = $user_email = null;
		
		$_ctx->agora_recover = new ArrayObject();
		$_ctx->agora_recover['login'] = '';
		$_ctx->agora_recover['email'] = '';
		
		# Recover password
		if ($allow && !empty($_POST['ru_login']) && !empty($_POST['ru_email']))
		{
			$user_id = !empty($_POST['ru_login']) ? $_POST['ru_login'] : null;
			$user_email = !empty($_POST['ru_email']) ? $_POST['ru_email'] : '';
			$_ctx->agora_recover['login'] = $user_id;
			$_ctx->agora_recover['email'] = $user_email;
			try
			{
				$recover_key = $core->auth->setRecoverKey($user_id,$user_email);
				$_ctx->agora->sendRecoveryEmail($user_email,$user_id,$recover_key);
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
				$_ctx->agora->sendNewPasswordEmail($recover_res['user_email'],$recover_res['user_id'],$recover_res['new_pass']);
				http::head(200,'OK');
				$_ctx->agora_message = __('Your new password is in your mailbox.');
$_ctx->agora_message .= printf($recover_res);
			}
			
			catch (Exception $e)
			{
				$_ctx->form_error = $e->getMessage();
			}
		
		}
		
	self::serveDocument('recover.html');
	return;
	}

	public static function newaccount($args)
	{
		// Ajouter un test sur les conditions générales ... TODO behavior ?
		// URL register : create the user but without any perm
		// register/?key=12345678 : end of registration : add perm 'member'
		global $core, $_ctx;
		
		$_ctx->agora_register = new ArrayObject();
		$_ctx->agora_register['login'] = '';
		$_ctx->agora_register['email'] = '';
		$_ctx->agora_register['preview'] = false;
		$_ctx->agora_register['key'] = false;
		$_ctx->agora_register['pwd'] = '';
		
		$url = $core->blog->url.$core->url->getBase("agora");
		
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
			
			$_ctx->agora_register['login'] = $login;
			$_ctx->agora_register['email'] = $mail;
			$_ctx->agora_register['pwd'] = $pwd;
			
			if ($register_preview)
			{
				# --BEHAVIOR-- publicBeforeSignUp
				$core->callBehavior('publicBeforeSignUp',$_ctx->agora_register);
				$_ctx->agora_register['preview'] = true;
			}
			else
			{
				$cur = $core->con->openCursor($core->prefix.'user');
				$cur->user_id = $login;
				$cur->user_email = html::clean($mail);
				$cur->user_pwd = $pwd;
				$cur->user_lang = $core->blog->settings->system->lang;
				$cur->user_tz = $core->blog->settings->system->blog_timezone;
				$cur->user_default_blog = $core->blog->id;
				//$redir = http::getSelfURI();
				//$redir .= strpos($redir,'?') !== false ? '&' : '?';
				
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
					
					# --BEHAVIOR-- publicBeforeUserCreate
					$core->callBehavior('publicBeforeUserCreate',$cur);
					
					$user_id = $core->auth->sudo(array($core,'addUser'),$cur);
					$_ctx->agora->sendActivationEmail($mail,$user_id,$pwd);
					# --BEHAVIOR-- publicAfterUserCreate
					$core->callBehavior('publicAfterUserCreate',$cur,$user_id);
					
					//header('Content-Type: text/html; charset=UTF-8');
					http::head(201,'Created');
					//header('Content-Type: text/html');
					//header("Refresh: 5;URL=$url");
					$_ctx->agora_message = sprintf(__('User %s successfully created. You will receive an email to activate your account.'),'<strong>'.$user_id.'</strong>');
					//return;
					
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
					$_ctx->unregistred_user = $_ctx->agora->getUnregistredUser($key);
					// throw Exception if invalid key ...
					
					$user_id = $_ctx->unregistred_user['user_id'];
					$user_status = $_ctx->unregistred_user['user_status'];
					
					// http://dev.dotclear.org/2.0/browser/trunk/inc/core/class.dc.core.php#L684 

					if ($_ctx->agora->isMember($user_id) === true)
					//if ($core->auth->check('member',$core->blog->id))
					{
						// User has permission "member of agora"
						throw new Exception(sprintf(__('User %s is already registred. You can log in.'),html::escapeHTML($user_id)));
					}
					else
					{
						$perm = array('member' => '');
						$core->auth->sudo(array($core,'setUserBlogPermissions'),$user_id,$core->blog->id,$perm);
						//http::head(200,'OK');
						//header('Content-Type: text/html');
						$_ctx->agora_message = sprintf(__('User %s is now registred. You can now log in.'),'<strong>'.$user_id.'</strong>');
						//return;
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
		
		self::serveDocument('register.html','text/html',false);
		return;
	}
	
	public static function login($args)
	{
		// module de password recovery : envoi d'un email avec mot de passe généré auto.. OK
		// URL forum/login : login user 
		
		global $core, $_ctx;
		$url = $core->blog->url.$core->url->getBase("agora");
/*$users = $_ctx->agora->getUsers();
while ($users->fetch()){
print_r($users->user_id);}exit;*/
		if (!isset($_SESSION['sess_user_id']))
		{
			$login = isset($_POST['li_login']) && isset($_POST['li_pwd']);

			if ($login)
			{
				$login = trim($_POST['li_login']);
				$pwd = trim($_POST['li_pwd']);
				//$redir .= strpos($redir,'?') !== false ? '&' : '?';

				try
				{
					$user_id = $_ctx->agora->userlogIn($login,$pwd);
					http::redirect($core->blog->url.$core->url->getBase('agora'));
					return;
				}

				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage(); 
				}
			}
			self::serveDocument('login.html');
			return;
		}
		else
		{
			//http::head(100,'Continue');
			header('Location: '.$url);
			return;
		}
	}

	public static function logout($args)
	{
		// URL forum/logout : logout user without template
		
		global $core;
		
		if (isset($_SESSION['sess_user_id']))
		{
			$_SESSION['sess_user_id'] = null;
			$core->session->destroy();
			
			if (isset($_COOKIE['dc_agora_'.$core->blog->id]))
			{
				unset($_COOKIE['dc_agora_'.$this->core->blog->id]);
				setcookie('dc_agora_'.$core->blog->id,false,-600);
			}
			//what about comment_info cookie ?
		}
		
		http::redirect($core->blog->url.$core->url->getBase('agora'));
		return;
	}

	public static function userlist($args)
	{
	  //todo 
	}

	public static function profile($args)
	{
		// URL forum/profile/batman : edit/view profile ..
		// URL forum/profile/batman/ban : ban user  ..
		
		global $core, $_ctx;
		
		if (($args == '') || (!is_string($args)))
		{
			self::p404();
		}
		else 
		{
			$user_id = ($core->auth->userID() != false && isset($_SESSION['sess_user_id'])) ? $core->auth->userID() : '';
			//$_ctx->users->user_id = $args;
			$_ctx->users = $_ctx->agora->getUser($args);
			//$_ctx->users = $core->getUser($args);
//while ($_ctx->users->fetch()){
//print_r($_ctx->users->user_id);}exit; // end trace
			if ($_ctx->users->isEmpty()) {
				self::p404();
			}
			
			$_ctx->profile_user = new ArrayObject();
			
			$_ctx->profile_user['pseudo'] = $_ctx->users->user_displayname;
			$_ctx->profile_user['email'] = $_ctx->users->user_email;
			$_ctx->profile_user['url'] = $_ctx->users->user_url;
			$_ctx->profile_user['status'] = $_ctx->users->user_status;
			$_ctx->profile_user['pwd'] = '';
			$_ctx->profile_user['msg'] = '';
			
			
			//$core->auth->check('moderator',$core->blog->id)
			// $_ctx->agora->isModerator($user_id) === false
			if ($args != $user_id)
			{
				self::serveDocument('profile.html','text/html',false);
				return;
			}
			
			if (!empty($_POST['submit']))
			{
				$_ctx->profile_user['pseudo'] = trim($_POST['li_pseudo']);;
				$_ctx->profile_user['email']  = trim($_POST['li_email']);
				$_ctx->profile_user['url']  = trim($_POST['li_url']);
				$_ctx->profile_user['pwd'] = trim($_POST['li_pwd']);
				$_ctx->profile_user['pwd2'] = trim($_POST['li_pwd2']);
				$redir = $redir = $core->blog->url.$core->url->getBase("profile").'/'.$args;
				$redir .= strpos($redir,'?') !== false ? '&' : '?';
				
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
						$cur->user_email = $_ctx->profile_user['email'];
						$cur->user_displayname = $_ctx->profile_user['pseudo'];
						$cur->user_url = $_ctx->profile_user['url'];
						if (!empty($_ctx->profile_user['pwd']))
						{
							$cur->user_pwd =  $_ctx->profile_user['pwd'];
						}
						
						# --BEHAVIOR-- publicBeforeUserCreate
						$core->callBehavior('publicBeforeUserUpdate',$cur,$user_id);
						
						$id = $core->auth->sudo(array($core,'updUser'),$user_id,$cur);
						
						$_ctx->agora_message = sprintf(__('User %s successfully updated.'),'<strong>'.$args.'</strong>');
						# --BEHAVIOR-- publicAfterUserCreate
						$core->callBehavior('publicAfterUserUpdate',$cur,$id);
						
						if (!empty($id))
						{
							$redir_arg = 'updated=1';
						}
						else
						{
							$redir_arg = 'error=1';
						}
						
						header('Location: '.$redir.$redir_arg);
					}
					catch (Exception $e)
					{
						$_ctx->form_error = $e->getMessage();
					}
					
				}
			}
		}
		
		self::serveDocument('profile_me.html','text/html',false);
		return;
	}

	public static function agora($args)
	{
		global $core;
		
		$n = self::getPageNumber($args);
		
		$core->blog->withoutPassword(true);
		
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
				$core->url->type = $n > 1 ? 'agora-page' : 'agora';
			}
			
			if (empty($_GET['q'])) {
				self::serveDocument('agora.html','text/html',false);
			} else {
				self::search();
			}
		}
	}

	public static function fsearch()
	{
		// dunno if we keep it or we use dcOpensearch ?
		global $core;
		
		$GLOBALS['_fsearch'] = !empty($_GET['q']) ? rawurldecode($_GET['q']) : '';
		if ($GLOBALS['_fsearch']) {
			$GLOBALS['_fsearch_count'] = $_ctx->agora->getPostsPlus(array('search' => $GLOBALS['_fsearch']),true)->f(0);
		}
		
		self::serveDocument('forum_search.html');
		return;
	}

	public static function place($args)
	{
		// URL agora/place/cat_url : view threads of a place
		
		global $core, $_ctx;
		
		$core->blog->withoutPassword(true);
		
		$core->addBehavior('coreInitWikiPost',array('agoraBehaviors','coreInitWikiPost'));
		//$core->addBehavior('coreBeforePostCreate',array('agoraBehaviors','coreBeforePostCreate'));
		
		$n = self::getPageNumber($args);
		
		if ($args == '' && !$n) {
			self::p404();
		}
		//$params['without_empty'] = false;
		$params['cat_url'] = $args;
		$params['post_type'] = 'thread';
		//$params['thread_id'] = '';
		
		//$_ctx->categories = $_ctx->agora->getCategoriesPlus($params);
		$_ctx->categories = $core->blog->getCategories($params);
		
		if ($_ctx->categories->isEmpty())
		{
			self::p404();
		}
		
		if ($n) {
			$GLOBALS['_page_number'] = $n;
		}
		
		$user_id = $core->auth->userID();
		
		$_ctx->thread_preview = new ArrayObject();
		$_ctx->thread_preview['title'] = '';
		$_ctx->thread_preview['content'] = '';
		$_ctx->thread_preview['rawcontent'] = '';
		$_ctx->thread_preview['preview'] = false;
		
		$thread_new = isset($_POST['t_content']) && isset($_POST['t_title']);
		
		//Setting for quick new thread ?
		
		if ($thread_new && ($_ctx->agora->isMember($user_id) === true))
		//if ($thread_new && ($core->auth->check('member',$core->blog->id)))
		{
			$title = $_POST['t_title'];
			$content = $_POST['t_content'];
			$preview = !empty($_POST['preview']);
			
			if ($content != '')
			{ 
				$core->initWikiPost();
				/// coreInitWikiPost
				$content = $core->wikiTransform($content);
				$content = $core->HTMLfilter($content);
			}
			
			$_ctx->thread_preview['title'] = $title ;
			$_ctx->thread_preview['content'] = $content;
			$_ctx->thread_preview['rawcontent'] = $_POST['t_content'];
			
			if ($preview)
			{
				# --BEHAVIOR-- publicBeforePostPreview
				$core->callBehavior('publicBeforeThreadPreview',$_ctx->thread_preview);
				
				$_ctx->thread_preview['preview'] = true;
			}
			
			else
			{
				$cur = $core->con->openCursor($core->prefix.'post');
				$cur->user_id = $core->auth->userID() ;
				$cur->cat_id = $_ctx->categories->cat_id;
				$cur->post_title = $title;
				$cur->post_format = 'wiki';
				$cur->post_status = 1;
				$cur->post_lang = $core->auth->getInfo('user_lang');
				$cur->post_content = $_POST['t_content'];
				$cur->post_type = 'thread';
				$cur->post_open_comment = 1;
				
				// thread_id : (new field in base ): link between posts of a same thread
				//$cur->thread_id = '';
			
				$redir = $core->blog->url.$core->url->getBase("place").'/'.$_ctx->categories->cat_url;
				$redir .= strpos($redir,'?') !== false ? '&' : '?';
			
				try
				{
					# --BEHAVIOR-- publicBeforePostCreate
					$core->callBehavior('publicBeforeThreadCreate',$cur);
				
					$post_id = $core->auth->sudo(array($core->blog,'addPost'),$cur);
				
					# --BEHAVIOR-- publicAfterPostCreate
					$core->callBehavior('publicAfterThreadCreate',$cur,$post_id);
				
					$redir_arg = 'pub=1';
				
					header('Location: '.$redir.$redir_arg);
					return;
				}
		
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}
			}
		}
		self::serveDocument('place.html','text/html',false);
		return;
	}

	public static function newthread($args)
	{
		global $core, $_ctx;
		$user_id = ($core->auth->userID() != false && isset($_SESSION['sess_user_id'])) ? $core->auth->userID() : '';
		
		if ($args) {$args =  substr($args,1);}

		$core->addBehavior('coreInitWikiPost',array('agoraBehaviors','coreInitWikiPost'));
		
		if (/*$args == '' ||*/ !$core->auth->userID()) {
			self::p404();
		}
		
		$params['cat_url'] = $args;
		
		//$_ctx->categories = $_ctx->agora->getCategoriesPlus($params);
		$_ctx->categories = $core->blog->getCategories($params);
		
		if ($_ctx->categories->isEmpty())
		{
			//self::p404();
		}
		
		$_ctx->thread_preview = new ArrayObject();
		$_ctx->thread_preview['title'] = '';
		$_ctx->thread_preview['content'] = '';
		$_ctx->thread_preview['rawcontent'] = '';
		$_ctx->thread_preview['preview'] = false;
		$_ctx->thread_preview['cat'] = ($_ctx->categories->isEmpty()) ? '' : $_ctx->categories->cat_id;
		$_ctx->thread_preview['not_empty'] = ( $args == '' ) ? false : true;
		
		$thread_new = isset($_POST['t_content']) && isset($_POST['t_title']);
		$post_excerpt = $post_excerpt_xhtml = $post_content_xhtml = '';
		if ($thread_new && ($_ctx->agora->isMember($user_id) === true))
		//if ($thread_new && ($core->auth->check('member',$core->blog->id)))
		{
			$title = $_POST['t_title'];
			$content = $_POST['t_content'];
			$preview = !empty($_POST['preview']);
			
			if ($content != '')
			{ 
				$core->initWikiPost();
				/// coreInitWikiPost
				$content = $core->wikiTransform($content);
				$content = $core->HTMLfilter($content);
			}
			
			$_ctx->thread_preview['title'] = $title ;
			$_ctx->thread_preview['content'] = $content;
			$_ctx->thread_preview['rawcontent'] = $_POST['t_content'];
			$_ctx->thread_preview['cat'] = (integer) $_POST['t_cat'];
			$_ctx->thread_preview['not_empty'] = true;
			
			if ($preview)
			{
				# --BEHAVIOR-- publicBeforePostPreview
				$core->callBehavior('publicBeforeThreadPreview',$_ctx->thread_preview);
				
				$_ctx->thread_preview['preview'] = true;
			}
			
			else
			{
				$core->blog->setPostContent(
					'','wiki',$core->auth->getInfo('user_lang'),$post_excerpt,$post_excerpt_xhtml,$_POST['t_content'],$post_content_xhtml
				);
				$cur = $core->con->openCursor($core->prefix.'post');
				# Magic tweak doesn't work here
				//$core->blog->settings->system->post_url_format = "{id}";
				
				# Magic tweak :)
				# TODO : setting
				$core->blog->settings->system->post_url_format = '{id}';
				
				$cur->user_id = $user_id;
				$cur->cat_id = ((integer) $_POST['t_cat']) ? (integer) $_POST['t_cat'] : null;
				$cur->post_title = $title;
				$offset = dt::getTimeOffset($core->blog->settings->system->blog_timezone);
				$cur->post_dt = date('Y-m-d H:i:s',time() + $offset);
				$cur->post_format = 'wiki';
				$cur->post_status = 1;
				$cur->post_lang = $core->auth->getInfo('user_lang');
				$cur->post_content = $_POST['t_content'];
				$cur->post_content_xhtml = $post_content_xhtml;
				$cur->post_type = 'thread';
				$cur->post_open_comment = 1;
				$redir = $core->blog->url.$core->url->getBase("thread").'/';
				//$redir .= strpos($redir,'?') !== false ? '&' : '?';
			
				try
				{
					# --BEHAVIOR-- publicBeforeThreadCreate
					$core->callBehavior('publicBeforeThreadCreate',$cur);
				
					$post_id = $core->auth->sudo(array($core->blog,'addPost'),$cur);
					// todo : via publicAfterThreadCreate 
					//$core->meta->setPostMeta($post_id,'thread_nbmessages', 1);
					$core->auth->sudo(array($core->meta,'setPostMeta'),$post_id,'thread_nbmessages', 1);
				
					# --BEHAVIOR-- publicAfterThreadCreate
					$core->callBehavior('publicAfterThreadCreate',$cur,$post_id);
				
					$redir .= $cur->post_url;
					$redir .= strpos($redir,'?') !== false ? '&' : '?';
					$redir_arg = 'pub=1';
				
					header('Location: '.$redir.$redir_arg);
					return;
				}
		
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}
			}
		}
		self::serveDocument('newthread.html','text/html',false);
		return;
	}

	public static function thread($args)
	{
		global $core, $_ctx;
		
		$core->addBehavior('coreInitWikiPost',array('agoraBehaviors','coreInitWikiPost'));
		/*
		URL forum/thread/id : view a full thread (first and answers) serve a template
		Moderator : 
		URL forum/thread/id(& or ?)action=pin : marks as selected 
		URL forum/thread/id(& or ?)action=unpin : marks as unselected 
		URL forum/thread/id(& or ?)action=close : close the thead : thread->commentsActive : false 
		URL forum/thread/id(& or ?)action=open : open the thead : thread->commentsActive : true 
		*/
		//$n = self::getPageNumber($args);
		
		if ($args == ''){// && !$n) {
			self::p404();
		}
		$core->blog->withoutPassword(false);
		//if ($n) {
		//	$GLOBALS['_page_number'] = $n;
		//}
		
		$user_id = ($core->auth->userID() != false && isset($_SESSION['sess_user_id'])) ? $core->auth->userID() : '';
		$action =  !empty($_GET['action']) ? $_GET['action'] : null;
		
		$params = new ArrayObject();
		$params['post_url'] = $args;
		$params['post_type'] = 'thread';
		
		//$_ctx->posts = $_ctx->agora->getPostsPlus($params);
		$_ctx->posts = $core->blog->getPosts($params);
		
		if ($_ctx->posts->isEmpty() )//|| $_ctx->posts->thread_id != '')
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
				self::serveDocument('password-form.html','text/html',false);
				return;
			}
		}

		$_ctx->message_preview = new ArrayObject();
		$_ctx->message_preview['content'] = '';
		$_ctx->message_preview['title'] = '';
		$_ctx->message_preview['rawcontent'] = '';
		$_ctx->message_preview['preview'] = false;
		
		// Mark as selected or unselected 
		if ($_ctx->agora->isModerator($user_id) === true && 
		//if ($core->auth->check('moderator',$core->blog->id) &&
		(($action == 'pin') || ($action == 'unpin')))
		{
			$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts->post_url;
			$redir .= strpos($redir,'?') !== false ? '&' : '?';
			
			try
			{
				$core->auth->sudo(array($core->blog,'updPostSelected'),$_ctx->posts->post_id,$action == 'pin');
				
				$redir_arg = $action;
				$redir_arg .= '=1';
				
				header('Location: '.$redir.$redir_arg);
				return;
			}
			
			catch (Exception $e)
			{
				$_ctx->form_error = $e->getMessage();
			}
		}
		
		// Mark as selected or unselected - open or close thread
		if ($_ctx->agora->isModerator($user_id) === true && 
		//if ($core->auth->check('moderator',$core->blog->id) &&
		(($action == 'close') || ($action == 'open')))
		{
			$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts->post_url;
			$redir .= strpos($redir,'?') !== false ? '&' : '?';
			
			try
			{
				$core->auth->sudo(array($_ctx->agora,'updPostClosed'),$_ctx->posts->post_id,$action == 'open');
				
				$redir_arg = $action;
				$redir_arg .= '=1';
				
				header('Location: '.$redir.$redir_arg);
				return;
			}
			
			catch (Exception $e)
			{
				$_ctx->form_error = $e->getMessage();
			}
		}
		
		// Quick Answer 
		// In comments ?
		if ($_ctx->agora->isMember($user_id) === true)
		//if ($core->auth->check('member',$core->blog->id))
		{
			$thread_message = (isset($_POST['p_content']) && $_ctx->posts->commentsActive());
			
			if ($thread_message)
			{
				$content = $_POST['p_content'];
				$preview = !empty($_POST['preview']);
				
				if ($content != '')
				{ 
					$core->initWikiPost();
					/// coreInitWikiPost
					$content = $core->wikiTransform($content);
					$content = $core->HTMLfilter($content);
				}
				
				$_ctx->message_preview['content'] = $content;
				$_ctx->message_preview['rawcontent'] = $_POST['p_content'];
				
				if ($preview)
				{
					# --BEHAVIOR-- publicBeforePostPreview
					$core->callBehavior('publicBeforeMessagePreview',$_ctx->message_preview);
					
					$_ctx->message_preview['preview'] = true;
				}
				
				else
				{

					$cur = $core->con->openCursor($core->prefix.'message');
					$cur->user_id = $user_id;
					$cur->message_format = 'wiki';
					$cur->message_content = $_POST['p_content'];
					$offset = dt::getTimeOffset($core->blog->settings->system->blog_timezone);
					$cur->message_dt = date('Y-m-d H:i:s',time() + $offset);
					//$cur->message_dt = date('Y-m-d H:i:s');
					$cur->post_id = $_ctx->posts->post_id;
					$cur->message_status =  1 ;
					
					$redir = $_ctx->posts->getURL();
					$redir .= strpos($redir,'?') !== false ? '&' : '?';
					
					try
					{
						# --BEHAVIOR-- publicBeforePostCreate
						$core->callBehavior('publicBeforeMessageCreate',$cur);

						$message_id = $core->auth->sudo(array($_ctx->agora,'addMessage'),$cur);
			
						# --BEHAVIOR-- publicAfterPostCreate
						$core->callBehavior('publicAfterMessageCreate',$cur,$message_id);
						
						$_ctx->agora->triggerThread($_ctx->posts->post_id);
						
						$redir_arg = 'pub=1';
						
						header('Location: '.$redir.$redir_arg);
						return;
					}
					
					catch (Exception $e)
					{
						$_ctx->form_error = $e->getMessage();
					}
				}
			}
		}
		
		self::serveDocument('thread.html','text/html',false);
		return;
	}

	public static function threadpreview($args)
	{
		$core = $GLOBALS['core'];
		$_ctx = $GLOBALS['_ctx'];
		
		if (!preg_match('#^(.+?)/([0-9a-z]{40})/(.+?)$#',$args,$m)) {
			# The specified Preview URL is malformed.
			self::p404();
		}
		else
		{
			$user_id = $m[1];
			$user_key = $m[2];
			$post_url = $m[3];
			if (!$core->auth->checkUser($user_id,null,$user_key)) {
				# The user has no access to the entry.
				self::p404();
			}
			else
			{
				$_ctx->preview = true;
				self::thread($post_url);
			}
		}
	}
	
	public static function removethread($args)
	{
		global $core, $_ctx;
		$user_id = $core->auth->userID();
		
		if ($_ctx->agora->isModerator($user_id) === false)
		//if (!$core->auth->check('moderator',$core->blog->id))
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
		$params['post_type'] = 'thread';
		//$_ctx->posts = $_ctx->agora->getPostsPlus($params);
		$_ctx->posts = $core->blog->getPosts($params);

		if ($_ctx->posts->isEmpty() )
		{
			self::p404();
		}
		
		if ($_ctx->posts->cat_url) {
			$redir = $core->blog->url.$core->url->getBase("place").'/'.$_ctx->posts->cat_url;
		} else {
			$redir = $core->blog->url.$core->url->getBase("agora");
		}

		$redir .= strpos($redir,'?') !== false ? '&' : '?';
		
		try
		{
			# --BEHAVIOR-- publicBeforePostDelete
			$core->callBehavior('publicBeforePostDelete',$post_id);
			
			$core->auth->sudo(array($core->blog,'delPost'),$post_id);
			# update nb_comment (used as nb_answers for the thread)
			//$_ctx->agora->triggerThread($thread_id);
			
			# --BEHAVIOR-- publicAfterPostDelete
			$core->callBehavior('publicAfterPostDelete',$post_id);
			
			$redir_arg = 'del=1';
			
			header('Location: '.$redir.$redir_arg);
			return;
		}
		
		catch (Exception $e)
		{
			$_ctx->form_error = $e->getMessage();
		}
			
	}
	
	public static function editthread($args)
	{
		global $core, $_ctx;
		
		$core->addBehavior('coreInitWikiPost',array('agoraBehaviors','coreInitWikiPost'));
		$user_id = $core->auth->userID();
		
		if ($_ctx->agora->isModerator($user_id) === false)
		//if (!$core->auth->check('moderator',$core->blog->id))
		{
			self::p404();
		}
		
		$params['post_id'] = $args ;
		$params['post_type'] = 'thread';
		$_ctx->posts = $core->blog->getPosts($params);

		if ($_ctx->posts->isEmpty() )
		{
			self::p404();
		}

		$_ctx->thread_preview = new ArrayObject();
		$_ctx->thread_preview['content'] = $_ctx->posts->post_content_xhtml;
		$_ctx->thread_preview['title'] = '';
		$_ctx->thread_preview['rawcontent'] = '';
		$_ctx->thread_preview['preview'] = false;
		$_ctx->thread_preview['cat'] = $_ctx->posts->cat_id;
		$_ctx->thread_preview['not_empty'] = ( $args == '' ) ? false : true;
		
		$_ctx->thread_preview['rawcontent'] = $_ctx->posts->post_content;
		$_ctx->thread_preview['title'] = $_ctx->posts->post_title;
		
		$edit_post = isset($_POST['ed_content']) &&  isset($_POST['ed_title']);
		
		if ($edit_post)
		{
			$content = isset($_POST['ed_content'])? $_POST['ed_content'] : '';
			$title = isset($_POST['ed_title'])? $_POST['ed_title'] : '';
			$preview = !empty($_POST['preview']);
		
			if ($content != '')
			{ 
				$core->initWikiPost();
				/// coreInitWikiPost
				$content = $core->wikiTransform($content);
				$content = $core->HTMLfilter($content);
			}
			
			if ($title != '')
			{ 
				//$title = $core->HTMLfilter($title);
			}
			
			$_ctx->thread_preview['content'] = $content;
			$_ctx->thread_preview['rawcontent'] =  $_POST['ed_content'];
			$_ctx->thread_preview['title'] = $_POST['ed_title'];
			$_ctx->thread_preview['cat'] = $_POST['ed_cat'];
			$_ctx->thread_preview['not_empty'] = true;
			
			if ($preview)
			{
				# --BEHAVIOR-- publicBeforePostReview
				$core->callBehavior('publicBeforePostReview',$_ctx->post_preview);
			
				$_ctx->thread_preview['preview'] = true;
			}
			else
			{
				$post_id = $args;
				$cat_id = (integer) $_POST['ed_cat'];
				$cur = $core->con->openCursor($core->prefix.'post');
				$cur->post_id = $post_id;
				// on ne change pas la date quand on édite..
				$cur->post_dt = $_ctx->posts->post_dt;
				$cur->post_title = isset($_POST['ed_title'])? $_POST['ed_title'] : $_ctx->posts->post_title;
				$cur->post_content = isset($_POST['ed_content'])? $_POST['ed_content'] : $_ctx->posts->post_content;
				$cur->cat_id = ($cat_id ? $cat_id : null);
				$cur->post_format =  'wiki';
				
				$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts->post_url;
				$redir .= strpos($redir,'?') !== false ? '&' : '?';
				
				try
				{
					# --BEHAVIOR-- publicBeforePostUpdate
					$core->callBehavior('publicBeforePostUpdate',$cur,$post_id );
				
					$core->auth->sudo(array($core->blog,'updPost'),$post_id,$cur);
				
					# --BEHAVIOR-- publicAfterPostUpdate
					$core->callBehavior('publicAfterPostUpdate',$cur,$post_id);
				
					$redir_arg = 'edt=1';
				
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
		self::serveDocument('editthread.html','text/html',false);
		return;
	}

	public static function removemessage($args)
	{
		global $core, $_ctx;
		$user_id = $core->auth->userID();
		
		if ($_ctx->agora->isModerator($user_id) === false)
		//if (!$core->auth->check('moderator',$core->blog->id))
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

		//$_ctx->posts = $_ctx->agora->getPostsPlus($params);
		$_ctx->messages = $_ctx->agora->getMessages($params);

		if ($_ctx->messages->isEmpty() )
		{
			self::p404();
		}

		$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->messages->post_url;

		$redir .= strpos($redir,'?') !== false ? '&' : '?';
		
		try
		{
			# --BEHAVIOR-- publicBeforeMessageDelete
			$core->callBehavior('publicBeforeMessageDelete',$message_id);

			$core->auth->sudo(array($_ctx->agora,'delMessage'),$message_id);
			# update nb_comment (used as nb_answers for the thread)
			//$_ctx->agora->triggerThread($thread_id);
			
			# --BEHAVIOR-- publicAfterMessageDelete
			$core->callBehavior('publicAfterMessageDelete',$message_id);
			
			$redir_arg = 'del=1';
			
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
		
		$core->addBehavior('coreInitWikiPost',array('agoraBehaviors','coreInitWikiPost'));
		$user_id = $core->auth->userID();
		
		if ($_ctx->agora->isModerator($user_id) === false)
		//if (!$core->auth->check('moderator',$core->blog->id))
		{
			self::p404();
		}
		
		$params['message_id'] = $args ;
		$_ctx->messages = $_ctx->agora->getMessages($params);

		if ($_ctx->messages->isEmpty() )
		{
			self::p404();
		}

		$_ctx->message_preview = new ArrayObject();
		$_ctx->message_preview['content'] = $_ctx->messages->message_content_xhtml;
		$_ctx->message_preview['rawcontent'] = $_ctx->messages->message_content;
		$_ctx->message_preview['preview'] = false;

		
		$edit_message = isset($_POST['ed_content_m']);
		
		if ($edit_message)
		{
			$content = isset($_POST['ed_content_m'])? $_POST['ed_content_m'] : '';

			$preview = !empty($_POST['preview']);
		
			if ($content != '')
			{ 
				$core->initWikiPost();
				/// coreInitWikiPost
				$content = $core->wikiTransform($content);
				$content = $core->HTMLfilter($content);
			}
			
			$_ctx->message_preview['content'] = $content;
			$_ctx->message_preview['rawcontent'] =  $_POST['ed_content_m'];
			
			if ($preview)
			{
				# --BEHAVIOR-- publicBeforePostReview
				$core->callBehavior('publicBeforeMessagePreview',$_ctx->message_preview);
			
				$_ctx->message_preview['preview'] = true;
			}
			else
			{
				$message_id = $args;
				$cur = $core->con->openCursor($core->prefix.'message');
				$cur->message_id = $message_id;
				$cur->message_content = isset($_POST['ed_content_m'])? $_POST['ed_content_m'] : $m_content;
				$cur->message_format =  'wiki';
				
				$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->messages->post_url;
				$redir .= strpos($redir,'?') !== false ? '&' : '?';
				
				try
				{
					# --BEHAVIOR-- publicBeforePostUpdate
					$core->callBehavior('publicBeforeMessageUpdate',$cur,$message_id );
				
					$core->auth->sudo(array($_ctx->agora,'updMessage'),$message_id,$cur);
				
					# --BEHAVIOR-- publicAfterPostUpdate
					$core->callBehavior('publicAfterMessageUpdate',$cur,$message_id);
				
					$redir_arg = 'edm=1';
				
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
		self::serveDocument('editmessage.html','text/html',false);
		return;
	}

	public static function feed($args)
	{ // need review
		global $core, $_ctx;
		
		$type = null;
		$messages = false;
		$cat_url = false;
		$post_id = null;
		$params = array();
		$subtitle = '';
		
		$mime = 'application/xml';
		
		//$_ctx =& $GLOBALS['_ctx'];
		//$core =& $GLOBALS['core'];
		
		if (preg_match('!^([a-z]{2}(-[a-z]{2})?)/(.*)$!',$args,$m)) {
			$params['lang'] = $m[1];
			$args = $m[3];

			$_ctx->langs = $core->blog->getLangs($params);
		
			if ($_ctx->langs->isEmpty()) {
				self::p404();
			} else {
				$_ctx->cur_lang = $m[1];
			}
		}

		if (preg_match('#^(atom|rss2)/messages/([0-9]+)$#',$args,$m))
		{
			# Thread messages feed
			$type = $m[1];
			$messages = true;
			$post_id = (integer) $m[2];
		}
		elseif (preg_match('#^(?:place/(.+)/)?(atom|rss2)(/messages)?$#',$args,$m))
		{
			# All posts or comments feed
			$type = $m[2];
			$messages = !empty($m[3]);
			if (!empty($m[1])) {
				$cat_url = $m[1];
			}
		}
		else
		{
			self::p404();
			return;
		}
		
		if ($cat_url)
		{
			$params['cat_url'] = $cat_url;
			$params['post_type'] = 'thread';
			//$params['threads_only'] = true;
			$_ctx->categories = $core->blog->getCategories($params);

			if ($_ctx->categories->isEmpty()) {		//die ('coucou 1'); 		
				self::p404();
			}
			
			$subtitle = ' - '.$_ctx->categories->cat_title;
		}
		elseif ($post_id)
		{
			$params['post_id'] = $post_id;
			$params['post_type'] = 'thread';
			//$_ctx->posts = $_ctx->agora->getPostsPlus($params);
			$_ctx->posts = $core->blog->getPosts($params);
			
			if ($_ctx->posts->isEmpty()) { 
				self::p404();
			}
			//die($_ctx->posts->post_content);
			$subtitle = ' - '.$_ctx->posts->post_title;
		}
		
		$tpl = 'agora-'.$type;
		if ($messages) {
			$tpl .= '-messages';
			//$_ctx->nb_comment_per_page = $core->blog->settings->nb_comment_per_feed;
			$_ctx->nb_message_per_page = $core->blog->settings->agora->nb_message_per_feed;
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
		return;
	}
}
?>