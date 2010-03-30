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

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicBeforeDocument',array('agorapublicBehaviors','autoLogIn'));
$core->addBehavior('publicBeforeDocument',array('agorapublicBehaviors','cleanSession'));

//Admin announce set
$core->tpl->addValue('agoraAnnounce',array('agoraTemplate','agoraAnnounce'));

// URLs
$core->tpl->addValue('agoraURL',array('agoraTemplate','agoraURL'));
$core->tpl->addValue('registerURL',array('agoraTemplate','registerURL'));
$core->tpl->addValue('loginURL',array('agoraTemplate','loginURL'));
$core->tpl->addValue('profileURL',array('agoraTemplate','profileURL'));
$core->tpl->addValue('logoutURL',array('agoraTemplate','logoutURL'));
$core->tpl->addValue('AgoraFeedURL',array('agoraTemplate','AgoraFeedURL'));
$core->tpl->addValue('placeFeedURL',array('agoraTemplate','placeFeedURL'));


// Register page
$core->tpl->addBlock('IfRegisterPreview',array('agoraTemplate','IfRegisterPreview'));
$core->tpl->addValue('RegisterPreviewLogin',array('agoraTemplate','RegisterPreviewLogin'));
$core->tpl->addValue('RegisterPreviewEmail',array('agoraTemplate','RegisterPreviewEmail'));

// places loop
$core->tpl->addValue('placeURL',array('agoraTemplate','placeURL'));
$core->tpl->addValue('placeThreadsNumber',array('agoraTemplate','placeThreadsNumber'));
$core->tpl->addValue('placeAnswersNumber',array('agoraTemplate','placeAnswersNumber'));
$core->tpl->addValue('placeNewThreadLink',array('agoraTemplate','placeNewThreadLink'));
$core->tpl->addValue('placeID',array('agoraTemplate','placeID'));
$core->tpl->addValue('placeSpacer',array('agoraTemplate','placeSpacer'));
$core->tpl->addBlock('placeComboSelected',array('agoraTemplate','placeComboSelected'));

// Pagination plus (getMessages)
$core->tpl->addBlock('agoPagination',array('agoraTemplate','agoPagination'));
$core->tpl->addValue('agoPaginationCounter',array('agoraTemplate','agoPaginationCounter'));
$core->tpl->addValue('agoPaginationCurrent',array('agoraTemplate','agoPaginationCurrent'));
$core->tpl->addBlock('agoPaginationIf',array('agoraTemplate','agoPaginationIf'));
$core->tpl->addValue('agoPaginationURL',array('agoraTemplate','agoPaginationURL'));

