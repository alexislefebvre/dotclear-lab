<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2008 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Gallery plugin for DC2 is free software; you can redistribute it and/or modify
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

$core->addBehavior('initWidgets',array('galWidgets','initWidgets'));


class galWidgets 
{
	public static function initWidgets(&$widgets)
	{
		$widgets->create('listgal',__('Galleries'),array('tplGallery','listgalWidget'));
		$widgets->listgal->setting('title',__('Title:'),'');
		$widgets->listgal->setting('limit',__('Limit (empty means no limit):'),'20');
		$widgets->listgal->setting('display',__('Display mode'),'gal_only','combo',
			array(__('Galleries only') => 'gal_only', __('Categories only') => 'cat_only', __('Both') => 'both'));
		$widgets->listgal->setting('orderby',__('Order by'),'name','combo',
			array(__('Gallery name') => 'name', __('Gallery date') => 'date'));
		$widgets->listgal->setting('orderdir',__('Sort:'),'desc','combo',
			array(__('Ascending') => 'asc', __('Descending') => 'desc'));
		$widgets->listgal->setting('homeonly',__('Home page only'),1,'check');

		$widgets->create('randomimage',__('Random image'),array('tplGallery','randimgWidget'));
		$widgets->randomimage->setting('title',__('Title:'),'');
		$widgets->randomimage->setting('homeonly',__('Home page only'),1,'check');

		$widgets->create('lastimage',__('Last images'),array('tplGallery','lastimgWidget'));
		$widgets->lastimage->setting('title',__('Title:'),'');
		$widgets->lastimage->setting('limit',__('Limit (empty means no limit):'),'5');
		$widgets->lastimage->setting('homeonly',__('Home page only'),1,'check');
	}
}
