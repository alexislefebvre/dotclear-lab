<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is Rétrocontrôle, a plugin for Dotclear.              *
 *                                                             *
 *  Copyright (c) 2006-2008                                    *
 *  Oleksandr Syenchuk, Alain Vagner and contributors.         *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with Rétrocontrôle (see COPYING.txt);        *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicBeforeDocument',
	create_function('&$core','$GLOBALS[\'mod_ts\'] = array(time());'));
?>