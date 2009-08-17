<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of noodles, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class genericNoodles
{
	public static function postURL($noodle,$content='')
	{
		global $core;

		$types = $core->getPostTypes();
		$reg = '@^'.str_replace('%s','(.*?)',
			preg_quote($core->blog->url.$types['post']['public_url'])).'$@';

		$ok = preg_match($reg,$content,$m);

		if (!$ok || !$m[1])	return '';

		$rs = $core->blog->getPosts(
			array('no_content'=>1,'post_url'=>urldecode($m[1]),'limit'=>1));

		if ($rs->isEmpty()) return '';

		return $rs->user_email;
	}
}

# Miscellaneous
class othersNoodles
{
	public static function publicPosts($core,$noodle)
	{
		if (!$noodle->active) return;

		$bhv = $noodle->place == 'prepend' || $noodle->place == 'before' ?
			'publicEntryBeforeContent' : 'publicEntryAfterContent';

		$core->addBehavior($bhv,array('othersNoodles','publicEntryContent'));
	}

	public static function publicEntryContent()
	{
		global $core,$_ctx,$__noodles;

		$m = $_ctx->posts->getAuthorEmail(false);
		$c = $__noodles->posts->css;
		$s = $__noodles->posts->size;
		$r = $__noodles->posts->rating;
		$d = $core->blog->settings->noodles_image ? 
			urlencode(libImagePath::getUrl($core,'noodles')) : '';

		echo 
		'<img class="noodles-posts" style="width:'.$s.'px;height:'.$s.'px;'.$c.'"'.
		'src="http://www.gravatar.com/avatar/'.md5($m).
		'?s='.$s.'&amp;r='.$r.'&amp;d='.$d.'" alt="" />';
	}

	public static function publicComments($core,$noodle)
	{
		if (!$noodle->active) return;

		$bhv = $noodle->place == 'prepend' || $noodle->place == 'before' ?
			'publicCommentBeforeContent' : 'publicCommentAfterContent';

		$core->addBehavior($bhv,array('othersNoodles','publicCommentContent'));
	}

	public static function publicCommentContent()
	{
		global $core,$_ctx,$__noodles;

		$m = $_ctx->comments->getEmail(false);
		$c = $__noodles->comments->css;
		$s = $__noodles->comments->size;
		$r = $__noodles->comments->rating;
		$d = $core->blog->settings->noodles_image ? 
			urlencode(libImagePath::getUrl($core,'noodles')) : '';

		echo 
		'<img class="noodles-comments" style="width:'.$s.'px;height:'.$s.'px;'.$c.'"'.
		'src="http://www.gravatar.com/avatar/'.md5($m).
		'?s='.$s.'&amp;r='.$r.'&amp;d='.$d.'" alt="" />';
	}
}

# Plugin Widgets
class widgetsNoodles
{
	public static function lastcomments($noodle,$content='')
	{
		global $core;

		$ok = preg_match('@\#c([0-9]+)$@',urldecode($content),$m);

		if (!$ok || !$m[1])	return '';

		$rs = $core->blog->getComments(
			array('no_content'=>1,'comment_id'=>$m[1],'limit'=>1));

		if (!$rs->isEmpty()) return $rs->comment_email;

		return '';
	}
}

# Plugin authorMode
class authormodeNoodles
{
	public static function authors($noodle,$content='')
	{
		global $core;
		$ok = preg_match('@\/([^\/]*?)$@',$content,$m);

		if (!$ok || !$m[1]) return '';

		$rs = $core->getUser($m[1]);

		if ($rs->isEmpty()) return '';

		return $rs->user_email;
	}

	public static function author($core,$noodle)
	{
		if ($noodle->active) 
			$core->addBehavior('publicHeadContent',
				array('authormodeNoodles','publicHeadContent'));
	}

	public static function publicHeadContent()
	{
		global $core,$_ctx,$__noodles;

		if ($_ctx->current_tpl != 'author.html') return;

		$id = $_ctx->users->user_id;
		$u = $core->getUser($id);
		$m = $u->user_email;
		$c = $__noodles->author->css;
		$s = $__noodles->author->size;
		$r = $__noodles->author->rating;
		$d = $core->blog->settings->noodles_image ? 
			urlencode(libImagePath::getUrl($core,'noodles')) : '';

		echo 
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		"$(function(){if(!document.getElementById){return;}\n".
		"$('".$__noodles->author->target."').".$__noodles->author->place."('".
		'<img class="noodles-comments" style="width:'.$s.'px;height:'.$s.'px;'.$c.'"'.
			'src="http://www.gravatar.com/avatar/'.md5($m).
			'?s='.$s.'&amp;r='.$r.'&amp;d='.$d.'" alt="" />'.
		"');});".
		"\n//]]>\n".
		"</script>\n";
	}
}
?>