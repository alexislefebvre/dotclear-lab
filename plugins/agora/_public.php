<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku ,Tomtom and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');

$core->addBehavior('publicLoginFormAfter',array('agorapublicBehaviors','publicLoginFormAfter'));
$core->addBehavior('agoraGetMessages',array('agoraBehaviors','agoraGetMessages'));
$core->addBehavior('templateBeforeBlock',array('agorapublicBehaviors','templateBeforeBlock'));
//$core->addBehavior('templateCustomSortByAlias',array('agorapublicBehaviors','templateCustomSortByAlias'));

$core->addBehavior('coreBlogGetPosts',array('agoraBehaviors','coreBlogGetPosts'));
$core->addBehavior('publicBeforeDocument',array('agoraBehaviors','autoLogIn'));
$core->addBehavior('publicBeforeDocument',array('agoraBehaviors','cleanSession'));

//Admin announce set
$core->tpl->addValue('agoraAnnounce',array('agoraTemplate','agoraAnnounce'));
$core->tpl->addBlock('SysIfAgoraMessage',array('agoraTemplate','SysIfAgoraMessage'));
$core->tpl->addBlock('SysIfNoAgoraMessage',array('agoraTemplate','SysIfNoAgoraMessage'));
$core->tpl->addValue('SysAgoraMessage',array('agoraTemplate','SysAgoraMessage'));

// URLs
$core->tpl->addValue('agoraURL',array('agoraTemplate','agoraURL'));
$core->tpl->addValue('registerURL',array('agoraTemplate','registerURL'));
$core->tpl->addValue('recoverURL',array('agoraTemplate','recoverURL'));
$core->tpl->addValue('loginURL',array('agoraTemplate','loginURL'));
$core->tpl->addValue('profileURL',array('agoraTemplate','profileURL'));
$core->tpl->addValue('logoutURL',array('agoraTemplate','logoutURL'));
$core->tpl->addValue('AgoraFeedURL',array('agoraTemplate','AgoraFeedURL'));
$core->tpl->addValue('placeFeedURL',array('agoraTemplate','placeFeedURL'));

// Register page
$core->tpl->addBlock('IfRegisterPreview',array('agoraTemplate','IfRegisterPreview'));
$core->tpl->addValue('RegisterPreviewLogin',array('agoraTemplate','RegisterPreviewLogin'));
$core->tpl->addValue('RegisterPreviewEmail',array('agoraTemplate','RegisterPreviewEmail'));

// Recovery page
$core->tpl->addValue('RecoverLogin',array('agoraTemplate','RecoverLogin'));
$core->tpl->addValue('RecoverEmail',array('agoraTemplate','RecoverEmail'));

// places loop
$core->tpl->addValue('placeURL',array('agoraTemplate','placeURL'));
$core->tpl->addValue('placeThreadsNumber',array('agoraTemplate','placeThreadsNumber'));
$core->tpl->addValue('placeAnswersNumber',array('agoraTemplate','placeAnswersNumber'));
$core->tpl->addValue('placeNewThreadLink',array('agoraTemplate','placeNewThreadLink'));
$core->tpl->addValue('placeID',array('agoraTemplate','placeID'));
$core->tpl->addValue('placeSpacer',array('agoraTemplate','placeSpacer'));
$core->tpl->addBlock('placeComboSelected',array('agoraTemplate','placeComboSelected'));

// Pagination plus (getMessages)
/*$core->tpl->addBlock('agoPagination',array('agoraTemplate','agoPagination'));
$core->tpl->addValue('agoPaginationCounter',array('agoraTemplate','agoPaginationCounter'));
$core->tpl->addValue('agoPaginationCurrent',array('agoraTemplate','agoPaginationCurrent'));
$core->tpl->addBlock('agoPaginationIf',array('agoraTemplate','agoPaginationIf'));
$core->tpl->addValue('agoPaginationURL',array('agoraTemplate','agoPaginationURL'));*/

