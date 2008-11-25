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
$core->tpl->addValue('PrivateMsgError',array('tplPrivate','PrivateMsgError'));

$core->addBehavior('publicBeforeDocument',array('urlPrivate','privacy'));


class urlPrivate extends dcUrlHandlers
{
	public static function privacy($args)
	{
		global $core,$_ctx;

		if ($core->blog->settings->private_flag)
		{
			$session_private = session_id();
			if (empty($session_private)) 
			{
				session_start();
			}
			if (!isset($_SESSION['sess_blog_private']) || $_SESSION['sess_blog_private'] == "")
			{
				if (!empty($_POST['private_pass'])) 
				{
					if (md5($_POST['private_pass']) == $core->blog->settings->blog_private_pwd)
						{
							$_SESSION['sess_blog_private'] = md5($_POST['private_pass']);
							return;
						}
					$_ctx->blogpass_error = __('Wrong password');
				}
				$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
				self::serveDocument('private.html');
				exit;
			}
			elseif (isset($_POST['blogout'])){
				session_unset();
				session_destroy();
				$_ctx->blogpass_error = __('Disconnected');
				$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
				self::serveDocument('private.html');
				exit;
			}
			return;
		}
		return;
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
		$url = isset($_SERVER['REQUEST_URI']) ? html::escapeHTML($_SERVER['REQUEST_URI']) : $core->blog->url;
		return '<?php echo $url; ?>';
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

	public static function privateWidgets(&$w) 
	{
		global $core;
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
 		$res = '<div class="blogout">'.
			($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
			'<form action="'.$core->blog->url.'logout" method="post">'.
			'<p class="buttons">'.
			'<input type="hidden" name="blogout" id="blogout" value="">'.
			'<input type="submit" value="'.__('Disconnect').'" class="logout"></p>'.
			'</form></div>';
		return $res;
	}
}
?>
