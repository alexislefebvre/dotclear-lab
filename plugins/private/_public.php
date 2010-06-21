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

$core->tpl->addValue('PrivatePageTitle',array('tplPrivate','PrivatePageTitle'));
$core->tpl->addValue('PrivateMsg',array('tplPrivate','PrivateMsg'));
$core->tpl->addValue('PrivateReqPage',array('tplPrivate','PrivateReqPage'));
$core->tpl->addBlock('IfPrivateMsgError',array('tplPrivate','IfPrivateMsgError'));
$core->tpl->addValue('PrivatePassRemember',array('tplPrivate','PrivatePassRemember'));
$core->tpl->addValue('PrivateMsgError',array('tplPrivate','PrivateMsgError'));

$s = privateSettings($core);
		
if ($s->private_flag)
{
	$core->addBehavior('publicBeforeDocument',array('urlPrivate','privateHandler'));
}

class urlPrivate extends dcUrlHandlers
{
	public static function privateFeed($args)
	{
		self::feed($args);
	}
	
	public static function publicFeed($args)
	{
		global $core,$_ctx;
		
		$type = null;
		$params = array();
		$mime = 'application/xml';
		
		if (preg_match('#^rss2/xslt$#',$args,$m))
		{
			# RSS XSLT stylesheet
			self::serveDocument('rss2.xsl','text/xml');
		}
		elseif (preg_match('#^(atom|rss2)/comments/([0-9]+)$#',$args,$m))
		{
			# Post comments feed
			$type = $m[1];
		}
		elseif (preg_match('#^(?:category/(.+)/)?(atom|rss2)(/comments)?$#',$args,$m))
		{
			# All posts or comments feed
			$type = $m[2];
		}
		
		$tpl =  $type == '' ? 'atom' : $type;
		$tpl .= '-pv.xml';
		
		if ($type == 'atom') {
			$mime = 'application/atom+xml';
		}
		
		header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->robots_policy,''));
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument($tpl,$mime);
		
		return;
	}

	public static function callbackfoo($args)
	{
		return;
	}

	public static function privateHandler($args)
	{
		global $core,$_ctx;

		$urlp = new urlHandler();
		$urlp->mode = $core->url->mode;
		$urlp->registerDefault(array('urlPrivate','callbackfoo'));
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		$s = privateSettings($core);
		$password = $s->blog_private_pwd;

		if (!isset($session))
		{
			$session = new sessionDB(
				$core->con,
				$core->prefix.'session',
				'dc_privateblog_sess_'.$core->blog->id,
				'/'
			);
			$session->start();
		}

		foreach ($core->url->getTypes() as $k=>$v)
		{
			$urlp->register($k,$v['url'],$v['representation'],array('urlPrivate','callbackfoo'));
		}

		$urlp->getDocument();
		$type = $urlp->type;
		unset($urlp);

		if ($type == 'feed' || $type == 'pubfeed' || $type == 'spamfeed' || $type == 'hamfeed' || $type == 'trackback') 
		{
			return;
		}
		else
		{
			// Add cookie test (automatic login)
			$cookiepass = "dc_privateblog_cookie_".$core->blog->id;
			
			if (!empty($_COOKIE[$cookiepass])) 
			{
				$cookiepassvalue = (($_COOKIE[$cookiepass]) == $password);
			} 
			else 
			{
				$cookiepassvalue = false;
			}
			
			if (!isset($_SESSION['sess_blog_private']) || $_SESSION['sess_blog_private'] == "")
			{
				if ($cookiepassvalue != false) 
				{
					$_SESSION['sess_blog_private'] = $_COOKIE[$cookiepass];
					return;
				}
				if (!empty($_POST['private_pass'])) 
				{
					if (md5($_POST['private_pass']) == $password)
					{
						$_SESSION['sess_blog_private'] = md5($_POST['private_pass']);
						
						if (!empty($_POST['pass_remember'])) 
						{
							setcookie($cookiepass,md5($_POST['private_pass']),time() + 31536000,'/');
						}
						return;
					}
					$_ctx->blogpass_error = __('Wrong password');
				}
				$session->destroy();
				self::serveDocument('private.html','text/html',false);
				exit;
			}
			elseif ($_SESSION['sess_blog_private'] != $password)
			{
				$session->destroy();
				$_ctx->blogpass_error = __('Wrong password');
				self::serveDocument('private.html','text/html',false);
				exit;
			}
			elseif (isset($_POST['blogout']))
			{
				$session->destroy();
				setcookie($cookiepass,'ciao',time() - 86400,'/');
				$_ctx->blogpass_error = __('Disconnected');
				self::serveDocument('private.html','text/html',false);
				exit;
			}
			return;
		}
	}
}

class tplPrivate
{
	public static function PrivatePageTitle($attr)
	{
		global $core;
		$s = privateSettings($core);
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$s->private_page_title').'; ?>';
	}

	public static function PrivateMsg($attr)
	{
		global $core;
		$s = privateSettings($core);
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$s->private_page_message').'; ?>';
	}

	public static function PrivateReqPage($attr)
	{
		return 
		'<?php echo(isset($_SERVER[\'REQUEST_URI\']) 
		? html::escapeHTML($_SERVER[\'REQUEST_URI\'])
		: $core->blog->url); ?>' ;
	}

	public static function IfPrivateMsgError($attr,$content)
	{
		return
		'<?php if ($_ctx->blogpass_error !== null) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	public static function PrivateMsgError($attr)
	{
		return 
		'<?php if ($_ctx->blogpass_error !== null) 
		{ echo $_ctx->blogpass_error; } ?>';
	}

	public static function PrivatePassRemember($attr)
	{
		global $core;
		$s = privateSettings($core);
		if ($s->private_conauto_flag)
		{
			$res = '<p><label class="classic">'.
				form::checkbox(array('pass_remember'),1).' '.
				__('Enable automatic connection').'</label></p>';
			return $res;
		}
		else
		{
			return;
		}
	}
}

class widgetsPrivage
{
	public static function widgetLogout($w) 
	{
		global $core;
		$s = privateSettings($core);

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		if ($s->private_flag)
		{
	 		$res = '<div class="blogout">'.
				($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
				($w->text ? $w->text : '').
				'<form action="'.$core->blog->url.'" method="post">'.
				'<p class="buttons">'.
				'<input type="hidden" name="blogout" id="blogout" value="" />'.
				'<input type="submit" value="'.html::escapeHTML($w->label).'" class="logout" /></p>'.
				'</form></div>';
			return $res;
		}
		else
		{
			return;
		}
	}
}
?>