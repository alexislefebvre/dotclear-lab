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
	
	public function __construct(&$core)
	{
		$this->con =& $core->con;
		$this->prefix =& $core->prefix;
		$this->core =& $core;
		
		$this->user_status['-1'] = __('pending');
		$this->user_status['0'] = __('suspended');
		$this->user_status['1'] = __('active');

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

	public function getPostsPlus($params=array(),$count_only=false)
	{
		if ($count_only)
		{
			$strReq = 'SELECT count(P.post_id) ';
		}
		else
		{
			if (!empty($params['no_content'])) {
				$content_req = '';
			} else {
				$content_req =
				'post_excerpt, post_excerpt_xhtml, '.
				'post_content, post_content_xhtml, post_notes, ';
			}
		
			if (!empty($params['columns']) && is_array($params['columns'])) {
				$content_req .= implode(', ',$params['columns']).', ';
			}
		
			$strReq =
			'SELECT P.post_id, P.blog_id, P.user_id, P.cat_id, post_dt, '.
			'post_tz, post_creadt, post_upddt, post_format, post_password, '.
			'post_url, post_lang, post_title, '.$content_req.
			'post_type, post_meta, post_status, post_selected, post_position, '.
			'nb_comment, post_open_comment, '.
			'thread_id, '.
			'U.user_name, U.user_firstname, U.user_displayname, U.user_email, '.
			'U.user_url, '.
			'C.cat_title, C.cat_url, C.cat_desc ';
		}
		
		$strReq .=
		'FROM '.$this->prefix.'post P '.
		'INNER JOIN '.$this->prefix.'user U ON U.user_id = P.user_id '.
		'LEFT OUTER JOIN '.$this->prefix.'category C ON P.cat_id = C.cat_id ';
		
		if (!empty($params['from'])) {
			$strReq .= $params['from'].' ';
		}
		
		$strReq .=
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND ((post_status = 1 ';
			
			if ($this->core->blog->without_password) {
				$strReq .= 'AND post_password IS NULL ';
			}
			$strReq .= ') ';
			
			if ($this->core->auth->userID()) {
				$strReq .= "OR P.user_id = '".$this->con->escape($this->core->auth->userID())."')";
			} else {
				$strReq .= ') ';
			}
		}
		
		# Adding parameters
		if (isset($params['post_type']))
		{
			if (is_array($params['post_type']) && !empty($params['post_type'])) {
				$strReq .= 'AND post_type '.$this->con->in($params['post_type']);
			} elseif ($params['post_type'] != '') {
				$strReq .= "AND post_type = '".$this->con->escape($params['post_type'])."' ";
			}
		}
		else
		{
			$strReq .= "AND post_type = 'post' ";
		}
		
		# Only if we want subjects 
		if (!empty($params['threads_only']))
		{
			$strReq .= 'AND thread_id IS NULL ';
		}
		
		if (!empty($params['post_id'])) {
			if (is_array($params['post_id'])) {
				array_walk($params['post_id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			} else {
				$params['post_id'] = array((integer) $params['post_id']);
			}
			$strReq .= 'AND P.post_id '.$this->con->in($params['post_id']);
		}

		if (!empty($params['thread_id'])) {
			if (is_array($params['thread_id'])) {
				array_walk($params['thread_id'],create_function('&$v,$k','if($v!==null){$v=(integer)$v;}'));
			} else {
				$params['thread_id'] = array((integer) $params['thread_id']);
			}
			$strReq .= 'AND P.thread_id '.$this->con->in($params['thread_id']);
		}
		
		if (!empty($params['post_url'])) {
			$strReq .= "AND post_url = '".$this->con->escape($params['post_url'])."' ";
		}
		
		if (!empty($params['user_id'])) {
			$strReq .= "AND U.user_id = '".$this->con->escape($params['user_id'])."' ";
		}
		
		if (!empty($params['cat_id']))
		{
			if (!is_array($params['cat_id'])) {
				$params['cat_id'] = array($params['cat_id']);
			}
			if (!empty($params['cat_id_not'])) {
				array_walk($params['cat_id'],create_function('&$v,$k','$v=$v." ?not";'));
			}
			$strReq .= 'AND '.$this->getPostsCategoryFilter($params['cat_id'],'cat_id').' ';
		}
		elseif (!empty($params['cat_url']))
		{
			if (!is_array($params['cat_url'])) {
				$params['cat_url'] = array($params['cat_url']);
			}
			if (!empty($params['cat_url_not'])) {
				array_walk($params['cat_url'],create_function('&$v,$k','$v=$v." ?not";'));
			}
			$strReq .= 'AND '.$this->getPostsCategoryFilter($params['cat_url'],'cat_url').' ';
		}
		
		/* Other filters */
		if (isset($params['post_status'])) {
			$strReq .= 'AND post_status = '.(integer) $params['post_status'].' ';
		}
		
		if (isset($params['post_selected'])) {
			$strReq .= 'AND post_selected = '.(integer) $params['post_selected'].' ';
		}
		
		if (!empty($params['post_year'])) {
			$strReq .= 'AND '.$this->con->dateFormat('post_dt','%Y').' = '.
			"'".sprintf('%04d',$params['post_year'])."' ";
		}
		
		if (!empty($params['post_month'])) {
			$strReq .= 'AND '.$this->con->dateFormat('post_dt','%m').' = '.
			"'".sprintf('%02d',$params['post_month'])."' ";
		}
		
		if (!empty($params['post_day'])) {
			$strReq .= 'AND '.$this->con->dateFormat('post_dt','%d').' = '.
			"'".sprintf('%02d',$params['post_day'])."' ";
		}
		
		if (!empty($params['post_lang'])) {
			$strReq .= "AND P.post_lang = '".$this->con->escape($params['post_lang'])."' ";
		}
		
		if (!empty($params['search']))
		{
			$words = text::splitWords($params['search']);
			
			if (!empty($words))
			{
				# --BEHAVIOR-- corePostSearch
				if ($this->core->hasBehavior('corePostSearch')) {
					$this->core->callBehavior('corePostSearch',$this->core,array(&$words,&$strReq,&$params));
				}
				
				if ($words)
				{
					foreach ($words as $i => $w) {
						$words[$i] = "post_words LIKE '%".$this->con->escape($w)."%'";
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
				$strReq .= 'ORDER BY post_selected DESC, '.$this->con->escape($params['order']).' ';
			} else {
				$strReq .= 'ORDER BY post_selected DESC,  post_dt DESC ';
			}
		}
		
		if (!$count_only && !empty($params['limit'])) {
			$strReq .= $this->con->limit($params['limit']);
		}

		$rs = $this->con->select($strReq);
		$rs->core = $this->core;
		$rs->_nb_media = array();
		$rs->extend('rsExtPost');
		
		# --BEHAVIOR-- coreBlogGetPosts
		$this->core->callBehavior('coreBlogGetPosts',$rs);
		
		return $rs;
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
		$strReq = 'SELECT COUNT(post_id) '.
				'FROM '.$this->prefix.'post '.
				'WHERE thread_id = '.(integer) $id.' '.
				'AND post_status = 1 ';
		
		$rs = $this->con->select($strReq);
		
		$cur = $this->con->openCursor($this->prefix.'post');
		
		if ($rs->isEmpty()) {
			return;
		}
		
		$cur->nb_comment = (integer) $rs->f(0);
		$cur->post_upddt = date('Y-m-d H:i:s');
		
		$cur->update('WHERE post_id = '.(integer) $id);
	}

	public function getThreadURL(&$rs)
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
}
?>