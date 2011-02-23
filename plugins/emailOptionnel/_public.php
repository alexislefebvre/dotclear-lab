<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Email Optionnel', a plugin for Dotclear.          *
 *                                                             *
 *  Copyright (c) 2007,2008,2011                               *
 *  Alex Pirine and contributors.                              *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Email Optionnel' (see COPYING.txt);    *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicPrepend',
	array('emailOptionnelBehaviors','publicPrepend'));
$core->addBehavior('publicBeforeCommentCreate',
	array('emailOptionnelBehaviors','publicBeforeCommentCreate'));
?>