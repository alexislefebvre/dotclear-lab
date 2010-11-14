<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Copy', a plugin for Dotclear 2                    *
 *                                                             *
 *  Copyright (c) 2010                                         *
 *  Alexandre Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Copy' (see COPYING.txt);               *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */		"Copy",
	/* Description*/	"Adds a button to copy posts",
	/* Author */		"Alexandre Syenchuk",
	/* Version */		"2010.11.1-alpha",
	/* Permissions */	"contentadmin"
);
?>