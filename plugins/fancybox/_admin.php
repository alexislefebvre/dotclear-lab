<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of fancybox, a plugin for Dotclear 2.
#
# Copyright (c)  2009 Osku and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('adminBlogPreferencesForm',array('FancyBoxBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('FancyBoxBehaviors','adminBeforeBlogSettingsUpdate'));

class FancyBoxBehaviors
{
	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>FancyBox</legend>'.
		'<p><label class="classic">'.
		form::checkbox('fancybox_enabled','1',$settings->fancybox_enabled).
		__('Enable FancyBox').'</label></p>'.
		'</fieldset>';
	}
	
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->setNameSpace('fancybox');
		$settings->put('fancybox_enabled',!empty($_POST['fancybox_enabled']),'boolean');
		$settings->setNameSpace('system');
	}
}
?>