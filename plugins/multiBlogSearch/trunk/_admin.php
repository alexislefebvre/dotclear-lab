<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiBlogSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminBlogPreferencesForm',array('multiBlogSearchBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('multiBlogSearchBehaviors','adminBeforeBlogSettingsUpdate'));

class multiBlogSearchBehaviors
{
	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>'.__('Multi blog search').'</legend>'.
		'<p><label class="classic">'.
		form::checkbox('multiblogsearch_enabled','1',$settings->multiblogsearch_enabled).
		__('Enable multi blog Search').'</label></p>'.
		'</fieldset>';
	}

	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->setNameSpace('multiblogsearch');
		$settings->put('multiblogsearch_enabled',!empty($_POST['multiblogsearch_enabled']),'boolean');
		$settings->setNameSpace('system');
	}
}

?>