// Thread loop
//$core->tpl->addBlock('ForumEntries',array('agoraTemplate','ForumEntries'));
$core->tpl->addValue('EntryIfClosed',array('agoraTemplate','EntryIfClosed'));
$core->tpl->addValue('EntryMessageCount',array('agoraTemplate','EntryMessageCount'));
$core->tpl->addValue('EntryCreaDate',array('agoraTemplate','EntryCreaDate'));
$core->tpl->addValue('EntryUpdDate',array('agoraTemplate','EntryUpdDate'));
// Thread loop, place context
$core->tpl->addBlock('IfThreadPreview',array('agoraTemplate','IfThreadPreview'));
$core->tpl->addValue('ThreadPreviewTitle',array('agoraTemplate','ThreadPreviewTitle'));
$core->tpl->addValue('ThreadPreviewContent',array('agoraTemplate','ThreadPreviewContent'));
//$core->tpl->addValue('ThreadURL',array('agoraTemplate','ThreadURL'));
$core->tpl->addValue('ThreadCategoryURL',array('agoraTemplate','ThreadCategoryURL'));
$core->tpl->addValue('MessageThreadURL',array('agoraTemplate','MessageThreadURL'));
$core->tpl->addValue('ThreadProfileUserID',array('agoraTemplate','ThreadProfileUserID'));
$core->tpl->addBlock('ThreadComboSelected',array('agoraTemplate','ThreadComboSelected'));
// Thread loop, thread context
$core->tpl->addBlock('IfAnswerPreview',array('agoraTemplate','IfAnswerPreview'));
$core->tpl->addValue('AnswerPreviewContent',array('agoraTemplate','AnswerPreviewContent'));
$core->tpl->addBlock('IfEditPreview',array('agoraTemplate','IfEditPreview'));
//$core->tpl->addBlock('IfIsThread',array('agoraTemplate','IfIsThread'));
$core->tpl->addValue('PostEditTitle',array('agoraTemplate','PostEditTitle'));
$core->tpl->addValue('PostEditContent',array('agoraTemplate','PostEditContent'));
$core->tpl->addValue('AnswerOrderNumber',array('agoraTemplate','AnswerOrderNumber'));
$core->tpl->addBlock('SysIfThreadUpdated',array('agoraTemplate','SysIfThreadUpdated'));
// Tread action modo suffixe
$core->tpl->addValue('ModerationDeleteThread',array('agoraTemplate','ModerationDeleteThread'));
$core->tpl->addValue('ModerationEditThread',array('agoraTemplate','ModerationEditThread'));
$core->tpl->addValue('ModerationDeleteMessage',array('agoraTemplate','ModerationDeleteMessage'));
$core->tpl->addValue('ModerationEditMessage',array('agoraTemplate','ModerationEditMessage'));
$core->tpl->addValue('ModerationPin',array('agoraTemplate','ModerationPin'));
$core->tpl->addValue('ModerationUnpin',array('agoraTemplate','ModerationUnpin'));
$core->tpl->addValue('ModerationClose',array('agoraTemplate','ModerationClose'));
$core->tpl->addValue('ModerationOpen',array('agoraTemplate','ModerationOpen'));

