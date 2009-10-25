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

$core->addBehavior('publicBeforeCommentPreview',array('publicJavatar','publicBeforeCommentPreview'));
$core->addBehavior('publicBeforeCommentCreate',array('publicJavatar','publicBeforeCommentCreate'));
$core->addBehavior('publicCommentFormAfterContent',array('publicJavatar','publicCommentFormAfterContent'));
$core->addBehavior('publicHeadContent',array('publicJavatar','publicHeadContent'));
$core->addBehavior('publicFooterContent',array('publicJavatar','publicFooterContent'));
$core->addBehavior('coreBlogGetComments',array('publicJavatar','coreBlogGetComments'));
$core->addBehavior('publicCommentBeforeContent',array('publicJavatar','publicCommentBeforeContent'));

$core->tpl->addValue('CommentAuthorJabberMD5',array('tplJavatar','CommentAuthorJabberMD5'));
?>