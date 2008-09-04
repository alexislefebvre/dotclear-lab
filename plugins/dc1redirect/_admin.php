<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2006-2008 Pep and contributors. All rights
# reserved. Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminBlogPreferencesForm',array('dc1redirectBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('dc1redirectBehaviors','adminBeforeBlogSettingsUpdate'));

class dc1redirectBehaviors
{
	public static function adminBlogPreferencesForm(&$core,&$settings)
	{
		if ($core->auth->isSuperAdmin())
		{
			echo
			'<fieldset><legend>'.__('Dotclear 1 URLs').'</legend>'.
			'<p><label class="classic">'.
			form::checkbox('dc1_redirect','1',$settings->dc1_redirect).
			__('Redirect Dotclear 1.x old URLs').'</label></p>'.
			'</fieldset>';
		}
	}
	
	public static function adminBeforeBlogSettingsUpdate(&$settings)
	{
		if ($GLOBALS['core']->auth->isSuperAdmin())
		{
			$settings->setNameSpace('dc1redirect');
			$settings->put('dc1_redirect',!empty($_POST['dc1_redirect']),'boolean');
			$settings->setNameSpace('system');
		}
	}
}
?>