// Thread loop
//$core->tpl->addBlock('ForumEntries',array('agoraTemplate','ForumEntries'));
$core->tpl->addValue('EntryIfClosed',array('agoraTemplate','EntryIfClosed'));
$core->tpl->addValue('EntryMessageCount',array('agoraTemplate','EntryMessageCount'));
$core->tpl->addValue('EntryCreaDate',array('agoraTemplate','EntryCreaDate'));
$core->tpl->addValue('EntryUpdDate',array('agoraTemplate','EntryUpdDate'));
// Thread loop, place context
$core->tpl->addBlock('IfThreadPreview',array('agoraTemplate','IfThreadPreview'));
$core->tpl->addValue('ThreadPreviewTitle',array('agoraTemplate','ThreadPreviewTitle'));
$core->tpl->addValue('ThreadPreviewContent',array('agoraTemplate','ThreadPreviewContent'));
//$core->tpl->addValue('ThreadURL',array('agoraTemplate','ThreadURL'));
$core->tpl->addValue('ThreadCategoryURL',array('agoraTemplate','ThreadCategoryURL'));
$core->tpl->addValue('MessageThreadURL',array('agoraTemplate','MessageThreadURL'));
$core->tpl->addValue('ThreadProfileUserID',array('agoraTemplate','ThreadProfileUserID'));
$core->tpl->addBlock('ThreadComboSelected',array('agoraTemplate','ThreadComboSelected'));
// Thread loop, thread context
$core->tpl->addBlock('IfAnswerPreview',array('agoraTemplate','IfAnswerPreview'));
$core->tpl->addValue('AnswerPreviewContent',array('agoraTemplate','AnswerPreviewContent'));
$core->tpl->addBlock('IfEditPreview',array('agoraTemplate','IfEditPreview'));
//$core->tpl->addBlock('IfIsThread',array('agoraTemplate','IfIsThread'));
$core->tpl->addValue('PostEditTitle',array('agoraTemplate','PostEditTitle'));
$core->tpl->addValue('PostEditContent',array('agoraTemplate','PostEditContent'));
$core->tpl->addValue('AnswerOrderNumber',array('agoraTemplate','AnswerOrderNumber'));
$core->tpl->addBlock('SysIfThreadUpdated',array('agoraTemplate','SysIfThreadUpdated'));
// Tread action modo suffixe
$core->tpl->addValue('ModerationDeleteThread',array('agoraTemplate','ModerationDeleteThread'));
$core->tpl->addValue('ModerationEditThread',array('agoraTemplate','ModerationEditThread'));
$core->tpl->addValue('ModerationDeleteMessage',array('agoraTemplate','ModerationDeleteMessage'));
$core->tpl->addValue('ModerationEditMessage',array('agoraTemplate','ModerationEditMessage'));
$core->tpl->addValue('ModerationPin',array('agoraTemplate','ModerationPin'));
$core->tpl->addValue('ModerationUnpin',array('agoraTemplate','ModerationUnpin'));
$core->tpl->addValue('ModerationClose',array('agoraTemplate','ModerationClose'));
$core->tpl->addValue('ModerationOpen',array('agoraTemplate','ModerationOpen'));

// Messages = answers to threads
$core->tpl->addBlock('Messages',array('agoraTemplate','Messages'));
$core->tpl->addBlock('MessagesHeader',array('agoraTemplate','MessagesHeader'));
$core->tpl->addBlock('MessagesFooter',array('agoraTemplate','MessagesFooter'));
$core->tpl->addValue('MessageIfFirst',array('agoraTemplate','MessageIfFirst'));
$core->tpl->addValue('MessageIfOdd',array('agoraTemplate','MessageIfOdd'));
$core->tpl->addValue('MessageIfMe',array('agoraTemplate','MessageIfMe'));
$core->tpl->addValue('MessageContent',array('agoraTemplate','MessageContent'));
$core->tpl->addValue('MessageID',array('agoraTemplate','MessageID'));
$core->tpl->addValue('MessageOrderNumber',array('agoraTemplate','MessageOrderNumber'));
$core->tpl->addValue('MessageAuthorID',array('agoraTemplate','MessageAuthorID'));
$core->tpl->addValue('MessageAuthorCommonName',array('agoraTemplate','MessageAuthorCommonName'));
$core->tpl->addValue('MessageDate',array('agoraTemplate','MessageDate'));
$core->tpl->addValue('MessageTime',array('agoraTemplate','MessageTime'));
$core->tpl->addValue('MessageCreaDate',array('agoraTemplate','MessageCreaDate'));
$core->tpl->addBlock('IfMessagePreview',array('agoraTemplate','IfMessagePreview'));
$core->tpl->addValue('MessagePreviewContent',array('agoraTemplate','MessagePreviewContent'));
$core->tpl->addValue('MessageEditContent',array('agoraTemplate','MessageEditContent'));
$core->tpl->addValue('MessageProfileUserID',array('agoraTemplate','MessageProfileUserID'));
$core->tpl->addValue('MessageEntryTitle',array('agoraTemplate','MessageEntryTitle'));
$core->tpl->addValue('MessageFeedID',array('agoraTemplate','MessageFeedID'));
//$core->tpl->addValue('',array('agoraTemplate',''));
//$core->tpl->addValue('',array('agoraTemplate',''));

// User 
$core->tpl->addBlock('authForm',array('agoraTemplate','authForm'));
$core->tpl->addBlock('notauthForm',array('agoraTemplate','notauthForm'));
$core->tpl->addValue('PublicUserID',array('agoraTemplate','PublicUserID'));
$core->tpl->addValue('PublicUserDisplayName',array('agoraTemplate','PublicUserDisplayName'));
$core->tpl->addBlock('userIsModo',array('agoraTemplate','userIsModo'));
$core->tpl->addValue('ProfileUserID',array('agoraTemplate','ProfileUserID'));
$core->tpl->addValue('ProfileUserDisplayName',array('agoraTemplate','ProfileUserDisplayName'));
$core->tpl->addValue('ProfileUserURL',array('agoraTemplate','ProfileUserURL'));
$core->tpl->addValue('ProfileUserEmail',array('agoraTemplate','ProfileUserEmail'));
$core->tpl->addValue('ProfileUserCreaDate',array('agoraTemplate','ProfileUserCreaDate'));
$core->tpl->addValue('ProfileUserUpdDate',array('agoraTemplate','ProfileUserUpdDate'));

//$core->tpl->addBlock('',array('agoraTemplate',''));
//$core->tpl->addValue('',array('agoraTemplate',''));


global $_ctx;

$_ctx->agora = new agora($core);
//$_ctx->log = new dcLog($core);

// Behaviors depend on admin settings


?>