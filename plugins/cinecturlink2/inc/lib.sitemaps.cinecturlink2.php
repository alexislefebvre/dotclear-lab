<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class sitemapsCinecturlink2
{
	public static function sitemapsDefineParts($map_parts)
	{
		$map_parts->offsetSet(__('Cineturlink'),'cinecturlink2');
	}
	public static function sitemapsURLsCollect($sitemaps)
	{
		global $core;

		if ($core->plugins->moduleExists('cinecturlink2') && $core->blog->settings->sitemaps_cinecturlink2_url)
		{
			$freq = $sitemaps->getFrequency($core->blog->settings->sitemaps_cinecturlink2_fq);
			$prio = $sitemaps->getPriority($core->blog->settings->sitemaps_cinecturlink2_pr);
			$base = $core->blog->url.$core->url->getBase('cinecturlink2');

			$sitemaps->addEntry($base,$prio,$freq);

			$C2 = new cinecturlink2($core);
			$cats = $C2->getCategories();
			while ($cats->fetch()) {
				$sitemaps->addEntry($base."/".$core->blog->settings->cinecturlink2_public_caturl.'/'.urlencode($cats->cat_title),$prio,$freq);
			}
		}
	}
}
?>