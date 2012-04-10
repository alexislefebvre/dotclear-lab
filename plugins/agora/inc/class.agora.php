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

/**
@ingroup DC_CORE
@nosubgrouping
@brief Dotclear agora class.

Dotclear agora class instance is provided by dcCore $blog property.
*/
class agora 
{
	private $user_status = array();
	private $message_status = array();
	public $media_dir = 'agora-medias';

	/**
	Inits agora object
	
	@param	core		<b>dcCore</b>		Dotclear core reference
	*/	
	public function __construct($core)
	{
		$this->con =& $core->con;
		$this->prefix =& $core->prefix;
		$this->core =& $core;
		
		$this->user_status['-2'] = __('not verified');
		$this->user_status['-1'] = __('pending');
		$this->user_status['0'] = __('suspended');
		$this->user_status['1'] = __('active');
		
		$this->message_status['-3'] =  __('junk');
		$this->message_status['-2'] = __('pending');
		$this->message_status['-1'] = __('scheduled');
		$this->message_status['0'] = __('unpublished');
		$this->message_status['1'] = __('published');

		// Manage Medias
		if ($this->withMedia()) {
			$this->media = new dcMedia($this->core);
			$this->media->chdir($this->media_dir);
		}
		

		# --BEHAVIOR-- AgoraConstruct
		$this->core->callBehavior('AgoraConstruct',$this);
	}

	public function withMedia()
	{
		return mediaAgora::canWriteImages(true);
	}

