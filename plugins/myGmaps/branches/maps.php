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

require_once DC_ROOT.'/inc/admin/prepend.php';

$p_url	= 'plugin.php?p='.basename(dirname(__FILE__));
$default_tab = isset($_GET['tab']) ? $_GET['tab'] : 'entries-list';
$s =& $core->blog->settings->myGmaps;

dcPage::check('usage,contentadmin');

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
	__('pending') => '-2',
	__('online') => '1'
	);
	
	$post_maps_combo = array(
	'-' => '',
	__('none') => 'none',
	__('point of interest') => 'point of interest',
	__('polyline') => 'polyline',
	__('included kml file') => 'included kml file'
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
		__('Publish') => 'publish',
		__('Mark as pending') => 'pending'
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
$user_id = !empty($_GET['user_id']) ?	$_GET['user_id'] : '';
$cat_id = !empty($_GET['cat_id']) ?	$_GET['cat_id'] : '';
$status = isset($_GET['status']) ?	$_GET['status'] : '';
$month = !empty($_GET['month']) ?		$_GET['month'] : '';
$post_maps = !empty($_GET['post_maps']) ?		$_GET['post_maps'] : '';
$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'post_dt';
$order = !empty($_GET['order']) ? $_GET['order'] : 'desc';

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
$params['post_type'] = 'map';
//$params['columns'] = array('post_maps');

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
/* Pager class
-------------------------------------------------------- */
class adminGmapList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No element').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';

			$html_block =
			'<table class="clear"><tr>'.
			'<th colspan="2">'.__('Title').'</th>'.
			'<th>'.__('Date').'</th>'.
			'<th>'.__('Category').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th class="nowrap">'.__('Map element type').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>%s</table>';

			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';

			$blocks = explode('%s',$html_block);

			echo $blocks[0];

			while ($this->rs->fetch())
			{
				echo $this->postLine();
			}

			echo $blocks[1];

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}

	private function postLine()
	{
		if ($this->core->auth->check('categories',$this->core->blog->id)) {
			$cat_link = '<a href="category.php?id=%s">%s</a>';
		} else {
			$cat_link = '%2$s';
		}
		$p_url	= 'plugin.php?p='.basename(dirname(__FILE__));
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->post_status) {
			case 1:
				$img_status = sprintf($img,__('published'),'check-on.png');
				break;
			case -2:
				$img_status = sprintf($img,__('pending'),'check-wrn.png');
				break;
		}
		if ($this->rs->cat_title) {
			$cat_title = sprintf($cat_link,$this->rs->cat_id,
			html::escapeHTML($this->rs->cat_title));
		} else {
			$cat_title = __('None');
		}

		$res = '<tr class="line'.($this->rs->post_status != 1 ? ' offline' : '').'"'.
		' id="p'.$this->rs->post_id.'">';
		
		$meta =& $GLOBALS['core']->meta;
		$meta_rs = $meta->getMetaStr($this->rs->post_meta,'map');

		$res .=
		'<td class="nowrap">'.
		form::checkbox(array('entries[]'),$this->rs->post_id,'','','',!$this->rs->isEditable()).'</td>'.
		'<td class="maximal"><a href="'.$p_url.'&amp;do=edit&amp;id='.$this->rs->post_id.'">'.
		html::escapeHTML($this->rs->post_title).'</a></td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt).'</td>'.
		'<td class="nowrap">'.$cat_title.'</td>'.
		'<td class="nowrap">'.$this->rs->user_id.'</td>'.
		'<td class="nowrap">'.__($meta_rs).'</td>'.
		'<td class="nowrap status">'.$img_status.'</td>'.
		'</tr>';

		return $res;
	}
}

