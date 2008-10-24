<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2008 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

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
