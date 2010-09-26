<?php

class ofcEmailOptionnel extends optionsForComment
{
	public static function optionsForCommentAdminFormMode($core)
	{
		$p = $core->blog->settings->optionsForComment->mode == 'emailoptionnel';
		
		echo '
		<p><label class="classic">'.
		form::radio(array('mode'),'emailoptionnel',$p).
		__('Optional email address').'</label></p>';
	}
	
	public static function optionsForCommentPublicPrepend($core,$rs)
	{
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