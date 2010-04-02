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

$core->meta = new dcMeta($core);
$core->media = new dcMedia($core);
$params=array();
$gal_combo=array();

# Getting galleries
try {
	$gal_combo['-'] = '';
	$paramgal = array();
	$paramgal['no_content'] = true;
	$gal_rs = $core->gallery->getGalleries($paramgal, false);
	while ($gal_rs->fetch()) {
		$gal_combo[$gal_rs->post_title]=$gal_rs->post_id;
		$gal_title[$gal_rs->post_id]=$gal_rs->post_title;
	}
	

} catch (Exception $e) {
	$core->error->add($e->getMessage());
}


$dirs_combo = array();
foreach ($core->media->getRootDirs() as $v) {
	if ($v->relname == "")
		$dirs_combo['/'] = ".";
	else
		$dirs_combo['/'.$v->relname] = $v->relname;
}

# Getting categories
try {
	$categories = $core->blog->getCategories(array('post_type' => 'galitem'));
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

# Getting langs
try {
	$langs = $core->blog->getLangs();
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
/* Get posts
-------------------------------------------------------- */
$user_id = !empty($_GET['user_id']) ?   $_GET['user_id'] : '';
$cat_id = !empty($_GET['cat_id']) ?     $_GET['cat_id'] : '';
$status = isset($_GET['status']) ?      $_GET['status'] : '';
$selected = isset($_GET['selected']) ?  $_GET['selected'] : '';
$month = !empty($_GET['month']) ?       $_GET['month'] : '';
$lang = !empty($_GET['lang']) ?         $_GET['lang'] : '';
$sortby = !empty($_GET['sortby']) ?     $_GET['sortby'] : $core->gallery->settings->gallery_admin_items_sortby;
$order = !empty($_GET['order']) ?       $_GET['order'] : $core->gallery->settings->gallery_admin_items_order;
$gal_id = !empty($_GET['gal_id']) ?     $_GET['gal_id'] : '';
$media_dir = !empty($_GET['media_dir']) ?     $_GET['media_dir'] : '';
$tag = !empty($_GET['tag']) ?     trim($_GET['tag']) : '';
$nb = !empty($_GET['nb']) ?     trim($_GET['nb']) : 0;

if (!empty($_GET['clearfilter'])) {
	unset($_SESSION['items_filter']);
	http::redirect("plugin.php?p=gallery&m=items");
} elseif (empty($_GET['filter']) && !empty($_SESSION['items_filter'])) {
	$s = unserialize(base64_decode($_SESSION['items_filter']));
	if ($s !== false) {
		$user_id = !empty($s['user_id'])     ?  $s['user_id'] : '';
		$cat_id = !empty($s['cat_id'])       ?  $s['cat_id'] : '';
		$status = isset($s['status'])        ?  $s['status'] : '';
		$selected = isset($s['selected'])    ?  $s['selected'] : '';
		$month = !empty($s['month'])         ?  $s['month'] : '';
		$lang = !empty($s['lang'])           ?  $s['lang'] : '';
		$sortby = !empty($s['sortby'])       ?  $s['sortby'] : $core->gallery->settings->gallery_admin_items_sortby;
		$order = !empty($s['order'])         ?  $s['order'] : $core->gallery->settings->gallery_admin_items_order;
		$gal_id = !empty($s['gal_id'])       ?  $s['gal_id'] : '';
		$media_dir = !empty($s['media_dir']) ?     $s['media_dir'] : '';
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
		'gal_id' => $gal_id,
		'media_dir' => $media_dir,
		'tag' => $tag,
		'nb' => $nb);
	$_SESSION['items_filter']=base64_encode(serialize($s));
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
	$combo_action[__('Maintenance')] = array(
		__('Remove image-post') => 'removeimgpost',
		__('set date to media exif date') => 'fixexif'
	);
	$combo_action[__('Tags')] = array(
		__('add tags') => 'tags'
	);
	$combo_action[__('Thumbnails')] = array(
		__('Generate missing thumbnails') => 'missingthumbs',
		__('Force thumbnail regeneration') => 'forcethumbs'
	);
}
$combo_action[__('Change')] = array(__('change category') => 'category');
if ($core->auth->check('admin',$core->blog->id)) {
	$combo_action[__('Change')][__('change author')] = 'author';
}
$combo_action[__('Mark')]=array(__('Select for integration') => 'selected',
				__('unselect for integration') => 'unselected');
/*if ($core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('delete')] = 'delete';
}*/

$show_filters = false;

$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$nb_per_page =  44;
if ((integer) $nb > 0) {
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

# - Gallery filter
if ($gal_id !== '' && isset($gal_title[$gal_id])) {
	$params['gal_id'] = $gal_id;
#	$show_filters = true;
}

# - Media dir filter
if ($media_dir !== '' && in_array($media_dir,$dirs_combo)) {
	$params['media_dir'] = $media_dir;
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
	
	if ($sortby != $core->gallery->settings->gallery_admin_items_sortby || 
		$order != $core->gallery->settings->gallery_admin_items_order) {
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

# --BEHAVIOR-- adminPostsActionsCombo
/*$core->callBehavior('adminPostsActionsCombo',array(&$combo_action));*/

$default_tab='item_list';
?>
<html>
<head>
  <title><?php echo __('Gallery Items'); ?></title>
  <script type="text/javascript">
  //<![CDATA[
  <?php echo "var text_show_hide_thumbnails ='".html::escapeJS(__('Show / Hide thumbnails'))."';\n"; ?>
  //]]>
  </script>
  <?php echo dcPage::jsLoad('index.php?pf=gallery/js/_items_adv.js').
             dcPage::jsPageTabs($default_tab);
	if (!$show_filters) {
		echo dcPage::jsLoad('js/filter-controls.js');
	}
  ?>
  <link rel="stylesheet" type="text/css" href="index.php?pf=gallery/admin_css/style.css" />
</head>
<body>

<?php

echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; '.__('Galleries').' &gt; '.__('Entries').'</h2>';
echo '<p><a href="plugin.php?p=gallery" class="multi-part">'.__('Galleries').'</a></p>';
echo '<div class="multi-part" id="item_list" title="'.__('Images').'">';

if (!$show_filters) {
	echo '<p><a id="filter-control" class="form-control" href="#">'.
	__('Filters').'</a></p>';
}

echo
'<form action="plugin.php" method="get" id="filters-form">'.
'<fieldset><legend>'.__('Filters').'</legend>'.
'<div class="three-cols">'.
'<div class="col">'.
'<label>'.__('Gallery:').
form::combo('gal_id',$gal_combo,$gal_id).
'</label> '.
'<label>'.__('Media dir:').
form::combo('media_dir',$dirs_combo,$media_dir).
'</label> '.
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
'<input type="hidden" name="m" value="items" />'.
'<input type="submit" name="filter" value="'.__('filter').'" />'.
(($show_filters || ($gal_id != ''))?
'&nbsp;<a href="plugin.php?p=gallery&amp;m=items&amp;clearfilter=1" class="button" type="submit" title="'.__('Clear filter').'">'.__('Clear filter').'</a></p>':"").
'</div>'.
'</div>'.
'<br class="clear" />'. //Opera sucks
'</fieldset>'.
'</form>';








# Get posts
try {
	$images = $core->gallery->getGalImageMedia($params);
	$counter = $core->gallery->getGalImageMedia($params,true);
	$gal_list = new adminImageList($core,$images,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}




if (!$core->error->flag()) {
	if ($gal_id !== '' && isset($gal_title[$gal_id])) {
		echo '<fieldset><legend>'.__('Gallery content');
		if ($show_filters) 
			echo ' ('.__('filtered').')';
		
		echo '</legend>'.
		'<h3>'.__('Gallery').' : '.$gal_title[$gal_id].
		'&nbsp[<a href="plugin.php?p=gallery&amp;m=gal&id='.$gal_id.'">'.__('edit').'</a>]</h3>'.
		'</fieldset>';
	}
	$lists_helper = '<p class="col">'.__('Selection').' : '.
		'<a href="#" class="sel_all">'.__('all').'</a>, '.
		'<a href="#" class="sel_none">'.__('none').'</a>, '.
		'<a href="#" class="sel_invert">'.__('invert').'</a></p>';
	echo
	# Show posts
	$gal_list->displayArray($page,$nb_per_page,
	'<form action="plugin.php?p=gallery&amp;m=itemsactions" method="post" id="form-entries">'.
	$lists_helper.
	'%s'.
	'<div class="two-cols">'.
	$lists_helper.
	'<p class="col right">'.__('Selected entries action:').
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden(array('user_id'),$user_id).
	form::hidden(array('cat_id'),$cat_id).
	form::hidden(array('status'),$status).
	form::hidden(array('selected'),$selected).
	form::hidden(array('month'),$month).
	form::hidden(array('lang'),$lang).
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('gal_id'),$gal_id).
	form::hidden(array('media_dir'),$media_dir).
	form::hidden(array('tag'),$tag).
	form::hidden(array('page'),$page).
	form::hidden(array('nb'),$nb_per_page).
	$core->formNonce().
	'</div>'.
	'</form>'
	);
}
?>
</div>
<?php 
echo '<p><a href="plugin.php?p=gallery&amp;m=newitems" class="multi-part">'.__('Manage new items').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=options" class="multi-part">'.__('Options').'</a></p>';
if ($core->auth->isSuperAdmin())
	echo '<p><a href="plugin.php?p=gallery&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
?>
</body>
</html>