// Messages = answers to threads
$core->tpl->addBlock('Messages',array('agoraTemplate','Messages'));
$core->tpl->addBlock('MessagesHeader',array('agoraTemplate','MessagesHeader'));
$core->tpl->addBlock('MessagesFooter',array('agoraTemplate','MessagesFooter'));
$core->tpl->addValue('MessageIfFirst',array('agoraTemplate','MessageIfFirst'));
$core->tpl->addValue('MessageIfOdd',array('agoraTemplate','MessageIfOdd'));
$core->tpl->addValue('MessageContent',array('agoraTemplate','MessageContent'));
$core->tpl->addValue('MessageID',array('agoraTemplate','MessageID'));
$core->tpl->addValue('MessageOrderNumber',array('agoraTemplate','MessageOrderNumber'));
$core->tpl->addValue('MessageAuthorID',array('agoraTemplate','MessageAuthorID'));
$core->tpl->addValue('MessageAuthorCommonName',array('agoraTemplate','MessageAuthorCommonName'));
$core->tpl->addValue('MessageDate',array('agoraTemplate','MessageDate'));
$core->tpl->addValue('MessageTime',array('agoraTemplate','MessageTime'));
$core->tpl->addBlock('IfMessagePreview',array('agoraTemplate','IfMessagePreview'));
$core->tpl->addValue('MessagePreviewContent',array('agoraTemplate','MessagePreviewContent'));
$core->tpl->addValue('MessageEditContent',array('agoraTemplate','MessageEditContent'));
$core->tpl->addValue('MessageProfileUserID',array('agoraTemplate','MessageProfileUserID'));
$core->tpl->addValue('MessageEntryTitle',array('agoraTemplate','MessageEntryTitle'));
$core->tpl->addValue('MessageFeedID',array('agoraTemplate','MessageFeedID'));
//$core->tpl->addValue('',array('agoraTemplate',''));
//$core->tpl->addValue('',array('agoraTemplate',''));

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
	return;
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
					
					header('Content-Type: text/html; charset=UTF-8');
					http::head(201,'Created');
					header('Content-Type: text/html');
					header("Refresh: 5;URL=$url");
					echo sprintf(__('User %s successfully created. You will receive an email to activate your account.'),'<strong>'.$user_id.'</strong>');
					return;
					
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
						return;
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
		return;
	}
	
	public static function login($args)
	{
		// Ajouter un test sur les conditions générales ...
		// module de password recovery : envoi d'un email avec mot de passe généré auto..
		// URL forum/login : login user 
		
		global $core, $_ctx;
		$url = $core->blog->url.$core->url->getBase("agora");

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
			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
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
			
			//$_ctx->agora->isModerator($user_id) === false
			if ($args != $user_id)
			{
				$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
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
		return;
	}

	public static function old_forum($args)
	{
		// URL forum/ : home of the forum : see categories aka places
		
		global $core, $_ctx;
		
		//getCategoriesPlus ... 
		$params['without_empty'] = false;
		$_ctx->categories = $_ctx->agora->getCategoriesPlus($params);
		//die(var_dump($_ctx->categories));
		
		if (empty($_GET['q'])) {
			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
			self::serveDocument('forum.html','text/html',false);
			return;
		} else {
			 self::fsearch();
		}
	}
	
	public static function agora($args)
	{
		global $core;
		
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
				$core->url->type = $n > 1 ? 'agora-page' : 'agora';
			}
			
			if (empty($_GET['q'])) {
				$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
				self::serveDocument('agora.html','text/html',false);
			} else {
				self::search();
			}
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
		return;
	}

	public static function place($args)
	{
		// URL forum/sub/sub_url : view threads of a place
		// URL forum/sub/sub_url /newthread/ : write a new thread in the category
		
		global $core, $_ctx;
		
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
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
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
				$cur = $core->con->openCursor($core->prefix.'post');
				# Magic tweak doesn't work here
				//$core->blog->settings->system->post_url_format = "{id}";
				$cur->user_id = $user_id;
				$cur->cat_id = ((integer) $_POST['t_cat']) ? (integer) $_POST['t_cat'] : null;
				$cur->post_title = $title;
				$offset = dt::getTimeOffset($core->blog->settings->blog_timezone);
				$cur->post_dt = date('Y-m-d H:i:s',time() + $offset);
				$cur->post_format = 'wiki';
				$cur->post_status = 1;
				$cur->post_lang = $core->auth->getInfo('user_lang');
				$cur->post_content = $_POST['t_content'];
				$cur->post_type = 'thread';
				$cur->post_open_comment = 1;
				$redir = $core->blog->url.$core->url->getBase("thread").'/';
				//$redir .= strpos($redir,'?') !== false ? '&' : '?';
			
				try
				{
					# --BEHAVIOR-- publicBeforeThreadCreate
					$core->callBehavior('publicBeforeThreadCreate',$cur);
				
					$post_id = $core->auth->sudo(array($core->blog,'addPost'),$cur);
					//$meta = new dcMeta($core);
					//$meta->setPostMeta($post_id,'nb_messages',1);
				
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
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
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
		
		/*$thread_id = $_ctx->posts->post_id;
		$_ctx->post_preview = new ArrayObject();
		$_ctx->post_preview['content'] = '';
		$_ctx->post_preview['title'] = '';
		$_ctx->post_preview['rawcontent'] = '';
		$_ctx->post_preview['preview'] = false;*/
		//$_ctx->nb_message_per_page = $core->blog->settings->agora_nb_msg_per_page_per_thread;

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
					/*$cur = $core->con->openCursor($core->prefix.'post');
					$cur->user_id = $user_id;
					$cur->cat_id = $_ctx->posts->cat_id;
					$cur->post_format =  'wiki';
					$cur->post_status =  1;
					$cur->post_lang = $core->auth->getInfo('user_lang');
					$cur->post_title = $_ctx->posts->post_title;
					$cur->post_content = $_POST['p_content'];
					$cur->post_type =  'thread';*/
					$cur = $core->con->openCursor($core->prefix.'message');
					$cur->user_id = $user_id;
					$cur->message_format = 'wiki';
					$cur->message_content = $_POST['p_content'];
					$offset = dt::getTimeOffset($core->blog->settings->blog_timezone);
					$cur->message_dt = date('Y-m-d H:i:s',time() + $offset);
					//$cur->message_dt = date('Y-m-d H:i:s');
					$cur->post_id = $_ctx->posts->post_id;
					$cur->message_status =  1 ;
					
					$redir = $_ctx->posts->getURL();
					$redir .= strpos($redir,'?') !== false ? '&' : '?';
					
					// thread_id : new field in base : link between posts of a same thread
					//$cur->thread_id = $_ctx->posts->post_id;
					
					//$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts->post_url;
					//$redir .= strpos($redir,'?') !== false ? '&' : '?';
					
					//die(var_dump($cur));
					
					try
					{
						# --BEHAVIOR-- publicBeforePostCreate
						$core->callBehavior('publicBeforeMessageCreate',$cur);

						//$post_id = $core->auth->sudo(array($core->blog,'addPost'),$cur);
						//$comment_id = $core->blog->addComment($cur);
						# update nb_comment (used as nb_answers for the thread)
						//die($user_id);

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
		
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
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
		
		//$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts->post_url;
		$redir = $core->blog->url.$core->url->getBase("place").'/'.$_ctx->posts->cat_url;

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
		$_ctx->thread_preview['content'] = '';
		$_ctx->thread_preview['title'] = '';
		$_ctx->thread_preview['rawcontent'] = '';
		$_ctx->thread_preview['preview'] = false;
		$_ctx->thread_preview['cat'] = $_ctx->posts->cat_id;
		$_ctx->thread_preview['not_empty'] = ( $args == '' ) ? false : true;

		$p_content = $_ctx->posts->post_content;
		$p_title = $_ctx->posts->post_title;
		
		$_ctx->thread_preview['rawcontent'] = $p_content;
		$_ctx->thread_preview['title'] = $p_title;
		
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
				$cur = $core->con->openCursor($core->prefix.'post');
				$cur->post_id = $post_id;
				$cur->post_title = isset($_POST['ed_title'])? $_POST['ed_title'] : $_ctx->posts->post_title;
				$cur->post_content = isset($_POST['ed_content'])? $_POST['ed_content'] : $p_content;
				$cur->cat_id = isset($_POST['ed_cat'])? $_POST['ed_cat'] : $_ctx->posts->cat_id;
				$cur->post_format =  'wiki';
				
				$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts->post_url;
				
				/*else
				{
					//Ugly 
					$params['post_id'] = $_ctx->posts->thread_id;
					$params['no_content'] = true;
					$_ctx->posts2 = $_ctx->agora->getPostsPlus($params);
					$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts2->post_url;
				}*/
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
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('editpost.html','text/html',false);
		return;
	}

	public static function removemessage($args)
	{
		global $core, $_ctx;
		$user_id = $core->auth->userID();
		
		if ($_ctx->agora->isModerator($user_id) === false)
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
		$_ctx->message_preview['content'] = '';
		$_ctx->message_preview['rawcontent'] = '';
		$_ctx->message_preview['preview'] = false;

		$m_content = $_ctx->messages->message_content;
		
		$_ctx->message_preview['rawcontent'] = $m_content;
		
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
				
				/*else
				{
					//Ugly 
					$params['post_id'] = $_ctx->posts->thread_id;
					$params['no_content'] = true;
					$_ctx->posts2 = $_ctx->agora->getPostsPlus($params);
					$redir = $core->blog->url.$core->url->getBase("thread").'/'.$_ctx->posts2->post_url;
				}*/
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
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
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
			$_ctx->nb_message_per_page = $core->blog->settings->nb_message_per_feed;
		} else {
			$_ctx->nb_entry_per_page = $core->blog->settings->nb_post_per_feed;
			$_ctx->short_feed_items = $core->blog->settings->short_feed_items;
		}
		$tpl .= '.xml';
		
		if ($type == 'atom') {
			$mime = 'application/atom+xml';
		}
		
		$_ctx->feed_subtitle = $subtitle;
		header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->robots_policy,''));
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument($tpl,$mime);
		return;
	}
}

