<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dctribune, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku  and contributors
# Many thanks to Pep, Tomtom and JcDenis
# Originally from Antoine Libert
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$id = $_REQUEST['id'];

try {
	$rs = $core->tribune->getOneMsg($id);
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

if (!$core->error->flag() && $rs->isEmpty()) {
	$core->error->add(__('No such message'));
} else {
	$tribune_nick = $rs->tribune_nick;
	$tribune_msg = $rs->tribune_msg;
}

if (isset($rs) && !empty($_POST['edit_message']))
{
	$tribune_nick = $_POST['tribune_nick'];
	$tribune_msg = $_POST['tribune_msg'];
	$cur = $core->con->openCursor($core->prefix.'tribune');
	$cur->tribune_nick = $_POST['tribune_nick'];
	$cur->tribune_msg = $_POST['tribune_msg'];	
	try {
		$core->tribune->updateMsg($id,$cur);
		http::redirect($p_url.'&edit=1&id='.$id.'&upd=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
	<title><?php echo __('Free chatbox'); ?></title>
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &rsaquo; <a class="button" href="<?php echo $p_url;?>"><?php echo __('Free chatbox'); ?></a> &rsaquo; <?php echo __('Edit message'); ?> </h2>
<?php 
if (isset($rs))
{
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Message has been successfully updated').'</p>';
	}
	
	echo
		'<form action="plugin.php" method="post">'.
		'<fieldset><legend>'.__('Edit message').'</legend>'.
	
		'<p><label class="required" title="'.__('Required field').'">'.__('Nick:').' '.
		form::field('tribune_nick',30,255,html::escapeHTML($tribune_nick)).'</label></p>'.
	
		'<p class="area"><label class="required" title="'.__('Required field').'">'.__('Message:').' '.
		form::textarea('tribune_msg',50,3,html::escapeHTML($tribune_msg)).'</label></p>'.
	
		'<p>'.form::hidden('p','dctribune').
		form::hidden('edit',1).
		form::hidden('id',$id).
		$core->formNonce().
		'<input type="submit" name="edit_message" class="submit" value="'.__('save').'"/></p>'.
		'</fieldset>'.
		'</form>'
		;
}
?>
</body>
</html>
