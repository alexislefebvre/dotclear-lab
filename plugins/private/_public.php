<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Private mode, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('PrivateReqPage',array('tplPrivate','PrivateReqPage'));
$core->tpl->addValue('PrivateMsg',array('tplPrivate','PrivateMsg'));

$s = $core->blog->settings->private;
		
if ($s->private_flag) {
	$core->addBehavior('publicBeforeDocument',array('urlPrivate','privateHandler'));
}

if ($s->private_conauto_flag) {
	$core->addBehavior('publicPrivateFormAfterContent',array('behaviorsPrivate','publicPrivateFormAfterContent'));
}

$core->addBehavior('publicPrivateFormBeforeContent',array('behaviorsPrivate','publicPrivateFormBeforeContent'));

/**
*
*/
class urlPrivate extends dcUrlHandlers
{
	/**
	*
	*/
	public static function feedXslt($args)
	{
		self::serveDocument('rss2.xsl','text/xml');
	}	
	
	/**
	*
	*/
	public static function publicFeed($args)
	{
		#Don't reinvent the wheel - take a look to dcUrlHandlers/feed
		global $core,$_ctx;

		$type = null;
		$params = array();
		$mime = 'application/xml';
		
		if (preg_match('#^(atom|rss2)$#',$args,$m)) {
			# Atom or RSS2 ?
			$type = $m[0];
		}
		
		$tpl =  $type == '' ? 'atom' : $type;
		$tpl .= '-pv.xml';
		
		if ($type == 'atom') {
			$mime = 'application/atom+xml';
		}
		
		header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->system->robots_policy,''));
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument($tpl,$mime);
		
		return;
	}

	/**
	*
	*/
	public static function callbackfoo($args)
	{
		#Woohoo :)
		return;
	}

	/**
	*
	*/
	public static function privateHandler($args)
	{
		global $core,$_ctx;

		#New temporary urlHandlers 
		$urlp = new urlHandler();
		$urlp->mode = $core->url->mode;
		$urlp->registerDefault(array('urlPrivate','callbackfoo'));
		foreach ($core->url->getTypes() as $k => $v) {
			$urlp->register($k,$v['url'],$v['representation'],array('urlPrivate','callbackfoo'));
		}
		
		#Find type
		$urlp->getDocument();
		$type = $urlp->type;
		unset($urlp);
		
		#Looking for a new template (private.html)
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		
		#Load password from configuration
		$password = $core->blog->settings->private->blog_private_pwd;
		
		#Define allowed url->type 
		$allowed_types = new ArrayObject(array('feed','xslt','tag_feed','pubfeed','spamfeed','hamfeed','trackback','preview','pagespreview','contactme'));
		$core->callBehavior('initPrivateMode',$allowed_types);

		#Generic behavior
		$core->callBehavior('initPrivateHandler',$core);
		
		#Let's go : define a new session and start it
		if (!isset($session)) {
			$session = new sessionDB(
				$core->con,
				$core->prefix.'session',
				'dc_privateblog_sess_'.$core->blog->id,
				'/'
			);
			$session->start();
		}

		if (in_array($type,(array)$allowed_types)) {
			return;
		} else {
			#Add cookie test (automatic login)
			$cookiepass = "dc_privateblog_cookie_".$core->blog->id;
			
			if (!empty($_COOKIE[$cookiepass])) {
				$cookiepassvalue = (($_COOKIE[$cookiepass]) == $password);
			} 
			else {
				$cookiepassvalue = false;
			}
			
			#Let's rumble session, cookies & conf :)
			if (!isset($_SESSION['sess_blog_private']) || $_SESSION['sess_blog_private'] == "") {
				if ($cookiepassvalue != false) {
					$_SESSION['sess_blog_private'] = $_COOKIE[$cookiepass];
				}
				if (!empty($_POST['private_pass'])) {
					if (md5($_POST['private_pass']) == $password) {
						$_SESSION['sess_blog_private'] = md5($_POST['private_pass']);
						
						if (!empty($_POST['pass_remember'])) {
							setcookie($cookiepass,md5($_POST['private_pass']),time() + 31536000,'/');
						}
						return;
					}
					$_ctx->form_error = __('Wrong password');
				}
				$session->destroy();
				self::serveDocument('private.html','text/html',false);
				# --BEHAVIOR-- publicAfterDocument
				$core->callBehavior('publicAfterDocument',$core);
				exit;
			}
			elseif ($_SESSION['sess_blog_private'] != $password) {
				$session->destroy();
				self::serveDocument('private.html','text/html',false);
				# --BEHAVIOR-- publicAfterDocument
				$core->callBehavior('publicAfterDocument',$core);
				exit;
			}
			elseif (isset($_POST['blogout'])) {
				$session->destroy();
				setcookie($cookiepass,'ciao',time() - 86400,'/');
				$_ctx->form_error = __('You are now disconnected.');
				self::serveDocument('private.html','text/html',false);
				# --BEHAVIOR-- publicAfterDocument
				$core->callBehavior('publicAfterDocument',$core);
				exit;
			}
			return;
		}
	}
}

/**
*
*/
class behaviorsPrivate
{
	/**
	*
	*/
	public static function publicPrivateFormBeforeContent($core)
	{
		echo $core->blog->settings->private->message;
	}
	
	/**
	*
	*/
	public static function publicPrivateFormAfterContent($core)
	{
		echo '<p><label class="classic">'.
			form::checkbox(array('pass_remember'),1).' '.
			__('Enable automatic connection').'</label></p>';
	}
}

/**
*
*/
class tplPrivate
{
	/**
	*
	*/
	public static function PrivateMsg($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$GLOBALS[\'core\']->blog->settings->private->message').'; ?>';
	}

	/**
	*
	*/
	public static function PrivateReqPage($attr)
	{
		return  '<?php echo(isset($_SERVER[\'REQUEST_URI\']) 
			? html::escapeHTML($_SERVER[\'REQUEST_URI\'])
			: $core->blog->url); ?>' ;
	}
}

/**
*
*/
class widgetsPrivage
{
	/**
	*
	*/
	public static function widgetLogout($w) 
	{
		if ($GLOBALS['core']->blog->settings->private->private_flag)
		{
			if ($w->homeonly && $core->url->type != 'default') {
				return;
			}

	 		$res = '<div class="blogout">'.
				($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
				($w->text ? $w->text : '').
				'<form action="'.$GLOBALS['core']->blog->url.'" method="post">'.
				'<p class="buttons">'.
				'<input type="hidden" name="blogout" id="blogout" value="" />'.
				'<input type="submit" value="'.html::escapeHTML($w->label).'" class="logout" /></p>'.
				'</form></div>';
			return $res;
		}
	}
}
?>