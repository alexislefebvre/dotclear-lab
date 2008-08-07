<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'My URL handlers', a plugin for Dotclear 2         *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My URL handlers' (see COPYING.txt);    *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */		'My URL handlers',
	/* Description*/	'Changes Dotclear default URL handlers',
	/* Author */		'Oleksandr Syenchuk',
	/* Version */		'2008.08',
	/* Permissions */	'contentadmin',
	/* Priority */		1500
);
?>