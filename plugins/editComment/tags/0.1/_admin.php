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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminBlogPreferencesForm',array('editCommentAdmin','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('editCommentAdmin','adminBeforeBlogSettingsUpdate'));

class editCommentAdmin
{
	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>'.__('Comments edition').'</legend>'.
		'<p><label class="classic">'.
		form::checkbox('ec_enabled','1',$settings->ec_enabled).
		__('Enable plugin').'</label></p>'.
		'<p><label class="classic">'.__('People can edit their comments during').' '.
		form::field('ec_ttl',3,3,(!isset($settings->ec_ttl) ? $settings->ec_ttl : '5')).
		' '.__('minutes').'</p>'.
		form::checkbox('ec_countdown','1',$settings->ec_countdown).
		__('Display countdown').'</label></p>'.
		'</fieldset>';
	}

	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->setNameSpace('editcomment');
		$settings->put('ec_enabled',!empty($_POST['ec_enabled']),'boolean');
		$settings->put('ec_ttl',$_POST['ec_ttl'],'string');
		$settings->put('ec_countdown',!empty($_POST['ec_countdown']),'boolean');
		$settings->setNameSpace('system');
	}
}

?>