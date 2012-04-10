<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }
dcPage::check('agora,contentadmin');

$redir_url = $p_url.'&act=messages';
$msg_actions_url = $p_url.'&amp;act=messages-actions';

$message_id = '';
$message_dt = '';
$message_format = $core->auth->getOption('post_format');
$message_content = '';
$message_content_xhtml = '';
$message_status = $core->auth->getInfo('user_post_status');
$message_notes = '';

$page_title = __('New message');

$can_view_page = true;
$can_edit_page = $core->auth->check('page,usage',$core->blog->id);
$can_publish = $core->auth->check('agora,publish,contentadmin',$core->blog->id);
$can_delete = false;

$post_headlink = '<link rel="%s" title="%s" href="'.html::escapeURL($redir_url).'&amp;id=%s" />';
$post_link = '<a href="'.html::escapeURL($redir_url).'&amp;id=%s" title="%s">%s</a>';

$next_link = $prev_link = $next_headlink = $prev_headlink = null;

# If user can't publish
if (!$can_publish) {
	$post_status = -2;
}

# Status combo
foreach ($core->agora->getAllMessageStatus() as $k => $v) {
	$status_combo[$v] = (string) $k;
}

# Formaters combo
foreach ($core->getFormaters() as $v) {
	$formaters_combo[$v] = $v;
}

# Get message informations
if (!empty($_REQUEST['id']))
{
	$params['message_id'] = $_REQUEST['id'];
	
	$message = $core->agora->getMessages($params);
	
	if ($message->isEmpty())
	{
		$core->error->add(__('This message does not exist.'));
		$can_view_page = false;
	}
	else
	{
		$message_id = $message->message_id;
		$post_id = $message->post_id;
		$message_dt = date('Y-m-d H:i',strtotime($message->message_dt));
		$message_format = $message->message_format;
		$message_content = $message->message_content;
		$message_content_xhtml = $message->message_content_xhtml;
		$message_status = $message->message_status;
		$message_notes = $message->message_notes;
		
		$page_title = __('Edit message');
		
	}

	$can_edit = $can_delete = $can_publish = $core->auth->check('contentadmin',$core->blog->id);

	if (!$core->auth->check('contentadmin',$core->blog->id) && $core->auth->userID() == $message->user_id) {
		$can_edit = true;
		if ($core->auth->check('delete',$core->blog->id)) {
			$can_delete = true;
		}
		if ($core->auth->check('publish',$core->blog->id)) {
			$can_publish = true;
		}
	}
}



# Format content
if (!empty($_POST) && $can_edit_page)
{
	$message_format = $_POST['message_format'];
	$message_content = $_POST['message_content'];
	
	if (isset($_POST['message_status'])) {
		$message_status = (integer) $_POST['message_status'];
	}
	
	$message_notes = $_POST['message_notes'];
	
	if (empty($_POST['message_dt'])) {
		$message_dt = '';
	} else {
		$message_dt = strtotime($_POST['message_dt']);
		$message_dt = date('Y-m-d H:i',$message_dt);
	}
	
	$core->agora->setMessageContent(
		$message_id,$message_format,
		$message_content,$message_content_xhtml
	);
}

