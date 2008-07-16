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

class tplJavatar
{
        public static function CommentJID($attr)
        {
	     global $core;
		return '<?php echo rsExtCommentJavatar::getJID(); ?>';
        }
        
        public static function CommentPreviewJID($attr)
        {
	     global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comment_preview["jabber"]').'; ?>';
        }

        public static function JavatarSize($attr)
        {
	     global $core;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->javatar_img_size').'; ?>';
        }

        public static function JavatarImgDefaut($attr)
        {
	     global $core;
		$default_img = $core->blog->settings->javatar_default_img;
		if (!empty($default_img)) {
			if (strpos('/',$default_img) === 0) {
				$img = $default_img;
			}
			else {
				$img =
				$core->blog->settings->themes_url."/".
				$core->blog->settings->theme."/".
				$default_img;
			}
		}
		else {
			$img = html::stripHostURL($core->blog->getQmarkURL().'pf=javatar/icon_32.png');
		}
		return html::escapeHTML($img);
        }
	
	public static function CommentAuthorJavatar($attr)
	{
	     global $core;
	        return
		'<?php if($core->blog->settings->javatar_active) { '.
		'echo \<img src="http://presence.jabberfr.org/avatar.php?
		hash={{tpl:CommentJID}}
		&size={tpl:JavatarSize}}
		&default={tpl:JavatarImgDefaut}}"
		alt="avatar jabber" class="javatar" \/>;'.
		'} ?>';
	}
	
	public static function FormAuthorJavatar($attr)
	{
	     global $core;
	        return
		'<?php if($core->blog->settings->javatar_active) { '.
		'echo \<p class="field"><label for="c_mail">__(Jabber address&nbsp;:)</label>
		<input name="c_jabber" id="c_jabber" type="text" size="30" maxlength="255"
		value="{{tpl:CommentPreviewJID encode_html="1"}}" />
		</p>;'.
		'} ?>';
	}
}

?>
