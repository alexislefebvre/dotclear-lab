<?php

class ofcTwitterLogin extends optionsForComment
{
	public static function optionsForCommentAdminPrepend($core,$action)
	{
		if (!in_array($action,array('savesettings','registertwitterlogin'))) {
			return;
		}
		
		$has_registry = false;
		$has_tac = $core->plugins->moduleExists('TaC');

		if ($has_tac) {
			try {
				$tac = new tac($core,'optionsForComment',null);
				$has_registry = $tac->checkRegistry();
				
				if (!$has_registry && $action == 'registertwitterlogin') {
					$cur = $core->con->openCursor($core->prefix.'tac_registry');
					$cur->cr_id = 'optionsForComment';
					$cur->cr_key = 'tqzapL37XIJd5G7TMdtqHQ';
					$cur->cr_secret = 'ZVeUg1La2ZwxjJpQyp1L6tL1wqyFfJ3MYBpbaf1eEk';
					$cur->cr_url_request = 'http://twitter.com/oauth/request_token';
					$cur->cr_url_access = 'http://twitter.com/oauth/access_token';
					$cur->cr_url_autorize = 'http://twitter.com/oauth/authorize';
					$cur->cr_url_authenticate = 'https://api.twitter.com/oauth/authenticate';
					
					$tac->addRegistry($cur);
					
					if (!($tac->checkRegistry())) {
						throw new Exception(__('Failed to register plugin'));
					}
				}
				
				if ($has_registry && $action == 'savesettings') {
					$core->blog->settings->optionsForComment->put('twitterlogin',isset($_POST['twitterlogin']));
				}
			}
			catch(Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
	}
	
	public static function optionsForCommentAdminHeader($core)
	{
		echo '
		<style type="text/css">
		p.success { background: transparent url(images/check-on.png) no-repeat left center; padding-left: 18px; }
		p.failed { background: transparent url(images/check-off.png) no-repeat left center; padding-left: 18px; }
		</style>';
	}
	
	public static function optionsForCommentAdminForm($core)
	{
		$has_registry = false;
		$has_tac = $core->plugins->moduleExists('TaC');
		if ($has_tac) {
			try {
				$tac = new tac($core,'optionsForComment',null);
				$has_registry = $tac->checkRegistry();
			}
			catch(Exception $e) { }
			
			$p = (integer) $core->blog->settings->optionsForComment->twitterlogin;
		}
		
		echo '<fieldset><legend>'.__('Twitter login').'</legend>';
		
		if (!$has_tac) {
			echo '<p class="failed">'.__('To use this option you must install plugin called "TaC".').'</p>';
		}
		elseif (!$has_registry) {
			echo 
			'<p class="failed">'.__('To use this option you must register it into the plugin "TaC".').'</p>'.
			'<p><a><a href="plugin.php?p=optionsForComment&amp;action=registertwitterlogin">'.__('Register extension').'</a></p>';
		}
		else {
			echo '
			<p class="success">'.__('Extension registered into the plugin "TaC".').'</p>
			<p><label class="classic">'.
			form::checkbox(array('twitterlogin'),'1',$p).
			__('Enable Twitter login on comments').'</label></p>';
		}
		echo '</fieldset>';
	}
	
	
	public static function optionsForCommentPublicPrepend($core,$rs)
	{
		if (!$core->blog->settings->optionsForComment->twitterlogin 
		 || !$core->plugins->moduleExists('TaC') 
		 || $rs['c_content'] === null 
		 || $rs['preview']) {
			return;
		}
		
		global $_ctx;
		
		if (!$_ctx->exists('ofcTwitterLogin_has_registry')) {
			libOfcTwitterLogin::loadContext($core,$_ctx);
		}
		
		if (!$_ctx->ofcTwitterLogin_has_access) {
			return;
		}
		
		$rs['c_name'] = $_ctx->ofcTwitterLogin_user->screen_name;
		$rs['c_mail'] = 'TwitterLogin@optionsForComment';
		$rs['c_site'] = 'http://twitter.com/'.$_ctx->ofcTwitterLogin_user->screen_name;;
	}
	
	public static function optionsForCommentPublicCreate($cur,$preview)
	{
		if ($GLOBALS['core']->blog->settings->optionsForComment->twitterlogin 
		 && $GLOBALS['core']->plugins->moduleExists('TaC') 
		 && $cur->comment_email == 'TwitterLogin@optionsForComment')
		{
			# set tpl fields
			$preview['name'] = '';
			$preview['mail'] = '';
			$preview['site'] = '';
			
			# set db fields
			$cur->comment_author= $_POST['c_name'];
			$cur->comment_email = $_POST['c_name'].'@twitter';
			$cur->comment_site = $_POST['c_site'];
		}
	}
	
	public static function optionsForCommentPublicHead($core,$_ctx,$js_vars)
	{
		if (!$core->blog->settings->optionsForComment->twitterlogin 
		 || !$core->plugins->moduleExists('TaC')) {
			return;
		}
		
		if (!$_ctx->exists('ofcTwitterLogin_has_registry')) {
			libOfcTwitterLogin::loadContext($core,$_ctx);
		}
		
		$js_vars["ofcTwitterLogin_has_access"] = (integer) $_ctx->ofcTwitterLogin_has_access;
		
		echo self::jsLoad($core->blog->getQmarkURL().'pf=optionsForComment/js/ofc.twitterlogin.js');
	}
	
	public static function optionsForCommentPublicForm($core,$_ctx)
	{
		if (!$core->blog->settings->optionsForComment->twitterlogin
		 || !$core->plugins->moduleExists('TaC')) { 
			return;
		}
		
		if (!$_ctx->exists('ofcTwitterLogin_has_registry')) {
			libOfcTwitterLogin::loadContext($core,$_ctx);
		}
		
		if (!$_ctx->ofcTwitterLogin_has_registry) {
			return;
		}
		
		$redir = urlencode($_ctx->posts->getURL());
		
		if (!$_ctx->exists('ofcTwitterLogin_user')) {
			echo 
			'<p class="ofc-twitterlogin">'.
			'<a href="'.$core->blog->getQmarkURL().$core->url->getBase('ofcTwitterLogin').'&do=login&redir='.$redir.'" title="Sign in with Twitter">'.
			'<img name="t_sign" src="'.$core->blog->getQmarkURL().'pf=TaC/img/tac_light.png" alt="Sign in with Twitter" />'.
			'</a></p>';
		}
		else {
			echo 
			'<p class="field"><label>'.
			'<img class="ofc-twitterimage" src="'.$_ctx->ofcTwitterLogin_user->profile_image_url.'" alt="Twitter avatar" />'.
			'</label>'.sprintf(__('Logged in as %s'),$_ctx->ofcTwitterLogin_user->screen_name).'<br />'.
			'<a class="ofc-twitterlogout" href="'.$core->blog->getQmarkURL().$core->url->getBase('ofcTwitterLogin').'&do=logout&redir='.$redir.'">'.
			__('Disconnect').'</a></p>';
		}
	}
}

class libOfcTwitterLogin
{
	# Commons for public side
	public static function loadContext($core,$_ctx)
	{
		if ('' == session_id()) { session_start(); }
		
		# Get a user id
		$user = self::getUser($core->blog->id);
		
		# Set a user id
		self::setUser($core->blog->id,$user);
		
		# Init vars
		$has_error = $has_registry = $has_access = $has_grant = false;
		
		try {
			# Launch TaC
			$_ctx->ofcTwitterLogin = new tac($core,'optionsForComment',$user);
			$has_registry = $_ctx->ofcTwitterLogin->checkRegistry();
			
			# Check if plugin is register to TaC
			if ($has_registry) {
				# Check if user has previous access
				$has_access = $_ctx->ofcTwitterLogin->checkAccess();
			}
			
			if ($has_access) {
				$user_info = $_ctx->ofcTwitterLogin->get('account/verify_credentials');
				if (!$user_info) {
					//todo: clean user info
				}
				else {
					$_ctx->ofcTwitterLogin_user = $user_info;
				}
			}
		}
		catch(Exception $e) {
			$has_registry = $has_access = $has_grant = false;
			$has_error = true;
			$core->error->add($e->getMessage());
		}
		
		# Put plugin info into context
		$_ctx->ofcTwitterLogin_has_error = $has_error;
		$_ctx->ofcTwitterLogin_has_registry = $has_registry;
		$_ctx->ofcTwitterLogin_has_access = $has_access;
		
		return $has_error; //return true if there's an error
	}
	
	public static function getUser($b)
	{
		$k = 'dc_ofctwitterlogin_'.$b;
		$v = '';
		
		if (!empty($_SESSION[$k])) {
			$v = $_SESSION[$k];
		}
		elseif (!empty($_COOKIE[$k])) {
			$v = $_COOKIE[$k];
		}
		if (strlen($v) != 32) {
			$v = md5(uniqid());
		}
		return $v;
	}
	
	public static function setUser($b,$v)
	{
		$k = 'dc_ofctwitterlogin_'.$b;
		
		$_SESSION[$k] = $v;
		setcookie($k,$v,time() + 2592000,'/');
	}
	
	public static function delUser($b)
	{
		$k = 'dc_ofctwitterlogin_'.$b;
		
		$_SESSION[$k] = '';
		setcookie($k,'',time() -3600,'/');
	}
	
	# Special plugin noodles
	public static function noodlesNoodleImageInfo($core,$rs)
	{
		if (preg_match('#^(.+)@twitter$#',$rs['mail'],$m)) {
		
			$size = (integer) $rs['size'];
			if ($size < 48) {
				$s = 'm';
			}
			elseif ($size > 48) {
				$s = 'b';
			}
			else {
				$s = 'n';
			}
			// use a third part service for twitter avatar...
			$rs['url'] = 'http://img.tweetimag.es/i/'.$m[1].'_'.$s;
		}
	}
}

class urlOfcTwitterLogin extends dcUrlHandlers
{
	public static function login($args)
	{
		global $core,$_ctx;
		$cookie_name = 'dc_ofctwitterlogin_'.$core->blog->id;
		
		# Check settings
		if (!$core->blog->settings->optionsForComment->active) {
			throw new Exception ("Not found",404);
		}
		
		# Check url
		if (empty($_GET['redir']) 
		 || empty($_GET['do'])
		 || !in_array($_GET['do'],array('login','logout','grant'))) {
			throw new Exception ("Method Not Allowed",405);
		}
		
		# Load plugin context
		if (true === libOfcTwitterLogin::loadContext($core,$_ctx)) {
			throw new Exception ("Internal Server Error",500); //!
		}
		
		# Check plugin
		if (!$_ctx->ofcTwitterLogin_has_registry) {
			throw new Exception ("Not Implemented",501); //!
		}
		
		# Clean url
		$redir = urldecode($_GET['redir']);
		
		# Login (request access)
		if ($_GET['do'] == 'login') {
			try {
				$url = $_ctx->ofcTwitterLogin->requestAccess(
					$core->blog->getQmarkURL().
					$core->url->getbase('ofcTwitterLogin').
					'&do=grant&redir='.$_GET['redir']
				);
				http::redirect($url);
			}
			catch(Exception $e) {
				throw new Exception ('Unauthorized ('.$e->getMessage().')',401);
			}
		}
		# Grant access (redirect from twitter)
		elseif ($_GET['do'] == 'grant') {
			try {
				if (!($_ctx->ofcTwitterLogin->grantAccess())) {
					$_ctx->ofcTwitterLogin->cleanAccess();
					libOfcTwitterLogin::delUser($core->blog->id);
				}
				http::redirect($redir);
			}
			catch(Exception $e) {
				throw new Exception ('Unauthorized ('.$e->getMessage().')',401);
			}
		}
		# Logout
		elseif ($_GET['do'] == 'logout') {
			try {
				$_ctx->ofcTwitterLogin->cleanAccess();
				libOfcTwitterLogin::delUser($core->blog->id);
				
				http::redirect($redir);
			}
			catch(Exception $e) {
				throw new Exception ("Internal Server Error",500);
			}
		}
	}
}
?>