<?php
# ***** BEGIN LICENSE BLOCK *****
#
# Tribune Libre is a small chat system for Dotclear 2
# Copyright (C) 2007  Antoine Libert
# 
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$id = $_REQUEST['id'];

try {
	$rs = $tribune->getOneMsg($id);
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
	
	try {
		$tribune->updateMsg($id,$tribune_nick,$tribune_msg);
		http::redirect($p_url.'&edit=1&id='.$id.'&upd=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
	<title>Tribune Libre</title>
</head>

<body>
<?php echo '<p><a href="'.$p_url.'">'.__('Return to Tribune Libre').'</a></p>';

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
	
		'<p><label class="required" title="'.__('Required field').'">'.__('Message:').' '.
		form::field('tribune_msg',30,255,html::escapeHTML($tribune_msg)).'</label></p>'.
	
		'<p>'.form::hidden('p','tribune').
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
