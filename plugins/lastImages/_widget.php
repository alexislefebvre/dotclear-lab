<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of lastImages, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Philippe Amalgame and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('initWidgets',array('lastImagesBehaviors','initWidgets'));

class lastImagesBehaviors
{
	
	public static function initWidgets(&$w)
	{
		global $core;
		
		$w->create('lastImages',__('Last images'),array('publicLastImages','lastImages'));

		$w->lastImages->setting('title',__('Title:'),__('Last images'));
		$w->lastImages->setting('limit',__('Limit (empty means no limit):'),'3');
		$w->lastImages->setting('category',__('Category list:'),'','text');
		$w->lastImages->setting('homeonly',__('Home page only'),1,'check');
		$w->lastImages->setting('selected',__('Selected posts'),0,'check');
		$w->lastImages->setting('random',__('Random sort'),0,'check');
		$w->lastImages->setting('post_title',__('Display post title'),0,'check');
		
		$w->lastImages->setting('size',__('Image size'),1,'combo',
			array('thumbnail' => 't', 'square' => 'sq', 'small' => 's', 'medium' => 'm', 'original' => 'o'));
	}
}
?>