class widgetsAgora
{
	public static function memberWidget($w)
	{
		global $core;
		
		if ($core->url->type != 'place' && $core->url->type != 'thread' 
			&& $core->url->type != 'agora' && $core->url->type != 'agora-page') {
			return;
		}
		
		$user_displayname = ($core->auth->getInfo('user_displayname') == '' )? $core->auth->userID() : $core->auth->getInfo('user_displayname');
		
		$content = 
			'<li><a href="'.$core->blog->url.$core->url->getBase("agora").'">'.__('Home').'</a></li>';
		$content .=
			($core->auth->userID() != false && isset($_SESSION['sess_user_id'])) ?
			'<li><a href="'.$core->blog->url.$core->url->getBase("newthread").'">'.__('New thread').'</a></li>'.
			'<li><a href="'.$core->blog->url.$core->url->getBase("profile").'/'.$core->auth->userID().'"><strong>'.$user_displayname.'</strong></a></li>'.
			'<li><a href="'.$core->blog->url.$core->url->getBase("logout").'">'.__('Logout').'</a></li>' : 
			'<li><a href="'.$core->blog->url.$core->url->getBase("login").'">'.__('Login').'</a></li>'.
			'<li><a href="'.$core->blog->url.$core->url->getBase("register").'">'.__('Register').'</a></li>';
		
		return
		'<div class="agorabox">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.
		$content.
		'</ul>'.
		'</div>';
	}
	
