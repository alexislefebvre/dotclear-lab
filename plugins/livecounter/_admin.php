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

$core->addBehavior('initWidgets',array('adminLiveCounter','initWidget'));

class adminLiveCounter
{
	public static function initWidget(&$w)
	{
		global $core;
		
		$w->create('livecounter',__('Connected visitors'),
			array('publicLiveCounter','showInWidget'));
		$w->livecounter->setting('title',
			__('Title:'),__('Connected people'));
		$w->livecounter->setting('content',
			__('Content (%s = number of connected visitors):'),
			'<p>'.__('%s visitors are online.').'</p>');
		$w->livecounter->setting('content_one',
			__('Content if a single visitor is online:'),
			'<p>'.__('You are the only one connected to this site.').'</p>');
		$w->livecounter->setting('homeonly',
			__('Home page only'),false,'check');
		$w->livecounter->setting('expires',
			__('Expiration time'),5);
	}
}
?>
