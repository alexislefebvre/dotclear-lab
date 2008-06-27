<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Live Counter', a plugin for Dotclear 2            *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Live Counter' (see COPYING.txt);       *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('initWidgets',array('adminLiveCounter','initWidget'));

class adminLiveCounter
{
	public static function initWidget(&$w)
	{
		global $core;
		
		$w->create('livecounter',__('Connected visitors'),
			array('publicLiveCounter','showInWidget'));
		$w->livecounter->setting('title',
			__('Title:'),__('Connected visitors'));
		
		$w->livecounter->setting('content',
			__('Content when several users are connected (%1$s = number of visitors, %2$s = list of visitors):'),
			__('%1$s visitors are online : %2$s.'),'textarea');
		$w->livecounter->setting('content_one',
			__('Content when just one visitor is connected (%1$s = number of visitors, %2$s = list of visitors):'),
			__('Hello %2$s, you are lonely on this site.'),'textarea');
		
		$w->livecounter->setting('one_unknown',
			__('When an unknown visitor is connected:'),
			__('an unknown visitor'));
		$w->livecounter->setting('more_unknown',
			__('When several unknown visitors are connected (%s = number of unknown visitors):'),
			__('%s unknown visitors'));
		
		$w->livecounter->setting('timeout',
			__('Timeout (in minutes):'),5);
		$w->livecounter->setting('show_links',
			__('Link connected users to their websites'),true,'check');
		$w->livecounter->setting('homeonly',
			__('Home page only'),false,'check');
	}
}
?>