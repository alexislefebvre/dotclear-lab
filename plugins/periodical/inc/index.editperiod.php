<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of periodical, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$period_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : -1;
$default_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'period';
$action_redir = $p_url.'&part='.$default_part.'&tab='.$default_tab.'&id='.$period_id.'&msg='.$action;

# Get period
$period = $per->getPeriods(array('periodical_id'=>$period_id));

# Not exists
if ($period->isEmpty()) {
	http::redirect($p_url.'&part=periods');
	exit();
}

# Update period
if ($action == 'updateperiod' && !empty($_POST))
{
	try {
		$period_title = isset($_POST['period_title']) ? $_POST['period_title'] : '';
		$period_pub_nb = isset($_POST['period_pub_nb']) ? abs((integer) $_POST['period_pub_nb']) : 1;
		$period_pub_int = isset($_POST['period_pub_int']) ? (string) $_POST['period_pub_int'] : 'day';
		$period_curdt = isset($_POST['period_curdt']) ? date('Y-m-d H:i:00',strtotime($_POST['period_curdt'])) : date('Y-m-d H:i:00');
		$period_enddt = isset($_POST['period_enddt']) ? date('Y-m-d H:i:00',strtotime($_POST['period_enddt'])) : date('Y-m-d H:i:00');
		
		$old_titles = $per->getPeriods(array('periodical_title'=>$period_title));
		if (!$old_titles->isEmpty()) {
			while($old_titles->fetch()) {
				if ($old_titles->periodical_id != $period_id) {
					throw New Exception(__('Period title is already taken:'.$old_titles->periodical_id.'='.$period_id));
				}
			}
		}
		if (empty($period_title)) {
			throw New Exception(__('Period title is required'));
		}
		if (strtotime($period_strdt) > strtotime($period_enddt)) {
			throw New Exception(__('Start date must be older than end date'));
		}

		$cur = $per->openCursor();
		$cur->periodical_title = $period_title;
		$cur->periodical_curdt = $period_curdt;
		$cur->periodical_enddt = $period_enddt;
		$cur->periodical_pub_int = $period_pub_int;
		$cur->periodical_pub_nb = $period_pub_nb;
		$per->updPeriod($period_id,$cur);

		http::redirect($action_redir);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Publish posts
if ($action == 'publish' && !empty($_POST['periodical_entries']))
{
	try {
		foreach($_POST['periodical_entries'] as $id)
		{
			$id = (integer) $id;
			$core->blog->updPostStatus($id,1);
			$per->delPost($id);
		}
		http::redirect($action_redir);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Unpublish posts
if ($action == 'unpublish' && !empty($_POST['periodical_entries']))
{
	try {
		foreach($_POST['periodical_entries'] as $id)
		{
			$id = (integer) $id;
			$core->blog->updPostStatus($id,0);
			$per->delPost($id);
		}
		http::redirect($action_redir);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Remove posts from periodical
if ($action == 'remove_post_periodical' && !empty($_POST['periodical_entries']))
{
	try {
		foreach($_POST['periodical_entries'] as $id)
		{
			$id = (integer) $id;
			$per->delPost($id);
		}
		http::redirect($action_redir);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}


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

$sortby_combo = array(
__('Date') => 'post_dt',
__('Create date') => 'post_creadt',
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

# Actions combo box
$combo_action = array();
$combo_action[__('Entries')][__('publish')] = 'publish';
$combo_action[__('Entries')][__('unpublish')] = 'unpublish';
$combo_action[__('Periodical')][__('remove from periodical')] = 'remove_post_periodical';

/* Filters
-------------------------------------------------------- */
$user_id = !empty($_GET['user_id']) ?	$_GET['user_id'] : '';
$cat_id = !empty($_GET['cat_id']) ?	$_GET['cat_id'] : '';
$selected = isset($_GET['selected']) ?	$_GET['selected'] : '';
$month = !empty($_GET['month']) ?		$_GET['month'] : '';
$lang = !empty($_GET['lang']) ?		$_GET['lang'] : '';
$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'post_dt';
$order = !empty($_GET['order']) ?		$_GET['order'] : 'desc';

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
$params['periodical_id'] = $period_id;

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
	$posts = $per->getPosts($params);
	$counter = $per->getPosts($params,true);
	$post_list = new adminPeriodicalList($core,$posts,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Display
echo '
<html><head><title>'.__('Periodical').'</title>'.
dcPage::jsDatePicker().
dcPage::jsPageTabs($default_tab).
dcPage::jsLoad('index.php?pf=periodical/js/period.js');
if (!$show_filters) {
	echo dcPage::jsLoad('js/filter-controls.js');
}
echo '</head>
<body>
<h2>'.
html::escapeHTML($core->blog->name).
' &rsaquo; <a href="'.$p_url.'&amp;part=periods">'.__('Periodical').'</a>'.
' &rsaquo; '.__('Edit period').
' - <a class="button" href="'.$p_url.'&amp;part=addperiod">'.__('New period').'</a>'.
'</h2>'.$msg;

# Period
echo '
<div class="multi-part" title="'.__('Period').'" id="period">
<form method="post" action="plugin.php">
<p><label>'.__('Title:').
form::field('period_title',60,255,html::escapeHTML($period->periodical_title),'maximal',3).'</label></p>
<div class="two-cols">
<div class="col">
<p><label>'.__('Next update:').
form::field('period_curdt',16,16,date('Y-m-d H:i',strtotime($period->periodical_curdt)),'',3).'</label></p>
<p><label>'.__('End date:').
form::field('period_enddt',16,16,date('Y-m-d H:i',strtotime($period->periodical_enddt)),'',3).'</label></p>
</div><div class="col">
<p><label>'.__('Publication frequency:').
form::combo('period_pub_int',$per->getTimesCombo(),$period->periodical_pub_int,'',3).'</label></p>
<p><label>'.__('Number of entries to publish every time:').
form::field('period_pub_nb',10,3,html::escapeHTML($period->periodical_pub_nb),'',3).'</label></p>
</div></div>
<div class="clear">
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('action'),'updateperiod').
form::hidden(array('id'),$period_id).
form::hidden(array('p'),'periodical').
form::hidden(array('part'),'editperiod').
form::hidden(array('tab'),'period').'
</p>
</div>
</form>
</div>';

# Posts linked to period
echo '
<div class="multi-part" title="'.__('Entries').'" id="posts">
<p><a id="filter-control" class="form-control" href="#">'.__('Filters').'</a></p>'.
'<form action="plugin.php?p=periodical&amp;part=editperiod&amp;tab=posts&id='.$period_id.'" method="get" id="filters-form">'.
'<fieldset><legend>'.__('Filters').'</legend>'.
'<div class="three-cols">'.
'<div class="col">'.
'<label>'.__('Author:').
form::combo('user_id',$users_combo,$user_id).'</label> '.
'<label>'.__('Category:').
form::combo('cat_id',$categories_combo,$cat_id).'</label> '.
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
form::hidden(array('id'),$period_id).
form::hidden(array('p'),'periodical').
form::hidden(array('part'),'editperiod').
form::hidden(array('tab'),'posts').
'</p>'.
'</div>'.
'</div>'.
'<br class="clear" />'. //Opera sucks
'</fieldset>'.
'</form>';

# Show posts
echo $post_list->postDisplay($page,$nb_per_page,
	'<form action="plugin.php" method="post" id="form-entries">'.

	'%s'.

	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.

	'<p class="col right">'.__('Selected entries action:').' '.
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden(array('id'),$period_id).
	form::hidden(array('user_id'),$user_id).
	form::hidden(array('cat_id'),$cat_id).
	form::hidden(array('selected'),$selected).
	form::hidden(array('month'),$month).
	form::hidden(array('lang'),$lang).
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('page'),$page).
	form::hidden(array('nb'),$nb_per_page).
	form::hidden(array('p'),'periodical').
	form::hidden(array('part'),'editperiod').
	form::hidden(array('tab'),'posts').
	$core->formNonce().
	'</div>'.
	'</form>'
);

echo '</div>';
?>