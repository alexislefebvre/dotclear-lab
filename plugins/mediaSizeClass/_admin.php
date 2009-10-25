<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of mediaSizeClass, a plugin for Dotclear.
# 
# Copyright (c) 2009 Kozlika and kindly contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('adminBlogPreferencesForm',array('addClassMediasBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('addClassMediasBehaviors','adminBeforeBlogSettingsUpdate'));

class addClassMediasBehaviors
{
	public static function adminBlogPreferencesForm(&$core,&$settings)
	{
		echo
		'<fieldset><legend>'.__('Add CSS classes to your medias').'</legend>'.
		'<p><label class="classic">'.
		form::checkbox('addclass_enabled','1',$settings->addclass_enabled).
		__("Add a CSS class in <img /> tag depending on media size inserted : \"thumbnail-img\" to thumbnail-size medias; \"square-img\" to square-size medias; \"small-img\" to small-size medias; \"medium-img\" to medium-size medias.").'</label></p>'.
		'</fieldset>';
	}
	
	public static function adminBeforeBlogSettingsUpdate(&$settings)
	{
		$settings->setNameSpace('addclass');
		$settings->put('addclass_enabled',!empty($_POST['addclass_enabled']));
		$settings->setNameSpace('system');
	}
}
?>