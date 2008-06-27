<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Empreinte', a plugin for Dotclear 2               *
 *                                                             *
 *  Copyright (c) 2007,2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Empreinte' (see COPYING.txt);          *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicBeforeCommentCreate',array('publicEmpreinte','publicBeforeCommentCreate'));
$core->addBehavior('coreBlogGetComments',array('publicEmpreinte','coreBlogGetComments'));

$core->tpl->addValue('PluginFileURL',array('tplEmpreinte','PluginFileURL'));
$core->tpl->addValue('CommentCheckNoEmpreinte',array('tplEmpreinte','CommentCheckNoEmpreinte'));
$core->tpl->addBlock('CommentIfUserAgent',array('tplEmpreinte','CommentIfUserAgent'));
$core->tpl->addValue('CommentSystem',array('tplEmpreinte','CommentSystem'));
$core->tpl->addValue('CommentBrowser',array('tplEmpreinte','CommentBrowser'));
$core->tpl->addValue('CommentSystemImg',array('tplEmpreinte','CommentSystemImg'));
$core->tpl->addValue('CommentBrowserImg',array('tplEmpreinte','CommentBrowserImg'));
?>