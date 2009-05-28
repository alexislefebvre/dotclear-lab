<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku , Tomtom and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicBeforeDocument',array('agorapublicBehaviors','autoLogIn'));
$core->addBehavior('publicBeforeDocument',array('agorapublicBehaviors','cleanSession'));

// URLs
$core->tpl->addValue('forumURL',array('agoraTemplate','forumURL'));
$core->tpl->addValue('registerURL',array('agoraTemplate','registerURL'));
$core->tpl->addValue('loginURL',array('agoraTemplate','loginURL'));
$core->tpl->addValue('profileURL',array('agoraTemplate','profileURL'));
$core->tpl->addValue('logoutURL',array('agoraTemplate','logoutURL'));

// Register page
$core->tpl->addBlock('IfRegisterPreview',array('agoraTemplate','IfRegisterPreview'));
$core->tpl->addValue('RegisterPreviewLogin',array('agoraTemplate','RegisterPreviewLogin'));
$core->tpl->addValue('RegisterPreviewEmail',array('agoraTemplate','RegisterPreviewEmail'));

// Subforums loop
$core->tpl->addBlock('Subforums',array('agoraTemplate','Subforums'));
$core->tpl->addBlock('SubforumFirstChildren',array('agoraTemplate','SubforumFirstChildren'));
$core->tpl->addValue('SubforumURL',array('agoraTemplate','SubforumURL'));
$core->tpl->addValue('SubforumThreadsNumber',array('agoraTemplate','SubforumThreadsNumber'));
$core->tpl->addValue('SubforumAnswersNumber',array('agoraTemplate','SubforumAnswersNumber'));
$core->tpl->addValue('SubForumNewThreadLink',array('agoraTemplate','SubForumNewThreadLink'));

// Pagination plus (see getPostPlus)
$core->tpl->addBlock('PaginationPlus',array('agoraTemplate','PaginationPlus'));

// Thread loop
$core->tpl->addBlock('ForumEntries',array('agoraTemplate','ForumEntries'));
$core->tpl->addValue('EntryIfClosed',array('agoraTemplate','EntryIfClosed'));
$core->tpl->addValue('ThreadAnswersCount',array('agoraTemplate','ThreadAnswersCount'));
$core->tpl->addValue('EntryCreaDate',array('agoraTemplate','EntryCreaDate'));
// Thread loop, subforum context
$core->tpl->addBlock('IfThreadPreview',array('agoraTemplate','IfThreadPreview'));
$core->tpl->addValue('ThreadPreviewTitle',array('agoraTemplate','ThreadPreviewTitle'));
$core->tpl->addValue('ThreadPreviewContent',array('agoraTemplate','ThreadPreviewContent'));
$core->tpl->addValue('ThreadURL',array('agoraTemplate','ThreadURL'));
$core->tpl->addValue('ThreadCategoryURL',array('agoraTemplate','ThreadCategoryURL'));
// Thread loop, thread context
$core->tpl->addBlock('IfAnswerPreview',array('agoraTemplate','IfAnswerPreview'));
$core->tpl->addValue('AnswerPreviewContent',array('agoraTemplate','AnswerPreviewContent'));
$core->tpl->addBlock('IfEditPreview',array('agoraTemplate','IfEditPreview'));
$core->tpl->addBlock('IfIsThread',array('agoraTemplate','IfIsThread'));
$core->tpl->addValue('PostEditTitle',array('agoraTemplate','PostEditTitle'));
$core->tpl->addValue('PostEditContent',array('agoraTemplate','PostEditContent'));
$core->tpl->addValue('AnswerOrderNumber',array('agoraTemplate','AnswerOrderNumber'));
$core->tpl->addBlock('SysIfThreadUpdated',array('agoraTemplate','SysIfThreadUpdated'));
// Tread action modo suffixe
$core->tpl->addValue('ModerationDelete',array('agoraTemplate','ModerationDelete'));
$core->tpl->addValue('ModerationEdit',array('agoraTemplate','ModerationEdit'));
$core->tpl->addValue('ModerationPin',array('agoraTemplate','ModerationPin'));
$core->tpl->addValue('ModerationUnpin',array('agoraTemplate','ModerationUnpin'));
$core->tpl->addValue('ModerationClose',array('agoraTemplate','ModerationClose'));
$core->tpl->addValue('ModerationOpen',array('agoraTemplate','ModerationOpen'));

