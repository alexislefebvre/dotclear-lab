<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pollsFactory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Getting categories
try {
	$categories = $core->blog->getCategories(array('post_type'=>'post'));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Getting authors
try {
	$users = $core->blog->getPostsUsers();
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Getting dates
try {
	$dates = $core->blog->getDates(array('type'=>'month'));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Getting langs
try {
	$langs = $core->blog->getLangs();
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Creating filter combo boxes
if (!$core->error->flag())
{
	# Filter form we'll put in html_block
	$users_combo = $categories_combo = array();
	$users_combo['-'] = $categories_combo['-'] = '';
	while ($users->fetch())
	{
		$user_cn = dcUtils::getUserCN($users->user_id,$users->user_name,
		$users->user_firstname,$users->user_displayname);
		
		if ($user_cn != $users->user_id) {
			$user_cn .= ' ('.$users->user_id.')';
		}
		
		$users_combo[$user_cn] = $users->user_id; 
	}
	
	while ($categories->fetch()) {
		$categories_combo[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
			html::escapeHTML($categories->cat_title).
			' ('.$categories->nb_post.')'] = $categories->cat_id;
	}
	
	$status_combo = array(
	'-' => ''
	);
	foreach ($core->blog->getAllPostStatus() as $k => $v) {
		$status_combo[$v] = (string) $k;
	}
	
	$selected_combo = array(
	'-' => '',
	__('selected') => '1',
	__('not selected') => '0'
	);
	
	# Months array
	$dt_m_combo['-'] = '';
	while ($dates->fetch()) {
		$dt_m_combo[dt::str('%B %Y',$dates->ts())] = $dates->year().$dates->month();
	}
	
	$lang_combo['-'] = '';
	while ($langs->fetch()) {
		$lang_combo[$langs->post_lang] = $langs->post_lang;
	}

	$pollstatus_combo = array(
		'-' => '',
		__('Uncomplete') => '-2',
		__('Offline') => '0',
		__('Online') => '1'
	);
	
	$sortby_combo = array(
	__('Date') => 'post_dt',
	__('Title') => 'post_title',
	__('Category') => 'cat_title',
	__('Author') => 'user_id',
	__('Status') => 'post_status',
	__('Selected') => 'post_selected'
	);
	
	$order_combo = array(
	__('Descending') => 'desc',
	__('Ascending') => 'asc'
	);
}

# Actions combo box
$combo_action = array();
if ($core->auth->check('publish,contentadmin',$core->blog->id)) {
	$combo_action[__('entries')][__('publish')] = 'publish';
	$combo_action[__('entries')][__('unpublish')] = 'unpublish';
}
$combo_action[__('entries')][__('mark as selected')] = 'selected';
$combo_action[__('entries')][__('mark as unselected')] = 'unselected';
if ($core->auth->check('admin',$core->blog->id)) {
	$combo_action[__('polls')][__('delete poll')] = 'delete_poll';
	$combo_action[__('polls')][__('close poll')] = 'close_poll';
}

/* Filters
-------------------------------------------------------- */
$user_id = !empty($_GET['user_id']) ?	$_GET['user_id'] : '';
$cat_id = !empty($_GET['cat_id']) ?	$_GET['cat_id'] : '';
$status = isset($_GET['status']) ?	$_GET['status'] : '';
$selected = isset($_GET['selected']) ?	$_GET['selected'] : '';
$month = !empty($_GET['month']) ?		$_GET['month'] : '';
$lang = !empty($_GET['lang']) ?		$_GET['lang'] : '';
$pollstatus = isset($_GET['pollstatus']) ? $_GET['pollstatus'] : '';
$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'post_dt';
$order = !empty($_GET['order']) ?		$_GET['order'] : 'desc';

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

# - User filter
if ($user_id !== '' && in_array($user_id,$users_combo)) {
	$params['user_id'] = $user_id;
	$show_filters = true;
}

# - Categories filter
if ($cat_id !== '' && in_array($cat_id,$categories_combo)) {
	$params['cat_id'] = $cat_id;
	$show_filters = true;
}

# - Status filter
if ($status !== '' && in_array($status,$status_combo)) {
	$params['post_status'] = $status;
	$show_filters = true;
}

# - Selected filter
if ($selected !== '' && in_array($selected,$selected_combo)) {
	$params['post_selected'] = $selected;
	$show_filters = true;
}

# - Month filter
if ($month !== '' && in_array($month,$dt_m_combo)) {
	$params['post_month'] = substr($month,4,2);
	$params['post_year'] = substr($month,0,4);
	$show_filters = true;
}

# - Lang filter
if ($lang !== '' && in_array($lang,$lang_combo)) {
	$params['post_lang'] = $lang;
	$show_filters = true;
}

# - Poll status filter
if (in_array($pollstatus,$pollstatus_combo)) {
	$params['poll_status'] = $pollstatus;
	if ($pollstatus !== '') {
		$show_filters = true;
	}
}

# - Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
	}
	
	if ($sortby != 'post_dt' || $order != 'desc') {
		$show_filters = true;
	}
}

# Get posts
try {
	$posts = $fact->getPolls($params);
	$counter = $fact->getPolls($params,true);
	$post_list = new adminPollList($core,$posts,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Display

if (!$show_filters) {
	$echo .= '<p><a id="filter-control" class="form-control" href="#">'.
	__('Filters').'</a></p>';
}

$echo .=
'<form action="plugin.php?p=pollsFactory&amp;tab=polls" method="get" id="filters-form">'.
'<fieldset><legend>'.__('Filters').'</legend>'.
'<div class="three-cols">'.
'<div class="col">'.
'<label>'.__('Author:').
form::combo('user_id',$users_combo,$user_id).'</label> '.
'<label>'.__('Category:').
form::combo('cat_id',$categories_combo,$cat_id).'</label> '.
'<label>'.__('Status:').
form::combo('status',$status_combo,$status).'</label> '.
'<label>'.__('Poll status:').
form::combo('pollstatus',$pollstatus_combo,$pollstatus).'</label> '.
'</div>'.

'<div class="col">'.
'<label>'.__('Selected:').
form::combo('selected',$selected_combo,$selected).'</label> '.
'<label>'.__('Month:').
form::combo('month',$dt_m_combo,$month).'</label> '.
'<label>'.__('Lang:').
form::combo('lang',$lang_combo,$lang).'</label> '.
'</div>'.

'<div class="col">'.
'<p><label>'.__('Order by:').
form::combo('sortby',$sortby_combo,$sortby).'</label> '.
'<label>'.__('Sort:').
form::combo('order',$order_combo,$order).'</label></p>'.
'<p><label class="classic">'.form::field('nb',3,3,$nb_per_page).' '.
__('Entries per page').'</label> '.
'<input type="submit" value="'.__('filter').'" />'.
form::hidden(array('p'),'pollsFactory').
form::hidden(array('tab'),'polls').
'</p>'.
'</div>'.
'</div>'.
'<br class="clear" />'. //Opera sucks
'</fieldset>'.
'</form>';

# Show posts
$echo .= $post_list->pollDisplay($page,$nb_per_page,
	'<form action="plugin.php" method="post" id="form-entries">'.

	'%s'.

	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.

	'<p class="col right">'.__('Selected entries action:').' '.
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden(array('user_id'),$user_id).
	form::hidden(array('cat_id'),$cat_id).
	form::hidden(array('status'),$status).
	form::hidden(array('selected'),$selected).
	form::hidden(array('month'),$month).
	form::hidden(array('lang'),$lang).
	form::hidden(array('pollstatus'),$pollstatus).
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('page'),$page).
	form::hidden(array('nb'),$nb_per_page).
	form::hidden(array('p'),'pollsFactory').
	form::hidden(array('tab'),'polls').
	$core->formNonce().
	'</div>'.
	'</form>'
);
?>