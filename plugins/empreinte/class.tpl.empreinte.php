<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Empreinte', a plugin for Dotclear 2               *
 *                                                             *
 *  Copyright (c) 2007,2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Empreinte' (see COPYING.txt);          *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

class tplEmpreinte
{
	public static function CommentIfUserAgent($attr,$content)
	{
		return
		'<?php $c_info = @publicEmpreinte::$c_info[$_ctx->comments->comment_id]; '.
		'if (!( empty($c_info["browser"]) || empty($c_info["system"]) )) : ?>'.
		$content.'<?php endif; unset($c_info); ?>';
	}
	
	public static function CommentCheckNoEmpreinte()
	{
		return
		'<?php if(!empty($_ctx->comment_preview[\'no_empreinte\']) '.
		'|| !empty($_POST[\'no_empreinte\'])) { echo \' checked="checked"\'; } ?>';
	}
	
	public static function CommentBrowser($attr)
	{
		$lcase = (integer) (boolean) @$attr['lowercase'];
		return '<?php echo $_ctx->comments->getBrowser('.$lcase.'); ?>';
	}
	
	public static function CommentSystem($attr)
	{
		$lcase = (integer) (boolean) @$attr['lowercase'];
		return '<?php echo $_ctx->comments->getSystem('.$lcase.'); ?>';
	}
	
	public static function CommentBrowserImg()
	{
		return self::PluginFileURL().'empreinte/icons/'.self::CommentBrowser(array('lowercase'=>1)).'.png';
	}
	
	public static function CommentSystemImg()
	{
		return self::PluginFileURL().'empreinte/icons/'.self::CommentSystem(array('lowercase'=>1)).'.png';
	}
	
	public static function PluginFileURL()
	{
		$url = $GLOBALS['core']->blog->url;
		$ext = strpos($url,'?') === false ? '?' :  '';
		return $url.$ext.'pf=';
	}
}
?>
