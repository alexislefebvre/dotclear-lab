<?php
/***************************************************************\
 *  This is 'Dcom', a plugin for Dotclear 2                    *
 *                                                             *
 *  Copyright (c) 2007                                         *
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
			$p = array();
		}
		
		if (!isset($p['homeonly'])) {
			$p['homeonly'] = false;
		}
		else {
			$p['homeonly'] = (bool) $p['homeonly'];
		}
		
		if (!isset($p['c_limit'])) {
			$p['c_limit'] = 10;
		}
		else {
			$p['c_limit'] = abs((integer) $p['c_limit']);
		}
		
		if (!isset($p['t_limit'])) {
			$p['t_limit'] = 20;
		}
		else {
			$p['t_limit'] = abs((integer) $p['t_limit']);
		}
		
		if (!isset($p['co_limit'])) {
			$p['co_limit'] = 80;
		}
		else {
			$p['co_limit'] = abs((integer) $p['co_limit']);
		}
		
		if (!isset($p['title'])) {
			$p['title'] = __('Last comments');
		}
		
		if (empty($p['stringformat'])) {
			$p['stringformat'] =
				'<a href="%5$s" title="%4$s">%2$s - %3$s<br/>%1$s</a>';
		}
		
		if (empty($p['dateformat'])) {
			$p['dateformat'] =
				$core->blog->settings->date_format.','.
				$core->blog->settings->time_format;
		}
	}
}
?>