// User 
$core->tpl->addBlock('authForm',array('agoraTemplate','authForm'));
$core->tpl->addBlock('notauthForm',array('agoraTemplate','notauthForm'));
$core->tpl->addValue('PublicUserID',array('agoraTemplate','PublicUserID'));
$core->tpl->addValue('PublicUserDisplayName',array('agoraTemplate','PublicUserDisplayName'));
$core->tpl->addBlock('userIsModo',array('agoraTemplate','userIsModo'));
$core->tpl->addValue('ProfileUserID',array('agoraTemplate','ProfileUserID'));
$core->tpl->addValue('ProfileUserDisplayName',array('agoraTemplate','ProfileUserDisplayName'));
$core->tpl->addValue('ProfileUserURL',array('agoraTemplate','ProfileUserURL'));
$core->tpl->addValue('ProfileUserEmail',array('agoraTemplate','ProfileUserEmail'));
$core->tpl->addValue('ProfileUserCreaDate',array('agoraTemplate','ProfileUserCreaDate'));
$core->tpl->addValue('ProfileUserUpdDate',array('agoraTemplate','ProfileUserUpdDate'));

//$core->tpl->addBlock('',array('agoraTemplate',''));
//$core->tpl->addValue('',array('agoraTemplate',''));

global $_ctx;

$_ctx->agora = new agora($core);
$_ctx->log = new dcLog($core);

class agorapublicBehaviors
{
	public static function autoLogIn()
	{
		global $core, $_ctx;

		$core->session = new sessionDB(
			$core->con,
			$core->prefix.'session',
			'dc_agora_sess_'.$core->blog->id,
			''
		);

		if (isset($_COOKIE['dc_agora_sess_'.$core->blog->id]))
		{
			# If we have a session we launch it now
			if (!$core->auth->checkSession())
			{
				# Avoid loop caused by old cookie
				$p = $core->session->getCookieParameters(false,-600);
				$p[3] = '/';
				call_user_func_array('setcookie',$p);
			}
		}

		if (!isset($_SESSION['sess_user_id']))
		{
			if (isset($_COOKIE['dc_agora_'.$core->blog->id])
			&& strlen($_COOKIE['dc_agora_'.$core->blog->id]) == 104)
			{
				# If we have a remember cookie, go through auth process with key
				$login = substr($_COOKIE['dc_agora_'.$core->blog->id],40);
				$login = @unpack('a32',@pack('H*',$login));
				if (is_array($login))
				{
					$login = $login[1];
					$key = substr($_COOKIE['dc_agora_'.$core->blog->id],0,40);
					$passwd = null;
				}
				else
				{
					$login = null;
				}
				
				$_ctx->agora->userlogIn($login,$passwd,$key);
			}
		}

		return;
	}

	public static function cleanSession()
	{
		global $core;

		$strReq = 'DELETE FROM '.$core->prefix.'session '.
				"WHERE ses_time < ".(time() - 3600*24*14);

		$core->con->execute($strReq);
	}
}


class urlAgora extends dcUrlHandlers
{
	public static function recover($args)
	{
		// forum/recover : set a recovery key 
		// forum/recover/blabla : create a newpassword and send it to user mailbox
		
		global $core, $_ctx;
		
		$recover = $core->auth->allowPassChange() && !empty($_REQUEST['recover']);
		$akey = ($core->auth->allowPassChange() && !empty($args)) ? $args : null;
		
		$user_id = $user_pwd = $user_key = $user_email = null;
		
		$_ctx->agora_recovery = new ArrayObject();
		$_ctx->agora_recovery['msg'] = '';
		
		# Recover password
		if ($recover && !empty($_POST['user_id']) && !empty($_POST['user_email']))
		{
			$user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;
			$user_email = !empty($_POST['user_email']) ? $_POST['user_email'] : '';
			try
			{
				$recover_key = $core->auth->setRecoverKey($user_id,$user_email);
				$_ctx->agora->sendRecoveryEmail($mail,$recover_key);
				http::head(200,'OK');
				header('Content-Type: text/html');
				echo sprintf(__('The e-mail was sent successfully to %s.'),'<strong>'.$user_email.'</strong>');
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
				header('Content-Type: text/plain');
				echo __('Your new password is in your mailbox.');
			}
			
			catch (Exception $e)
			{
				$_ctx->form_error = $e->getMessage();
			}
		
		}
		
	$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
	self::serveDocument('recovery.html');
	exit;
	}

