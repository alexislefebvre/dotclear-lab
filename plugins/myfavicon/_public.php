<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of My Favicon, a plugin for Dotclear.
# 
# Copyright (c) 2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

if (is_callable('dcTemplate','SysBehavior')) {
	$core->addBehavior('publicHeadContent',array('myFavicon','publicHeadContent'));
} else {
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
	
	public static function publicHeadContent($core)
	{
		$res = self::faviconHTML($core->blog->settings->myfavicon);
		if (!empty($res)) {
			echo $res."\n";
		}
	}
	
	public static function templateBeforeValue($core,$id,$attr)
	{
		if ($id == 'include' && isset($attr['src']) && $attr['src'] == '_head.html') {
			return
			'<?php if (method_exists("myFavicon","faviconHTML")) {'.
			'echo myFavicon::faviconHTML();} ?>';
		}
	}

	private static function faviconHTML($settings)
	{
		$favicon_url = $settings->url;
		$favicon_ie6 = $settings->ie6;
		
		if (empty($favicon_url)) {
			return;
		}
		
		$extension = files::getExtension($favicon_url);
		
		if (!isset(self::$allowed_mimetypes[$extension])) {
			$mimetype = files::getMimeType($favicon_url);
			if (!in_array($mimetype,self::$allowed_mimetypes)) {
				return '<!-- Bad favicon MIME type. -->'."\n";
			}
		}
		else {
			$mimetype = self::$allowed_mimetypes[$extension];
		}
		
		$rel = ($favicon_ie6 ? 'shortcut ' : '').'icon';
		return '<link rel="'.$rel.'" type="'.$mimetype.
			'" href="'.html::escapeHTML($favicon_url).'" />';
	}
}
?>