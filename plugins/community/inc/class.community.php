<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of community, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class community
{
	public $err;
	public $msg;
	public $page;

	protected $standby;
	protected $moderated;
	protected $admin_email;

	public function __construct(&$core)
	{
		$this->core =& $core;

		$this->err = array();
		$this->msg = '';
		$this->page = '';

		$this->standby = unserialize($core->blog->settings->community_standby);
		$this->moderated = $core->blog->settings->community_moderated;
		$this->admin_email = $core->blog->settings->community_admin_email;
	}

	public function setPage($page)
	{
		$this->page = $page;
	}

	public function signUp()
	{
		$account['login'] = trim($_POST['su_login']);
		$account['clear_passwd'] = trim($_POST['su_passwd']);
		$account['passwd'] = crypt::hmac(DC_MASTER_KEY,$account['clear_passwd']);
		$account['name'] = trim($_POST['su_name']);
		$account['firstname'] = trim($_POST['su_firstname']);
		$account['displayname'] = trim($_POST['su_displayname']);
		$account['email'] = trim($_POST['su_email']);
		$account['url'] = trim($_POST['su_website']);
		$account['desc'] = trim($_POST['su_desc']);
		$account['lang'] = trim($_POST['su_lang']);
		$account['tz'] = trim($_POST['su_tz']);
		$account['moderated'] = $this->moderated;
		$account['creadt'] = time() + dt::getTimeOffset($account['tz']);
		$account['key'] = md5(trim($_POST['su_login']));
		
		if (empty($account['login'])) {
			$this->err[] = __('You have to fill the field login');
		}
		if (empty($_POST['su_passwd'])) {
			$this->err[] = __('You have to fill the field password');
		}
		if ($_POST['su_confirm_passwd'] != $_POST['su_passwd']) {
			$this->err[] = __('You have to enter same password in a comfirm field');
		}
		if (strlen($account['clear_passwd']) < 6) {
			$this->err[] = __('Password must contain at least 6 characters.');
		}
		if (!text::isEmail($_POST['su_email'])) {
			$this->err[] = __('You have to enter a valid email address.');
		}
		if (array_key_exists($account['login'],$this->standby) || $this->core->userExists($account['login'])) {
			$this->err[] = __('This login already exists');
		}

		if (count($this->err) == 0) {
			if ($this->moderated) {
				//$this->sendModerationEmail($account);
			}
			else {
				$this->sendActivationEmail($account);
			}
			$this->standby[$account['login']] = $account;
			$this->core->blog->settings->setNamespace('community');
			$this->core->blog->settings->put('community_standby',serialize($this->standby),'string');
		}
	}

	public function register($hash)
	{
		$key = '';
		foreach ($this->standby as $k => $v) {
			$key = ($hash == $v['key']) ? $k : $key;
		}

		if (empty($key)) {
			$this->err[] = __('Invalid confirmation id. Please verify your email.');
		}
		else {
			$user_options = $this->core->userDefaults();

			$cur = $this->core->con->openCursor($this->core->prefix.'user');

			$cur->user_id = $this->standby[$key]['login'];
			$cur->user_super = 0;
			$cur->user_pwd = $this->standby[$key]['passwd'];
			$cur->user_name = $this->standby[$key]['name'];
			$cur->user_firstname = $this->standby[$key]['firstname'];
			$cur->user_displayname = $this->standby[$key]['displayname'];
			$cur->user_email = $this->standby[$key]['email'];
			$cur->user_url = $this->standby[$key]['url'];
			$cur->user_desc = $this->standby[$key]['desc'];
			$cur->user_lang = $this->standby[$key]['lang'];
			$cur->user_tz = $this->standby[$key]['tz'];
			$cur->user_options = serialize($user_options);
			$cur->user_creadt = array($this->standby[$key]['creadt']);

			$cur->insert();

			$this->setCommunityPermission($cur->user_id);
			$this->core->auth->afterAddUser($cur);
			
			$this->sendInformationEmail($this->standby[$key]);

			$link = '<a href="'.$this->core->blog->getQmarkURL().'community/login">'.__('log in').'</a>';

			$this->msg = sprintf(__('You have successfully activated account for user %s ! You can now %s.'),'<q>'.$this->standby[$key]['login'].'</q>',$link);

			unset($this->standby[$key]);

			$this->core->blog->settings->setNamespace('community');
			$this->core->blog->settings->put('community_standby',serialize($this->standby),'string');
		}
	}

	public function logIn($login = '',$passwd = '',$key = '')
	{
		$login = empty($login) ? trim($_POST['li_login']) : $login;
		$passwd = empty($passwd) ? trim($_POST['li_passwd']) : $passwd;
		$key = empty($key) ? null : $key;

		if (!$this->core->auth->checkUser($login,$passwd,$key)) {
			$this->err[] = __('Invalid login or password. Please, try again.');
		}
		else{
			$this->core->session->start();
			$_SESSION['sess_user_id'] = $login;
			$_SESSION['sess_browser_uid'] = http::browserUID(DC_MASTER_KEY);
			$_SESSION['sess_blog_id'] = $this->core->blog->id;
			$_SESSION['sess_community'] = 1;
			if (isset($_POST['li_remember'])) {
				$cookie_community =
					http::browserUID(DC_MASTER_KEY.$login.crypt::hmac(DC_MASTER_KEY,$passwd)).
					bin2hex(pack('a32',$login));
				setcookie('dc_community_'.$this->core->blog->id,$cookie_community,strtotime('+15 days'));
			}
			$name = (string)dcUtils::getUserCN($this->core->auth->userID(),$this->core->auth->getInfo('user_name'),$this->core->auth->getInfo('user_firstname'),$this->core->auth->getInfo('user_displayname'));
			$mail = $this->core->auth->getInfo('user_email');
			$site = $this->core->auth->getInfo('user_url');
			setrawcookie('comment_info',rawurlencode($name."\n".$mail."\n".$site),strtotime('+30 days'));
			if (isset($_POST['li_login_go'])) {
				http::redirect($this->core->blog->url);
				exit;
			}
		}
	}

	public function logOut()
	{
		$this->core->session->destroy();
		if (isset($_COOKIE['dc_community_'.$this->core->blog->id])) {
			unset($_COOKIE['dc_community_'.$this->core->blog->id]);
			setcookie('dc_community_'.$this->core->blog->id,false,-600);
		}
		if (isset($_COOKIE['comment_info'])) {
			unset($_COOKIE['comment_info']);
			setcookie('comment_info','',-600);
		}
		http::redirect($this->core->blog->url);
		exit;
	}

	public function edit()
	{
		$account['login'] = trim($_POST['p_login']);
		$account['clear_passwd'] = trim($_POST['p_passwd']);
		$account['passwd'] = crypt::hmac(DC_MASTER_KEY,$account['clear_passwd']);
		$account['name'] = trim($_POST['p_name']);
		$account['firstname'] = trim($_POST['p_firstname']);
		$account['displayname'] = trim($_POST['p_displayname']);
		$account['email'] = trim($_POST['p_email']);
		$account['url'] = trim($_POST['p_website']);
		$account['desc'] = trim($_POST['p_desc']);
		$account['lang'] = trim($_POST['p_lang']);
		$account['tz'] = trim($_POST['p_tz']);
		
		if (empty($account['login'])) {
			$this->err[] = __('You have to fill the field login');
		}
		if ((empty($_POST['p_passwd']) && !empty($_POST['p_confirm_passwd'])) || (empty($_POST['p_confirm_passwd']) && !empty($_POST['p_passwd']))) {
			if ($_POST['p_confirm_passwd'] != $_POST['p_passwd']) {
				$this->err[] = __('You have to enter same password in a comfirm field');
			}
			if (strlen($account['clear_passwd']) < 6) {
				$this->err[] = __('Password must contain at least 6 characters.');
			}
		}
		if (!empty($_POST['p_passwd']) && !empty($_POST['p_confirm_passwd'])) {
			if ($_POST['p_confirm_passwd'] != $_POST['p_passwd']) {
				$this->err[] = __('You have to enter same password in a comfirm field');
			}
		}
		if (!text::isEmail($_POST['p_email'])) {
			$this->err[] = __('You have to enter a valid email address.');
		}
		if ($account['login'] != $this->core->auth->userID()) {
			$this->err[] = __('You have to put the good login');
		}

		if (count($this->err) == 0) {
			$user_options = $this->core->userDefaults();

			$cur = $this->core->con->openCursor($this->core->prefix.'user');

			$cur->user_id = $account['login'];
			$cur->user_super = $this->core->auth->isSuperAdmin() ? 1 : 0;
			$cur->user_name = $account['name'];
			$cur->user_firstname = $account['firstname'];
			$cur->user_displayname = $account['displayname'];
			$cur->user_email = $account['email'];
			$cur->user_url = $account['url'];
			$cur->user_desc = $account['desc'];
			$cur->user_lang = $account['lang'];
			$cur->user_tz = $account['tz'];
			$cur->user_options = serialize($user_options);
			$cur->user_upddt = array('NOW()');
			if (!empty($_POST['p_passwd']) && !empty($_POST['p_confirm_passwd'])) {
				$cur->user_pwd = $account['passwd'];
			}

			$cur->update("WHERE user_id = '".$this->core->con->escape($account['login'])."' ");

			$this->msg = __('You have successfully edited your account.');
		}
	}

	public function getStandbyUsers()
	{
		return $this->standby;
	}
	
	public function delete($id)
	{
		if (array_key_exists($id,$this->standby)) {
			unset($this->standby[$id]);
		}

		$this->core->blog->settings->setNamespace('community');
		$this->core->blog->settings->put('community_standby',serialize($this->standby),'string');
	}

	protected function sendActivationEmail($account)
	{
		$sub = __('Account confirmation request on community');
		$msg = 
			sprintf(__('Welcome to the community of %s'),$this->core->blog->name)."\n".
			__('To activate your account and verify your e-mail address, please click on the following link:').
			"\n\n".
			$this->core->blog->getQmarkURL().'community/signup/'.$account['key'].
			"\n\n".
			__('If you have received this mail in error, you do not need to take any action to cancel the account.').
			__('The account will not be activated, and you will not receive any further emails.').
			__('If clicking the link above does not work, copy and paste the URL in a new browser window instead.').
			"\n\n".
			__('Thank you for particape to our community.').
			"\n\n".
			__('This is a post-only mailing. Replies to this message are not monitored or answered.').
			"\n\n";	
		$this->msg = sprintf(__('User %s successfully created! You will receive an email to activate your account.'),'<q>'.$account['login'].'</q>');

		$this->sendEmail($account['email'],$sub,$msg);
	}
	
	protected function sendModerationEmail($account)
	{
		$sub = __('New community request');
		$msg = 
			__("Hi there!\n\nYou received a new request for the community.").
			"\n\n".
			sprintf(__('Blog: %s'),$this->core->blog->name)."\n".
			sprintf(__('Login: %s'),$account['login'])."\n".
			sprintf(__('Email: %s'),$account['email']).
			"\n\n".
			sprintf(__('To accept or reject this request, please go to this page : %s'),DC_ADMIN_URL.'user.php').
			"\n\n";	
		$this->msg = __('Your request have been saved. You will receive an email when an administrator will accept it.');

		$this->sendEmail($this->admin_email,$sub,$msg);
	}
	
	protected function sendInformationEmail($account)
	{
		$sub = __('Account information about community');
		$msg = 
			sprintf(__('Hello %s'),$account['login']).
			"\n\n".
			sprintf(__('Here are some informations about your community account on %s'),$this->core->blog->name)."\n".
			"--------------------------------------------------------------------------\n".
			sprintf(__('Login: %s'),$account['login'])."\n".
			sprintf(__('Password: %s'),$account['clear_passwd'])."\n".
			"\n\n".
			sprintf(__('You can login right now at : %s'),$this->core->blog->getQmarkURL().'community/login/').
			"\n\n";

		$this->sendEmail($account['email'],$sub,$msg);
	}
	
	protected function sendEmail($dest,$sub,$msg)
	{
		$headers = array(
			'From: '.mail::B64Header($this->core->blog->name).' community <no-reply@'.str_replace('http://','',http::getHost()).' >',
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

	protected function setCommunityPermission($id)
	{
		$perms['community_default'] = true;

		if ($this->core->auth->isSuperAdmin()) {
			$this->core->auth->setUserPermissions($id,$perms);
		}
		else {
			$strReq = 'DELETE FROM '.$this->core->prefix.'permissions '.
			"WHERE user_id = '".$this->core->con->escape($id)."' ";

			$this->core->con->execute($strReq);

			$perms = '|'.implode('|',array_keys($perms)).'|';

			$cur = $this->core->con->openCursor($this->core->prefix.'permissions');

			$cur->user_id = (string) $id;
			$cur->blog_id = (string) $this->core->blog->id;
			$cur->permissions = $perms;

			$cur->insert();
		}
	}
}

?>