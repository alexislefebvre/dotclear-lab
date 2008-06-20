<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('adminBlogPreferencesForm',array('lightBoxBehaviors','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('lightBoxBehaviors','adminBeforeBlogSettingsUpdate'));

class lightBoxBehaviors
{
	public static function adminBlogPreferencesForm(&$core,&$settings)
	{
		echo
		'<fieldset><legend>lightBox</legend>'.
		'<p><label class="classic">'.
		form::checkbox('lightbox_enabled','1',$settings->lightbox_enabled).
		__('Enable lightBox').'</label></p>'.
		'</fieldset>';
	}
	
	public static function adminBeforeBlogSettingsUpdate(&$settings)
	{
		$settings->setNameSpace('lightbox');
		$settings->put('lightbox_enabled',!empty($_POST['lightbox_enabled']),'boolean');
		$settings->setNameSpace('system');
	}
}
?>