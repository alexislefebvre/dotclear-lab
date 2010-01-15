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

class widgetRateItCinecturlink2
{
	public static function initRank(&$types)
	{
		$types[] = array(__('Cinecturlink2')=>'cinecturlink2');
	}

	public static function parseRank(&$w)
	{
		global $core;
		if ($w->type == 'cinecturlink2') {
			if (!$core->blog->settings->rateit_cinecturlink2_active) {
				$w->type = '';
			} else {
				$sql = $w->sql;
				$sql['columns'][] = $core->con->concat("'".$core->blog->url.$core->url->getBase('cinecturlink2')."/'",'C.link_id').' AS url';
				$sql['columns'][] = 'C.link_title AS title';
				$sql['groups'][] = 'C.link_id';
				$sql['groups'][] = 'C.link_title';
				$sql['from'] .= ' INNER JOIN '.$core->prefix.'cinecturlink2 C ON CAST(C.link_id as char)=RI.rateit_id ';
				$w->sql = $sql;
			}
		}
	}
}
?>