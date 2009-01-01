<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('initWidgets',array('galWidgets','initWidgets'));


class galWidgets 
{
	public static function initWidgets(&$widgets)
	{
		$widgets->create('listgal',__('Galleries'),array('pubWidgetGallery','listgalWidget'));
		$widgets->listgal->setting('title',__('Title:'),'');
		$widgets->listgal->setting('limit',__('Limit (empty means no limit):'),'20');
/*		$widgets->listgal->setting('display',__('Display mode'),'gal_only','combo',
			array(__('Galleries only') => 'gal_only', __('Categories only') => 'cat_only', __('Both') => 'both'));*/
		$widgets->listgal->setting('cat_display',__('Categories display'),'none','combo',
			array(__('No display') => 'none', __('single category') => 'single', __('Category tree') =>'tree', __('Complete path to category') => 'breadcrumb'));
		$widgets->listgal->setting('cat_count',__('Include counters in categories'),1,'check');
		$widgets->listgal->setting('gal_display',__('Galleries display'),'normal','combo',
			array(__('No display') => 'none', __('Normal display') => 'normal'));
		$widgets->listgal->setting('gal_count',__('Include counters in galleries'),1,'check');
		$widgets->listgal->setting('orderby',__('Order by'),'name','combo',
			array(__('Gallery name') => 'name', __('Gallery date') => 'date'));
		$widgets->listgal->setting('orderdir',__('Sort:'),'desc','combo',
			array(__('Ascending') => 'asc', __('Descending') => 'desc'));
		$widgets->listgal->setting('homeonly',__('Home page only'),1,'check');

		$widgets->create('randomimage',__('Random image'),array('pubWidgetGallery','randimgWidget'));
		$widgets->randomimage->setting('title',__('Title:'),'');
		$widgets->randomimage->setting('size',__('Size :'),'t','combo',
			array(__('medium') => 'm', __('small') => 's', __('thumbnail') => 't', __('square') => 'sq'));
		$widgets->randomimage->setting('homeonly',__('Home page only'),1,'check');

		$widgets->create('lastimage',__('Last images'),array('pubWidgetGallery','lastimgWidget'));
		$widgets->lastimage->setting('title',__('Title:'),'');
		$widgets->lastimage->setting('limit',__('Limit (empty means no limit):'),'5');
		$widgets->lastimage->setting('homeonly',__('Home page only'),1,'check');

		$widgets->create('imgmeta',__('Image Metadata'),array('pubWidgetGallery','imageMetaWidget'));
		$widgets->imgmeta->setting('title',__('Title:'),'');
	}
}
