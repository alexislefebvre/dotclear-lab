<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Arlequin', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007,2011                                    *
 *  Alex Pirine and contributors.                              *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Arlequin' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('widgetsArlequin','widget'));

class widgetsArlequin
{
	public static function widget(&$w)
	{
		$w->create('arlequin',__('Theme switcher'),
			array('publicArlequinInterface','widget'));
		$w->arlequin->setting('title',__('Title:'),
			__('Choose a theme'));
	}
}
?>