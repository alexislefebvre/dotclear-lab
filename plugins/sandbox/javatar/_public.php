<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Javatar', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Javatar' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicHeadContent',array('publicJavatar','publicHeadContent'));
$core->addBehavior('publicBeforeCommentCreate',array('publicJavatar','publicBeforeCommentCreate'));
$core->addBehavior('coreBlogGetComments',array('publicJavatar','coreBlogGetComments'));
$core->tpl->addValue('CommentJID',array('tplJavatar','CommentJID'));
$core->tpl->addValue('CommentPreviewJID',array('tplJavatar','CommentPreviewJID'));
$core->tpl->addValue('JavatarSize',array('tplJavatar','JavatarSize'));
$core->tpl->addValue('JavatarImgDefaut',array('tplJavatar','JavatarImgDefaut'));
$core->tpl->addValue('CommentAuthorJavatar',array('tplJavatar','CommentAuthorJavatar'));
$core->tpl->addValue('FormAuthorJavatar',array('tplJavatar','FormAuthorJavatar'));
?>