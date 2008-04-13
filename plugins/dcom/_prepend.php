<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Dcom', a plugin for Dotclear 2                    *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk, Jean-François Michaud and contributors.*
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along 'Dcom' (see COPYING.txt);                    *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

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
			: $core->blog->settings->date_format.','.
			  $core->blog->settings->time_format;
	}
}
?>