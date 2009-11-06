<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of editComment, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class editComment
{
	public static function addTplPath()
	{	
		$GLOBALS['core']->tpl->setPath($GLOBALS['core']->tpl->getPath(), dirname(__FILE__).'/../default-templates');
	}
	
	public static function addLinks($core,$_ctx)
	{
		$url = sprintf('%1$s%2$s/%3$s/%4$s',$core->blog->url,$core->url->getBase('edit'),'comment',$_ctx->comments->comment_id);
		
		if (self::check() && $core->blog->settings->ec_enable) {
		
			echo '<p id="edit-links"><a href="'.$url.'" class="thickbox edit">'.__('Edit').'</a><p>';
		
			if ($core->blog->settings->ec_countdown) {
				$dt = strtotime($_ctx->comments->comment_dt);
				$diff = $dt + ($core->blog->settings->ec_ttl*60) - time();
				
				echo
					'<script type="text/javascript">'.
					'var d = new Date();'."\n".
					'd.setSeconds('.$diff.');'."\n".
					"$('#edit-links').next().countdown({".
					"date: d, msgNow: '', msgFormat: '".
					__('%h [hour|hours] %m [minute|minutes] %s [second|seconds]').
					"'});</script>";
			}
		}
	}
	
	public static function addHeaderFiles($core,$_ctx)
	{
		echo 
			$core->blog->settings->ec_countdown ?
			'<script type="text/javascript" src="'.$core->blog->url.'pf=editComment/js/countdown.min.js"></script>'."\n" :
			'';
		echo 
			'<script type="text/javascript" src="'.$core->blog->url.'pf=editComment/js/thickbox.min.js"></script>'."\n".
			'<link rel="stylesheet" href="'.$core->blog->url.'pf=editComment/css/thickbox.min.css" type="text/css" media="screen" />'."\n".
			'<style type="text/css" media="screen">'."\n".
			'a.edit { padding-left: 20px; background: transparent url('.$core->blog->url.'pf=editComment/img/edit.png) no-repeat top left; }'."\n".
			'</style>';
	}
	
	protected static function check()
	{
		global $core,$_ctx;
		
		$flag = false;
		
		$cookie = isset($_COOKIE['comment_info']) ? explode("\n",$_COOKIE['comment_info']) : null;
		
		if ($cookie !== null) {
			if ($cookie[0] === $_ctx->comments->comment_author && $cookie[1] === $_ctx->comments->comment_email && $cookie[2] === $_ctx->comments->comment_url) {
				$flag = true;
			}
		}
		
		if (http::realIP() === $_ctx->comments->comment_ip) {
			$flag = true;
		}
		
		$diff = time() - strtotime($_ctx->comments->comment_dt);
		
		if ($diff > ($core->blog->settings->ec_ttl*60)) {
			$flag = false;
		}
		
		return $flag;
	}
	
	public static function update()
	{
		global $core,$_ctx;
		
		$id = html::escapeHTML($_POST['c_id']);
		
		$_ctx->comments = $core->blog->getComments(array('comment_id' => $id));
		
		if (self::check()) {
			if ($core->blog->settings->wiki_comments) {
				$core->initWikiComment();
			} else {
				$core->initWikiSimpleComment();
			}
			$content = $core->wikiTransform($_POST['c_content']);
			$content = $core->HTMLfilter($content);
			
			$cur = $core->con->openCursor($core->prefix.'comment');
			
			$cur->comment_author = $_POST['c_name'];
			$cur->comment_email = html::clean($_POST['c_mail']);
			$cur->comment_site = html::clean($_POST['c_site']);
			$cur->comment_content = $content;
			$cur->comment_ip = http::realIP();
			
			if (!text::isEmail($cur->comment_email)) {
				throw new Exception(__('You must provide a valid email address.'));
			}
			
			# --BEHAVIOR-- adminBeforeCommentUpdate
			$core->callBehavior('adminBeforeCommentUpdate',$cur,$id);
			
			$core->auth->sudo(array($core->blog,'updComment'),$id,$cur);
			
			# --BEHAVIOR-- adminAfterCommentUpdate
			$core->callBehavior('adminAfterCommentUpdate',$cur,$id);
			
			return true;
		}
		else {
			throw new Exception(__('You are not allowed to update this comment. Maybe time is finished'));
		}
	}
}

?>