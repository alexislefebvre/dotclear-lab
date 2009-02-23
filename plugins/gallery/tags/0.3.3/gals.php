<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

require DC_ROOT.'/inc/admin/lib.pager.php';
require dirname(__FILE__).'/class.dc.gallerylists.php';

$core->gallery = new dcGallery($core);

# Getting categories
try {
	$categories = $core->blog->getCategories();
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
	$dates = $core->blog->getDates(array('type'=>'month','post_type'=>'gal'));
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
	$users_combo['-'] = $categories_combo['-'] = $dirs_combo['-'] = '';
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



/* Get posts
-------------------------------------------------------- */
$user_id = !empty($_GET['user_id']) ?   $_GET['user_id'] : '';
$cat_id = !empty($_GET['cat_id']) ?     $_GET['cat_id'] : '';
$status = isset($_GET['status']) ?      $_GET['status'] : '';
$selected = isset($_GET['selected']) ?  $_GET['selected'] : '';
$month = !empty($_GET['month']) ?       $_GET['month'] : '';
$lang = !empty($_GET['lang']) ?         $_GET['lang'] : '';
$sortby = !empty($_GET['sortby']) ?     $_GET['sortby'] : $core->blog->settings->gallery_admin_gals_sortby;
$order = !empty($_GET['order']) ?       $_GET['order'] : $core->blog->settings->gallery_admin_gals_order;
$tag = !empty($_GET['tag']) ?     trim($_GET['tag']) : '';
$nb = !empty($_GET['nb']) ?     trim($_GET['nb']) : 0;

if (!empty($_GET['clearfilter'])) {
	unset($_SESSION['gals_filter']);
	http::redirect("plugin.php?p=gallery");
} elseif (empty($_GET['filter']) && !empty($_SESSION['gals_filter'])) {
	$s = unserialize(base64_decode($_SESSION['gals_filter']));
	if ($s !== false) {
		$user_id = !empty($s['user_id'])     ?  $s['user_id'] : '';
		$cat_id = !empty($s['cat_id'])       ?  $s['cat_id'] : '';
		$status = isset($s['status'])        ?  $s['status'] : '';
		$selected = isset($s['selected'])    ?  $s['selected'] : '';
		$month = !empty($s['month'])         ?  $s['month'] : '';
		$lang = !empty($s['lang'])           ?  $s['lang'] : '';
		$sortby = !empty($s['sortby'])       ?  $s['sortby'] : $core->blog->settings->gallery_admin_gals_sortby;
		$order = !empty($s['order'])         ?  $s['order'] : $core->blog->settings->gallery_admin_gals_sortby;
		$tag = !empty($s['tag'])             ?  trim($s['tag']) : '';
		$nb = !empty($s['nb']) ?     trim($s['nb']) : '';
	}
} elseif (!empty($_GET['filter'])) {
	$s = array(
		'user_id' => $user_id,
		'cat_id' => $cat_id,
		'status' => $status,
		'selected' => $selected,
		'month' => $month,
		'lang' => $lang,
		'sortby' => $sortby,
		'order' => $order,
		'tag' => $tag,
		'nb' => $nb);
	$_SESSION['gals_filter']=base64_encode(serialize($s));
}

# Actions combo box
$combo_action = array();
if ($core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('Status')] = array(
		__('publish') => 'publish',
		__('unpublish') => 'unpublish',
		__('schedule') => 'schedule',
		__('mark as pending') => 'pending'
	);
}
$combo_action[__('Change')]=array(__('change category') => 'category');
if ($core->auth->check('admin',$core->blog->id)) {
	$combo_action[__('Change')][__('change author')] = 'author';
}
$combo_action[__('Maintenance')]=array(__('update') => 'update');
if ($core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('Maintenance')][__('delete')] = 'delete';
}

# --BEHAVIOR-- adminPostsActionsCombo
$core->callBehavior('adminGalleriesActionsCombo',array(&$combo_action));

$default_tab = 'gal_list';

$show_filters = false;

$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$nb_per_page =  30;

if ((integer) $nb > 0) {
	if ($nb_per_page != $nb) {
		$show_filters = true;
	}
	$nb_per_page = (integer) $nb;
}

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

if (!in_array($sortby,$sortby_combo))
	$sortby="post_dt";
