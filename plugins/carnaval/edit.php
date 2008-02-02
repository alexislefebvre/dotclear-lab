<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$id = $_REQUEST['id'];

try {
	$rs = $carnaval->getClass($id);
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

if (!$core->error->flag() && $rs->isEmpty()) {
	$core->error->add(__('No such Class'));
} else {
	$comment_author = $rs->comment_author;
	$comment_author_mail = $rs->comment_author_mail;
	$comment_author_site = $rs->comment_author_site;
	$comment_class = $rs->comment_class;
}

# Update a link
if (isset($rs) && !empty($_POST['edit_class']))
{
	$comment_author = $_POST['comment_author'];
	$comment_author_mail = $_POST['comment_author_mail'];
	$comment_author_site = $_POST['comment_author_site'];
	$comment_class = $_POST['comment_class'];
	
		
	try {
		$carnaval->updateClass($id,$comment_author,$comment_author_mail,$comment_author_site,$comment_class);
		http::redirect($p_url.'&edit=1&id='.$id.'&upd=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
  <title>Carnaval</title>
</head>

<body>
<?php echo '<p><a href="'.$p_url.'">'.__('Return to Carnaval').'</a></p>'; ?>

<?php

if (isset($rs))
{
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('CSS Class has been successfully updated').'</p>';
	}
	
	echo
	'<form action="plugin.php" method="post">'.
	'<fieldset class="two-cols"><legend>'.__('Edit Class').'</legend>'.
	
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Name:').' '.
	form::field('comment_author',30,255,html::escapeHTML($comment_author)).'</label></p>'.
	
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Mail:').' '.
	form::field('comment_author_mail',30,255,html::escapeHTML($comment_author_mail)).'</label></p>'.
	
	'<p class="col"><label>'.__('URL:').' '.
	form::field('comment_author_site',30,255,html::escapeHTML($comment_author_site)).'</label></p>'.
	
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('CSS Class:').' '.
	form::field('comment_class',30,255,html::escapeHTML($comment_class)).'</label></p>'.
	
	'<p>'.form::hidden('p','carnaval').
	form::hidden('edit',1).
	form::hidden('id',$id).
	$core->formNonce().
	'<input type="submit" name="edit_class" class="submit" value="'.__('save').'"/></p>'.
	'</fieldset>'.
	
	'</form>';
}
?>
</body>
</html>