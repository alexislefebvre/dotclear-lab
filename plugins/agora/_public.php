<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012 Osku and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

// Template directory :
$core->addBehavior('publicBeforeDocument',array('agorapublicBehaviors','publicBeforeDocument'));

if ($core->blog->settings->agora->private_flag)
{
	$core->addBehavior('publicBeforeDocument',array('urlAgora','checkAuthHandler'));
}
// Behaviors
// Login page : display recover and register links :
$core->addBehavior('publicLoginFormAfter',array('agorapublicBehaviors','publicLoginFormAfter'));

// New and edit post : display categories and selected check
$core->addBehavior('publicEditEntryFormBefore',array('agorapublicBehaviors','publicEntryFormBefore'));
$core->addBehavior('publicEntryFormBefore',array('agorapublicBehaviors','publicEntryFormBefore'));
$core->addBehavior('publicEntryPreviewBeforeContent',array('agorapublicBehaviors','publicEntryPreviewBeforeContent'));
// Preview category and selected flag on public page 
$core->addBehavior('publicBeforePostPreview',array('agorapublicBehaviors','publicBeforePostPreview'));
// Save category id and selected flag in database :
$core->addBehavior('publicBeforePostCreate',array('agorapublicBehaviors','publicBeforePostUpdate'));
$core->addBehavior('publicBeforePostUpdate',array('agorapublicBehaviors','publicBeforePostUpdate'));

// Profile and Preferences nickname field display
$core->addBehavior('publicPreferencesFormBefore',array('agorapublicBehaviors','publicPreferencesFormBefore'));
// Profile password, description and avatar fields display
$core->addBehavior('publicPreferencesFormAfter',array('agorapublicBehaviors','publicPreferencesFormAfter'));
// Profile and preferences description display
//$core->addBehavior('publicProfileContentAfter',array('agorapublicBehaviors','publicPreferencesContentAfter'));
//$core->addBehavior('publicPreferencesContentAfter',array('agorapublicBehaviors','publicPreferencesContentAfter'));
// Save nickname, description and avatar
$core->addBehavior('publicBeforeUserUpdate',array('agorapublicBehaviors','publicBeforeUserUpdate'));


if ($core->blog->settings->agora->agora_flag)
{
	// Avatars behaviors :
	if ((string)$core->blog->settings->agora->avatar >= 1)
	{
		$core->addBehavior('publicPreferencesBeforeContent',array('agorapublicBehaviors','publicPreferencesBeforeContent'));
		if ((string)$core->blog->settings->agora->avatar >= 2)
		{
			$core->addBehavior('publicProfileBeforeContent',array('agorapublicBehaviors','publicPreferencesBeforeContent'));
			$core->addBehavior('publicEntryBeforeContent',array('agorapublicBehaviors','publicEntryBeforeContent'));
			$core->addBehavior('publicMessageBeforeContent',array('agorapublicBehaviors','publicMessageBeforeContent'));
		}
	}
	// Status post info display 
	if ($core->blog->settings->agora->modo_links)
	{
		$core->addBehavior('publicEntryAfterContent',array('agorapublicBehaviors','publicEntryAfterContent'));
		$core->addBehavior('publicMessageAfterContent',array('agorapublicBehaviors','publicMessageAfterContent'));
	}

	$core->addBehavior('publicHeadContent',array('agorapublicBehaviors','markItUpCSS'));
	$core->addBehavior('publicFooterContent',array('agorapublicBehaviors','markItUpJS'));
}

// New attribut messagesActive.
$core->addBehavior('tplIfConditions',array('agorapublicBehaviors','tplIfConditions'));
$core->addBehavior('templateBeforeBlock',array('agorapublicBehaviors','templateBeforeBlock'));

// Need tests and review :
//$core->addBehavior('templateCustomSortByAlias',array('agorapublicBehaviors','templateCustomSortByAlias'));

// Manage user connection : check cookie and delete all old sessions
$core->addBehavior('publicPrepend',array('agoraBehaviors','sessionHandler'));
$core->addBehavior('publicAfterDocument',array('agoraBehaviors','sessionCleaner'));

if ($core->blog->settings->agora->trig_date) {
	// We update post published date when a published message is created :
	$core->addBehavior('publicAfterMessageCreate',array('agorapublicBehaviors','updPubDatePost'));
}

// Update messages entry count
$core->addBehavior('publicAfterMessageCreate',array('agorapublicBehaviors','countMessages'));

//------------------------------------------------------------------------
// tpl blocks & values
//------------------------------------------------------------------------

