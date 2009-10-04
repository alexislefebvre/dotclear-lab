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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

require DC_ROOT.'/inc/admin/lib.pager.php';

if (!empty($_REQUEST['edit']) && !empty($_REQUEST['id'])) {
	include dirname(__FILE__).'/edit.php';
	return;
}

if (!empty($_REQUEST['config'])) {
	include dirname(__FILE__).'/config.php';
	return;
}

if (is_null($core->blog->settings->tribune_flag)) {
	try {
		$core->blog->settings->setNameSpace('tribune');

		// Tribune is not active by default
		$core->blog->settings->put('tribune_flag',false,'boolean','Enable chatbox plugin');
		$core->blog->settings->put('tribune_syntax_wiki',false,'boolean','Syntax Wiki for chatbox');
		$core->blog->settings->put('tribune_display_order',false,'boolean','Inverse order of chatbox');
		$core->blog->settings->put('tribune_refresh_time',30000,'integer','Refresh rate of Tribune in millisecondes');
		$core->blog->settings->put('tribune_message_length',140,'integer','Number of messages displayed in chatbox');
		$core->blog->settings->put('tribune_limit',10,'integer','Number of messages displayed in chatbox');
		
		$core->blog->settings->setNameSpace('system');
		
		$core->blog->triggerBlog();
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$default_tab = '';
$params=array();

$status = isset($_GET['status']) ?      $_GET['status'] : '';
$nb = !empty($_GET['nb']) ?     trim($_GET['nb']) : 0;

if(empty($_GET['filter']) && !empty($_SESSION['messages_filter'])) {
	$s = unserialize(base64_decode($_SESSION['messages_filter']));
	if ($s !== false) {
		$status = isset($s['status'])        ?  $s['status'] : '';
		$nb = !empty($s['nb']) ?     trim($s['nb']) : '';
	}
} elseif (!empty($_GET['filter'])) {
	$s = array(
		'status' => $status,
		'nb' => $nb);
	$_SESSION['messages_filter']=base64_encode(serialize($s));
}

$combo_action = array();
$combo_action[__('Status')] = array(
__('publish') => 'publish',
__('unpublish') => 'unpublish',
__('delete') => 'delete'
);

$status_combo = array(
'-' => '',
__('published') => '1',
__('unpublished') => '0'
);

$sortby_combo = array(
__('Date') => 'tribune_dt',
__('Status') => 'tribune_state',
);

$order_combo = array(
__('Descending') => 'desc',
__('Ascending') => 'asc'
);
	
$show_filters =  false;
$add_message = false;

$sortby = !empty($_GET['sortby']) ?  $_GET['sortby'] : '';
$order = !empty($_GET['order']) ?  $_GET['order'] : '';
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$ip = !empty($_GET['ip']) ? $_GET['ip'] : '';
$nb_per_page =  10;
if ((integer) $nb > 0) {
	if ($nb_per_page != $nb) {
		$show_filters = true;
	}
	$nb_per_page = (integer) $nb;
}

# - Status filter
if ($status !== '' && in_array($status,$status_combo)) {
	$params['tribune_state'] = $status;
	$show_filters = true;
}

# - IP filter
if ($ip) {
	$params['tribune_ip'] = $ip;
	$show_filters = true;
}

if (!in_array($sortby,$sortby_combo))
	$sortby="tribune_dt";
if (!in_array($order,$order_combo))
	$order="desc";
# - Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
	}
	
	//$show_filters = true;
}

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
				$core->tribune->changeState($v, $status);
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
				$core->tribune->delMsg($v);
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
	$cur = $core->con->openCursor($core->prefix.'tribune');
	$cur->tribune_nick = $_POST['tribune_nick'];
	$cur->tribune_msg = $_POST['tribune_msg'];

	try {
		$tid = $core->tribune->addMsg($cur);
		http::redirect($p_url.'&addmsg=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
		$add_message = true;
	}
}

if (!empty($_POST['saveconfig']))
{
	try
	{
		$tribune_flag = (empty($_POST['tribune_flag']))?false:true;

		$core->blog->settings->setNamespace('tribune');
 		$core->blog->settings->put('tribune_flag',$tribune_flag,'boolean','Active the tribune module');
		$core->blog->triggerBlog();

		$msg = __('Configuration successfully updated.');
	}

	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);

