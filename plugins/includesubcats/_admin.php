<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Include subcats" plugin.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$core->addBehavior('adminBlogPreferencesForm',array('ISCBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('ISCBehaviors','adminBeforeBlogSettingsUpdate'));

class ISCBehaviors
{
	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>'.__('Sub-categories').'</legend>'.
		'<p><label class="classic">'.
		form::checkbox('incsubcat_enabled','1',$settings->incsubcat_enabled).
		__('Include sub-categories in category page and category posts feed').'</label></p>'.
		'</fieldset>';
	}
	
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->setNameSpace('incsubcat');
		$settings->put('incsubcat_enabled',!empty($_POST['incsubcat_enabled']));
		$settings->setNameSpace('system');
	}
}

?>
