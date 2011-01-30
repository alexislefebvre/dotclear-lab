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

class ofcAnonymousComment extends optionsForComment
{
	public static function optionsForCommentAdminPrepend($core,$action)
	{
		if (!defined('DC_CONTEXT_ADMIN') || $action != 'savesettings') {
			return;
		}
		
		$core->blog->settings->optionsForComment->put('anonymouscomment',!empty($_POST['anonymouscomment']));
	}
	
	public static function optionsForCommentAdminFormMode($core)
	{
		if (!defined('DC_CONTEXT_ADMIN')){return;}
		
		$p = (boolean) $core->blog->settings->optionsForComment->anonymouscomment;
		
		echo '
		<p><label class="classic">'.
		form::checkbox(array('anonymouscomment'),'1',$p).
		__('Allow anonnymous comments').'</label></p>';
	}
	
	public static function optionsForCommentPublicPrepend($core,$rs)
	{
		if (!$core->blog->settings->optionsForComment->anonymouscomment 
		 || empty($_POST['c_anonymous']) 
		 || $rs['c_content'] === null 
		 || $rs['preview']) {
			return;
		}
		
		$rs['c_name'] = 'Somebody';
		$rs['c_mail'] = 'AnonymousComment@optionsForComment';
		$rs['c_site'] = '';
	}
	
	public static function optionsForCommentPublicCreate($cur,$preview)
	{
		if ($GLOBALS['core']->blog->settings->optionsForComment->anonymouscomment 
		 && $cur->comment_author == 'Somebody' 
		 && $cur->comment_email == 'AnonymousComment@optionsForComment')
		{
			# set tpl fields
			$preview['name'] = '';
			$preview['mail'] = '';
			$preview['site'] = '';
			
			# set db fields
			$cur->comment_author= __('Somebody');
			$cur->comment_email = '';
			$cur->comment_site = '';
			
			# no remember
			if (!self::remember()) {
				return;
			}
			
			# set cookie
			self::setCookie($cur->comment_author,$cur->comment_email,$cur->comment_site);
		}
	}
	
	public static function optionsForCommentPublicHead($core,$_ctx,$js_vars)
	{
		if (!$core->blog->settings->optionsForComment->anonymouscomment) {
			return;
		}
		
		echo self::jsLoad($core->blog->getQmarkURL().'pf=optionsForComment/js/ofc.anonymouscomment.js');
	}
	
	public static function optionsForCommentPublicForm($core,$_ctx)
	{
		if (!$core->blog->settings->optionsForComment->anonymouscomment) { 
			return;
		}
		
		$chk = !empty($_POST['c_anonymous']) ? ' checked="checked"' : '';
		
		echo 
		'<p class="anonymous">'.
		'<input name="c_anonymous" id="c_anonymous" type="checkbox"'.$chk.' /> '.
		'<label for="c_anonymous">'.__('Anonymous comment').'</label>'.
		'</p>';
	}
}
?>