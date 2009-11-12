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

class agora 
{
	private $user_status = array();
	private $message_status = array();
	
	public function __construct($core)
	{
		$this->con =& $core->con;
		$this->prefix =& $core->prefix;
		$this->core =& $core;
		
		$this->user_status['-1'] = __('pending');
		$this->user_status['0'] = __('suspended');
		$this->user_status['1'] = __('active');
		
		$this->message_status['-2'] = __('junk');
		$this->message_status['-1'] = __('pending');
		$this->message_status['0'] = __('unpublished');
		$this->message_status['1'] = __('published');
		
		$this->core->auth = new dcPublicAuth($core);
		$this->core->log = new dcLog($core);
	}
	
	public function getUser($id)
	{
		$params['user_id'] = $id;
		
		return $this->getUsers($params);
	}
	
	public function getUsers($params=array(),$count_only=false)
	{
		if ($count_only)
		{
			$strReq =
			'SELECT count(U.user_id) '.
			'FROM '.$this->prefix.'user U '.
			'WHERE NULL IS NULL ';
		}
		else
		{
			$strReq =
			'SELECT U.user_id,user_super,user_status,user_pwd,user_name,'.
			'user_firstname,user_displayname,user_email,user_url,'.
			'user_desc, user_lang,user_tz, user_post_status,user_options, '.
			'user_creadt, user_upddt, '.
			'count(P.post_id) AS nb_post '.
			'FROM '.$this->prefix.'user U '.
				'LEFT JOIN '.$this->prefix.'post P ON U.user_id = P.user_id '.
			'WHERE NULL IS NULL ';
		}
		
		if (!empty($params['q'])) {
			$q = $this->con->escape(str_replace('*','%',strtolower($params['q'])));
			$strReq .= 'AND ('.
				"LOWER(U.user_id) LIKE '".$q."' ".
				"OR LOWER(user_name) LIKE '".$q."' ".
				"OR LOWER(user_firstname) LIKE '".$q."' ".
				') ';
		}
		
		if (!empty($params['user_id'])) {
			$strReq .= "AND U.user_id = '".$this->con->escape($params['user_id'])."' ";
		}
		
		if (!$count_only) {
			$strReq .= 'GROUP BY U.user_id,user_super,user_status,user_pwd,user_name,'.
			'user_firstname,user_displayname,user_email,user_url,'.
			'user_desc, user_lang,user_tz,user_post_status,user_options ';
			
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
		$rs->user_creadt = strtotime($rs->user_creadt);
		$rs->user_upddt = strtotime($rs->user_upddt);
		$rs->extend('rsExtUser');
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
	
	public function userlogIn($login,$passwd,$key = '')
	{
		$key = empty($key) ? null : $key;
		
		//if (!$this->core->auth->checkUser($login,$passwd,$key) || (!$this->isMember($login)))
		// As dcAuth checkUser through findUserBlog need a 'usage' perm, we use dcPublicAuth::checkUser
		if (empty($passwd))
		{
			throw new Exception(__('Cannot login. Empty password.'));
		}
		
		if (!$this->core->auth->checkPublicUser($login,$passwd,$key))
		{
			throw new Exception(__('Cannot login. Check.'));
		}
		elseif ($this->isMember($login) === false)
		{
			throw new Exception(__('User is not a member of forum'));
		}
		else
		{
			$this->core->session->start();
			$_SESSION['sess_user_id'] = $login;
			$_SESSION['sess_browser_uid'] = http::browserUID(DC_MASTER_KEY);
			$_SESSION['sess_blog_id'] = $this->core->blog->id;
			$_SESSION['sess_user_lastseen'] = $this->getLastVisitUser($login);
			$_SESSION['sess_forum'] = 1;
			if (isset($_POST['li_remember'])) {
				$cookie_forum =
				http::browserUID(DC_MASTER_KEY.$login.crypt::hmac(DC_MASTER_KEY,$passwd)).
				bin2hex(pack('a32',$login));
				setcookie('dc_forum_'.$this->core->blog->id,$cookie_forum,strtotime('+15 days'));
			}
			
			// later, we may set the cookie for comments...
			//$name = (string)dcUtils::getUserCN($this->core->auth->userID(),$this->core->auth->getInfo('user_name'),$this->core->auth->getInfo('user_firstname'),$this->core->auth->getInfo('user_displayname'));
			//$mail = $this->core->auth->getInfo('user_email');
			//$site = $this->core->auth->getInfo('user_url');
			//setrawcookie('comment_info',rawurlencode($name."\n".$mail."\n".$site),strtotime('+30 days'));

			return $login;
		}
		
	}

	public function sendActivationEmail($mail,$user_id,$pwd)
	{
		$key = $this->core->auth->setRecoverKey($user_id,$mail);
		$link = $this->core->blog->url.$this->core->url->getBase('register');
		$link .= strpos($link,'?') !== false ? '&' : '?';
		$url_forum = $this->core->url->getBase('forum');
		$url_login = $this->core->url->getBase('login');
		$sub = __('Account confirmation request on Agora');
		$msg = 
		sprintf(__('Welcome to the forum of %s'),$this->core->blog->name)."\n".
		__('To activate your account and verify your e-mail address, please click on the following link:').
		"\n\n".
		$link.'key='.$key.
		"\n\n".
		__('Your indormations:').
		sprintf(__('Login: %s'),$user_id)."\n".
		sprintf(__('Password: %s'),$pwd)."\n".
		__('Agora connection:').
		$url_login.
		"\n\n".
		__('If you have received this mail in error, you do not need to take any action to cancel the account.').
		__('The account will not be activated, and you will not receive any further emails.').
		__('If clicking the link above does not work, copy and paste the URL in a new browser window instead.').
		"\n\n".
		__('Thank you for particape to our agora.').
		"\n\n".
		__('This is a post-only mailing. Replies to this message are not monitored or answered.').
		"\n\n";   

		$this->sendEmail($mail,$sub,$msg);
	}

	public function sendRecoveryEmail($mail,$key)
	{
		
		$this->sendEmail($mail,$sub,$msg);
	}
	
	public function sendNewPasswordEmail($mail,$user_id,$pwd)
	{
		
		$this->sendEmail($mail,$sub,$msg);
	}
	
	protected function sendEmail($dest,$sub,$msg)
	{
		$headers = array(
		'From: '.mail::B64Header($this->core->blog->name).' forum <no-reply@'.str_replace('http://','',http::getHost()).' >',
		'Content-Type: text/plain; charset=UTF-8;',
		'X-Originating-IP: '.http::realIP(),
		'X-Mailer: Dotclear',
		'X-Blog-Id: '.mail::B64Header($this->core->blog->id),
		'X-Blog-Name: '.mail::B64Header($this->core->blog->name),
		'X-Blog-Url: '.mail::B64Header($this->core->blog->url)
		);
		
		$sub = '['.$this->core->blog->name.'] '.$sub;
		$sub = mail::B64Header($sub);
		
		mail::sendMail($dest,$sub,$msg,$headers);
	}

	private function getPostsCategoryFilter($arr,$field='cat_id')
	{
		$field = $field == 'cat_id' ? 'cat_id' : 'cat_url';
		
		$sub = array();
		$not = array();
		$queries = array();
		
		foreach ($arr as $v)
		{
			$v = trim($v);
			$args = preg_split('/\s*[?]\s*/',$v,-1,PREG_SPLIT_NO_EMPTY);
			$id = array_shift($args);
			$args = array_flip($args);
			
			if (isset($args['not'])) { $not[$id] = 1; }
			if (isset($args['sub'])) { $sub[$id] = 1; }
			if ($field == 'cat_id') {
				$queries[$id] = 'P.cat_id = '.(integer) $id;
			} else {
				$queries[$id] = "C.cat_url = '".$this->con->escape($id)."' ";
			}
		}
		
		if (!empty($sub)) {
			$rs = $this->con->select(
				'SELECT cat_id, cat_url, cat_lft, cat_rgt FROM '.$this->prefix.'category '.
				"WHERE blog_id = '".$this->con->escape($this->id)."' ".
				'AND '.$field.' '.$this->con->in(array_keys($sub))
			);
			
			while ($rs->fetch()) {
				$queries[$rs->f($field)] = '(C.cat_lft BETWEEN '.$rs->cat_lft.' AND '.$rs->cat_rgt.')';
			}
		}
		
		# Create queries
		$sql = array(
			0 => array(), # wanted categories
			1 => array()  # excluded categories
		);
		
		foreach ($queries as $id => $q) {
			$sql[(integer) isset($not[$id])][] = $q;
		}
		
		$sql[0] = implode(' OR ',$sql[0]);
		$sql[1] = implode(' OR ',$sql[1]);
		
		if ($sql[0]) {
			$sql[0] = '('.$sql[0].')';
		} else {
			unset($sql[0]);
		}
		
		if ($sql[1]) {
			$sql[1] = '(P.cat_id IS NULL OR NOT('.$sql[1].'))';
		} else {
			unset($sql[1]);
		}
		
		return implode(' AND ',$sql);
	}

	public function triggerThread($id)
	{
		/*$strReq = 'SELECT COUNT(post_id) '.
				'FROM '.$this->prefix.'post '.
				'WHERE thread_id = '.(integer) $id.' '.
				'AND post_status = 1 ';
		
		$rs = $this->con->select($strReq);*/
		
		$cur = $this->con->openCursor($this->prefix.'post');
		
		/*if ($rs->isEmpty()) {
			return;
		}
		*/
		//$cur->nb_comment = (integer) $rs->f(0);
		$cur->post_dt = date('Y-m-d H:i:s');
		
		$cur->update('WHERE post_id = '.(integer) $id);
	}

	public function getThreadURL($rs)
	{
		$thread_id = $rs->thread_id;
		
		$strReq = 'SELECT post_url '.
				'FROM '.$this->prefix.'post '.
				'WHERE post_id = '.(integer) $thread_id.' ';
		
		$rs = $this->con->select($strReq);
		
		if ($rs->isEmpty()) {
			return;
		}
		
		return $rs->post_url;
	}

	/**
	Retrieves categories. <var>$params</var> is an associative array which can
	take the following parameters:
	
	- post_type: Get only entries with given type (default "post")
	- cat_url: filter on cat_url field
	- cat_id: filter on cat_id field
	- start: start with a given category
	- level: categories level to retrieve
	- with_empty: filter empty categories
	
	@param    params    <b>array</b>        Parameters
	@return   <b>record</b>
	*/
	public function getCategoriesPlus($params=array())
	{
		// From /inc/core/class.dc.blog.php getCategories
		//Just authorize Empty Categories
		$c_params = array();
		if (isset($params['post_type'])) {
			$c_params['post_type'] = $params['post_type'];
			unset($params['post_type']);
		}
		$counter = $this->getCategoriesCounter($c_params);
		$counter2 = $this->getCategoriesCounter($c_params,true);
		
		//$without_empty = isset($params['without_empty']) ? (bool) $params['without_empty'] : ($this->core->auth->userID() == false);
		//$with_empty = isset($params['with_empty']) ? (bool) $params['with_empty'] : ($this->core->auth->userID() == false);
		//if (isset($params['with_empty'])) //&& ($params['with_empty']))) 
		//{ 
		//	$with_empty = true; 
		//} else { 
		//	$with_empty = $this->core->auth->userID() != false; # For public display   $this->core->auth->userID() !=
		//} 
		
		$start = isset($params['start']) ? (integer) $params['start'] : 0;
		$l = isset($params['level']) ? (integer) $params['level'] : 0;
		
		$rs = $this->core->blog->categories()->getChildren($start,null,'desc');
		
		# Get each categories total posts count
		$data = array();
		$stack = array();
		$stack2 = array();
		$level = 0;
		$cols = $rs->columns();
		while ($rs->fetch())
		{
			$nb_post = isset($counter[$rs->cat_id]) ? (integer) $counter[$rs->cat_id] : 0;
			$nb_answer = isset($counter2[$rs->cat_id]) ? (integer) $counter2[$rs->cat_id] : 0;
			
			if ($rs->level > $level) {
				$nb_total = $nb_post;
				$stack[$rs->level] = (integer) $nb_post;
				$nb_total2 = $nb_answer;
				$stack2[$rs->level] = (integer) $nb_answer;
			} elseif ($rs->level == $level) {
				$nb_total = $nb_post;
				$stack[$rs->level] += $nb_post;
				$nb_total2 = $nb_answer;
				$stack2[$rs->level] += $nb_answer;
			} else {
				$nb_total = $stack[$rs->level+1] + $nb_post;
				$nb_total2 = $stack2[$rs->level+1] + $nb_answer;
				if (isset($stack[$rs->level])) {
					$stack[$rs->level] += $nb_total;
					$stack2[$rs->level] += $nb_answer;
				} else {
					$stack[$rs->level] = $nb_total;
					$stack2[$rs->level] = $nb_total2;
				}
				unset($stack[$rs->level+1]);
				unset($stack2[$rs->level+1]);
			}
			
			//if (($nb_total == 0) && true) {
			//	continue;
			//}
			
			$level = $rs->level;
			
			$t = array();
			foreach ($cols as $c) {
				$t[$c] = $rs->f($c);
			}
			$t['nb_post'] = $nb_post;
			$t['nb_total'] = $nb_total;
			$t['nb_answer'] = $nb_answer;
			$t['nb_total2'] = $nb_total2;
			
			if ($l == 0 || ($l > 0 && $l == $rs->level)) {
				array_unshift($data,$t);
			}
		}
		
		# We need to apply filter after counting
		if (!empty($params['cat_id']))
		{
			$found = false;
			foreach ($data as $v) {
				if ($v['cat_id'] == $params['cat_id']) {
					$found = true;
					$data = array($v);
					break;
				}
			}
			if (!$found) {
				$data = array();
			}
		}
		
		if (!empty($params['cat_url']) && empty($params['cat_id']))
		{
			$found = false;
			foreach ($data as $v) {
				if ($v['cat_url'] == $params['cat_url']) {
					$found = true;
					$data = array($v);
					break;
				}
			}
			if (!$found) {
				$data = array();
			}
		}
		
		return staticRecord::newFromArray($data);
	}

	private function getCategoriesCounter($params=array(),$bis=false)
	{
		$strReq =
		'SELECT  C.cat_id, COUNT(P.post_id) AS nb_post, SUM(P.nb_comment) AS nb_answer '.
		'FROM '.$this->prefix.'category AS C '.
		'JOIN '.$this->prefix."post P ON (C.cat_id = P.cat_id AND P.blog_id = '".$this->con->escape($this->core->blog->id)."' ) ".
		"WHERE C.blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		
		if (!$this->core->auth->userID()) {
			$strReq .= 'AND P.post_status = 1 ';
		}
		
		if (!empty($params['post_type'])) {
			$strReq .= "AND post_type = '".$this->con->escape($params['post_type'])."' ";
		}
		else {
			$strReq .= "AND post_type = 'threadpost' ";
		}
		
		$strReq .= 'AND P.thread_id is NULL ';
		
		$strReq .= 'GROUP BY C.cat_id ';
		
		$rs = $this->con->select($strReq);
		$counters = array();
		$counters2 = array();
		while ($rs->fetch()) {
			$counters[$rs->cat_id] = $rs->nb_post;
			$counters2[$rs->cat_id] = $rs->nb_answer;
		}
		
		if ($bis) {
			return $counters2;
		} else {
			return $counters;
		}
	}

	public function getCategoryFirstChildren($id)
	{
		return $this->getCategoriesPlus(array('start' => $id,'level' => $id == 0 ? 1 : 2));
	}

	public function updPostClosed($id,$closed)
	{
		if (!$this->core->auth->check('usage,contentadmin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to close this thread'));
		}
		
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
		return $this->hasUserPerm($user_id,'member');
	}
	
	public function isModerator($user_id)
	{
		return $this->hasUserPerm($user_id,'moderator');
	}

	public function hasUserPerm($user_id,$perm)
	{
		$res = $this->core->getUserPermissions($user_id);
		$blog_id = $this->core->blog->id;
		
		if (!empty($res))
		{
			if (array_key_exists($perm,$res[$blog_id]['p'])) {
				return true;
			}
		}
		return false;
	}
	
	public function getAllMessageStatus()
	{
		return $this->message_status;
	}
	
	public function triggerMessage($id,$del=false)
	{
		global $core;
		$id = (integer) $id;
		
		$strReq = 'SELECT post_id '.
				'FROM '.$this->prefix.'message '.
				'WHERE message_id = '.$id.' ';
		
		$rs = $this->con->select($strReq);

		if ($rs->isEmpty()) {
			return;
		}
		
		$post_id = $rs->post_id;
		
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
			$nb = $rs->f(0);		
		}
		
		$meta = new dcMeta($core);
		$meta->delPostMeta($post_id,'nb_messages');
		$meta->setPostMeta($post_id,'nb_messages',$nb);
		
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
				'message_content, message_content_xhtml, message_notes, ';
			}
			
			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ',$params['columns']).', ';
			}
			
			$strReq =
			'SELECT message_id,M.post_id, M.user_id, message_dt, '.
			'message_tz, message_upddt, message_format, '.
			$content_req.' message_status, '.
			'P.post_title, P.post_url, P.post_type, P.post_dt, './/P.user_id, '.
			//'U.user_name, U.user_firstname, U.user_displayname, U.user_email, '.
			//'U.user_url, '.
			'V.user_name, V.user_firstname, V.user_displayname, V.user_email, '.
			'V.user_url, '.
			'C.cat_title, C.cat_url, C.cat_desc ';
			
		}
		
		$strReq .=
		'FROM '.$this->prefix.'message M '.
		'INNER JOIN '.$this->prefix.'post P ON P.post_id = M.post_id '.
		//'INNER JOIN '.$this->prefix.'user U ON U.user_id = M.user_id '.
		'LEFT OUTER JOIN '.$this->prefix.'category C ON P.cat_id = C.cat_id '.
		'LEFT OUTER JOIN '.$this->prefix.'user V ON M.user_id = V.user_id ';
		
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		$strReq .=
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND ((message_status = 1 AND P.post_status = 1 ';
			
			$strReq .= ') ';
			
			if ($this->core->auth->userID()) {
				$strReq .= "OR P.user_id = '".$this->con->escape($this->core->auth->userID())."')";
			} else {
				$strReq .= ') ';
			}
		}
		
		if (!empty($params['post_type']))
		{
			if (is_array($params['post_type']) && !empty($params['post_type'])) {
				$strReq .= 'AND post_type '.$this->con->in($params['post_type']);
			} else {
				$strReq .= "AND post_type = '".$this->con->escape($params['post_type'])."' ";
			}
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
		
		if (isset($params['message_status'])) {
			$strReq .= 'AND message_status = '.(integer) $params['message_status'].' ';
		}
		
		if (!empty($params['message_status_not']))
		{
			$strReq .= 'AND message_status <> '.(integer) $params['message_status_not'].' ';
		}
		

		if (isset($params['q_author'])) {
			$q_author = $this->con->escape(str_replace('*','%',strtolower($params['q_author'])));
			$strReq .= "AND LOWER(comment_author) LIKE '".$q_author."' ";
		}
		
		if (!empty($params['search']))
		{
			$words = text::splitWords($params['search']);
			
			if (!empty($words))
			{
				# --BEHAVIOR coreCommentSearch
				if ($this->core->hasBehavior('coreMessageSearch')) {
					$this->core->callBehavior('coreMessageSearch',$this->core,array(&$words,&$strReq,&$params));
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
				$strReq .= 'ORDER BY message_dt DESC ';
			}
		}
		
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}
		
		$rs = $this->con->select($strReq);
		$rs->core = $this->core;
		$rs->extend('rsExtMessage');
		
		# --BEHAVIOR-- coreBlogGetComments
		$this->core->callBehavior('agoraBlogGetMessages',$rs);
		
		return $rs;
	}
	
	public function addMessage($cur)
	{
		if (!$this->core->auth->check('usage,contentadmin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to create an message'));
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
			$cur->message_upddt = date('Y-m-d H:i:s');

			$offset = dt::getTimeOffset($this->core->blog->settings->blog_timezone);
			$cur->message_dt = date('Y-m-d H:i:s',time() + $offset);
			$cur->message_tz = $this->core->blog->settings->blog_timezone;
			
			# Post excerpt and content
			$this->getMessageContent($cur,$cur->message_id);
			
			$this->getMessageCursor($cur);
			
			if (!$this->core->auth->check('publish,contentadmin',$this->core->blog->id)) {
				$cur->message_status = -2;
			}
			//die(var_dump($cur->message_words));
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
			throw new Exception(__('You are not allowed to update comments'));
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
		
		if (!$this->core->auth->check('publish,contentadmin',$this->core->blog->id)) {
			$cur->unsetField('message_status');
		}
		
		# --BEHAVIOR-- coreBeforeCommentUpdate
		$this->core->callBehavior('coreBeforeMessageUpdate',$this,$cur,$rs);
		
		$cur->update('WHERE message_id = '.$id.' ');
		
		# --BEHAVIOR-- coreAfterCommentUpdate
		$this->core->callBehavior('coreAfterMessageUpdate',$this,$cur,$rs);
		
		$this->triggerMessage($id);
		$this->core->blog->triggerBlog();
	}
	
	public function updMessageStatus($id,$status)
	{
		if (!$this->core->auth->check('publish,contentadmin',$this->core->blog->id)) {
			throw new Exception(__("You are not allowed to change this message's status"));
		}
		
		$cur = $this->con->openCursor($this->prefix.'message');
		$cur->message_status = (integer) $status;
		$this->updMessage($id,$cur);
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
				throw new Exception(__('You are not allowed to delete this comment'));
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
				//die(var_dump('error'.$message_content));

		$this->setMessageContent(
			$message_id,$cur->message_format,
			$message_content,$message_content_xhtml
		);
//die(var_dump('errorse&nbsp;:'.$message_content_xhtml));

		$cur->message_content = $message_content;
		$cur->message_content_xhtml = $message_content_xhtml;
	}

	public function setMessageContent($message_id,$format,&$content,&$content_xhtml)
	{
		if ($content) {
			$content_xhtml = $this->core->callFormater($format,$content);
			$content_xhtml = $this->core->HTMLfilter($content_xhtml);
		} else {
			$content_xhtml = '';
		}
		# --BEHAVIOR-- coreAfterPostContentFormat
		$this->core->callBehavior('coreAfterMessageContentFormat',array(
			'content' => &$content,
			'content_xhtml' => &$content_xhtml
		));

	}
}
?>