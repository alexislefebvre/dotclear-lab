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

class ofcEmailOptionnel extends optionsForComment
{
	public static function optionsForCommentAdminFormMode($core)
	{
		if (!defined('DC_CONTEXT_ADMIN')){return;}
		
		$p = $core->blog->settings->optionsForComment->mode == 'emailoptionnel';
		
		echo '
		<p><label class="classic">'.
		form::radio(array('mode'),'emailoptionnel',$p).
		__('Optional email address').'</label></p>';
	}
	
	public static function optionsForCommentPublicPrepend($core,$rs)
	{
		if (!defined('DC_CONTEXT_ADMIN')){return;}
		
		if ($core->blog->settings->optionsForComment->mode != 'emailoptionnel' 
		 || $rs['c_content'] === null 
		 || $rs['preview'] 
		 || $rs['c_mail'] != '') {
			return;
		}
		
		$rs['c_mail'] = 'EmailOptionnel@optionsForComment';
	}
	
	public static function optionsForCommentPublicCreate($cur,$preview)
	{
		if ($GLOBALS['core']->blog->settings->optionsForComment->mode != 'emailoptionnel'  
		 && $cur->comment_email == 'EmailOptionnel@optionsForComment')
		{
			# set tpl fields
			$preview['mail'] = '';
			
			# set db fiels
			$cur->comment_email = '';
			
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
		if ($core->blog->settings->optionsForComment->mode != 'emailoptionnel' ) {
			return;
		}
		
		$js_vars["ofcMsg['email']"] = __('Email address');
		$js_vars["ofcMsg['optional']"] = __('optional');
		
		echo self::jsLoad($core->blog->getQmarkURL().'pf=optionsForComment/js/ofc.emailoptionnel.js');
	}
}
?>