<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of myLocation, a plugin for Dotclear.
#
# Copyright (c) 2010 Tomtom and contributors
# http://blog.zenstyle.fr/
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminBlogPreferencesForm',array('myLocationBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('myLocationBehaviors','adminBeforeBlogSettingsUpdate'));

class myLocationBehaviors
{
	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>'.__('Geolocation').'</legend>'.
		'<p><label class="classic">'.
		form::checkbox('mylocation_enable','1',$settings->myLocation->enable).
		__('Enable geolocation').'</label></p>'.
		'</fieldset>';
	}

	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->myLocation->put('enable',!empty($_POST['mylocation_enable']));
	}
}

?>