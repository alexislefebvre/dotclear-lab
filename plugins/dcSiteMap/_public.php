<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2010 Gaetan Guillard and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->url->register('dcsitemap','sitemap','^sitemap(?:/(.+))?$',array('urlSiteMap','sitemap'));

$core->tpl->addValue('SiteMapURL',array('tplSiteMap','SiteMapURL'));
$core->tpl->addValue('SiteMapPageTitle',array('tplSiteMap','SiteMapPageTitle'));


class urlSiteMap extends dcUrlHandlers
{
	public static function sitemap($args)
	{
		global $core;

		if (!$core->blog->settings->dcsitemap->dsm_flag) {
			self::p404();
			exit;
		}
				
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('site_map.html');
		exit;
	}
}

class tplSiteMap
{
	public static function SiteMapURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("dcsitemap")').'; ?>';
	}
	
	public static function SiteMapPageTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->dcsitemap->dsm_title').'; ?>';
	}
			
	# Widget function
	public static function dcSiteMapWidget($w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$res =
		'<div class="sitemap">'.
		'<h2><a href="'.$core->blog->url.$core->url->getBase('dcsitemap').'">'.
		($w->title ? html::escapeHTML($w->title) : __('Site map')).
		'</a></h2>'.
		'</div>';
		
		return $res;
	}
}
?>