<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is Jabber Notifications, a plugin for Dotclear 2      *
 *                                                             *
 *  Copyright (c) 2007,2008,2011                               *
 *  Alex Pirine, Olivier Tétard and contributors.              *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with Jabber Notifications (see COPYING.txt); *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */		'Jabber Notifications',
	/* Description*/	'Jabber notifications for new comments',
	/* Author */		'Alex Pirine',
	/* Version */		'2011.02',
	/* Permissions */	'admin,usage,publish,delete,contentadmin,categories,media,media_admin'
);
?>