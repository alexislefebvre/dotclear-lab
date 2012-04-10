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
require dirname(__FILE__).'/lib/agora.pager.php';

if ($_REQUEST['act'] == 'messages') {
	include dirname(__FILE__).'/messages.php';
	return;
} 
if ($_REQUEST['act'] == 'messages-actions') {
	include dirname(__FILE__).'/messages_actions.php'; //not ready yet
	return;
}
if ($_REQUEST['act'] == 'options') {
	include dirname(__FILE__).'/options.php'; // to finalize
	return;
}

// NO ADMIN posts list

if (!defined('DC_CONTEXT_ADMIN')) { return; }
dcPage::check('admin');


# Creating filter combo boxes
$sortby_combo = array(
__('Username') => 'U.user_id',
__('Status') => 'user_status',
__('Display name') => 'user_displayname',
__('Number of entries') => 'nb_post',
__('Number of messages') => 'nb_message'
);

$order_combo = array(
__('Descending') => 'desc',
__('Ascending') => 'asc'
);

# Actions combo box
$combo_action[__('Status')] = array(
	__('Active') => 'active',
	__('Ban') => 'ban');
$combo_action[__('Content')] = array(
	__('Moderate') => 'modo',
	__('Not moderate') => 'free'
);

# --BEHAVIOR-- adminUsersActionsCombo
$core->callBehavior('agoraUsersActionsCombo',array(&$combo_action));


# Get users
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$nb_per_page =  30;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	$nb_per_page = $_GET['nb'];
}

$q = !empty($_GET['q']) ? $_GET['q'] : '';
$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'user_id';
$order = !empty($_GET['order']) ?		$_GET['order'] : 'asc';

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);

$show_filters = false;

# - Search filter
if ($q) {
	$params['q'] = $q;
	$show_filters = true;
}

# - Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
		$show_filters = true;
	}
}

if (!empty($_POST['action']) && !empty($_POST['users']))
{
	switch ($_POST['action']) {
		case 'active' : 
		foreach ($_POST['users'] as $k => $v)
		{
			try {
				$core->agora->moderateUser($v,1);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
				break;
			}
		}
		if (!$core->error->flag()) {
			http::redirect($p_url.'&msg=active');
		}
		break;

		case 'ban' : 
		foreach ($_POST['users'] as $k => $v)
		{
			try {
				$core->agora->moderateUser($v,(string)0);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
				break;
			}
		}
		if (!$core->error->flag()) {
			http::redirect($p_url.'&msg=ban');
		}
		break;

		case 'modo' : 
		foreach ($_POST['users'] as $k => $v)
		{
			try {
				$core->agora->moderateUser($v,'',(string)0);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
				break;
			}
		}
		if (!$core->error->flag()) {
			http::redirect($p_url.'&msg=modo');
		}
		break;
		
		case 'free' : 
		foreach ($_POST['users'] as $k => $v)
		{
			try {
				$core->agora->moderateUser($v,'',1);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
				break;
			}
		}
		if (!$core->error->flag()) {
			http::redirect($p_url.'&msg=free');
		}
		break;
	}
}

$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$msg_list = array(
	'active' => __('Selected user(s) is (are) now active.'),
	'ban' => __('Selected sers(s) is (are) now banned.'),
	'modo' => __('Selected user(s) is (are) now moderated.'),
	'free' => __('Selected users(s) is (are) not moderated anymore.')
);

if (isset($msg_list[$msg])) {
	$msg = sprintf('<p class="message">%s</p>',$msg_list[$msg]);
}


try {
	$rs = $core->agora->getUsers($params);
	$counter = $core->agora->getUsers($params,1);
	$user_list = new agoraUserList($core,$rs,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

/* DISPLAY
-------------------------------------------------------- */
?>
<html>
<head>
	<title><?php echo __('Public users'); ?></title>
	<?php echo dcPage::jsToolMan(); 
	if (!$show_filters) {
		echo dcPage::jsLoad('js/filter-controls.js');
	}?>
	<?php echo dcPage::jsLoad('index.php?pf=agora/js/users.js'); ?>
	<?php echo '<link rel="stylesheet" type="text/css" href="index.php?pf=agora/style/admin.css" />'; ?>
</head>
<body>
<?php 
if (!$core->error->flag())
{
	echo $msg.
	'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; <span class="page-title">'.__('Public users').'</span></h2>';

	if ($core->auth->isSuperAdmin()) {
		echo '<p class="top-add"><strong><a class="button add" href="user.php">'.__('Create a new user').'</a></strong></p>';
	}

	if (!$show_filters) {
		echo '<p><a id="filter-control" class="form-control" href="#">'.__('Filters').'</a></p>';
	}
	
	echo
	'<form action="'.$p_url.'" method="get" id="filters-form">'.
	'<fieldset class="two-cols"><legend>'.__('Filters').'</legend>'.
	
	'<div class="col">'.
	'<p><label for="sortby">'.__('Order by:').' '.
	form::combo('sortby',$sortby_combo,$sortby).
	'</label> '.
	'<label for="order">'.__('Sort:').' '.
	form::combo('order',$order_combo,$order).
	'</label></p>'.
	'</div>'.
	
	'<div class="col">'.
	'<p><label for="q">'.__('Search:').' '.
	form::field('q',20,255,html::escapeHTML($q)).
	'</label></p>'.
	'<p><label for="nb" class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
	__('Users per page').'</label> '.
	'<input type="hidden" name="p" value="agora" />'.
	'<input type="submit" value="'.__('Apply filters').'" /></p>'.
	'</div>'.
	
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	'</form>';
	
	# Show users
	$user_list->display($page,$nb_per_page,
	'<form action="'.$p_url.'" method="post" id="users-requests">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right"><label for="dispatch_action" class="classic">'.
	__('Selected users action:').' '.
	//form::combo('dispatch_action',$combo_action).
	form::hidden(array('p'),'agora').
	form::combo('action',$combo_action).
	$core->formNonce().
	'</label> '.
	'<input type="submit" value="'.__('ok').'" />'.
	'</p>'.
	'</div>'.
	'</form>'
	);
}

?>
</body>
</html>