try {
	$rs = $core->tribune->getMsgs($params);
	$count = $core->tribune->getMsgs($params,true);
	$pager = new pager($page,$count->f(0),$nb_per_page);
	$pager->var_page = 'page';
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
?>
<html>
<head>
	<title><?php echo __('Free chatbox'); ?></title>
	<?php echo dcPage::jsToolMan(); 
	if (!$show_filters) {
		echo dcPage::jsLoad('js/filter-controls.js');
	}
	if (!$add_message) {
		echo dcPage::jsLoad('index.php?pf=dctribune/js/_tribune.js');
	}
	echo dcPage::jsLoad('index.php?pf=dctribune/js/_messages.js');
	?>
	
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &rsaquo; <?php echo sprintf(__('Free chatbox (%s messages)'),$count->f(0));?>
 &rsaquo; <a class="button" href="<?php echo $p_url.'&amp;config=1'; ?>"><?php echo html::escapeHTML(
	__('Configuration')); ?></a> </h2>

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

?>

<div id="tribune_add">
<?php 
if (!$add_message) {
	echo '<div class="two-cols"><p><strong><a id="tribune-control" href="#">'.
	__('Write a new message').'</a></strong></p></div>';
}
?>

<?php
echo
	'<form action="'.$p_url.'" method="post" id="add-message-form">'.
	'<fieldset><legend>'.__('Publish a message').'</legend>'.

	'<p><label class="classic required" title="'.__('Required field').'">'.__('Nick:').' '.
	form::field('tribune_nick',30,255,$core->auth->getInfo('user_displayname'),'',7).'</label></p>'.

	'<p class="area"><label class="classic required" title="'.__('Required field').'">'.__('Message:').' '.
	form::textarea('tribune_msg',50,3,'','',7).'</label></p>'.

	'<p>'.form::hidden(array('p'),'dctribune').
	$core->formNonce().
	'<input type="submit" name="add_message" value="'.__('publish').'" /></p>'.
	'</fieldset>'.
	'</form>'.
	'</div>';
?>
<br/>
<div id="tribune_messages">
<?php 
if (!$show_filters) {
	echo '<p><a id="filter-control" class="form-control" href="#">'.
	__('Filters').'</a></p>';
}

echo
'<form action="plugin.php" method="get" id="filters-form">'.
'<fieldset><legend>'.__('Filters').'</legend>'.
'<div class="three-cols">'.

'<div class="col">'.
'<p><label>'.__('Status:').
form::combo('status',$status_combo,$status).
'</label>'.
'<label>'.__('IP address:').
form::field('ip',20,39,html::escapeHTML($ip)).
'</label>'.
'</p> '.
'</div>'.

'<div class="col">'.
'<p><label>'.__('Order by:').
form::combo('sortby',$sortby_combo,$sortby).
'</label></p> '.
'<p>'.
'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
__('Messages per page').'</label></p>'.
'</div>'.

'<div class="col">'.
'<p><label>'.__('Sort:').
form::combo('order',$order_combo,$order).
'</label></p>'.
'<input type="hidden" name="p" value="dctribune" />'.
'<input type="submit" name="filter" value="'.__('filter').'" /></p>'.
'</div>'.
'</div>'.
'<br class="clear" />'. //Opera sucks
'</fieldset>'.
'</form>'.
'</div>';
?>

<div id="tribune_list">
<form action="plugin.php" method="post" id="tribune-form">
		<table class="maximal">
			<thead>
				<tr>
					<th colspan="2"><?php echo __('Nick'); ?></th>
					<th><?php echo __('Message'); ?></th>
					<th><?php echo __('IP'); ?></th>
					<th><?php echo __('Date'); ?></th>
					<th><?php echo __('Status'); ?></th>
					<th>&nbsp;</th>
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
	$edit = '<a href="'.$p_url.'&amp;edit=1&amp;id='.$rs->tribune_id.'"><img src="images/edit-mini.png" alt="" title="'.__('modify this message').'" /></a>';
	echo
		'<tr class="line '.$line.'" id="l_'.$rs->tribune_id.'">'.
		'<td class="minimal">'.form::checkbox(array('checked[]'),$rs->tribune_id).'</td>'.
		'<td>'.html::escapeHTML($rs->tribune_nick).'</td>'.
		'<td class="maximal">'.html::decodeEntities(html::clean($rs->tribune_msg)).'</td>'.
		'<td>'.html::escapeHTML($rs->tribune_ip).'</td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$rs->tribune_dt).'</td>'.
		'<td class="nowrap status">'.$status.'</td>'.
		'<td class="nowrap status">'.$edit.'</td>'.
		'</tr>'
		;
}
?>
			</tbody>
		</table>

		<div class="two-cols">
		
			<?php echo '<p class="col checkboxes-helpers"></p>';?>
<?php echo
	'<p class="col right">'.__('Selected messages action:').' '.
	form::hidden(array('p'),'dctribune').
	form::combo('actiontribune',$combo_action).
	$core->formNonce().
	'<input type="submit" value="'.__('ok').'" /></p>';
?>
		</div>
	</form>
	<?php echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';?>
</div>

<br/>
</body>
</html>