	public static function moderateWidget($w)
	{
		global $core, $_ctx;
		
		if ($core->url->type != 'thread') {
			return;
		}
		
		$url = $core->blog->url.$core->url->getBase("thread")."/".$_ctx->posts->post_url;
		$url .= strpos($core->blog->url,'?') !== false ? '&' : '?';
		$openclose = $_ctx->posts->post_open_comment ? 
			'<li><a href="'.$url.'action=close'.'">'.__('Close the thread').'</a></li>' : 
			'<li><a href="'.$url.'action=open'.'">'.__('Open the thread').'</a></li>';
			
		$pinunpin = $_ctx->posts->post_selected ? 
			'<li><a href="'.$url.'action=unpin'.'">'.__('Unpin the thread').'</a></li>' : 
			'<li><a href="'.$url.'action=pin'.'">'.__('Pin the thread').'</a></li>';
		
		$res  = 
			(($core->auth->userID() != false) && $_ctx->agora->isModerator($core->auth->userID()) === true) ?
			'<div class="agoramodobox">'.
			($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
			'<ul>'.
			$openclose.
			$pinunpin.
			'</ul>'.
			'</div>' :'';
		
		return $res;
	}

	public static function categoriesWidget($w)
	{
		global $core;

		if ($core->url->type != 'place' && $core->url->type != 'thread' 
			&& $core->url->type != 'agora' && $core->url->type != 'agora-page') {
			return;
		}
		
		$rs = $core->blog->getCategories(array('post_type'=>'thread'));
		if ($rs->isEmpty()) {
			return;
		}
		
		$res =
		'<div class="places">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '');
		
		$ref_level = $level = $rs->level-1;
		while ($rs->fetch())
		{
			$class = '';
			if (($core->url->type == 'place' && $_ctx->categories instanceof record && $_ctx->categories->cat_id == $rs->cat_id)
			|| ($core->url->type == 'thread' && $_ctx->posts instanceof record && $_ctx->posts->cat_id == $rs->cat_id)) {
				$class = ' class="place-current"';
			}
			
			if ($rs->level > $level) {
				$res .= str_repeat('<ul><li'.$class.'>',$rs->level - $level);
			} elseif ($rs->level < $level) {
				$res .= str_repeat('</li></ul>',-($rs->level - $level));
			}
			
			if ($rs->level <= $level) {
				$res .= '</li><li'.$class.'>';
			}
			
			$res .=
			'<a href="'.$core->blog->url.$core->url->getBase('place').'/'.
			$rs->cat_url.'">'.
			html::escapeHTML($rs->cat_title).'</a>'.
			($w->postcount ? ' ('.$rs->nb_post.')' : '');
			
			
			$level = $rs->level;
		}
		
		if ($ref_level - $level < 0) {
			$res .= str_repeat('</li></ul>',-($ref_level - $level));
		}
		$res .= '</div>';
		
		return $res;
	}

	public static function bestofWidget($w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'agora') {
			return;
		}
		
		$params = array(
			'post_type' => 'thread',
			'post_selected'=>true,
			'no_content'=>true,
			'order'=>'post_dt desc');
		
		$rs = $core->blog->getPosts($params);
		
		if ($rs->isEmpty()) {
			return;
		}
		
		$res =
		'<div class="selected">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch()) {
			$res .= ' <li><a href="'.$rs->getURL().'">'.html::escapeHTML($rs->post_title).'</a></li> ';
		}
		