	public function uploadFile($f,$filename,$user_id)
	{
		$target_dir = md5($user_id);
		try
		 {
			files::uploadStatus($f);
			if (!is_dir($target_dir)) {
				$this->media->makeDir($target_dir);
			}
			$this->media->chdir($this->media_dir.'/'.$target_dir);
			$this->media->uploadFile($f['tmp_name'],$filename,null,false,true);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	public function removeFile($filename,$user_id)
	{
		$target_dir = md5($user_id);
		try
		{
			$this->media->chdir($this->media_dir.'/'.$target_dir);
			$this->media->removeFile($filename);
			$this->media->chdir($this->media_dir);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	//@}
	
	/// @name Users management methods
	//@{
	/**
	Returns a user by its ID.
	
	@param	id		<b>string</b>		User ID
	@return	<b>record</b>
	*/
	public function getUser($id)
	{
		$params['user_id'] = $id;
		
		return $this->getUsers($params);
	}

	/**
	Returns a users list. <b>$params</b> is an array with the following
	optionnal parameters:
	
	 - <var>q</var>: search string (on user_id, user_name, user_firstname)
	 - <var>user_id</var>: user ID
	 - <var>order</var>: ORDER BY clause (default: user_id ASC)
	 - <var>limit</var>: LIMIT clause (should be an array ![limit,offset])
	
	@param	params		<b>array</b>		Parameters
	@param	count_only	<b>boolean</b>		Only counts results
	@param	perm	<b>string</b>		Permission (default: usage)
	@return	<b>record</b>
	*/
	public function getUsers($params=array(),$count_only=false,$perm='member')
	{
		$perm = $this->con->escape('%|'.strtolower($perm).'|%');
		if ($count_only)
		{
			$strReq =
			'SELECT count(U.user_id) '.
			'FROM '.$this->prefix.'user U '.
				'LEFT JOIN '.$this->prefix."permissions PE ON '".$this->con->escape($this->core->blog->id)."' = PE.blog_id ".
			"WHERE PE.user_id = U.user_id ".
			"AND (permissions LIKE '".$perm."' OR permissions LIKE '%|contentadmin|%' OR permissions LIKE '%|admin|%')";
		}
		else
		{
			$strReq =
			'SELECT U.user_id,user_status,user_pwd,user_name,'.
			'user_firstname,user_displayname,user_email,user_url,'.
			'user_desc, user_lang,user_tz, user_post_status,user_options, '.
			'user_creadt, user_upddt '.
			//'count(DISTINCT(P.post_id)) AS nb_post, '.
			//'count(DISTINCT(M.message_id)) AS nb_message '.
			'FROM '.$this->prefix.'user U '.
				//'LEFT JOIN '.$this->prefix."post P ON U.user_id = P.user_id ".$posts_public.
				//'LEFT JOIN '.$this->prefix.'message M ON U.user_id = M.user_id '.$messages_public.
				'JOIN '.$this->prefix."permissions PE ON '".$this->con->escape($this->core->blog->id)."' = PE.blog_id ".
			"WHERE PE.user_id = U.user_id ".
			//"AND '".$this->con->escape($this->core->blog->id)."' = P.blog_id ".
			"AND (permissions LIKE '".$perm."' OR permissions LIKE '%|contentadmin|%' OR permissions LIKE '%|admin|%')";
		}
		
		if (!empty($params['public'])) {
			//$posts_public = " AND (P.post_status = 1 ) ";
			//$messages_public = " AND (M.message_status = 1) ";
		}
	
		if (!empty($params['q'])) {
			$q = $this->con->escape(str_replace('*','%',strtolower($params['q'])));
			$strReq .= 'AND ('.
				"LOWER(U.user_id) LIKE '".$q."' ".
				"OR LOWER(user_name) LIKE '".$q."' ".
				"OR LOWER(user_firstname) LIKE '".$q."' ".
				') ';
		}
		
		if (isset($params['user_id']))
		{
			$strReq .= 'AND U.user_id '.$this->con->in($params['user_id']);
		}
		
		if (isset($params['user_status'])) {
			$strReq .= 'AND user_status = '.(integer) $params['user_status'].' ';
		}
		
		if (!$count_only) {
			$strReq .= 'GROUP BY U.user_id,user_status,user_pwd,user_name,'.
			'user_firstname,user_displayname,user_email,user_url,'.
			'user_desc, user_lang,user_tz,user_post_status,user_options,'.
			'user_creadt, user_upddt, '.
			'permissions ';
			
			if (!empty($params['order']) && !$count_only) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY U.user_id ASC ';
			}
		}
		
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}

		$rs = $this->con->select($strReq);
		$rs->extend('rsExtUser');
		
		# --BEHAVIOR-- agoraGetUsers
		$this->core->callBehavior('agoraGetUsers',$rs);
		return $rs;
	}
	
	public function getLogsLastVisit($params=array(), $count_only=false)
	{
		$params['log_msg'] = 'lastvisit';
		$rs = $this->core->log->getLogs($params,$count_only);
		return $rs;
	}

	public function getLastVisitUser($user_id)
	{
		$params['user_id'] = $user_id;
		$rs = $this->getLogsLastVisit($params);
		return $rs;
	}

	public function getAllUserStatus()
	{
		return $this->user_status;
	}
	
	public function getUserStatus($s)
	{
		if (isset($this->user_status[$s])) {
			return $this->user_status[$s];
		}
		return $this->user_status['1'];
	}
	
	public function getUnregistredUser($recover_key)
	{
		$strReq = 'SELECT user_id, user_status '.
		'FROM '.$this->prefix.'user U '.
		"WHERE user_recover_key = '".$this->con->escape($recover_key)."' ";

		$rs = $this->con->select($strReq);
		
		if ($rs->isEmpty()) {
			throw new Exception(__('This is a wrong registration URL. Registration failed.'));
		}

		$cur = $this->con->openCursor($this->prefix.'user');
		$cur->user_recover_key = null;
		
		$cur->update("WHERE user_recover_key = '".$this->con->escape($recover_key)."'");
		
		return array('user_status' => $rs->user_status, 'user_id' => $rs->user_id);
	}
	
	public function checkUserStatus($user_id)
	{
		$strReq = 'SELECT user_id, user_status '.
		'FROM '.$this->prefix.'user U '.
		"WHERE U.user_id = '".$this->con->escape($user_id)."' ";
		
		$rs = $this->con->select($strReq);
		
		if ($rs->isEmpty()) {
			//throw new Exception(__('This is a wrong registration URL. Registration failed.'));
			return false;
		}
		
		return $rs->user_status;
	}

	public function moderateUser($user_id,$status,$post_status)
	{
		if (!$this->core->auth->check('admin',$this->core->blog->id)) {
			throw new Exception(__('You need to be administrator to moderate users.'));
		}
		$warn_user = false;
		
		$cur = $this->core->con->openCursor($this->core->prefix.'user');
		
		if ($status == '' && $post_status =='') {
			//throw new Exception(sprintf(__('User_id "%s" is unchanged.'),html::escapeHTML($user_id))); 
			return;
		}

		$current_status = $this->checkUserStatus($user_id);
		
		if ((string) $current_status == -1) {
			$warn_user = true;
		}

		if ($status != '') {
			$cur->user_status = $status;
		}
		if ($post_status != '') {
			$cur->user_post_status = $post_status;
		}
		
		try 
		{
			$this->core->auth->sudo(array($this->core,'updUser'),$user_id,$cur);
			if ($warn_user) {
				mailAgora::sendWelcomeEmail($cur);
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}
		
		return true;
	}
	
	public function userLogin($login,$passwd,$key = '')
	{
		$key = empty($key) ? null : $key;
		
		//if (!$this->core->auth->checkUser($login,$passwd,$key) || (!$this->isMember($login)))
		// As dcAuth checkUser through findUserBlog need a 'usage' perm, we use dcPublicAuth::checkUser
		if (empty($passwd) && empty($key))
		{
			throw new Exception(__('Authentication failed.'));
		}
		if (!$this->isMember($login) || $this->checkUserStatus($login) != 1)
		{
			throw new Exception(__('This user is not allowed to log in.'));
		}
		if ($this->core->auth->checkUser($login,$passwd,$key,false) === false)
		{
			throw new Exception(__('Authentication failed.'));
		}
		$cookie_name = $this->core->blog->settings->agora->global_auth ? 'dc_agora_sess' : 'dc_agora_'.$this->core->blog->id;
		
		if (!session_id()) {
			$this->core->session->start();
		}
		$_SESSION['sess_user_id'] = $login;
		$_SESSION['sess_browser_uid'] = http::browserUID(DC_MASTER_KEY);
		if ($this->core->blog->settings->agora->global_auth === false) {
			$_SESSION['sess_blog_id'] = $this->core->blog->id;
		}
		//$_SESSION['sess_user_lastseen'] = $this->getLastVisitUser($login);
		$_SESSION['sess_type'] = 'agora';
		if (isset($_POST['user_remember'])) {
			$cookie_agora =
			http::browserUID(DC_MASTER_KEY.$login.crypt::hmac(DC_MASTER_KEY,$passwd)).
			bin2hex(pack('a32',$login));
			setcookie('dc_agora_auto_'.$this->core->blog->id,$cookie_agora,strtotime('+15 days'));
		}
		
		// Tweak for comment cookie
		$name = (string)dcUtils::getUserCN($this->core->auth->userID(),$this->core->auth->getInfo('user_name'),$this->core->auth->getInfo('user_firstname'),$this->core->auth->getInfo('user_displayname'));
		$mail = $this->core->auth->getInfo('user_email');
		$site = $this->core->auth->getInfo('user_url');
		setrawcookie('comment_info',rawurlencode($name."\n".$mail."\n".$site),strtotime('+30 days'));

		return $login;
	}

	// Update published date of post
	// Needed for forum usage.
	public function triggerPost($id)
	{
		$cur = $this->con->openCursor($this->prefix.'post');
		$offset = dt::getTimeOffset($this->core->blog->settings->system->blog_timezone);
		$cur->post_dt = date('Y-m-d H:i:s',time() + $offset);
		$cur->update('WHERE post_id = '.(integer) $id);
	}

	public function updPostCloseComments($id,$closed)
	{
		if (!$this->core->auth->check('usage,contentadmin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to close this entry'));
		}
		/*if (!$this->checkPermission($core->auth->userID(),'usage,contentadmin')) {
			throw new Exception(__('You are not allowed to close this entry'));
		}*/
		
		$id = (integer) $id;
		$closed = (boolean) $closed;
		
		# If user is only usage, we need to check the post's owner
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id))
		{
			$strReq = 'SELECT post_id '.
					'FROM '.$this->prefix.'post '.
					'WHERE post_id = '.$id.' '.
					"AND blog_id = '".$this->con->escape($this->core->blog->id)."' ".
					"AND user_id = '".$this->con->escape($this->core->auth->userID())."' ";
			
			$rs = $this->con->select($strReq);
			
			if ($rs->isEmpty()) {
				throw new Exception(__('You are not allowed to mark this entry as closed'));
			}
		}
		
		$cur = $this->con->openCursor($this->prefix.'post');
		
		$cur->post_open_comment = (integer) $closed;
		$cur->post_upddt = date('Y-m-d H:i:s');
		
		$cur->update(
			'WHERE post_id = '.$id.' '.
			"AND blog_id = '".$this->con->escape($this->core->blog->id)."' "
		);
		$this->core->blog->triggerBlog();
	}

