<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Dcom, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

class commonDcom
{
	public static function adjustDefaults(&$p)
	{
		global $core;
		
		if (!is_array($p)) {
			$r = array();
		} else {
			$r = $p;
		}
		
		$p = array();
		$p['homeonly'] = empty($r['homeonly'])
			? false
			: true;
		$p['c_limit'] = isset($r['c_limit'])
			? abs((integer) $r['c_limit'])
			: 10;
		$p['t_limit'] = isset($r['t_limit'])
			? abs((integer) $r['t_limit'])
			: 20;
		$p['co_limit'] = !empty($r['co_limit'])
			? abs((integer) $r['co_limit'])
			: 80;
		$p['title'] = isset($r['title'])
			? (string) $r['title']
			: __('Last comments');
		$p['stringformat'] = !empty($r['stringformat'])
			? (string) $r['stringformat']
			: '<a href="%5$s" title="%4$s">%2$s - %3$s<br/>%1$s</a>';
		$p['dateformat'] = !empty($r['dateformat'])
			? (string) $r['dateformat']
			: $core->blog->settings->system->date_format.','.
			  $core->blog->settings->system->time_format;
	}
}
?>