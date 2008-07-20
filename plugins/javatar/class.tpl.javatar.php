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
	     global $core,$_ctx;
	     $f = $core->tpl->getFilters($attr);
	     return '<?php echo '.sprintf($f,'$_ctx->comments->getJID()').'; ?>';
        }
        
        public static function CommentPreviewJabber($attr)
        {
	     global $core,$_ctx;
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
		$javatar_img_size = $core->blog->settings->javatar_img_size;
		if (!empty($default_img)) {
			$img = $default_img;
		}
		else {
			$img = $core->blog->getQmarkURL().'pf=javatar/default/logo-32.png';
		}
		return html::escapeHTML($img);
        }
	
	public static function CommentAuthorJavatar($attr)
	{
	     global $core;
		$f = $core->tpl->getFilters($attr);
	        return
		'<?php if($core->blog->settings->javatar_active) : ?>
		<img src="http://presence.jabberfr.org/avatar.php?hash='.self::CommentJID($f).'&size='.self::JavatarSize($f).'&default='.self::JavatarImgDefaut($f).'"
		alt="avatar jabber" class="javatar" />
		<?php endif; ?>';
	}
}
?>
