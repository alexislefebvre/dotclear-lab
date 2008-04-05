<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'My Favicon', a plugin for Dotclear 2              *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My Favicon' (see COPYING.txt);         *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

if (is_callable('dcTemplate','SysBehavior')) {
	$core->addBehavior('publicHeadContent',array('myFavicon','publicHeadContent'));
}
else {
	$core->addBehavior('templateBeforeValue',array('myFavicon','templateBeforeValue'));
}

class myFavicon
{
	public static $allowed_mimetypes = array(
		'image/x-icon',
		'image/png',
		'image/bmp',
		'image/gif',
		'image/jpeg',
		'video/x-mng'
	);
	
	public static function publicHeadContent(&$core)
	{
		$res = self::faviconHTML($core->blog->settings);
		if (!empty($res)) {
			echo $res."\n";
		}
	}
	
	public static function templateBeforeValue(&$core,$id,$attr)
	{
		if ($id == 'include' && isset($attr['src']) && $attr['src'] == '_head.html') {
			return
			'<?php if (method_exists("myFavicon","faviconHTML")) {'.
			'echo myFavicon::faviconHTML();} ?>';
		}
	}

	private static function faviconHTML(&$sets)
	{
		$favicon_url = $sets->favicon_url;
		$favicon_mimetype = $sets->favicon_mimetype;
		
		if (empty($favicon_url) || empty($favicon_mimetype)
		|| !in_array($favicon_mimetype,self::$allowed_mimetypes)) {
			return;
		}
		
		return '<link rel="icon" type="'.$favicon_mimetype.
			'" href="'.html::escapeHTML($favicon_url).'" />';
	}
}
?>