		$res .= '</ul></div>';
		
		return $res;
	}

	public static function lastthreadsWidget($w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'agora') {
			return;
		}
		
		$params['post_type'] = 'thread';
		$params['limit'] = abs((integer) $w->limit);
		$params['order'] = 'post_id desc';
		$params['no_content'] = true;
		
		if ($w->category)
		{
			if ($w->category == 'null') {
				$params['sql'] = ' AND p.cat_id IS NULL ';
			} elseif (is_numeric($w->category)) {
				$params['cat_id'] = (integer) $w->category;
			} else {
				$params['cat_url'] = $w->category;
			}
		}
		
		$rs = $core->blog->getPosts($params);
		
		if ($rs->isEmpty()) {
			return;
		}
		
		$res =
		'<div class="lastthreads">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch()) {
			$res .= '<li><a href="'.$rs->getURL().'">'.
			html::escapeHTML($rs->post_title).'</a></li>';
		}
		
		$res .= '</ul></div>';
		
		return $res;
	}

	public static function lastmessagesWidget($w)
	{
		global $core, $_ctx;
		
		if ($w->homeonly && $core->url->type != 'agora') {
			return;
		}
		
		$params['limit'] = abs((integer) $w->limit);
		//$params['order'] = 'message_dt desc';
		if ($w->category)
		{
			if ($w->category == 'null') {
				$params['sql'] = ' AND p.cat_id IS NULL ';
			} elseif (is_numeric($w->category)) {
				$params['cat_id'] = (integer) $w->category;
			} else {
				$params['cat_url'] = $w->category;
			}
		}
		$agora = new agora($core);
		$rs = $agora->getMessages($params);

		if ($rs->isEmpty()) {
			return;
		}
		
		$res = '<div class="lastmessages">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch())
		{
			$res .= '<li><a href="'.$rs->getThreadURL().'#m'.$rs->message_id.'">'.
			html::escapeHTML($rs->post_title).' - '.
			html::escapeHTML($rs->getAuthorCN()).
			'</a></li>';
		}
		
		$res .= '</ul></div>';
		
		return $res;
	}

	public static function subscribeWidget($w)
	{
		global $core, $_ctx;

		if ($w->homeonly && $core->url->type != 'agora') {
			return;
		}
		
		$type = ($w->type == 'atom' || $w->type == 'rss2') ? $w->type : 'rss2';
		$mime = $type == 'rss2' ? 'application/rss+xml' : 'application/atom+xml';
		
		$p_title = __('This agora\'s threads %s feed');
		$c_title = __('This agora\'s messages %s feed');
		
		$res =
		'<div class="syndicate">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		$res .=
		'<li><a type="'.$mime.'" '.
		'href="'.$core->blog->url.$core->url->getBase('agora_feed').'/'.$type.'" '.
		'title="'.sprintf($p_title,($type == 'atom' ? 'Atom' : 'RSS')).'" class="feed">'.
		__('Threads feed').'</a></li>'.
		'<li><a type="'.$mime.'" '.
		'href="'.$core->blog->url.$core->url->getBase('agora_feed').'/'.$type.'/messages" '.
		'title="'.sprintf($c_title,($type == 'atom' ? 'Atom' : 'RSS')).'" class="feed">'.
		__('Messages feed').'</a></li>';
		
		$res .= '</ul></div>';
		
		return $res;
	}
}
?>
