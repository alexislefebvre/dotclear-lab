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

$core->url->register('community','community','^community/(.*)$',array('communityUrl','checkUrl'));

$core->addBehavior('publicBeforeDocument',array('communityBehaviors','addTplPath'));
$core->addBehavior('publicBeforeDocument',array('communityBehaviors','autoLogIn'));
$core->addBehavior('publicBeforeDocument',array('communityBehaviors','cleanSession'));

$core->tpl->addBlock('CommunityIf', array('communityTpl','communityIf'));

$core->tpl->addValue('CommunityMessage', array('communityTpl','communityMessage'));
$core->tpl->addValue('CommunityError', array('communityTpl','communityError'));
$core->tpl->addValue('CommunitySignUpURL', array('communityTpl','communitySignUpURL'));
$core->tpl->addValue('CommunityLogInURL', array('communityTpl','communityLogInURL'));
$core->tpl->addValue('CommunityProfileURL', array('communityTpl','communityProfileURL'));
$core->tpl->addValue('CommunityLogin', array('communityTpl','communityLogin'));
$core->tpl->addValue('CommunityName', array('communityTpl','communityName'));
$core->tpl->addValue('CommunityFirstName', array('communityTpl','communityFirstName'));
$core->tpl->addValue('CommunityDisplayName', array('communityTpl','communityDisplayName'));
$core->tpl->addValue('CommunityEmail', array('communityTpl','communityEmail'));
$core->tpl->addValue('CommunityWebsite', array('communityTpl','communityWebsite'));
$core->tpl->addValue('CommunityDesc', array('communityTpl','communityDesc'));
$core->tpl->addValue('CommunityLanguages', array('communityTpl','communityLanguages'));
$core->tpl->addValue('CommunityTimeZone', array('communityTpl','communityTimeZone'));

class communityBehaviors
{
	public static function addTplPath()
	{
		global $core;
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
	}
	
	public static function autoLogIn()
	{
		global $core, $_ctx;

		$core->session = new sessionDB(
			$core->con,
			$core->prefix.'session',
			'dc_community_sess_'.$core->blog->id,
			''
		);

		if (isset($_COOKIE['dc_community_sess_'.$core->blog->id])) {
			# If we have a session we launch it now
			if (!$core->auth->checkSession()) {
				# Avoid loop caused by old cookie
				$p = $core->session->getCookieParameters(false,-600);
				$p[3] = '/';
				call_user_func_array('setcookie',$p);
			}
		}

		if (!isset($_SESSION['sess_user_id'])) {
			if (isset($_COOKIE['dc_community_'.$core->blog->id]) && strlen($_COOKIE['dc_community_'.$core->blog->id]) == 104) {
				# If we have a remember cookie, go through auth process with key
				$login = substr($_COOKIE['dc_community_'.$core->blog->id],40);
				$login = @unpack('a32',@pack('H*',$login));
				if (is_array($login)) {
					$login = $login[1];
					$key = substr($_COOKIE['dc_community_'.$core->blog->id],0,40);
					$passwd = null;
				}
				else {
					$login = null;
				}
				$_ctx->community = new community($core,$_ctx);
				$_ctx->community->logIn($login,$passwd,$key);
				$_ctx->community = null;
			}
		}
	}

	public static function cleanSession()
	{
		global $core;

		$strReq = 'DELETE FROM '.$core->prefix.'session '.
				"WHERE ses_time < ".(time() - 3600*24*14);

		$core->con->execute($strReq);
	}
}

class communityUrl extends dcUrlHandlers
{
	public static function checkUrl($args)
	{
		global $core,$_ctx;

		if (!$core->blog->settings->community_activated) {
			self::p404();
			exit;
		}

		$_ctx->community = new community($core,$_ctx);
		$page = split('/',$args);
		if (count($page) >= 1) {
			$_ctx->community->setPage($page[0]);
			if (isset($_POST['su_signup_go'])) {
				$_ctx->community->signUp();
			}
			if ($page[0] == 'signup' && count($page) == 2) {
				$_ctx->community->register($page[1]);
			}
			if (isset($_POST['li_login_go'])) {
				$_ctx->community->logIn();
			}
			if (isset($_POST['p_edit_go']) && isset($_SESSION['sess_user_id'])) {
				$_ctx->community->edit();
			}
			if ($page[0] == 'logout' && isset($_SESSION['sess_user_id'])) {
				$_ctx->community->logOut();
			}
			self::serveDocument($page[0].'.html');
			exit;
		}
	}
}

