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
if (!empty($_REQUEST['id'])) {
	include dirname(__FILE__).'/message.php';
	return;
} 

require dirname(__FILE__).'/lib/admin.messages.pager.php';

dcPage::check('usage,contentadmin');

# Creating filter combo boxes
# Filter form we'll put in html_block
$status_combo = array(
'-' => ''
);
foreach ($core->blog->agora->getAllMessageStatus() as $k => $v) {
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


/* Get comments
-------------------------------------------------------- */
$author = isset($_GET['author']) ?	$_GET['author'] : '';
$status = isset($_GET['status']) ?		$_GET['status'] : '';
$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'message_dt';
$order = !empty($_GET['order']) ?		$_GET['order'] : 'desc';

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
if ($author !== '') {
	$params['q_author'] = $author;
	$show_filters = true;
}

# - Status filter
if ($status !== '' && in_array($status,$status_combo)) {
	$params['message_status'] = $status;
	$show_filters = true;
} elseif (!$with_spam) {
	$params['message_status_not'] = -2;
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


/* Get comments
-------------------------------------------------------- */
try {
	$messages = $core->blog->agora->getMessages($params);
	$counter = $core->blog->agora->getMessages($params,true);
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
  dcPage::jsLoad('index.php?pf=agora/js/_messages.js').
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
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Messages').'</h2>';

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
	'<br /><p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
	__('Messages per page').'</label></p>'.
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
	'<p><label>'.__('Author:').' '.
	form::field('author',20,255,html::escapeHTML($author)).
	'</label></p>'.
	'<p><input type="hidden" name="p" value="agora" />'.
	'<input type="hidden" name="act" value="messages" />'.
	'<input type="submit" value="'.__('filter').'" /></p>'.
	'</div>'.
	
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	'</form>';
	
	if (!$with_spam) {
		$spam_count = $core->blog->agora->getMessages(array('message_status'=>-2),true)->f(0);
		if ($spam_count == 1) {
			echo '<p>'.sprintf(__('You have one spam message.'),'<strong>'.$spam_count.'</strong>').' '.
			'<a href="plugin.php?p=agora&act=messages?status=-2">'.__('Show it.').'</a></p>';
		} elseif ($spam_count > 1) {
			echo '<p>'.sprintf(__('You have %s spam messages.'),'<strong>'.$spam_count.'</strong>').' '.
			'<a href="plugin.php?p=agora&act=messages?status=-2">'.__('Show them.').'</a></p>';
		}
	}
	
	# Show messages
	$message_list->display($page,$nb_per_page,
	'<form action="plugin?p=agora&amp;act=messages-actions" method="post" id="form-messages">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.__('Selected messages action:').' '.
	form::combo('action',$combo_action).
	$core->formNonce().
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('author'),preg_replace('/%/','%%',$author)).
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
