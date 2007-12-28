<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Lite Box', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Lite Box' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$core->addBehavior('templateBeforeValue',array('publicLiteBox','templateBeforeValue'));

class publicLiteBox
{
	public static function templateBeforeValue(&$core,$id,$attr)
	{	
		if ($id == 'include' && isset($attr['src']) && $attr['src'] == '_head.html') {
			return file_get_contents(dirname(__FILE__).'/_litebox.html');
		}
	}
}
?>
