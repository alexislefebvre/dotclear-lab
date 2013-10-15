<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of DotClear.
#
# Plugin Bloganniv by Francis Trautmann
# Contributor: Pierre Van Glabeke
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('blogAnnivWidgets','initWidgets'));

class blogAnnivWidgets
{
	public static function initWidgets($w)
	{
		global $core;
		$w->create('blogAnniv',__('Blog Anniv'),array('tplBlogAnniv','BlogAnnivWidget'));
		$w->blogAnniv->setting('title',__('Title :'),'');
		$w->blogAnniv->setting('ftdatecrea',__('Born Date (dd/mm/yyyy) or blank:'),'');
		$w->blogAnniv->setting('dispyearborn',__('Display Born Date'),1,'check');
		$w->blogAnniv->setting('dispyear',__('Display Year(s) Old'),1,'check');
		$w->blogAnniv->setting('homeonly',__('Display on:'),0,'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
				)
		);
    $w->blogAnniv->setting('content_only',__('Content only'),0,'check');
    $w->blogAnniv->setting('class',__('CSS class:'),'');
	}
}

?>