class communityTpl
{
	public static function communityIf($attr,$content)
	{
		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';

		if (isset($attr['message'])) {
			$sign = (boolean) $attr['message'] ? '!' : '';
			$if[] = $sign.'empty($_ctx->community->msg)';
		}

		if (isset($attr['error'])) {
			$sign = (boolean) $attr['error'] ? '>' : '==';
			$if[] = 'count($_ctx->community->err) '.$sign.' 0';
		}

		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}

	public static function communityMessage($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->community->msg').'; ?>';
	}

	public static function communityError($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php foreach ($_ctx->community->err as $k => $v) { echo '.sprintf($f,'"<li>".$v."</li>"').'; } ?>';
	}

	public static function communitySignUpURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->getQmarkURL()."community/signup"').'; ?>';
	}

	public static function communityLogInURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->getQmarkURL()."community/login"').'; ?>';
	}

	public static function communityProfileURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->getQmarkURL()."community/profile"').'; ?>';
	}

	public static function communityLogin($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		$res = "<?php\n";
		$res .= '$v = isset($_SESSION["sess_user_id"]) ? $core->auth->userID() : "";'."\n";
		$res .= '$v = isset($_POST["su_login"]) ? $_POST["su_login"] : (isset($_POST["p_login"]) ? $_POST["p_login"] : $v); '."\n";
		$res .= 'echo '.sprintf($f,'$v').';'."\n";
		$res .= "?>";

		return $res;
	}

	public static function communityName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		$res = "<?php\n";
		$res .= '$v = isset($_SESSION["sess_user_id"]) ? $core->auth->getInfo("user_name") : "";'."\n";
		$res .= '$v = isset($_POST["su_name"]) ? $_POST["su_name"] : (isset($_POST["p_name"]) ? $_POST["p_name"] : $v); '."\n";
		$res .= 'echo '.sprintf($f,'$v').';'."\n";
		$res .= "?>";

		return $res;
	}

	public static function communityFirstName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		$res = "<?php\n";
		$res .= '$v = isset($_SESSION["sess_user_id"]) ? $core->auth->getInfo("user_firstname") : "";'."\n";
		$res .= '$v = isset($_POST["su_firstname"]) ? $_POST["su_firstname"] : (isset($_POST["p_firstname"]) ? $_POST["p_firstname"] : $v); '."\n";
		$res .= 'echo '.sprintf($f,'$v').';'."\n";
		$res .= "?>";

		return $res;
	}

	public static function communityDisplayName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		$res = "<?php\n";
		$res .= '$v = isset($_SESSION["sess_user_id"]) ?  $core->auth->getInfo("user_displayname") : "";'."\n";
		$res .= '$v = isset($_POST["su_displayname"]) ? $_POST["su_displayname"] : (isset($_POST["p_displayname"]) ? $_POST["p_displayname"] : $v); '."\n";
		$res .= 'echo '.sprintf($f,'$v').';'."\n";
		$res .= "?>";

		return $res;
	}

	public static function communityEmail($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		$res = "<?php\n";
		$res .= '$v = isset($_SESSION["sess_user_id"]) ?  $core->auth->getInfo("user_email") : "";'."\n";
		$res .= '$v = isset($_POST["su_email"]) ? $_POST["su_email"] : (isset($_POST["p_email"]) ? $_POST["p_email"] : $v); '."\n";
		$res .= 'echo '.sprintf($f,'$v').';'."\n";
		$res .= "?>";

		return $res;
	}

	public static function communityWebsite($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		$res = "<?php\n";
		$res .= '$v = isset($_SESSION["sess_user_id"]) ?  $core->auth->getInfo("user_url") : "";'."\n";
		$res .= '$v = isset($_POST["su_website"]) ? $_POST["su_website"] : (isset($_POST["p_website"]) ? $_POST["p_website"] : $v); '."\n";
		$res .= 'echo '.sprintf($f,'$v').';'."\n";
		$res .= "?>";

		return $res;
	}

	public static function communityDesc($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		$res = "<?php\n";
		$res .= "\$strReq = 'SELECT user_desc '.";
		$res .= "'FROM '.\$core->con->escapeSystem(\$core->blog->prefix.'user').' '.";
		$res .= "'WHERE user_id = \''.\$core->con->escape(\$core->auth->userID()).'\' ';\n";
		$res .= "\$_rs = \$core->con->select(\$strReq);\n";
		$res .= "\$v = !\$_rs->isEmpty() ? \$_rs->user_desc : '';\n";
		$res .= '$v = isset($_POST["su_desc"]) ? $_POST["su_desc"] : (isset($_POST["p_desc"]) ? $_POST["p_desc"] : $v); '."\n";
		$res .= 'echo '.sprintf($f,'$v').';'."\n";
		$res .= "?>";

		return $res;
	}

	public static function communityLanguages($attr)
	{
		$res = "<?php\n";
		$res .= '$langs = l10n::getISOcodes(1,1);'."\n";
		$res .= 'foreach ($langs as $k => $v) {'."\n";
		$res .= '$lang_avail = $v == "en" || is_dir(DC_L10N_ROOT."/".$v);'."\n";
		$res .= '$lang_combo[] = new formSelectOption($k,$v,$lang_avail ? "avail10n" : "");'."\n";
		$res .= "}\n";
		$res .= '$post = isset($_SESSION["sess_user_id"]) ?  $core->auth->getInfo("user_lang") : $core->blog->settings->lang;'."\n";
		$res .= '$post = isset($_POST["su_lang"]) ? $_POST["su_lang"] : (isset($_POST["p_lang"]) ? $_POST["p_lang"] : $post); '."\n";
		$res .= '$name = $_ctx->community->page == "profile" ? "p_lang" : "su_lang";'."\n";
		$res .= 'echo form::combo($name,$lang_combo,$post,"l10n",12);'."\n";
		$res .= "?>\n";

		return $res;
	}

	public static function communityTimeZone($attr)
	{
		$res = "<?php\n";
		$res .= '$v = isset($_SESSION["sess_user_id"]) ?  $core->auth->getInfo("user_tz") : $core->blog->settings->blog_timezone;'."\n";
		$res .= '$v = isset($_POST["su_tz"]) ? $_POST["su_tz"] : (isset($_POST["p_tz"]) ? $_POST["p_tz"] : $v); '."\n";
		$res .= '$name = $_ctx->community->page == "profile" ? "p_tz" : "su_tz";'."\n";
		$res .= 'echo form::combo($name,dt::getZones(true,true),$v,"",13);'."\n";
		$res .= "?>\n";

		return $res;
		
	}
}

