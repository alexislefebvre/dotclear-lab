<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
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

require dirname(__FILE__).'/../../inc/admin/lib.pager.php';
require dirname(__FILE__).'/class.dc.gallerylists.php';

$core->meta = new dcMeta($core);
$core->gallery= new dcGallery($core);
$core->media = new dcMedia($core);
$params=array();
$gal_combo=array();

# Getting galleries
try {
	$gal_combo['-'] = '';
	$paramgal = array();
	$paramgal['post_type'] = 'gal';
	$paramgal['no_content'] = true;
	$gal_rs = $core->blog->getPosts($paramgal, false);
	while ($gal_rs->fetch()) {
		$gal_combo[$gal_rs->post_title]=$gal_rs->post_id;
		$gal_title[$gal_rs->post_id]=$gal_rs->post_title;
	}
	

} catch (Exception $e) {
	$core->error->add($e->getMessage());
}


$dirs_combo = array();
foreach ($core->media->getRootDirs() as $v) {
	if ($v->w) {
		$dirs_combo['/'.$v->relname] = $v->relname;
	}
}

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
		$categories_combo[html::escapeHTML($categories->cat_title)] = $categories->cat_id;
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
$sortby = !empty($_GET['sortby']) ?     $_GET['sortby'] : 'post_dt';
$order = !empty($_GET['order']) ?       $_GET['order'] : 'desc';
$gal_id = !empty($_GET['gal_id']) ?     $_GET['gal_id'] : '';
$media_dir = !empty($_GET['media_dir']) ?     $_GET['media_dir'] : '';
$tag = !empty($_GET['tag']) ?     trim($_GET['tag']) : '';
# Actions combo box
$combo_action = array();
if ($core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('publish')] = 'publish';
	$combo_action[__('unpublish')] = 'unpublish';
	$combo_action[__('schedule')] = 'schedule';
	$combo_action[__('mark as pending')] = 'pending';
	if ($gal_id == '')
		$combo_action[__('Add to gallery ...')] = 'addtogal';
	if ($gal_id !== '')
		$combo_action[__('remove from current gallery')] = 'removefromgal';
	$combo_action[__('Remove image-post')] = 'removeimgpost';
}
$combo_action[__('change category')] = 'category';
if ($core->auth->check('admin',$core->blog->id)) {
	$combo_action[__('change author')] = 'author';
}
if ($core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('delete')] = 'delete';
}

$show_filters = false;

$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$nb_per_page =  30;
if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	if ($nb_per_page != $_GET['nb']) {
		$show_filters = true;
	}
	$nb_per_page = (integer) $_GET['nb'];
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

# - Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
	}
	
	if ($sortby != 'post_dt' || $order != 'desc') {
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

?>
<html>
<head>
  <title><?php echo __('Gallery Items'); ?></title>
  <?php echo dcPage::jsLoad('index.php?pf=gallery/js/_items_lists.js').
             dcPage::jsPageTabs($default_tab);
	if (!$show_filters) {
		echo dcPage::jsLoad('js/filter-controls.js');
	}
  ?>
  <script type="text/javascript">
  //<![CDATA[
  <?php echo "text_show_hide_thumbnails ='".html::escapeJS(__('Show / Hide thumbnails'))."';\n"; ?>
  //]]>
  </script>
</head>
<body>

<?php

echo '<h2>'.$core->blog->name.' &gt; '.__('Entries').'</h2>';
echo '<p><a href="plugin.php?p=gallery" class="multi-part">'.__('Galleries').'</a></p>';
echo '<div class="multi-part" id="gal_list" title="'.__('Images').'">';

if (!$show_filters) {
	echo '<p><a id="filter-control" class="form-control" href="#">'.
	__('Filters').'</a></p>';
}

echo
'<form action="plugin.php" method="get" id="filters-form">'.
'<input type="hidden" name="p" value="gallery" />'.
'<input type="hidden" name="m" value="items" />'.
'<fieldset><legend>'.__('Filters').'</legend>'.
'<div class="three-cols">'.
'<div class="col">'.
'<label>'.__('Gallery:').dcPage::help('posts','f_gallery').
form::combo('gal_id',$gal_combo,$gal_id).
'</label> '.
'<label>'.__('Media dir:').dcPage::help('posts','f_gallery').
form::combo('media_dir',$dirs_combo,$media_dir).
'</label> '.
'<label>'.__('Month:').dcPage::help('posts','f_month').
form::combo('month',$dt_m_combo,$month).
'</label> '.
'<label>'.__('Lang:').dcPage::help('posts','f_lang').
form::combo('lang',$lang_combo,$lang).
'</label> '.
'<label>'.__('Tag:').dcPage::help('posts','f_lang').
form::field('tag',10,10,$tag).
'</label> '.
'</div>'.

'<div class="col">'.
'<label>'.__('Author:').dcPage::help('posts','f_author').
form::combo('user_id',$users_combo,$user_id).
'</label> '.
'<label>'.__('Category:').dcPage::help('posts','f_category').
form::combo('cat_id',$categories_combo,$cat_id).
'</label> '.
'<label>'.__('Status:').dcPage::help('posts','f_status').
form::combo('status',$status_combo,$status).
'</label> '.
'</div>'.

'<div class="col">'.
'<p><label>'.__('Order by:').dcPage::help('posts','f_sortby').
form::combo('sortby',$sortby_combo,$sortby).
'</label> '.
'<label>'.__('Sort:').dcPage::help('posts','f_order').
form::combo('order',$order_combo,$order).
'</label></p>'.
'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
__('Entries per page').dcPage::help('posts','f_nb').'</label> '.
'<input type="submit" value="'.__('filter').'" /></p>'.
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
		'&nbsp[<a href="plugin.php?p=gallery&m=gal&id='.$gal_id.'">'.__('edit').'</a>]</h3>'.
		'</fieldset>';
	}
	echo
	# Show posts
	$gal_list->display($page,$nb_per_page,
	'<form action="plugin.php?p=gallery&m=itemsactions" method="post" id="form-entries">'.
	'<p class="col checkboxes-helpers"></p>'.
	'%s'.
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	'<p class="col right">'.__('Selected entries action:').
	dcPage::help('posts','p_actions').
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
	'</div>'.
	'</form>'
	);
}
?>
</div>
<?php echo '<br/><p><a href="plugin.php?p=gallery&amp;m=newitems" class="multi-part">'.__('Manage new items').'</a></p>';?>
</body>
</html>