	public function isMember($user_id)
	{
		return ($this->checkPermission($user_id,'member') || $this->core->auth->isSuperAdmin());
	}
	
	public function isModerator($user_id)
	{
		return ($this->checkPermission($user_id,'contentadmin,admin') || $this->core->auth->isSuperAdmin());
	}

	
	public function checkPermission($user_id,$perm)
	{
		$p = explode(',',$perm);

		$res = $this->core->getUserPermissions($user_id);
		$blog_id = $this->core->blog->id;
		
		if (!empty($res) && array_key_exists($blog_id,$res) && is_array($res[$blog_id]['p']))
		{
			foreach ($p as $v)
			{
				if (array_key_exists('admin',$res[$blog_id]['p'])) {
					return true;
				}
				if (array_key_exists($v,$res[$blog_id]['p'])) {
					return true;
				}
			}
		}
		return false;
	}

	public function getMessageStatus($s)
	{
		if (isset($this->message_status[$s])) {
			return $this->message_status[$s];
		}
		return $this->message_status['0'];
	}
	
	public function getAllMessageStatus()
	{
		return $this->message_status;
	}
	
	public function triggerMessage($id,$del=false)
	{
		$id = (integer) $id;
		
		$strReq = 'SELECT post_id '.
				'FROM '.$this->prefix.'message '.
				'WHERE message_id = '.$id.' ';
		
		$rs = $this->con->select($strReq);

		if ($rs->isEmpty()) {
			return;
		}
		
		$post_id = $rs->post_id;
		// Need sudo as we are not always post owner :
		$this->core->auth->sudo(
			array($this->core->meta,'delPostMeta'),
			$post_id,
			'nb_message'
			);
		
		$strReq = 'SELECT COUNT(post_id) '.
				'FROM '.$this->prefix.'message '.
				'WHERE post_id = '.(integer) $post_id.' '.
				'AND message_status = 1 ';
		
		if ($del) {
			$strReq .= 'AND message_id <> '.$id.' ';
		}
		
		$rs = $this->con->select($strReq);
		
		if ($rs->isEmpty()) {
			return;
		}
		else {
			$this->core->auth->sudo(
				array($this->core->meta,'setPostMeta'),
				$post_id,
				'nb_message',
				$rs->f(0) + 1
				);
		}
	}
	