if (!in_array($order,$order_combo))
	$order="desc";
# - Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
	}
	
	if ($sortby != $core->blog->settings->gallery_admin_gals_sortby || 
		$order != $core->blog->settings->gallery_admin_gals_order) {
		$show_filters = true;
	}
}

# - Tag filter
if ($tag !=='') {
	$params['tag']=$tag;
	$show_filters = true;
}

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;

# Get posts
try {
	$gals = $core->gallery->getGalleries($params);
	$counter = $core->gallery->getGalleries($params,true);
	$gal_list = new adminGalleryList($core,$gals,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
$core->meta = new dcMeta($core);
?>

<html>
<head>
  <title><?php echo __('Galleries'); ?></title>
  <?php echo dcPage::jsPageTabs($default_tab).
  	dcPage::jsLoad('index.php?pf=gallery/js/_gals_lists.js');
	if (!$show_filters) {
		echo dcPage::jsLoad('js/filter-controls.js');
	}
  ?>
</head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
<?php echo __('Galleries').' &gt; '.__('Main menu'); ?></h2>
<?php
if (!$core->gallery->checkThemesDir()) {
	echo '<p class="error">'.
		__('Invalid theme dir detected in blog settings. Please update gallery_themes_path setting in about:config.').
	'</p>';
}

echo '<div class="multi-part" id="gal_list" title="'.__('Galleries').'">';

echo '<p><a class="button" href="plugin.php?p=gallery&amp;m=gal">'.__('New gallery').'</a></p>';
if (!$show_filters) {
	echo '<p><a id="filter-control" class="form-control" href="#">'.
	__('Filters').'</a></p>';
}

echo
'<form action="plugin.php" method="get" id="filters-form">'.
'<fieldset><legend>'.__('Filters').'</legend>'.
'<div class="three-cols">'.
'<div class="col">'.
'<label>'.__('Month:').
form::combo('month',$dt_m_combo,$month).
'</label> '.
'<label>'.__('Tag:').
form::field('tag',10,100,$tag).
'</label> '.
'</div>'.

'<div class="col">'.
'<label>'.__('Author:').
form::combo('user_id',$users_combo,$user_id).
'</label> '.
'<label>'.__('Category:').
form::combo('cat_id',$categories_combo,$cat_id).
'</label> '.
'<label>'.__('Status:').
form::combo('status',$status_combo,$status).
'</label> '.
'<label>'.__('Lang:').
form::combo('lang',$lang_combo,$lang).
'</label> '.

'</div>'.

'<div class="col">'.
'<p><label>'.__('Order by:').
form::combo('sortby',$sortby_combo,$sortby).
'</label> '.
'<label>'.__('Sort:').
form::combo('order',$order_combo,$order).
'</label></p>'.
'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
__('Entries per page').'</label></p>'.
'<p><input type="hidden" name="p" value="gallery" />'.
'<input type="submit" name="filter" value="'.__('filter').'" />'.
($show_filters?
'&nbsp;<a href="plugin.php?p=gallery&amp;clearfilter=1" class="button" type="submit" title="'.__('Clear filter').'">'.__('Clear filter').'</a></p>':"").
'</div>'.
'</div>'.
'<br class="clear" />'. //Opera sucks
'</fieldset>'.
'</form>';










if (!$core->error->flag()) {
	
	echo
	# Show posts
	$gal_list->display($page,30,
	'<form action="plugin.php?p=gallery&amp;m=galsactions" method="post" id="form-entries">'.
	'%s'.
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	'<p class="col right">'.__('Selected entries action:').
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" />'.
	$core->formNonce().'</p>'.
	'</div>'.
	'</form>'
	);
}
?>
<?php
	echo "</div>";
	echo '<p><a href="plugin.php?p=gallery&amp;m=items" class="multi-part">'.__('Images').'</a></p>';
	echo '<p><a href="plugin.php?p=gallery&amp;m=newitems" class="multi-part">'.__('Manage new items').'</a></p>';
	echo '<p><a href="plugin.php?p=gallery&amp;m=options" class="multi-part">'.__('Options').'</a></p>';
if ($core->auth->isSuperAdmin())
	echo '<p><a href="plugin.php?p=gallery&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
?>
</body>
</html>