// System Tpl
$core->tpl->addBlock('SysIfAgoraMessage',array('agoraTemplate','SysIfAgoraMessage'));
$core->tpl->addBlock('SysIfNoAgoraMessage',array('agoraTemplate','SysIfNoAgoraMessage'));
$core->tpl->addValue('SysAgoraMessage',array('agoraTemplate','SysAgoraMessage'));
//$core->tpl->addValue('SysUserSearchString',array('agoraTemplate','SysUserSearchString'));

// URLs
$core->tpl->addValue('RegisterURL',array('agoraTemplate','registerURL'));
$core->tpl->addValue('RecoverURL',array('agoraTemplate','recoverURL'));
$core->tpl->addValue('LoginURL',array('agoraTemplate','loginURL'));
$core->tpl->addValue('ProfileURL',array('agoraTemplate','profileURL'));
$core->tpl->addValue('LogoutURL',array('agoraTemplate','logoutURL'));
$core->tpl->addValue('PreferencesURL',array('agoraTemplate','preferencesURL'));

// Register page
$core->tpl->addBlock('IfRegisterPreview',array('agoraTemplate','IfRegisterPreview'));
$core->tpl->addValue('RegisterPreviewLogin',array('agoraTemplate','RegisterPreviewLogin'));
$core->tpl->addValue('RegisterPreviewEmail',array('agoraTemplate','RegisterPreviewEmail'));

// Recovery page
$core->tpl->addValue('RecoverLogin',array('agoraTemplate','RecoverLogin'));
$core->tpl->addValue('RecoverEmail',array('agoraTemplate','RecoverEmail'));

// category loop new post
$core->tpl->addValue('CategoryNewEntryLink',array('agoraTemplate','categoryNewEntryLink'));

// Entry/post loop
$core->tpl->addValue('EntryMessageCount',array('agoraTemplate','EntryMessageCount'));
$core->tpl->addValue('EntryUserAvatar',array('agoraTemplate','UserAvatar'));
$core->tpl->addBlock('IfEntryPreview',array('agoraTemplate','IfEntryPreview'));
$core->tpl->addValue('EntryPreviewTitle',array('agoraTemplate','EntryPreviewTitle'));
$core->tpl->addValue('EntryPreviewContent',array('agoraTemplate','EntryPreviewContent'));
$core->tpl->addValue('EntryUserProfileURL',array('agoraTemplate','EntryUserProfileURL'));
$core->tpl->addValue('EntryEditURL',array('agoraTemplate','EntryEditURL'));
$core->tpl->addValue('EntryUnpublishURL',array('agoraTemplate','EntryUnpublishURL'));
$core->tpl->addValue('EntryPublishURL',array('agoraTemplate','EntryPublishURL'));
$core->tpl->addValue('EntryStatus',array('agoraTemplate','EntryStatus'));
$core->tpl->addValue('EntryIfAuthMe',array('agoraTemplate','EntryIfAuthMe'));

// Edit post page
$core->tpl->addValue('EntryEditTitle',array('agoraTemplate','EntryEditTitle'));
$core->tpl->addValue('EntryEditExcerpt',array('agoraTemplate','EntryEditExcerpt'));
$core->tpl->addValue('EntryEditContent',array('agoraTemplate','EntryEditContent'));
$core->tpl->addBlock('SysIfEntryPublished',array('agoraTemplate','SysIfEntryPublished'));
$core->tpl->addBlock('SysIfEntryPending',array('agoraTemplate','SysIfEntryPending'));
$core->tpl->addBlock('SysIfEntryUpdated',array('agoraTemplate','SysIfEntryUpdated'));
// Tread action modo suffixe