# Get posts
try {
	$posts = $core->blog->getPosts($params);
	$counter = $core->blog->getPosts($params,true);
	$post_list = new adminGmapList($core,$posts,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Save activation 

$myGmaps_center = $s->myGmaps_center;
$myGmaps_zoom = $s->myGmaps_zoom;
$myGmaps_type = $s->myGmaps_type;

if (!empty($_POST['saveconfig'])) {
  try {
    $s->put('myGmaps_center',$_POST['myGmaps_center']);
	$s->put('myGmaps_zoom',$_POST['myGmaps_zoom']);	
	$s->put('myGmaps_type',$_POST['myGmaps_type']);
	
	http::redirect($p_url.'&do=list&tab=settings&upd=1');
	
  } catch (Exception $e) {
    $core->error->add($e->getMessage());
  }
  
}

/* DISPLAY
-------------------------------------------------------- */
?>
<html>
	<head>
		<title><?php echo __('Google Maps'); ?></title>
		<?php
		echo
		dcPage::jsToolMan().
		dcPage::jsPageTabs($default_tab).
		dcPage::jsLoad(DC_ADMIN_URL.'?pf=myGmaps/js/_maps_list.js').
		dcPage::jsLoad(DC_ADMIN_URL.'?pf=myGmaps/js/_admin_map.js')
		?>
        <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
		<style type="text/css">
        #map_canvas{height:400px;padding:3px;border:1px solid #999999;margin:-10px 0 1px 0; }
        </style>
	</head>
	<body>
<?php

# Display messages

if (isset($_GET['upd']))
{
	$p_msg = '<p class="message">%s</p>';
	
	$a_msg = array(
		__('Configuration successfully saved.')
	);
	
	$k = (integer) $_GET['upd']-1;
	
	if (array_key_exists($k,$a_msg)) {
		echo sprintf($p_msg,$a_msg[$k]);
	}
}

if (!$core->error->flag())
{
	
	echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Google Maps').'</h2>';
	
	//
	echo '<div class="multi-part" id="entries-list" title="'.__('Map elements').'">';
	echo '<p><strong><a href="'.$p_url.'&amp;do=edit">'.__('New element').'</a></strong></p>';
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
	$core->formNonce().
	'<input type="submit" name="maps_filters" value="'.__('filter').'" /></p>'.
	form::hidden(array('p'),'myGmaps').
	'</div>'.
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	'</form>';
	
	# Show posts
	$post_list->display($page,$nb_per_page,
	'<form action="'.$p_url.'" method="post" id="form-entries">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.__('Selected map elements action:').' '.
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden(array('user_id'),$user_id).
	form::hidden(array('cat_id'),$cat_id).
	form::hidden(array('status'),$status).
	form::hidden(array('month'),$month).
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('page'),$page).
	form::hidden(array('nb'),$nb_per_page).
	$core->formNonce().
	'</div>'.
	'</form>'
	);
	
	echo '</div>';
	
	$myGmaps_center = $s->myGmaps_center;
	$myGmaps_zoom = $s->myGmaps_zoom;
	$myGmaps_type = $s->myGmaps_type;
	
	echo '<div class="multi-part" id="settings" title="'.__('Settings').'">'.
	'<form method="post" action="'.$p_url.'" id="settings-form">'.
	'<fieldset><legend>'.__('Default map options').'</legend>'.
	'<p>'.__('Choose map center, zoom level and map type.').'</p>'.
	'</fieldset>'.
	'<p class="area" id="map_canvas"></p>'.
	'<fieldset>'.
		'<input type="hidden" name="myGmaps_center" id="myGmaps_center" value="'.$myGmaps_center.'" />'.
		'<input type="hidden" name="myGmaps_zoom" id="myGmaps_zoom" value="'.$myGmaps_zoom.'" />'.
		'<input type="hidden" name="myGmaps_type" id="myGmaps_type" value="'.$myGmaps_type.'" />'.
		$core->formNonce().
		'<input type="submit" name="saveconfig" value="'.__('Save configuration').'" />'.
	'</fieldset>'.
	'</form>'.
	'</div>';
}

dcPage::helpBlock('myGmaps');
?>
	</body>
</html>