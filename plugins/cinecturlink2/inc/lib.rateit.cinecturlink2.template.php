<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class templateRateItCinecturlink2
{
	public static function redirect($voted,$type,$id,$note)
	{
		if ($type == 'cinecturlink2')
		{
			http::redirect($core->blog->url.$core->url->getBase('cinecturlink2').'/detail/'.$id.($voted ? '#rateit' : ''));
		}
	}
	
	public static function title($type,&$title)
	{
		if ($type == "cinecturlink2") { return __('Rate this'); }
	}
	
	public static function params($type)
	{
		if ($type == 'cinecturlink2') 
		{
			return 
			"\$core->blog->settings->addNamespace('rateit'); \n".
			"if (\$_ctx->exists('cinecturlink') ".
			" && \$core->blog->settings->rateit->rateit_active ".
			" && \$core->blog->settings->rateit->rateit_cinecturlink2_active ".
			" && \$core->blog->settings->rateit->rateit_cinecturlink2_page) { \n".
			" \$rateit_params['type'] = 'cinecturlink2'; \n".
			" \$rateit_params['id'] = \$_ctx->c2_entries->link_id; \n".
			"} \n";
		}
	}
	
	public static function publicC2EntryAfterContent($core,$_ctx)
	{
		$core->blog->settings->addNamespace('rateit');
		
		if ($core->blog->settings->rateit->rateit_active 
		 && $core->blog->settings->rateit->rateit_cinecturlink2_active 
		 && $core->blog->settings->rateit->rateit_cinecturlink2_page 
		 && $_ctx->exists('cinecturlink') 
		) {
			$GLOBALS['rateit_params']['type'] = 'cinecturlink2';
			$GLOBALS['rateit_params']['id'] = $_ctx->c2_entries->link_id;
			
			echo $core->tpl->getData('rateit.html');
		}
		else
		{
			return;
		}
	}
}
?>