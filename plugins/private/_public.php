<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Private', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Private blog' (see LICENSE);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('PrivatePageTitle',array('tplPrivate','PrivatePageTitle'));
$core->tpl->addValue('PrivateMsg',array('tplPrivate','PrivateMsg'));
$core->tpl->addValue('PrivateReqPage',array('tplPrivate','PrivateReqPage'));
$core->tpl->addBlock('IfPrivateMsgError',array('tplPrivate','IfPrivateMsgError'));
$core->tpl->addValue('PrivatePassRemember',array('tplPrivate','PrivatePassRemember'));
$core->tpl->addValue('PrivateMsgError',array('tplPrivate','PrivateMsgError'));

if ($core->blog->settings->private_flag)
{
	$core->addBehavior('publicBeforeDocument',array('urlPrivate','privacy'));
}

class urlPrivate extends dcUrlHandlers
{
	public static function privateFeed($args)
	{
		self::feed($args);
	}

	public static function callbackbidon($args)
	{
		return;
	}

	public static function privacy($args)
	{
		global $core,$_ctx;

		$urlp = new urlHandler();
		$urlp->mode = $core->url->mode;
		$urlp->registerDefault(array('urlPrivate','callbackbidon'));

		$path = str_replace(http::getHost(),'',$core->blog->url);
		if ($core->blog->settings->url_scan == 'query_string')
		{
			$path = str_replace(basename($core->blog->url),'',$path);
		}
		if (!isset($session))
		{
			$session = new sessionDB(
				   $core->con,
				   $core->prefix.'session',
				   'dc_privateblog',
				   $path
			);
			$session->start();
		}

		foreach ($core->url->getTypes() as $k=>$v)
		{
			$urlp->register($k,$v['url'],$v['representation'],array('urlPrivate','callbackbidon'));
		}

		$urlp->getDocument();
		$type = $urlp->type;
		unset($urlp);

		if ($type == 'feed' || $type == 'spamfeed' || $type == 'hamfeed' || $type == 'trackback') 
		{
			return;
		}

		else
		{
			// Add cookie test
			$cookiepass="dc_blog_private_".$core->blog->id;
			if (!empty($_COOKIE[$cookiepass])) {
				$cookiepassvalue=(($_COOKIE[$cookiepass]) ==
							   $core->blog->settings->blog_private_pwd);
			} else {
				$cookiepassvalue=false;
			}
			if (!isset($_SESSION['sess_blog_private']) || $_SESSION['sess_blog_private'] == "")
			{
				if ($cookiepassvalue != false) {
					$_SESSION['sess_blog_private'] = $_COOKIE[$cookiepass];
					setcookie($cookiepass,$_COOKIE[$cookiepass],time()+31536000,'/');
					return;

				}
				if (!empty($_POST['private_pass'])) 
				{
					if (md5($_POST['private_pass']) == $core->blog->settings->blog_private_pwd)
						{
							$_SESSION['sess_blog_private'] = md5($_POST['private_pass']);
							if (!empty($_POST['pass_remember'])) 
							{
								setcookie($cookiepass,md5($_POST['private_pass']),time()+31536000,'/');
							}
							return;
						}
					$_ctx->blogpass_error = __('Wrong password');
				}
				$session->destroy();
				$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
				self::serveDocument('private.html','text/html',false);
				exit;
			}
			elseif ($_SESSION['sess_blog_private'] != $core->blog->settings->blog_private_pwd)
			{
				$session->destroy();
				$_ctx->blogpass_error = __('Wrong password');
				$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
				self::serveDocument('private.html','text/html',false);
				exit;
			}
			elseif (isset($_POST['blogout']))
			{
				$session->destroy();
				$_ctx->blogpass_error = __('Disconnected');
				$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
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
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->blog_private_title').'; ?>';
	}

	public static function PrivateMsg($attr)
	{
		return '<?php echo $core->blog->settings->blog_private_msg; ?>';
	}

	public static function PrivateReqPage($attr)
	{
		return '<?php echo(isset($_SERVER[\'REQUEST_URI\']) ? html::escapeHTML($_SERVER[\'REQUEST_URI\']) : $core->blog->url); ?>';
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
		return '<?php if ($_ctx->blogpass_error !== null) { echo $_ctx->blogpass_error; } ?>';
	}

	public static function PrivatePassRemember($attr)
	{
		$res = '<p><label class="classic">'.
			form::checkbox(array('pass_remember'),1,'','',2).' '.
			__('Enable automatic connection').'</label></p>';
		return $res;
	}

	public static function privateWidgets(&$w) 
	{
		global $core;
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		if ($core->blog->settings->private_flag)
		{
	 		$res = '<div class="blogout">'.
				($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
				'<form action="'.$core->blog->url.'" method="post">'.
				'<p class="buttons">'.
				'<input type="hidden" name="blogout" id="blogout" value="">'.
				'<input type="submit" value="'.__('Disconnect').'" class="logout"></p>'.
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