class communityPublic
{
	public static function countSessions()
	{
		$rs = $GLOBALS['core']->con->select('SELECT * FROM '.$GLOBALS['core']->prefix.'session WHERE ses_value != \'\'');
		
		$count = 0;
		
		while ($rs->fetch()) {
			if (preg_match('#.*sess_community.*#',$rs->f('ses_value')) > 0) {
				$count++;
			}
		}
		
		return $count;
	}

	public static function widget(&$w)
	{
		global $core,$_ctx;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		$base_url = $core->blog->getQmarkURL().'community/%s';

		$title = strlen($w->title) > 0 ? '<h2>'.$w->title.'</h2>' : '';

		$content =
			!isset($_SESSION['sess_user_id']) ?
			'<p><a href="'.sprintf($base_url,'login').'">'.__('Log in').'</a></p>'.
			'<p>'.__('No account?').' <a href="'.sprintf($base_url,'signup').'">'.__('Sign up now').'</a></p>' : 
			'<p>'.sprintf(__('Welcome %s'),$_SESSION['sess_user_id']).'</p>'.
			'<p>'.sprintf(__('There are %s connected users right now'),'<strong>'.communityPublic::countSessions().'</strong>').'</p>'.
			'<ul>'.
			'<li><a href="'.sprintf($base_url,'profile').'">'.__('Profil').'</a></li>'.
			'<li><a href="'.sprintf($base_url,'logout').'">'.__('Logout').'</a></li>'.
			'</ul>';

		$block = sprintf('<div id="community">%s</div>',$content);

		return $title.$block;
	}
}

?>