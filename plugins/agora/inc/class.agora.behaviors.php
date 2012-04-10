<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012 Osku and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class agoraBehaviors
{
	public static function dashboardFavs($core,$favs)
	{
		$favs['messages'] = new ArrayObject(array('messages',__('Messages'),'plugin.php?p=agora&amp;act=messages',
			'index.php?pf=agora/icon-messages-16.png','index.php?pf=agora/icon-messages.png',
			'usage,contentadmin',null,null));
		$favs['publicusers'] = new ArrayObject(array('publicusers',__('Public users'),'plugin.php?p=agora',
			'index.php?pf=agora/icon-users-16.png','index.php?pf=agora/icon-users.png',
			'usage,contentadmin',null,null));
	}
############################
#PUBLIC HANDLERS FOR USERS
############################
	public static function sessionHandler()
	{
		global $core, $_ctx;
		
		$cookie_name = $core->blog->settings->agora->global_auth ? 'dc_agora_sess' : 'dc_agora_'.$core->blog->id;
		
		$core->session = new sessionDB(
			$core->con,
			$core->prefix.'session',
			$cookie_name
		);

		if (isset($_COOKIE[$cookie_name]))
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
		$cookie_auto_name = 'dc_agora_auto_'.$core->blog->id;

		if (!isset($_SESSION['sess_user_id']))
		{
			if (isset($_COOKIE[$cookie_auto_name])
			&& strlen($_COOKIE[$cookie_auto_name]) == 104)
			
			{
				# If we have a remember cookie, go through auth process with key
				$login = substr($_COOKIE[$cookie_auto_name],40);
				$login = @unpack('a32',@pack('H*',$login));
				if (is_array($login))
				{
					$login = $login[1];
					$key = substr($_COOKIE[$cookie_auto_name],0,40);
					$passwd = null;
				}
				else
				{
					$login = null;
				}
				
				$core->agora->userlogIn($login,$passwd,$key);
			}
		}
		return;
	}

	public static function sessionCleaner()
	{
		global $core;
		
		$strReq = 'DELETE FROM '.$core->prefix.'session '.
				"WHERE ses_time < ".(time() - 3600*24*8);
		
		$core->con->execute($strReq);
	}

##########################
#ADMIN/PUBLIC POST 
##########################
// All entries are initialised with 
// - nb_message = 1 (0 in fact)
// - post_open_message = 2 (1 for true in fact)
	public static function initNbMessages($blog,$cur)
	{
		global $core;
		
		$core->auth->sudo(array($core->meta,'setPostMeta'),$cur->post_id,'nb_message', 1);
		$core->auth->sudo(array($core->meta,'setPostMeta'),$cur->post_id,'post_open_message', 2);
		
	}
	
	public static function adminPostFormSidebar($post)
	{
		global $core;
		
		$id = isset($post) && !$post->isEmpty() ? $post->post_id : null;
		
		$params = array(
			'post_id' => $id
		);
		
		$counter = $core->agora->getMessages($params,true)->f(0);
		
		if ((string) $counter == 0) {
			$message_info = __('No message');
		} elseif ((string) $counter == 1) {
			$message_info = sprintf('<a href="plugin.php?p=agora&amp;act=messages&amp;post_id=%s">'.__('One message').'</a>',$id);
		} else {
			$message_info = sprintf('<a href="plugin.php?p=agora&amp;act=messages&amp;post_id=%s">'.__('%d messages').'</a>',$id,$counter);
		}
		// (Workaround) Messages open : 2 else messages closed : 1
		$post_open_msg = ($id)? (boolean)$post->checkIfMessagesOpen() : true;
		
		echo 
			'<h3>'.__('Messages:').'</h3>
			<p><label for="post_open_msg" class="classic">'.
				form::checkbox('post_open_msg',1,$post_open_msg).' '.
			__('Accept messages').'</label></p>';
		if ($id) { echo '<p>'.$message_info.'</p>';}
	}
	
	public static function adminBeforePostUpdate($cur,$post_id)
	{
		global $core;
		
		$post_id = (integer) $post_id;
		$msg_open = (empty($_POST['post_open_msg'])) ? 1 : 2;
		
		$core->meta->delPostMeta($post_id,'post_open_message');
		$core->meta->setPostMeta($post_id,'post_open_message',$msg_open);
	}

##########################
#USER
##########################

	public static function publicAfterUserCreate($cur,$user_id)
	{
		global $core;
		// add the simple permission to public access 
		// Remember good user status is mandatory
		$perm = array('member' => '');
		$core->auth->sudo(array($core,'setUserBlogPermissions'),$user_id,$core->blog->id,$perm);
	}

	public static function adminPostHeaders()
	{
		return;
		//'<script type="text/javascript" src="index.php?pf=agora/js/_messages.js"></script>'."\n";
	}
	

	public static function agoraGetUsers($rs)
	{
		$rs->extend('rsExtMember');
	}
	
	public static function coreBlogGetPosts($rs)
	{
		$rs->extend('rsExtThread');
	}
	
	public static function agoraGetMessages($rs)
	{
		$rs->extend('rsExtMessagePublic');
	}

	// Not used for now
	public static function coreInitWikiPost($wiki2xhtml)
	{
		global $core;
		
		$wiki2xhtml->setOpts(array(
			'active_title' => 0,
			'active_auto_br' => 0,
			'active_auto_urls' => 0,
			'active_urls' => 1,
			'active_auto_img' => 1,
			'active_img' => 1,
			'active_footnotes' => 0
		));
		return;
	}

	// Not used for now
	public static function coreInitWikiComment($wiki2xhtml)
	{
		global $core;
		
		$wiki2xhtml->setOpts(array(
			'active_quote' => 1,
			'active_auto_img' => 0,
			'active_img' => 0
		));
		return;
	}
	
	// For user_desc
	public static function coreInitWikiSimpleComment($wiki2xhtml)
	{
		global $core;
		
		$wiki2xhtml->setOpts(array(
			'active_title' => 0,
			'active_auto_br' => 0,
			'active_auto_urls' => 1,
			'active_urls' => 1,
			'active_auto_img' => 0,
			'active_img' => 0,
			'active_footnotes' => 0,
			'active_em' => 1,
			'active_strong' => 1,
			'active_br' => 1,
			'active_q' => 1
		));
		return;	
	}

########################################
#POSTS LIST
########################################
	public static function adminPostsActionsCombo($args)
	{
		$args[0][__('Agora')] = array(__('Count messages') => 'messagescount',
		__('Allow messages') => 'messagesopen',
		__('Disallow messages') => 'messagesclose');
	}
	
	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action == 'messagescount')
		{
			try
			{
				while ($posts->fetch())
				{
					$core->agora->countMessages($posts->post_id);
				}
				
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		if ($action == 'messagesopen')
		{
			try
			{
				while ($posts->fetch())
				{
					$core->agora->allowMessages($posts->post_id);
				}
				
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		if ($action == 'messagesclose')
		{
			try
			{
				while ($posts->fetch())
				{
					$core->agora->allowMessages($posts->post_id,false);
				}
				
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}

#####################################
#IMPORT / EXPORT
#####################################
	/**
	* Behaviors export
	*/
	public static function exportFull($core,$exp)
	{
		$exp->exportTable('message');
	}

	public static function exportSingle($core,$exp,$blog_id)
	{
		$exp->export('message',
			'SELECT M.* '.
			'FROM '.$core->prefix.'message M, '.$core->prefix.'post P '.
			'WHERE P.post_id = M.post_id '.
			"AND P.blog_id = '".$blog_id."'"
		);
	}

	/**
	* Behaviors import
	*/
	public static function importInit($bk,$core)
	{
		$bk->cur_message = $core->con->openCursor($core->prefix.'message');
	}

	public static function importSingle($line,$bk,$core)
	{
		if ($line->__name == 'message') {
			$rs = $core->con->select('SELECT MAX(message_id) FROM '.$core->prefix.'message');
			$bk->stack['message_id'] = ((integer) $rs->f(0))+1;
			//print_r($bk->old_ids);die;
			if (isset($bk->old_ids['post'][(integer) $line->post_id])) {
				$message_id = $bk->stack['message_id'];
				
				$line->message_id = $message_id;
				$line->post_id = $bk->old_ids['post'][(integer) $line->post_id];
				
				self::importSingleMessage($line,$bk,$core);
				
				$bk->stack['message_id']++;
			} else {
				throw new Exception(__('The backup file does not appear to be well formed.'));
			}
		}
	}
	
	public static function importSingleMessage($line,$bk,$core)
	{
		$bk->cur_message->clean();
		
		$bk->cur_message->message_id          = (integer) $line->message_id;
		$bk->cur_message->post_id             = (integer) $line->post_id;
		$bk->cur_message->user_id             = (string) $bk->getUserId($line->user_id);
		$bk->cur_message->message_dt			= (string) $line->message_dt;
		$bk->cur_message->message_tz			= (string) $line->message_tz;
		$bk->cur_message->message_creadt		= (string) $line->message_creadt;
		$bk->cur_message->message_upddt			= (string) $line->message_upddt;
		$bk->cur_message->message_format		= (string) $line->message_format;
		$bk->cur_message->message_content		= (string) $line->message_content;
		$bk->cur_message->message_content_xhtml	= (string) $line->message_content_xhtml;
		$bk->cur_message->message_notes			= (string) $line->message_notes;
		$bk->cur_message->message_words			= (string) $line->message_words;
		$bk->cur_message->message_status		= (string) $line->message_status;
		
		$bk->cur_message->insert();
	}

	public static function importFull($line,$bk,$core)
	{
		if ($line->__name == 'message') {
			self::importSingleMessage($line,$bk,$core);
		}
	}
}
?>
