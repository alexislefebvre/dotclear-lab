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
	#FIXME Mimetypes in common/lib.files.php (Clearbricks) are not enough
	public static $allowed_mimetypes = array(
		'ico' => 'image/x-icon',
		'png' => 'image/png',
		'bmp' => 'image/bmp',
		'gif' => 'image/gif',
		'jpg' => 'image/jpeg',
		'mng' => 'video/x-mng'
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

	private static function faviconHTML(&$settings)
	{
		$favicon_url = $settings->favicon_url;
		
		if (empty($favicon_url)) {
			return;
		}
		
		$extension = files::getExtension($favicon_url);
		
		if (!isset(self::$allowed_mimetypes[$extension])) {
			$mimetype = files::getMimeType($favicon_url);
			if (!in_array($mimetype,self::$allowed_mimetypes)) {
				return;
			}
		}
		else {
			$mimetype = self::$allowed_mimetypes[$extension];
		}
		
		return '<link rel="icon" type="'.$mimetype.
			'" href="'.html::escapeHTML($favicon_url).'" />';
	}
}
?>