	public function countMessages($post_id)
	{
		global $core;
		
		$core->meta->delPostMeta($post_id,'nb_message');
		
		$strReq = 'SELECT COUNT(post_id) '.
				'FROM '.$this->prefix.'message '.
				'WHERE post_id = '.(integer) $post_id.' '.
				'AND message_status = 1 ';
		
		$rs = $this->con->select($strReq);
		
		if ($rs->isEmpty()) {
			$core->meta->setPostMeta($post_id,'nb_message',1);
		}
		else {
			$core->meta->setPostMeta($post_id,'nb_message',$rs->f(0) + 1);
		}
	}
	
	public function allowMessages($post_id,$open=true)
	{
		global $core;
		
		$core->meta->delPostMeta($post_id,'post_open_message');
		
		if ($open) {
			$core->meta->setPostMeta($post_id,'post_open_message',2);
		}
		else {
			$core->meta->setPostMeta($post_id,'post_open_message',1);
		}
	}
	
	public function getMessages($params=array(),$count_only=false)
	{
		if ($count_only)
		{
			$strReq = 'SELECT count(message_id) ';
		}
		else
		{
			if (!empty($params['no_content'])) {
				$content_req = '';
			} else {
				$content_req =
				'message_content, message_content_xhtml, message_notes, message_words, ';
			}
			
			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ',$params['columns']).', ';
			}
			
			$strReq =
			'SELECT message_id,M.post_id, M.user_id, message_dt, '.
			'message_tz, message_creadt, message_upddt, message_format, '.
			$content_req.' message_status, P.post_status, '.
			'P.post_title, P.post_url, P.post_type, P.post_dt, P.user_id AS post_user_id, '.
			'V.user_name, V.user_firstname, V.user_displayname, V.user_email, '.
			'V.user_url, '.
			'C.cat_title, C.cat_url, C.cat_desc ';
			
		}
		
