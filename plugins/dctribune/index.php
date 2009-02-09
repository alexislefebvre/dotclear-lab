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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!empty($_REQUEST['edit']) && !empty($_REQUEST['id'])) {
	include dirname(__FILE__).'/edit.php';
	return;
}

$default_tab = '';
$combo_action = array();
$combo_action[__('publish')] = 'publish';
$combo_action[__('unpublish')] = 'unpublish';
$combo_action[__('delete')] = 'delete';

if (!empty($_POST['actiontribune']) && !empty($_POST['checked']))
{
	switch ($_POST['actiontribune']) {
	case 'publish' : $status = 1; break;
	case 'unpublish' : $status = 0; break;
	case 'delete' : $status = -1; break;
	default : $status = 1; break;
	}

	if($status >= 0)
	{
		foreach ($_POST['checked'] as $k => $v)
		{
			try {
				dcTribune::changeState($v, $status);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
				break;
			}
		}
	
		if (!$core->error->flag()) {
			http::redirect($p_url.'&msg='.$status);
		}
	} 
	else
	{
		foreach ($_POST['checked'] as $k => $v)
		{
			try {
				dcTribune::delMsg($v);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
				break;
			}
		}
	
		if (!$core->error->flag()) {
			http::redirect($p_url.'&removed=1');
		}
	}
}

if (!empty($_POST['add_message']))
{
	$now = time();
	$offset = dt::getTimeOffset($core->blog->settings->blog_timezone,$now);
	$tribune_nick = $_POST['tribune_nick'];
	$tribune_msg = $_POST['tribune_msg'];

	try {
		dcTribune::addMsg($tribune_nick,$tribune_msg,$now + $offset,http::realIP());
		http::redirect($p_url.'&addmsg=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
		$default_tab = 'add-message';
	}
}

try {
	$rs = dcTribune::getMsg(0,false,3);
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
?>
<html>
<head>
	<title>Tribune Libre</title>
	<?php echo dcPage::jsToolMan(); ?>
	<?php echo dcPage::jsPageTabs($default_tab); ?>
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &rsaquo; <?php echo __('Free chatbox'); ?></h2>

<?php
if (isset($_GET['removed'])) {
	echo '<p class="message">'.__('Message(s) deleted.').'</p>';
}

if (isset($_GET['addmsg'])) {
	echo '<p class="message">'.__('Message added.').'</p>';
}

if (isset($_GET['msg'])) {
	if($_GET['msg'] == 0)
		echo '<p class="message">'.__('Message(s) selected offline.').'</p>';
	else
		echo '<p class="message">'.__('Message(s) selected online.').'</p>';
}

if (isset($_GET['dbcleaned'])) {
	echo '<p class="message">'.__('Database cleaned up.').'</p>';
}
?>
<div class="multi-part" title="<?php echo __('Tribune Libre'); ?>">
	<form action="plugin.php" method="post" id="tribune-form">
		<table class="maximal">
			<thead>
				<tr>
					<th colspan="2"><?php echo __('Message'); ?></th>
					<th><?php echo __('Date'); ?></th>
					<th><?php echo __('Nick'); ?></th>
					<th><?php echo __('IP'); ?></th>
					<th><?php echo __('Status'); ?></th>
				</tr>
			</thead>
			<tbody id="tribune-list">
<?php
while ($rs->fetch())
{
	if($rs->tribune_state == 0) {
		$line = 'offline';
		$status = '<img alt="'.__('unpublished').'" title="'.__('unpublished').'" src="images/check-off.png" />';
	}
	else
	{
		$line = '';
		$status = '<img alt="'.__('published').'" title="'.__('published').'" src="images/check-on.png" />';
	}
	echo
		'<tr class="line '.$line.'" id="l_'.$rs->tribune_id.'">'.
		'<td class="minimal">'.form::checkbox(array('checked[]'),$rs->tribune_id).'</td>'.
		'<td><a href="'.$p_url.'&amp;edit=1&amp;id='.$rs->tribune_id.'">'.
		html::escapeHTML($rs->tribune_msg).'</a></td>'.
		'<td>'.dt::dt2str(__('%Y-%m-%d %H:%M'),$rs->tribune_dt).'</td>'.
		'<td>'.html::escapeHTML($rs->tribune_nick).'</td>'.
		'<td>'.html::escapeHTML($rs->tribune_ip).'</td>'.
		'<td class="nowrap status">'.$status.'</td>'.
		'</tr>'
		;
}
?>
			</tbody>
		</table>

		<div class="two-cols">
			<p class="col checkboxes-helpers"></p>
<?php echo
		'<p class="col right">'.__('Selected messages action:').' '.
		form::hidden(array('p'),'dctribune').
		form::combo('actiontribune',$combo_action).
		$core->formNonce().
		'<input type="submit" value="'.__('ok').'" /></p>'
?>
		</div>
	</form>
</div>

<?php
echo
	'<div class="multi-part" id="add-message" title="'.__('Add a new message').'">'.
	'<form action="plugin.php" method="post" id="add-message-form">'.
	'<fieldset><legend>'.__('Your message').'</legend>'.
	'<p><label class=" classic required" title="'.__('Required field').'">'.__('Nick:').' '.
	form::field('tribune_nick',30,255,$core->auth->getInfo('user_displayname'),'',7).'</label></p>'.
	
	'<p><label class=" classic required" title="'.__('Required field').'">'.__('Message:').' '.
	form::field('tribune_msg',100,255,'','',7).'</label></p>'.
	
	'<p>'.form::hidden(array('p'),'dctribune').
	$core->formNonce().
	'<input type="submit" name="add_message" value="'.__('save').'" /></p>'.
	'</fieldset>'.
	'</form>'.
	'</div>'
	;
?>
</body>
</html>
