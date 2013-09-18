<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Include subcats" plugin.
#
# Copyright (c) 2009-2013 Bruno Hondelatte and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

require dirname(__FILE__).'/_widgets.php';
	
$core->addBehavior('adminBlogPreferencesForm',array('ISCBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('ISCBehaviors','adminBeforeBlogSettingsUpdate'));

class ISCBehaviors
{
	public static function adminBlogPreferencesForm($core,$settings)
	{
		$core->blog->settings->addNamespace('incsubcat');
		echo
		'<div class="fieldset"><h4>'.__('Sub-categories').'</h4>'.
		'<p><label class="classic">'.
		form::checkbox('incsubcat_enabled','1',$settings->incsubcat->incsubcat_enabled).
		__('Include sub-categories in category page and category posts feed').'</label></p>'.
		'</div>';
	}
	
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->addNamespace('incsubcat');
		$settings->incsubcat->put('incsubcat_enabled',!empty($_POST['incsubcat_enabled']));
	}
}
?>