		$strReq .=
		'FROM '.$this->prefix.'message M '.
		'INNER JOIN '.$this->prefix.'post P ON P.post_id = M.post_id '.
		'LEFT OUTER JOIN '.$this->prefix.'category C ON P.cat_id = C.cat_id '.
		'LEFT OUTER JOIN '.$this->prefix.'user V ON M.user_id = V.user_id ';
		
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		$strReq .=
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND ((message_status = 1 AND post_status = 1 ';
			
			$strReq .= ') ';
			
			if ($this->core->auth->userID()) {
				$strReq .= "OR M.user_id = '".$this->con->escape($this->core->auth->userID())."')";
			} else {
				$strReq .= ') ';
			}
		}
		
		if (!empty($params['post_type']))
		{
			$strReq .= 'AND post_type '.$this->con->in($params['post_type']);
		}

		if (!empty($params['user_id'])) {
			$strReq .= "AND M.user_id = '".$this->con->escape($params['user_id'])."' ";
		}
		
		if (!empty($params['post_id'])) {
			$strReq .= 'AND P.post_id = '.(integer) $params['post_id'].' ';
		}
		
		if (!empty($params['cat_id'])) {
			$strReq .= 'AND P.cat_id = '.(integer) $params['cat_id'].' ';
		}
		
		if (!empty($params['message_id'])) {
			$strReq .= 'AND message_id = '.(integer) $params['message_id'].' ';
		}

		if (isset($params['post_status'])) {
			$strReq .= 'AND post_status = '.(integer) $params['post_status'].' ';
		}
		
		if (isset($params['message_status'])) {
			$strReq .= 'AND message_status = '.(integer) $params['message_status'].' ';
		}
		
		if (!empty($params['message_status_not']))
		{
			$strReq .= 'AND message_status <> '.(integer) $params['message_status_not'].' ';
		}
		

		if (isset($params['q_author'])) {
			$q_author = $this->con->escape(str_replace('*','%',strtolower($params['q_author'])));
			$strReq .= "AND LOWER(M.user_id) LIKE '".$q_author."' ";
		}
		
		if (!empty($params['search']))
		{
			$words = text::splitWords($params['search']);
			
			if (!empty($words))
			{
				# --BEHAVIOR coreMessageSearch
				if ($this->core->hasBehavior('agoraMessageSearch')) {
					$this->core->callBehavior('agoraMessageSearch',$this->core,array(&$words,&$strReq,&$params));
				}
				
				if ($words)
				{
					foreach ($words as $i => $w) {
						$words[$i] = "message_words LIKE '%".$this->con->escape($w)."%'";
					}
					$strReq .= 'AND '.implode(' AND ',$words).' ';
				}
			}
		}
		
		if (!empty($params['sql'])) {
			$strReq .= $params['sql'].' ';
		}
		
		if (!$count_only)
		{
			if (!empty($params['order'])) {
				$strReq .= 'ORDER BY '.$this->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY message_dt ASC ';
			}
		}
		
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}
		