# Update thread
if (!empty($_POST) && !empty($_POST['save']) && $can_edit_page)
{
	$cur = $core->con->openCursor($core->prefix.'message');
	
	# Magic tweak :)
	//$core->blog->settings->system->post_url_format = $page_url_format;
	
	$cur->message_dt = $message_dt ? date('Y-m-d H:i:00',strtotime($message_dt)) : '';
	$cur->message_format = $message_format;
	$cur->message_content = $message_content;
	$cur->message_content_xhtml = $message_content_xhtml;
	$cur->message_status = $message_status;
	$cur->message_notes = $message_notes;

	# Update message
	if ($message_id)
	{
		try
		{
			# --BEHAVIOR-- adminBeforeMessageUpdate
			$core->callBehavior('adminBeforeMessageUpdate',$cur,$message_id);
			
			$core->agora->updMessage($message_id,$cur);
			
			# --BEHAVIOR-- adminAfterMessageUpdate
			$core->callBehavior('adminAfterMessageUpdate',$cur,$message_id);
			
			http::redirect($redir_url.'&id='.$message_id.'&upd=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
	else
	{
		$cur->user_id = $core->auth->userID();
		
		try
		{
			# --BEHAVIOR-- adminBeforeMessageCreate
			$core->callBehavior('adminBeforeMessageCreate',$cur);
			
			$return_id = $core->agora->addMessage($cur);
			
			# --BEHAVIOR-- adminAfterMessageCreate
			$core->callBehavior('adminAfterMessageCreate',$cur,$return_id);
			
			http::redirect($redir_url.'&id='.$return_id.'&crea=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
}

if (!empty($_POST['delete']) && $can_delete)
{
	try {
		# --BEHAVIOR-- adminBeforePageDelete
		$core->callBehavior('adminBeforeMessageDelete',$message_id);
		$core->agora->delMessage($message_id);
		http::redirect($redir_url);
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

/* DISPLAY
-------------------------------------------------------- */
$default_tab = 'edit-message';
if (!$can_edit_page) {
	$default_tab = '';
}
if (!empty($_GET['me'])) {
	$default_tab = 'messages';
}

?>
<html>
<head>
  <title><?php echo $page_title.' - '.__('Messages'); ?></title>
  <script type="text/javascript">
  //<![CDATA[
  <?php echo dcPage::jsVar('dotclear.msg.confirm_delete_message',__("Are you sure you want to delete this message?")); ?>
  //]]>
  </script>
  <?php echo
  dcPage::jsDatePicker().
  dcPage::jsToolBar().
  dcPage::jsModal().
  //dcPage::jsLoad('js/_post.js').
  dcPage::jsLoad('index.php?pf=agora/js/messages.js').
  dcPage::jsConfirmClose('entry-form','message-form').
  # --BEHAVIOR-- adminMessageHeaders
  $core->callBehavior('adminMessageHeaders').
  dcPage::jsPageTabs($default_tab).
  $next_headlink."\n".$prev_headlink;
  ?>
</head>

<body>

<?php

if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Message has been successfully updated.').'</p>';
}
elseif (!empty($_GET['crea'])) {
		echo '<p class="message">'.__('Message has been successfully created.').'</p>';
}

# XHTML conversion
if (!empty($_GET['xconv']))
{
	$message_content = $message_content_xhtml;
	$message_format = 'xhtml';
	
	echo '<p class="message">'.__('Don\'t forget to validate your XHTML conversion by saving your message.').'</p>';
}

echo '<h2>'.html::escapeHTML($core->blog->name).
' &rsaquo; <a href="'.$redir_url.'">'.__('Messages').'</a> &rsaquo; <span class="page-title">'.$page_title.'<span>';

echo '</h2>';

# Exit if we cannot view thread
if (!$can_view_page) {
	echo '</body></html>';
	return;
}


/* Post form if we can edit post
-------------------------------------------------------- */
if ($can_edit_page)
{
	echo '<div class="multi-part" title="'.__('Edit message').'" id="edit-message">';
	echo '<form action="'.html::escapeURL($redir_url).'" method="post" id="message-form">';
	echo '<div id="entry-sidebar">';
	
	echo
	'<p><label>'.__('Message status:').
	form::combo('message_status',$status_combo,$message_status,'',3,!$can_publish).
	'</label></p>'.
	
	'<p><label>'.__('Published on:').
	form::field('message_dt',16,16,$message_dt,'',3).'</label></p>'.
	
	'<p><label>'.__('Text formating:').
	form::combo('message_format',$formaters_combo,$message_format,'',3).
	($message_id && $message_format != 'xhtml' ? '<a href="'.html::escapeURL($redir_url).'&amp;id='.$message_id.'&amp;xconv=1">'.__('Convert to XHTML').'</a>' : '').
	'</label></p>';
	
	
	# --BEHAVIOR-- adminThreadFormSidebar
	$core->callBehavior('adminMessageFormSidebar',isset($message) ? $message : null);
	
	echo '</div>';		// End #entry-sidebar
	
	echo '<div id="entry-content"><fieldset class="constrained">';
	
	echo
	
	'<p class="area"><label class="required" '.
	'for="message_content"><abbr title="'.__('Required field').'">*</abbr>'.__('Content:').'</label> '.
	form::textarea('message_content',50,$core->auth->getOption('edit_size'),html::escapeHTML($message_content),'',2).
	'</p>'.

	'<p class="area" id="notes-area"><label for="post_notes">'.__('Notes:').'</label>'.
	form::textarea('message_notes',50,5,html::escapeHTML($message_notes)).
	'</p>';
	# --BEHAVIOR-- adminMessageForm
	$core->callBehavior('adminMessageForm',isset($message) ? $message : null);
	
	echo
	'<p>'.
	($message_id ? form::hidden('id',$message_id) : '').
	'<input type="submit" value="'.__('Save').' (s)" tabindex="4" '.
	'accesskey="s" name="save" /> '.
	($can_delete ? '<input type="submit" class="delete" value="'.__('delete').'" name="delete" />' : '').
	$core->formNonce().
	'</p>';
	
	echo '</fieldset></div>';		// End #entry-content
	echo '</form>';
	echo '</div>';
	
	if ($post_id && !empty($post_media))
	{
		echo
		'<form action="post_media.php" id="attachment-remove-hide" method="post">'.
		'<div>'.form::hidden(array('post_id'),$post_id).
		form::hidden(array('media_id'),'').
		form::hidden(array('remove'),1).
		$core->formNonce().'</div></form>';
	}
}
dcPage::helpBlock('core_wiki');
?>
</body>
</html>
