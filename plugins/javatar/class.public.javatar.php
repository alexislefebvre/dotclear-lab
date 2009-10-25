<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Javatar', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Javatar' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

class publicJavatar
{
	public static $c_info = array();

	public static function publicBeforeCommentPreview()
	{
		global $_ctx;
		
		$_ctx->comment_preview['jabber'] = $_POST['c_jabber'];
	}

	public static function publicBeforeCommentCreate(&$cur)
	{
		global $core;
		
		$cur->comment_jabber = $_POST['c_jabber'];
	}

	public static function publicCommentFormAfterContent()
	{
		global $core,$_ctx;
		$jabber = null;
		
		if (!$core->blog->settings->javatar_active) {
			return;
		}
		$jabber = isset($_ctx->comment_preview['jabber']) ? html::escapeHTML($_ctx->comment_preview['jabber']) : '';

		echo '<p class="field jabber"><label for="c_jabber">'.__('Jabber').
		' ('.__('optionnal').') '.':</label>'.
		'<input name="c_jabber" id="c_jabber" type="text" size="30" maxlength="255"'.
		'value="'.$jabber.'"/></p>'; 
	}

	public static function publicCommentBeforeContent()
	{
		global $core,$_ctx;
		
		if (!$core->blog->settings->javatar_active) {
			return;
		}

		$jid = $_ctx->comments->getJID();
		$mid = md5($_ctx->comments->comment_email);
		$default_img = $core->blog->settings->javatar_default_img;
		$javatar_img_size = $core->blog->settings->javatar_img_size;
		if (!empty($default_img)) {
			$img = $default_img;
		}
		else {
			$img = $core->blog->getQmarkURL().'pf=javatar/default/logo-'.$javatar_img_size.'.png';
		}
		if (!$core->blog->settings->gravatar_default) {
			echo '<img src="http://presence.jabberfr.org/avatar.php?hash='.$jid.'&size='.$javatar_img_size.'&default='.$img.'"
			alt="avatar jabber" class="javatar" />';
		}
		else {
			echo '<img src="http://presence.jabberfr.org/avatar.php?hash='.$jid.'&size='.$javatar_img_size.'&default=http://www.gravatar.com/avatar/'.$mid.'?s='.$javatar_img_size.'"
			alt="avatar" class="javatar" />';
		}
	}

	public static function publicHeadContent()
	{
		global $core;
		
		if (!$core->blog->settings->javatar_active) {
			return;
		}
		
		$custom_css = $core->blog->settings->javatar_custom_css;
				
		if (!empty($custom_css))
		{
			if (strpos('/',$custom_css) === 0) {
				$css = $custom_css;
			}
			else {
				$css =
					$core->blog->settings->themes_url."/".
					$core->blog->settings->theme."/".
					$custom_css;
			}
		}
		else
		{
			$css = html::stripHostURL($core->blog->getQmarkURL().'pf=javatar/default/javatar-default.css');
		}
		
		echo
		'<style type="text/css" media="screen">@import url('.$css.');</style>'."\n";
	}

	public static function publicFooterContent()
	{
		global $core;
		
		if (!$core->blog->settings->javatar_active) {
			return;
		}
		
		$js = html::stripHostURL($core->blog->getQmarkURL().'pf=javatar/js/post.js');
		
		echo
		'<script type="text/javascript" src="'.$js.'"></script>'."\n";
	}

	public static function coreBlogGetComments(&$c_rs)
	{
		$ids = array();
		while ($c_rs->fetch())
		{
			if (!$c_rs->comment_trackback) {
				$ids[] = $c_rs->comment_id;
			}
		}
		
		if (empty($ids)) {
			return;
		}

		$ids = implode(', ',$ids);

		$strReq =
		'SELECT comment_id, comment_jabber '.
		'FROM '.$c_rs->core->prefix.'comment '.
		'WHERE comment_id  IN ('.$ids.')';
		$rs = $c_rs->core->con->select($strReq);

		while ($rs->fetch())
		{
			self::$c_info[$rs->comment_id] = array(
			'javatar'=>$rs->comment_jabber
			);
		}
		
		$c_rs->extend('rsExtCommentJavatar');
	}
}
?>