		$rs = $this->con->select($strReq);
		$rs->core = $this->core;
		$rs->extend('rsExtMessage');
		
		# --BEHAVIOR-- agoraGetMessages
		$this->core->callBehavior('agoraGetMessages',$rs);
		
		return $rs;
	}
	
	public function addMessage($cur)
	{
		if (!$this->core->auth->check('usage,contentadmin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to create a message'));
		}
		
		$this->con->writeLock($this->prefix.'message');
		try
		{
			# Get ID
			$rs = $this->con->select(
				'SELECT MAX(message_id) '.
				'FROM '.$this->prefix.'message ' 
				);
			
			$cur->message_id = (integer) $rs->f(0) + 1;

			$cur->message_creadt = date('Y-m-d H:i:s');
			$cur->message_upddt = date('Y-m-d H:i:s');
			$cur->message_tz = $this->core->auth->getInfo('user_tz');
			
			# Post excerpt and content
			$this->getMessageContent($cur,$cur->message_id);
			
			$this->getMessageCursor($cur);
			
			/*if (!$this->core->auth->check('publish,contentadmin',$this->core->blog->id)) {
				$cur->message_status = -2;
			}*/
			
			$cur->insert();
			$this->con->unlock();
		}
		catch (Exception $e)
		{
			$this->con->unlock();
			throw $e;
		}
		$this->triggerMessage($cur->message_id);
		$this->core->blog->triggerBlog();
		
		return $cur->message_id;
	}
	
	public function updMessage($id,$cur)
	{
		if (!$this->core->auth->check('usage,contentadmin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to update messages'));
		}
		
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such message ID'));
		}
		
		$rs = $this->getMessages(array('message_id' => $id));
		
		if ($rs->isEmpty()) {
			throw new Exception(__('No such message ID'));
		}
		
		#If user is only usage, we need to check the post's owner
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id))
		{
			if ($rs->user_id != $this->core->auth->userID()) {
				throw new Exception(__('You are not allowed to update this message'));
			}
		}
		
		$this->getMessageContent($cur,$cur->message_id);
		
		$this->getMessageCursor($cur);
		
		$cur->message_upddt = date('Y-m-d H:i:s');
		
		if (!$this->checkPermission($this->core->auth->userID(),'publish,contentadmin')) {
			$cur->unsetField('message_status');
		}
		
		# --BEHAVIOR-- coreBeforeMessageUpdate
		$this->core->callBehavior('coreBeforeMessageUpdate',$this,$cur,$rs);
		
		$cur->update('WHERE message_id = '.$id.' ');
		
		# --BEHAVIOR-- coreAfterMessageUpdate
		$this->core->callBehavior('coreAfterMessageUpdate',$this,$cur,$rs);
		
		$this->triggerMessage($id);
		$this->core->blog->triggerBlog();
	}
	
	public function updMessageStatus($id,$status)
	{
		if (!$this->core->auth->check('publish,contentadmin',$this->core->blog->id)) {
			throw new Exception(__("You are not allowed to change this message status"));
		}
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id))
		{
			$strReq = 'SELECT message_id '.
					'FROM '.$this->prefix.'message '.
					'WHERE message_id = '.$id.' '.
					"AND user_id = '".$this->con->escape($this->core->auth->userID())."' ";
			
			$rs = $this->con->select($strReq);
			
			if ($rs->isEmpty()) {
				throw new Exception(__('You are not allowed to change this message status'));
			}
		}
		
		$cur = $this->con->openCursor($this->prefix.'message');
		
		$cur->message_status = $status;
		$cur->message_upddt = date('Y-m-d H:i:s');
		
		$cur->update(
			'WHERE message_id = '.$id.' '
			);
		$this->triggerMessage($id);
		$this->core->blog->triggerBlog();
	}
	
	public function delMessage($id)
	{
		if (!$this->core->auth->check('delete,contentadmin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to delete messages'));
		}
		
		$id = (integer) $id;
		
		if (empty($id)) {
			throw new Exception(__('No such message ID'));
		}
		
		#If user can only delete, we need to check the post's owner
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id))
		{
			$strReq = 'SELECT P.post_id '.
				'FROM '.$this->prefix.'post P, '.$this->prefix.'message M '.
				'WHERE P.post_id = M.post_id '.
				"AND P.blog_id = '".$this->con->escape($this->core->blog->id)."' ".
				'AND message_id = '.$id.' '.
				"AND user_id = '".$this->con->escape($this->core->auth->userID())."' ";
			
			$rs = $this->con->select($strReq);
			
			if ($rs->isEmpty()) {
				throw new Exception(__('You are not allowed to delete this message'));
			}
		}
		
		$strReq = 'DELETE FROM '.$this->prefix.'message '.
				'WHERE message_id = '.$id.' ';
		
		$this->triggerMessage($id,true);
		$this->con->execute($strReq);
		$this->core->blog->triggerBlog();
	}
	
	private function getMessageCursor($cur,$message_id=null)
	{
		if ($cur->message_content == '') {
			throw new Exception(__('No message content'));
		}

		if ($cur->message_dt == '') {
			$offset = dt::getTimeOffset($this->core->auth->getInfo('user_tz'));
			$now = time() + $offset;
			$cur->message_dt = date('Y-m-d H:i:00',$now);
		}
		
		$message_id = is_int($message_id) ? $message_id : $cur->message_id;
		
		if ($cur->message_content_xhtml == '') {
			throw new Exception(__('No message content xhtml'));
		}
		
		# Words list
		if ($cur->message_content_xhtml !== null)
		{
			$words = $cur->message_content_xhtml;
			
			$cur->message_words = implode(' ',text::splitWords($words));
		}
	}
	

	private function getMessageContent($cur,$message_id)
	{
		$message_content = $cur->message_content;
		$message_content_xhtml = $cur->message_content_xhtml;

		$this->setMessageContent(
			$message_id,$cur->message_format,
			$message_content,$message_content_xhtml
		);

		$cur->message_content = $message_content;
		$cur->message_content_xhtml = $message_content_xhtml;
	}

	public function setMessageContent($message_id,$format,&$content,&$content_xhtml)
	{
		if ($format == 'wiki')
		{
			$this->core->initWikiComment();
		}
		if ($content) {
			$content_xhtml = $this->core->callFormater($format,$content);
			$content_xhtml = $this->core->HTMLfilter($content_xhtml);
		} else {
			$content_xhtml = '';
		}
		# --BEHAVIOR-- coreAfterMessageContentFormat
		$this->core->callBehavior('coreAfterMessageContentFormat',array(
			'content' => &$content,
			'content_xhtml' => &$content_xhtml
		));
	}

	public function getConnectedUsers()
	{
		$strReq = 'SELECT ses_value '.
			'FROM '.$this->prefix.'session '.
			'WHERE NULL IS NULL ';
			
		$rs = $this->con->select($strReq);
		$res = $users = array();
		while ($rs->fetch()) 
		{
			$datas = explode(';',$rs->ses_value,-1);

			foreach ($datas as $data) 
			{
				$v = explode('|',trim($data));
				$res[$rs->index()][$v[0]] = @unserialize($v[1]);
			}
			
			$test = ($this->core->blog->settings->agora->global_auth === true) ? true : 
				$res[$rs->index()]['sess_blog_id'] == $this->core->blog->id ; 
			
			if (isset($res[$rs->index()]['sess_type'])) 
			{
				if (($res[$rs->index()]['sess_type'] == 'agora') && $test)
				{
					$users['user_id'][] = $res[$rs->index()]['sess_user_id'];
				} 
				else
				{
					unset($res[$rs->index()]);
				}
			}
			else 
			{
				unset($res[$rs->index()]);
			}
		}
		return $users;
	}
}
?>
