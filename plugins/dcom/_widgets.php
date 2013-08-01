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
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('adminDcom','initWidget'));

class adminDcom
{
	public static function initWidget($w)
	{
		global $core;
		
		commonDcom::adjustDefaults($p);
		
		$w->create('lastcomments',__('Last comments'),
			array('publicDcom','showWidget'));
		$w->lastcomments->setting('title',
			__('Title:'),$p['title']);
		$w->lastcomments->setting('c_limit',
			__('Comments limit:'),$p['c_limit']);
		$w->lastcomments->setting('t_limit',
			__('Title lenght limit:'),$p['t_limit']);
		$w->lastcomments->setting('co_limit',
			__('Comment lenght limit:'),$p['co_limit']);
		$w->lastcomments->setting('dateformat',
			__('Date format (leave empty to use default blog format):'),$p['dateformat']);
		$w->lastcomments->setting('stringformat',
			__('String format (%1$s = date; %2$s = title; %3$s = author; %4$s = content of the comment; %5$s = comment URL):'),
			$p['stringformat']);
		$w->lastcomments->setting('homeonly',__('Display on:'),0,'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
				)
		);
    $w->lastcomments->setting('content_only',__('Content only'),0,'check');
    $w->lastcomments->setting('class',__('CSS class:'),'');
	}
}
?>
