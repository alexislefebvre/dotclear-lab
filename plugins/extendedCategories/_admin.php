<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of ExtendedCategorie, a plugin for Dotclear.
# 
# Copyright (c) 2009 Rocky Horror
# rockyhorror@divingislife.net
# 
# Licensed under the GPL version 3.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/gpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('initWidgets',array('ExtendCategorieBehaviors','initWidgets'));
 
class ExtendCategorieBehaviors
{
	public static function initWidgets(&$w)
	{
		$w->create('ExtendCategorie',__('Extend categories list'),array('PublicExtendCategorie','extendcategorieslist'));
		$w->ExtendCategorie->setting('postcount',__('With entries counts'),0,'check');
		$w->ExtendCategorie->setting('title',__('Title:'),__('Categories'));
	}
}

?>