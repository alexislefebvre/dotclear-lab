<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of myGmaps, a plugin for Dotclear 2.
#
# Copyright (c) 2010 Philippe aka amalgame
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$tab	= isset($_GET['tab']) ? $_GET['tab'] : 'list';

# Save config
if (!empty($_POST['save'])) {
	try {
		$core->blog->settings->myGmaps->put('center',$_POST['center']);
		$core->blog->settings->myGmaps->put('zoom',$_POST['zoom']);
		$core->blog->settings->myGmaps->put('map_type',$_POST['map_type']);
		$core->blog->settings->myGmaps->put('scrollwheel',$_POST['scrollwheel']);
		http::redirect($p_url.'&go=maps&tab=config&upd=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
# Add icon
if (!empty($_POST['send'])) {
	try{
		files::uploadStatus($_FILES['upfile']);
		$fm = new filemanager(dirname(__FILE__).'/icons');
		$fm->uploadFile($_FILES['upfile']['tmp_name'],$_FILES['upfile']['name']);
		http::redirect($p_url.'&go=maps&tab=icons&add=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
# Delete icons
if (!empty($_POST['delete'])) {
	try {
		foreach ($_POST['ids'] as $filename) {
			unlink(dirname(__FILE__).'/icons/'.$filename);
		}
		http::redirect($p_url.'&go=maps&tab=icons&del=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
# Getting categories
try {
	$categories = $core->blog->getCategories(array('post_type'=>'map'));
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
	$dates = $core->blog->getDates(array('type'=>'month','post_type'=>'map'));
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
	
	$categories_combo[__('None')] = 'NULL';
	while ($categories->fetch()) {
		$categories_combo[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
			html::escapeHTML($categories->cat_title).
			' ('.$categories->nb_post.')'] = $categories->cat_id;
	}
	
	$status_combo = array(
	'-' => '',
	__('published') => '1',
	__('pending') => '-2',
	__('unpublished') => '0'
	);
	
	$post_maps_combo = array(
	'-' => '',
	__('None') => 'none',
	__('Point of interest') => 'marker',
	__('Polyline') => 'polyline',
	__('Polygon') => 'polygon',
	__('Included kml file') => 'kml'
	);
	
	# Months array
	$dt_m_combo['-'] = '';
	while ($dates->fetch()) {
		$dt_m_combo[dt::str('%B %Y',$dates->ts())] = $dates->year().$dates->month();
	}
	
	$sortby_combo = array(
	__('Date') => 'post_dt',
	__('Title') => 'post_title',
	__('Category') => 'cat_title',
	__('Author') => 'user_id',
	__('Status') => 'post_status'
	);
	
	$order_combo = array(
	__('Descending') => 'desc',
	__('Ascending') => 'asc'
	);
}

# Actions combo box
$combo_action = array();
if ($core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('Status')] = array(
		__('Published') => 'published',
		__('Pending') => 'pending',
		__('Unpublished') => 'unpublished'
	);
}

$combo_action[__('Change')] = array(__('Change category') => 'category');
if ($core->auth->check('admin',$core->blog->id))
{
	$combo_action[__('Change')] = array_merge($combo_action[__('Change')],
		array(__('Change author') => 'author'));
}
if ($core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('Delete')] = array(__('Delete') => 'delete');
}

/* Get posts
-------------------------------------------------------- */
foreach ($filters as $k => $v) {
	${$k} = array_key_exists($k,$_GET) ? $_GET[$k] : $v;
}

$show_filters = false;

$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;

if (!empty($nb) && (integer) $nb > 0) {
	if ($nb_per_page != $nb) {
		$show_filters = true;
	}
	$nb_per_page = (integer) $nb;
}

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;
$params['post_type'] = 'map';

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

# - Month filter
if ($month !== '' && in_array($month,$dt_m_combo)) {
	$params['post_month'] = substr($month,4,2);
	$params['post_year'] = substr($month,0,4);
	$show_filters = true;
}

# - Map type filter
if ($post_maps != '' && in_array($post_maps,$post_maps_combo)) {
	$params['sql'] .= "AND post_meta LIKE '%".$post_maps."%' ";
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
	$posts = $core->blog->getPosts($params);
	$post_list = new adminMyGmapsList($core,$posts,$posts->count());
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

/* DISPLAY
-------------------------------------------------------- */

echo
'<html>'.
'<head>'.
	'<title>'.__('Google Maps').'</title>'.
	dcPage::jsPageTabs($tab).
	dcPage::jsLoad(DC_ADMIN_URL.'?pf=myGmaps/js/_maps.js').
	myGmapsUtils::jsCommon().
	'<link type="text/css" rel="stylesheet" href="'.DC_ADMIN_URL.'?pf=myGmaps/css/style.css" />'.
	'<link type="text/css" rel="stylesheet" href="'.DC_ADMIN_URL.'?pf=myGmaps/css/ui.theme.css" />'.
	'</script>'.
'</head>'.
'<body>';

# Display messages
$msg = '';
if (isset($_GET['upd']) && $_GET['upd'] === '1') {
	$msg = __('Configuration has been successfully saved');
}
if (isset($_GET['add']) && $_GET['add'] === '1') {
	$msg = __('Icon has been successfully added');
}
if (isset($_GET['del']) && $_GET['del'] === '1') {
	$msg = __('Selected icons have been successfully deleted');
}
if (isset($_GET['act']) && $_GET['act'] === '1') {
	$msg = __('Selected map posts status have been successfully changed');
}
if (isset($_GET['act']) && $_GET['act'] === '2') {
	$msg = __('Selected map posts categories have been successfully changed');
}
if (isset($_GET['act']) && $_GET['act'] === '3') {
	$msg = __('Selected map posts authors have been successfully changed');
}
if (isset($_GET['act']) && $_GET['act'] === '4') {
	$msg = __('Selected map posts have been successfully deleted');
}
echo $msg !== '' ? sprintf('<p class="message">%s</p>',$msg) : '';

if (!$core->error->flag())
{
	echo
	'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Google Maps').'&nbsp;-&nbsp;'.
	'<a href="'.$p_url.'&amp;go=map" class="button">'.__('New element').'</a></h2>';
	
	echo '<div class="multi-part" id="list" title="'.__('Map elements').'">';
	
	if (!$show_filters) {
		echo 
		dcPage::jsLoad('js/filter-controls.js').
		'<p><a id="filter-control" class="form-control" href="#">'.
		__('Filters').'</a></p>';
	}
	
	echo
	'<form action="'.$p_url.'" method="get" id="filters-form">'.
	'<fieldset><legend>'.__('Filters').'</legend>'.
	'<div class="three-cols">'.
	'<div class="col">'.
	'<label>'.__('Author:').
	form::combo('user_id',$users_combo,$user_id).'</label> '.
	'<label>'.__('Category:').
	form::combo('cat_id',$categories_combo,$cat_id).'</label> '.
	'<label>'.__('Status:').
	form::combo('status',$status_combo,$status).'</label> '.
	'</div>'.
	
	'<div class="col">'.
	'<label>'.__('Month:').
	form::combo('month',$dt_m_combo,$month).'</label> '.
	'<label>'.__('Map element type:').
	form::combo('post_maps',$post_maps_combo,$post_maps).'</label> '.
	'</div>'.
	
	'<div class="col">'.
	'<p><label>'.__('Order by:').
	form::combo('sortby',$sortby_combo,$sortby).'</label> '.
	'<label>'.__('Sort:').
	form::combo('order',$order_combo,$order).'</label></p>'.
	'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
	__('Map elements per page').'</label> '.
	'<input type="submit" name="maps_filters" value="'.__('filter').'" /></p>'.
	form::hidden(array('p'),'myGmaps').
	'</div>'.
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	'</form>';
	
	$hidden_fields = '';
	foreach ($filters as $k) {
		if (array_key_exists($k,$_GET)) {
			$hidden_fields .= form::hidden(array($k),$_GET[$k]);
		}
	}
	# Show posts
	$post_list->display($page,$nb_per_page,$p_url,
	'<form action="'.$p_url.'&amp;go=maps_actions" method="post" id="form-entries">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.__('Selected map elements action:').' '.
	form::combo('action',$combo_action).
	$hidden_fields.
	'<input type="submit" value="'.__('ok').'" /></p>'.
	$core->formNonce().
	'</div>'.
	'</form>'
	);
	
	echo '</div>';
	
	echo
	'<div class="multi-part" id="config" title="'.__('Configuration').'">'.
	'<form method="post" action="'.$p_url.'" id="settings-form">'.
	'<fieldset><legend>'.__('Default map options').'</legend>'.
		'<p>'.__('Choose map center, zoom level / mode and map type:').'</p>'.
		'<p class="form-note">'.__('Clic on a map to set the center').'</p>'.
		'<p class="field"><label>'.__('Use zoom scrollwheel').
		form::checkbox('scrollwheel',1,$core->blog->settings->myGmaps->scrollwheel).
		'</label></p>'.
		'<div class="area" id="map_canvas"></div>'.
	'</fieldset>'.
	'<p class="area" id="map-details-area" >'.
		'<label for="map-details">'.__('Map details:').'</label>'.
		'<div id="map-details"></div>'.
	'</p>'.
	'<p>'.
		form::hidden('center',$core->blog->settings->myGmaps->center).
		form::hidden('zoom',$core->blog->settings->myGmaps->zoom).
		form::hidden('map_type',$core->blog->settings->myGmaps->map_type).
		$core->formNonce().
		'<input type="submit" name="save" value="'.__('Save configuration').'" />'.
	'</p>'.
	'</form>'.
	'</div>';
	
	echo
	'<div class="multi-part" id="icons" title="'.__('Icons').'">'.
	'<form method="post" action="'.$p_url.'&amp;tab=icons" id="icons-form" enctype="multipart/form-data">'.
	'<fieldset><legend>'.__('Add icon').'</legend>'.
	'<p><label>'.__('Choose a file:').
	' ('.sprintf(__('Maximum size %s'),files::size(DC_MAX_UPLOAD_SIZE)).')'.
	'<input type="file" name="upfile" size="20" />'.
	'</label></p>'.
	'<p><input type="submit" name="send" value="'.__('send').'" /></p>'.
	'</fieldset>'.
	'<fieldset><legend>'.__('Delete icons').'</legend>'.
	'<p>'.__('Select icons to delete by clicking on them').'</p>'.
	'<ul>';
	foreach (myGmapsUtils::getIcons() as $icon) {
		echo sprintf(
			'<li class="ui-corner-top ui-corner-bottom" style="background-image: url(%1$s); background-repeat: no-repeat; background-position: top center;">%2$s</li>',
			myGmapsUtils::getIconURL(basename($icon)),
			form::checkbox(array('ids[]'),basename($icon),false)
		);
	}
	echo
	'</ul>'.
	'<p>'.
	$core->formNonce().
	'<input type="submit" name="delete" value="'.__('Delete selected icons').'" />'.
	'</p>'.
	'</fieldset>'.
	'</form>'.
	'</div>';
}

dcPage::helpBlock('myGmaps');

echo
'</body>'.
'</html>';

?>