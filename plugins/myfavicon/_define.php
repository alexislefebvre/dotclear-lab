<?php
/***************************************************************\
 *  This is 'My Favicon', a plugin for Dotclear 2              *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My Favicon' (see COPYING.txt);         *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */		"My Favicon",
	/* Description*/	"Add a customized favicon to your blog",
	/* Author */		"Oleksandr Syenchuk, Pierre Van Glabeke",
	/* Version */		'0.8',
	/* Properties */
	array(
		'permissions' => 'contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://forum.dotclear.org/viewtopic.php?pid=323172#p323172',
		'details' => 'http://plugins.dotaddict.org/dc2/details/myfavicon'
	)
);