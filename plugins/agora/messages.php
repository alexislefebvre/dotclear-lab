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
if (!empty($_REQUEST['id'])) {
	include dirname(__FILE__).'/message.php';
	return;
} 

dcPage::check('usage,contentadmin');

# Creating filter combo boxes
if (!$core->error->flag())
{
	$status_combo = array(
	'-' => ''
	);
	foreach ($core->agora->getAllMessageStatus() as $k => $v) {
		$status_combo[$v] = (string) $k;
	}


	$sortby_combo = array(
	__('Date') => 'message_dt',
	__('Entry title') => 'post_title',
	__('Author') => 'user_id',
	__('Status') => 'message_status'
	);

	$order_combo = array(
	__('Descending') => 'desc',
	__('Ascending') => 'asc'
	);
}

# Creating filter combo boxes
# Filter form we'll put in html_block



/* Get Messages
-------------------------------------------------------- */
$user_id = isset($_GET['user_id']) ?	$_GET['user_id'] : '';
$post_id = isset($_GET['post_id']) ?	$_GET['post_id'] : '';
$status = isset($_GET['status']) ?	$_GET['status'] : '';
$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'message_dt';
$order = !empty($_GET['order']) ?	$_GET['order'] : 'desc';

$with_spam = $author || $status || $sortby != 'message_dt' || $order != 'desc' ;

$show_filters = false;

$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	if ($nb_per_page != $_GET['nb']) {
		$show_filters = true;
	}
	$nb_per_page = (integer) $_GET['nb'];
}

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;

# Author filter
if ($user_id !== '') {
	$params['q_author'] = $user_id;
	$show_filters = true;
}

# Entry filter
if ($post_id !== '') {
	$params['post_id'] = $post_id;
	$show_filters = true;
}

# - Status filter
if ($status !== '' && in_array($status,$status_combo)) {
	$params['message_status'] = $status;
	$show_filters = true;
} elseif (!$with_spam) {
	$params['message_status_not'] = -3;
}

# Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
	}
	
	if ($sortby != 'message_dt' || $order != 'desc') {
		$show_filters = true;
	}
}

# Actions combo box
$combo_action = array();
if ($core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('publish')] = 'publish';
	$combo_action[__('unpublish')] = 'unpublish';
	$combo_action[__('mark as pending')] = 'pending';
	$combo_action[__('mark as junk')] = 'junk';
}
if ($core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('delete')] = 'delete';
}


/* Get messages
-------------------------------------------------------- */
try {
	$messages = $core->agora->getMessages($params);
	$counter = $core->agora->getMessages($params,true);
	$message_list = new adminMessageList($core,$messages,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

/* DISPLAY
-------------------------------------------------------- */
?>
<html>
<head>
  <title><?php echo __('Messages'); ?></title>
  <?php echo
  dcPage::jsDatePicker().
  dcPage::jsToolBar().
  dcPage::jsModal().
  dcPage::jsLoad('index.php?pf=agora/js/messages.js').
  dcPage::jsConfirmClose('message-form').
  # --BEHAVIOR-- adminPageHeaders
  $core->callBehavior('adminMessageHeaders').
  dcPage::jsPageTabs($default_tab);
 if (!$show_filters) {
  	echo dcPage::jsLoad('js/filter-controls.js');
  }?>
  <script type="text/javascript">
  //<![CDATA[
  <?php echo dcPage::jsVar('dotclear.msg.confirm_delete_messages',__("Are you sure you want to delete these messages?")); ?>
  //]]>
  </script>
</head>
<body>
<?php
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; <span class="page-title">'.__('Messages').'</span></h2>';

if (!$core->error->flag())
{
	# Filters
	if (!$show_filters) {
		echo '<p><a id="filter-control" class="form-control" href="#">'.
		__('Filters').'</a></p>';
	}
	
	echo
	'<form action="'.$p_url.'" method="get" id="filters-form">'.
	'<fieldset><legend>'.__('Filters').'</legend>'.
	'<div class="three-cols">'.
	'<div class="col">'.
	'<label>'.__('Status:').' '.
	form::combo('status',$status_combo,$status).
	'</label>'.
	'<p><label>'.__('Author:').' '.
	form::field('user_id',10,30,$user_id).
	'</label></p>';
	if ($post_id != '') { echo '<p><label>'.__('Entry ID:').' '.
	form::field('post_id',10,30,$post_id);}
	echo '</label></p>'.
	 '</div>'.
	
	'<div class="col">'.
	'<p><label>'.__('Order by:').' '.
	form::combo('sortby',$sortby_combo,$sortby).
	'</label> '.
	'<label>'.__('Sort:').' '.
	form::combo('order',$order_combo,$order).
	'</label></p>'.
	'</div>'.
	
	'<div class="col">'.
	'<br />'.
	'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
	__('Messages per page').'</label></p>'.
	'<p><input type="hidden" name="p" value="agora" />'.
	'<input type="hidden" name="act" value="messages" />'.
	'<input type="submit" value="'.__('Apply filters').'" /></p>'.
	'</div>'.
	
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	'</form>';
	
	if (!$with_spam) {
		$spam_count = $core->agora->getMessages(array('message_status'=>-3),true)->f(0);
		if ($spam_count == 1) {
			echo '<p>'.sprintf(__('You have one spam message.'),'<strong>'.$spam_count.'</strong>').' '.
			'<a href="plugin.php?p=agora&act=messages&status=-3">'.__('Show it.').'</a></p>';
		} elseif ($spam_count > 1) {
			echo '<p>'.sprintf(__('You have %s spam messages.'),'<strong>'.$spam_count.'</strong>').' '.
			'<a href="plugin.php?p=agora&act=messages&status=-3">'.__('Show them.').'</a></p>';
		}
	}
	
	# Show messages
	$message_list->display($page,$nb_per_page,
	'<form action="plugin.php?p=agora&amp;act=messages-actions" method="post" id="form-messages">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.__('Selected messages action:').' '.
	form::combo('action',$combo_action).
	$core->formNonce().
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('user_id'),preg_replace('/%/','%%',$user_id)).
	form::hidden(array('post_id'),$post_id).
	form::hidden(array('status'),$status).
	form::hidden(array('page'),$page).
	form::hidden(array('nb'),$nb_per_page).
	'</div>'.
	
	'</form>'
	);
}


?>
</body>
</html>
