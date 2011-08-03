<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of simplyFavicon, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
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
	public static function adminBlogPreferencesForm($core,$blog_settings)
	{
		echo
		'<fieldset><legend>Favicon</legend>'.
		'<p><label class="classic">'.
		form::checkbox('simply_favicon','1',(boolean) $blog_settings->system->simply_favicon).
		__('Enable "Simply favicon" extension').'</label></p>'.
		'<p class="form-note">'.
		__("You must place an image called favicon.png or .jpg or .ico into your blog's public directory.").
		'</p>'.
		'</fieldset>';
	}
	
	public static function adminBeforeBlogSettingsUpdate($blog_settings)
	{
		$blog_settings->system->put('simply_favicon',!empty($_POST['simply_favicon']));
	}
}
?>