	public static function register($args)
	{
		// URL forum/register : create the user but without any perm
		// forum/register/?key=12345678 : end of registration : add perm 'member'
		global $core, $_ctx;
		
		$_ctx->agora_register = new ArrayObject();
		$_ctx->agora_register['login'] = '';
		$_ctx->agora_register['email'] = '';
		$_ctx->agora_register['preview'] = false;
		$_ctx->agora_register['key'] = false;
		$_ctx->agora_register['pwd'] = '';
		
		$url = $core->blog->url.$core->url->getBase("forum");
		
		$register = isset($_POST['ru_login']) && isset($_POST['ru_email']);
		$key =  !empty($_GET['key']) ? $_GET['key'] : null;
		
		if ($register)
		{
			// Spam trap
			if (!isset($_POST['email2']) || $_POST['email2'] !== '') {
				http::head('412');
				header('Content-Type: text/plain');
				echo "So Long, and Thanks For All the Fish";
				exit;
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
				$cur->user_lang = $core->blog->settings->lang;
				$cur->user_tz = $core->blog->settings->blog_timezone;
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
					
					http::head(201,'Created');
					header('Content-Type: text/html');
					header("Refresh: 5;URL=$url");
					echo sprintf(__('User %s successfully created. You will receive an email to activate your account.'),'<strong>'.$user_id.'</strong>');
					exit;
					
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
					{
						// User has permission "member of agora"
						throw new Exception(sprintf(__('User %s is already registred. You can log in.'),html::escapeHTML($user_id)));
					}
					else
					{
						$perm = array('member' => '');
						$core->auth->sudo(array($core,'setUserBlogPermissions'),$user_id,$core->blog->id,$perm);
						http::head(200,'OK');
						header('Content-Type: text/html');
						echo sprintf(__('User %s is now registred. You can now log in.'),'<strong>'.$user_id.'</strong>');
						exit;
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
		
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('register.html','text/html',false);
		exit;
	}
	
	public static function login($args)
	{
		// Ajouter un test sur les conditions générales ...
		// module de password recovery : envoi d'un email avec mot de passe généré auto..
		// URL forum/login : login user 
		
		global $core, $_ctx;
		$url = $core->blog->url.$core->url->getBase("forum");

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
					http::head(200,'OK');
					header('Content-Type: text/html');
					header("Refresh: 5;URL=$url");
					echo sprintf(__('Login succesfull. You will be redirected automatically to the %s'),'<a href="'.$url.'">Agora</a>');
					exit;
				}

				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}
			}
			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
			self::serveDocument('login.html');
			exit;
		}
		else
		{
			http::head(100,'Continue');
			header('Location: '.$url);
			exit;
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
		
		http::redirect($core->blog->url.$core->url->getBase('forum'));
		exit;
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
			$user_id = $core->auth->userID();
			$_ctx->profile = $_ctx->agora->getUser($args);
			if ($_ctx->profile->isEmpty()) {
				self::p404();
			}
			
			$_ctx->profile_user = new ArrayObject();
			
			$_ctx->profile_user['pseudo'] = $_ctx->profile->user_displayname;
			$_ctx->profile_user['email'] = $_ctx->profile->user_email;
			$_ctx->profile_user['url'] = $_ctx->profile->user_url;
			$_ctx->profile_user['status'] = $_ctx->profile->user_status;
			$_ctx->profile_user['pwd'] = '';
			$_ctx->profile_user['msg'] = '';
			
			//$_ctx->agora->isModerator($user_id) === false
			if ($args != $user_id)
			{
				$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
				self::serveDocument('profile.html','text/html',false);
				exit;
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
						
						$_ctx->profile_user['msg'] = sprintf(__('User %s successfully updated.'),'<strong>'.$args.'</strong>');
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
		
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('profile_me.html','text/html',false);
		exit;
	}

	public static function forum($args)
	{
		// URL forum/ : home of the forum : see categories aka subforums
		
		global $core, $_ctx;
		
		//getCategoriesPlus ... 
		$params['without_empty'] = false;
		$_ctx->categories = $_ctx->agora->getCategoriesPlus($params);
		
		if (empty($_GET['q'])) {
			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
			self::serveDocument('forum.html','text/html',false);
			exit;
		} else {
			 self::fsearch();
		}
	}

	public static function fsearch()
	{
		global $core;
		
		$GLOBALS['_fsearch'] = !empty($_GET['q']) ? rawurldecode($_GET['q']) : '';
		if ($GLOBALS['_fsearch']) {
			$GLOBALS['_fsearch_count'] = $_ctx->agora->getPostsPlus(array('search' => $GLOBALS['_fsearch']),true)->f(0);
		}
		
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('forum_search.html');
		exit;
	}

	public static function subforum($args)
	{
		// URL forum/sub/sub_url : view threads of a subforum
		// URL forum/sub/sub_url /newthread/ : write a new thread in the category
		
		global $core, $_ctx;
		
		$core->addBehavior('coreInitWikiPost',array('agoraBehaviors','coreInitWikiPost'));
		//$core->addBehavior('coreBeforePostCreate',array('agoraBehaviors','coreBeforePostCreate'));
		
		$n = self::getPageNumber($args);
		
		if ($args == '' && !$n) {
			self::p404();
		}
		$params['without_empty'] = false;
		$params['cat_url'] = $args;
		$params['thread_id'] = '';
		
		$_ctx->categories = $_ctx->agora->getCategoriesPlus($params);
		
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
				$cur->post_type = 'threadpost';
				$cur->post_open_comment = 1;
				
				// thread_id : (new field in base ): link between posts of a same thread
				//$cur->thread_id = '';
			
				$redir = $core->blog->url.$core->url->getBase("subforum").'/'.$_ctx->categories->cat_url;
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
					exit;
				}
		
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}
			}
		}
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('subforum.html','text/html',false);
		exit;
	}

	public static function newthread($args)
	{
		global $core, $_ctx;
		$user_id = $core->auth->userID();
		
		$core->addBehavior('coreInitWikiPost',array('agoraBehaviors','coreInitWikiPost'));
		
		if ($args == '' || !$core->auth->userID()) {
			self::p404();
		}
		
		$params['cat_url'] = $args;
		
		$_ctx->categories = $_ctx->agora->getCategoriesPlus($params);
		
		if ($_ctx->categories->isEmpty())
		{
			self::p404();
		}
		
		$_ctx->thread_preview = new ArrayObject();
		$_ctx->thread_preview['title'] = '';
		$_ctx->thread_preview['content'] = '';
		$_ctx->thread_preview['rawcontent'] = '';
		$_ctx->thread_preview['preview'] = false;
		
		$thread_new = isset($_POST['t_content']) && isset($_POST['t_title']);
		
		if ($thread_new && ($_ctx->agora->isMember($user_id) === true))
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
				$cur->post_type = 'threadpost';
				$cur->post_open_comment = 1;
				$redir = $core->blog->url.$core->url->getBase("subforum").'/'.$_ctx->categories->cat_url;
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
					exit;
				}
		
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}
			}
		}
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('newthread.html','text/html',false);
		exit;
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
		
		if ($args == '' && !$n) {
			self::p404();
		}
		
		//if ($n) {
		//	$GLOBALS['_page_number'] = $n;
		//}
		$user_id = $core->auth->userID();
		$action =  !empty($_GET['action']) ? $_GET['action'] : null;
		
		$params = new ArrayObject();
		$params['post_url'] = $args;
		$params['post_type'] = 'threadpost';
		
		$_ctx->posts = $_ctx->agora->getPostsPlus($params);
		
		if ($_ctx->posts->isEmpty() || $_ctx->posts->thread_id != '')
		{
			self::p404();
		}
		
		$thread_id = $_ctx->posts->post_id;
		$_ctx->post_preview = new ArrayObject();
		$_ctx->post_preview['content'] = '';
		$_ctx->post_preview['title'] = '';
		$_ctx->post_preview['rawcontent'] = '';
		$_ctx->post_preview['preview'] = false;
		
		// Mark as selected or unselected 
		if ($_ctx->agora->isModerator($user_id) === true && 
		(($action == 'pin') || ($action == 'unpin')))
		{
			$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts->post_url;
			$redir .= strpos($redir,'?') !== false ? '&' : '?';
			
			try
			{
				$core->auth->sudo(array($core->blog,'updPostSelected'),$thread_id,$action == 'pin');
				
				$redir_arg = $action;
				$redir_arg .= '=1';
				
				header('Location: '.$redir.$redir_arg);
				exit;
			}
			
			catch (Exception $e)
			{
				$_ctx->form_error = $e->getMessage();
			}
		}
		
		// Mark as selected or unselected - open or close thread
		if ($_ctx->agora->isModerator($user_id) === true && 
		(($action == 'close') || ($action == 'open')))
		{
			$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts->post_url;
			$redir .= strpos($redir,'?') !== false ? '&' : '?';
			
			try
			{
				$core->auth->sudo(array($_ctx->agora,'updPostClosed'),$thread_id,$action == 'open');
				
				$redir_arg = $action;
				$redir_arg .= '=1';
				
				header('Location: '.$redir.$redir_arg);
				exit;
			}
			
			catch (Exception $e)
			{
				$_ctx->form_error = $e->getMessage();
			}
		}
		
		// Quick Answer 
		if ($_ctx->agora->isMember($user_id) === true)
		{
			$thread_answer = (isset($_POST['p_content']) && $_ctx->posts->commentsActive());
			
			if ($thread_answer)
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
				
				$_ctx->post_preview['content'] = $content;
				$_ctx->post_preview['rawcontent'] = $_POST['p_content'];
				
				if ($preview)
				{
					# --BEHAVIOR-- publicBeforePostPreview
					$core->callBehavior('publicBeforePostPreview',$_ctx->post_preview);
					
					$_ctx->post_preview['preview'] = true;
				}
				
				else
				{
					$cur = $core->con->openCursor($core->prefix.'post');
					$cur->user_id = $user_id;
					$cur->cat_id = $_ctx->posts->cat_id;
					$cur->post_format =  'wiki';
					$cur->post_status =  1;
					$cur->post_lang = $core->auth->getInfo('user_lang');
					$cur->post_title = $_ctx->posts->post_title;
					$cur->post_content = $_POST['p_content'];
					$cur->post_type =  'threadpost';
					
					// thread_id : new field in base : link between posts of a same thread
					$cur->thread_id = $_ctx->posts->post_id;
					
					$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts->post_url;
					$redir .= strpos($redir,'?') !== false ? '&' : '?';
					
					try
					{
						# --BEHAVIOR-- publicBeforePostCreate
						$core->callBehavior('publicBeforePostCreate',$cur);
						
						$post_id = $core->auth->sudo(array($core->blog,'addPost'),$cur);
						# update nb_comment (used as nb_answers for the thread)
						$_ctx->agora->triggerThread($_ctx->posts->post_id);
						
						# --BEHAVIOR-- publicAfterPostCreate
						$core->callBehavior('publicAfterPostCreate',$cur,$post_id);
						
						$redir_arg = 'pub=1';
						
						header('Location: '.$redir.$redir_arg);
						exit;
					}
					
					catch (Exception $e)
					{
						$_ctx->form_error = $e->getMessage();
					}
				}
			}
		}
		
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('thread.html','text/html',false);
		exit;
	}
	
	public static function removepost($args)
	{
		global $core, $_ctx;
		$user_id = $core->auth->userID();
		
		if ($_ctx->agora->isModerator($user_id) === false)
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
		$params['post_type'] = 'threadpost';
		$_ctx->posts = $_ctx->agora->getPostsPlus($params);
		unset($params);
		$thread_id = $_ctx->posts->thread_id;
		$params['post_id'] = $_ctx->posts->thread_id;
		$params['post_type'] = 'threadpost';
		unset($_ctx->posts);
		$_ctx->posts = $_ctx->agora->getPostsPlus($params);
		$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts->post_url;

		$redir .= strpos($redir,'?') !== false ? '&' : '?';
		
		try
		{
			# --BEHAVIOR-- publicBeforePostDelete
			$core->callBehavior('publicBeforePostDelete',$post_id);
			
			$core->auth->sudo(array($core->blog,'delPost'),$post_id);
			# update nb_comment (used as nb_answers for the thread)
			$_ctx->agora->triggerThread($thread_id);
			
			# --BEHAVIOR-- publicAfterPostDelete
			$core->callBehavior('publicAfterPostDelete',$post_id);
			
			$redir_arg = 'del=1';
			
			header('Location: '.$redir.$redir_arg);
			exit;
		}
		
		catch (Exception $e)
		{
			$_ctx->form_error = $e->getMessage();
		}
			
	}
	
	public static function editpost($args)
	{
		global $core, $_ctx;
		$user_id = $core->auth->userID();
		
		if ($_ctx->agora->isModerator($user_id) === false)
		{
			self::p404();
		}
		
		$params['post_id'] = $args ;
		$params['post_type'] = 'threadpost';
		$_ctx->posts = $_ctx->agora->getPostsPlus($params);

		if ($_ctx->posts->isEmpty() )
		{
			self::p404();
		}

		$_ctx->post_preview = new ArrayObject();
		$_ctx->post_preview['content'] = '';
		$_ctx->post_preview['title'] = '';
		$_ctx->post_preview['rawcontent'] = '';
		$_ctx->post_preview['preview'] = false;

		$p_content = $_ctx->posts->post_content;
		//edit title if beginning of thread 
		$p_title = ($_ctx->posts->thread_id == '')? $_ctx->posts->post_title : '';
		
		$_ctx->post_preview['rawcontent'] = $p_content;
		$_ctx->post_preview['title'] = $p_title;
		$_ctx->post_preview['isThread'] = ($_ctx->posts->thread_id == '')? true : false;
		
		$edit_post = isset($_POST['ed_content']);
		
		if ($edit_post)
		{
			$content = isset($_POST['ed_content'])? $_POST['ed_content'] : '';;
			$preview = !empty($_POST['preview']);
		
			if ($content != '')
			{ 
				$core->initWikiPost();
				/// coreInitWikiPost
				$content = $core->wikiTransform($content);
				$content = $core->HTMLfilter($content);
			}
			
			$_ctx->post_preview['content'] = $content;
			$_ctx->post_preview['rawcontent'] =  $_POST['ed_content'];
			$_ctx->post_preview['title'] = isset($_POST['ed_title'])? $_POST['ed_title'] : $p_title;
			
			if ($preview)
			{
				# --BEHAVIOR-- publicBeforePostReview
				$core->callBehavior('publicBeforePostReview',$_ctx->post_preview);
			
				$_ctx->post_preview['preview'] = true;
			}
			else
			{
				$post_id = $args;
				$cur = $core->con->openCursor($core->prefix.'post');
				$cur->post_id = $post_id;
				$cur->post_title = isset($_POST['ed_title'])? $_POST['ed_title'] : $_ctx->posts->post_title;
				$cur->post_content = isset($_POST['ed_content'])? $_POST['ed_content'] : $p_content;
				$cur->post_format =  'wiki';
				
				if  ($_ctx->post_preview['isThread'])
				{
					$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts->post_url;
				}
				else
				{
					//Ugly 
					$params['post_id'] = $_ctx->posts->thread_id;
					$params['no_content'] = true;
					$_ctx->posts2 = $_ctx->agora->getPostsPlus($params);
					$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts2->post_url;
				}
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
					exit;
				}
			
				catch (Exception $e)
				{
					$_ctx->form_error = $e->getMessage();
				}
			
			}
		}
		# The entry
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('editpost.html','text/html',false);
		exit;

	}

	public static function feed($args)
	{
		//Todo 
	}
}
?>