// Messages loop
$core->tpl->addBlock('Messages',array('agoraTemplate','Messages'));
$core->tpl->addValue('MessageEntryURL',array('agoraTemplate','MessageEntryURL'));
$core->tpl->addBlock('MessagesHeader',array('agoraTemplate','MessagesHeader'));
$core->tpl->addBlock('MessagesFooter',array('agoraTemplate','MessagesFooter'));
$core->tpl->addBlock('MessageIf',array('agoraTemplate','MessageIf'));
$core->tpl->addValue('MessageIfFirst',array('agoraTemplate','MessageIfFirst'));
$core->tpl->addValue('MessageIfOdd',array('agoraTemplate','MessageIfOdd'));
$core->tpl->addValue('MessageIfMe',array('agoraTemplate','MessageIfMe'));
$core->tpl->addValue('MessageIfAuthMe',array('agoraTemplate','MessageIfAuthMe'));
$core->tpl->addValue('MessageEditURL',array('agoraTemplate','MessageEditURL'));
$core->tpl->addValue('MessageUnpublishURL',array('agoraTemplate','MessageUnpublishURL'));
$core->tpl->addValue('MessagePublishURL',array('agoraTemplate','MessagePublishURL'));
$core->tpl->addValue('MessageStatus',array('agoraTemplate','MessageStatus'));
$core->tpl->addValue('MessageContent',array('agoraTemplate','MessageContent'));
$core->tpl->addValue('MessageID',array('agoraTemplate','MessageID'));
$core->tpl->addValue('MessageOrderNumber',array('agoraTemplate','MessageOrderNumber'));
$core->tpl->addValue('MessageAuthorLink',array('agoraTemplate','MessageAuthorLink'));
$core->tpl->addValue('MessageAuthorID',array('agoraTemplate','MessageAuthorID'));
$core->tpl->addValue('MessageAuthorCommonName',array('agoraTemplate','MessageAuthorCommonName'));
$core->tpl->addValue('MessageDate',array('agoraTemplate','MessageDate'));
$core->tpl->addValue('MessageTime',array('agoraTemplate','MessageTime'));
$core->tpl->addBlock('IfMessagePreview',array('agoraTemplate','IfMessagePreview'));
$core->tpl->addValue('MessagePreviewContent',array('agoraTemplate','MessagePreviewContent'));
$core->tpl->addValue('MessageEditContent',array('agoraTemplate','MessageEditContent'));
$core->tpl->addValue('MessageUserProfileURL',array('agoraTemplate','MessageUserProfileURL'));
$core->tpl->addValue('MessageEntryTitle',array('agoraTemplate','MessageEntryTitle'));
$core->tpl->addValue('MessageFeedID',array('agoraTemplate','MessageFeedID'));
$core->tpl->addValue('MessageIfModerator',array('agoraTemplate','MessageIfModerator'));
$core->tpl->addValue('MessageUserAvatar',array('agoraTemplate','UserAvatar'));
$core->tpl->addBlock('SysIfMessagePublished',array('agoraTemplate','SysIfMessagePublished'));
$core->tpl->addBlock('SysIfMessagePending',array('agoraTemplate','SysIfMessagePending'));
$core->tpl->addBlock('SysIfMessageUpdated',array('agoraTemplate','SysIfMessageUpdated'));

// Authenticated user : $core->auth->userID() SysAuthUserID
// to be deprecated : SysIfAuthenticated & SysIfNotAuthenticated
// Use <tpl:SysIf is_auth="1">
$core->tpl->addBlock('SysIfAuthenticated',array('agoraTemplate','authForm'));
$core->tpl->addBlock('SysIfNotAuthenticated',array('agoraTemplate','notauthForm'));
$core->tpl->addValue('SysAuthUserID',array('agoraTemplate','PublicUserID'));
$core->tpl->addValue('SysAuthUserDisplayName',array('agoraTemplate','PublicUserDisplayName'));
$core->tpl->addValue('SysAuthUserAvatar',array('agoraTemplate','PublicUserAvatar'));

// Users loop
$core->tpl->addBlock('Users',array('agoraTemplate','Users'));
$core->tpl->addBlock('UserIsModo',array('agoraTemplate','userIsModo'));
$core->tpl->addValue('UserID',array('agoraTemplate','UserID'));
$core->tpl->addValue('UserDisplayName',array('agoraTemplate','UserDisplayName'));
$core->tpl->addValue('UserURL',array('agoraTemplate','UserURL'));
$core->tpl->addValue('UserEmail',array('agoraTemplate','UserEmail'));
$core->tpl->addValue('UserDate',array('agoraTemplate','UserDate'));
$core->tpl->addValue('UserTime',array('agoraTemplate','UserTime'));
$core->tpl->addValue('UserIfModerator',array('agoraTemplate','UserIfModerator'));
$core->tpl->addBlock('UsersHeader',array('agoraTemplate','UsersHeader'));
$core->tpl->addBlock('UsersFooter',array('agoraTemplate','UsersFooter'));
$core->tpl->addValue('UserIfOdd',array('agoraTemplate','UserIfOdd'));
$core->tpl->addBlock('UserIf',array('agoraTemplate','UserIf'));
$core->tpl->addValue('UserStats',array('agoraTemplate','UserStats'));
$core->tpl->addValue('UserCommonName',array('agoraTemplate','UserCommonName'));
$core->tpl->addValue('UserProfileURL',array('agoraTemplate','UserProfileURL'));
$core->tpl->addValue('UserDesc',array('agoraTemplate','UserDesc'));
$core->tpl->addValue('UserEntriesCount',array('agoraTemplate','UserEntriesCount'));
$core->tpl->addValue('UserAllEntriesCount',array('agoraTemplate','UserAllEntriesCount'));
$core->tpl->addValue('UserMessagesCount',array('agoraTemplate','UserMessagesCount'));
$core->tpl->addValue('UserAvatar',array('agoraTemplate','UserAvatar'));
$core->tpl->addBlock('SysIfUserUpdated',array('agoraTemplate','SysIfUserUpdated'));


?>
