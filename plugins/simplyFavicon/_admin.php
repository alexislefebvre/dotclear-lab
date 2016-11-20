<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of simplyFavicon, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2016 JC Denis and contributors
# contact@jcdenis.fr http://jcdenis.net
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$core->addBehavior('adminBlogPreferencesForm',array('adminSimplyFavicon','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('adminSimplyFavicon','adminBeforeBlogSettingsUpdate'));

class adminSimplyFavicon
{
	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<div class="fieldset"><h4>Favicon</h4>'.
		'<p><label class="classic">'.
		form::checkbox('simply_favicon','1',(boolean) $settings->simplyfavicon->simply_favicon).
		__('Enable "Simply favicon" plugin').'</label></p>'.
		'<p class="form-note">'.
		__("You must place an image called favicon.png or .jpg or .ico into your blog's public directory.").
		'</p>'.
		'</div>';
	}
	
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->addNameSpace('simplyfavicon');
		$settings->simplyfavicon->put('simply_favicon',!empty($_POST['simply_favicon']));
		$settings->addNameSpace('system');
	}
}