<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of optionsForComment, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class optionsForComment
{
	# /inc/public/prepend.php#L116
	public static function publicPrepend($core)
	{
		$rs = new ArrayObject();
		$rs['c_content'] = !isset($_POST['c_content']) ? null : $_POST['c_content'];
		$rs['c_name'] = !empty($_POST['c_name']) ? $_POST['c_name'] : '';
		$rs['c_mail'] = !empty($_POST['c_mail']) ? $_POST['c_mail'] : '';
		$rs['c_site'] = !empty($_POST['c_site']) ? $_POST['c_site'] : '';
		$rs['c_remember'] = !empty($_POST['c_remember']);
		$rs['preview'] = !empty($_POST['preview']);
		
		$core->callBehavior('optionsForCommentPublicPrepend',$core,$rs);
		
		$com = $rs->getArrayCopy();
		foreach($com as $k => $v) {
			if ($k == 'c_content' && $v === null) continue;
			$_POST[$k] = $v;
		}
	}
	
	# /themes/default/tpl/_head.html#L12
	public static function publicHeadContent($core,$_ctx)
	{
		# Check page (only post page)
		if (!$_ctx->exists('posts')) {
			return;
		}
		
		# Additional CSS
		$p = $core->blog->settings->optionsForComment->css_extra;
		if (!empty($p)) {
			echo 
			"<!-- style for plugin optionsForComment -->\n".
			'<style type="text/css">'."\n".$p."</style>\n";
		}
		
		# JS class
		echo 
		"<!-- JS for plugin optionsForComment -->\n".
		self::jsLoad($core->blog->getQmarkURL().'pf=optionsForComment/js/optionsforcomment.js');
		
		$js_vars = new ArrayObject();
		
		$res = $core->callBehavior('optionsForCommentPublicHead',$core,$_ctx,$js_vars);
		
		echo 
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		"ofcMsg = Array();\n";
		$vars = $js_vars->getArrayCopy();
		foreach($vars as $k => $v) {
			echo self::jsVar($k,$v);
		}
		echo 
		"//]]>\n".
		"</script>\n".
		$res;
	}
	
	# /inc/public/lib.urlhandlers.php#L393
	public static function publicBeforeCommentPreview($_ctx_comment_preview)
	{
		global $core;
		$core->callBehavior('optionsForCommentPublicPreview',$_ctx_comment_preview);
	}
	
	# /inc/public/lib.urlhandlers.php#L419
	public static function publicBeforeCommentCreate($cur)
	{
		global $core, $_ctx;
		$core->callBehavior('optionsForCommentPublicCreate',$cur,$_ctx->comment_preview);
	}
	
	# /themes/default/tpl/post.html#L181
	public static function publicCommentFormBeforeContent($core,$_ctx)
	{
		$core->callBehavior('optionsForCommentPublicForm',$core,$_ctx);
	}
	
	public static function jsLoad($src)
	{
		return '<script type="text/javascript" src="'.html::escapeHTML($src).'"></script>'."\n";
	}
	
	public static function jsVar($n,$v)
	{
		return $n." = '".html::escapeJS($v)."';\n";
	}
	
	public static function remember($r=null)
	{
		//read
		if ($r === null) {
			return !empty($_POST['c_remember']);
		}
		//write
		$_POST['c_remember'] = (boolean) $r;
	}
	
	public static function getCookie()
	{
		$rs = array();
		$rs['name'] = '';
		$rs['site'] = '';
		$rs['mail'] = '';
		
		if (empty($_COOKIE['comment_info'])) {
			return $rs;
		}
		
		$v = explode("\n",$_COOKIE['comment_info']);
		if (count($v) != 3) {
			self::delInfoCookie();
			return $rs;
		}
		
		$rs['name'] = $v[0];
		$rs['site'] = $v[1];
		$rs['mail'] = $v[2];
		
		return $rs;
	}
	
	public static function setCookie($name,$mail,$site)
	{
		setcookie('comment_info',$name."\n".$mail."\n".$site,strtotime('+3 month'),'/');
	}
	
	public static function delCookie()
	{
		setcookie('comment_info','',time() -3600,'/');
	}
}
?>