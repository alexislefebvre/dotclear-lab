<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcMiniUrl, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class coreUpdateMiniUrl
{
	public static function post($blog,$cur)
	{
		$pattern['XHTML'] = '/<a(.+?)href="(.*?)"/';
		$pattern['Wiki'] = '/\[(.*?)\|(.*?)\|(.*?)\]/';

		$type = $cur->post_format == 'wiki' ? 'Wiki' : 'XHTML';

		$cur->post_excerpt = preg_replace_callback($pattern[$type],
			array('coreUpdateMiniUrl','replaceContent'.$type),$cur->post_excerpt);
		$cur->post_excerpt_xhtml = preg_replace_callback($pattern['XHTML'],
			array('coreUpdateMiniUrl','replaceContentXHTML'),$cur->post_excerpt_xhtml);

		$cur->post_content = preg_replace_callback($pattern[$type],
			array('coreUpdateMiniUrl','replaceContent'.$type),$cur->post_content);
		$cur->post_content_xhtml = preg_replace_callback($pattern['XHTML'],
			array('coreUpdateMiniUrl','replaceContentXHTML'),$cur->post_content_xhtml);
	}

	public static function comment($blog,$cur)
	{
		$pattern = '/<a(.+?)href="(.*?)"/';

		$cur->comment_content = preg_replace_callback($pattern,
			array('coreUpdateMiniUrl','replaceContentXHTML'),$cur->comment_content);
	}

	public static function category($blog,$cur)
	{
		$pattern = '/<a(.+?)href="(.*?)"/';

		$cur->cat_desc = preg_replace_callback($pattern,
			array('coreUpdateMiniUrl','replaceContentXHTML'),$cur->cat_desc);
	}

	public static function replaceContentXHTML($m)
	{
		$str = $m[2];
		if (empty($str)) return '';

		global $core;

		$miniurl = new dcMiniUrl($core,true);
		$id = $miniurl->auto($str,array('miniurl','customurl'));

		return 
		'<a'.$m[1].'href="'.
		(-1 != $id ? $core->blog->url.$core->url->getBase('miniUrl').'/'.$id : $m[2]).
		'"';
	}

	public static function replaceContentWiki($m)
	{
		$str = $m[2];
		if (empty($str)) return '';

		global $core;

		$miniurl = new dcMiniUrl($core,true);
		$id = $miniurl->auto($str,array('miniurl','customurl'));

		return 
		'['.$m[1].'|'.
		(-1 != $id ? $core->blog->url.$core->url->getBase('miniUrl').'/'.$id : $m[2]).
		'|'.$m[3].']';
	}
}
?>