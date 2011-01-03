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

dcPage::check('usage,contentadmin');

global $core;

$p_url	= 'plugin.php?p='.basename(dirname(__FILE__));
$default_tab = isset($_GET['tab']) ? $_GET['tab'] : 'entries-list';

$s =& $core->blog->settings->myGmaps;

$myGmaps_center = $s->myGmaps_center;
$myGmaps_zoom = $s->myGmaps_zoom;
$myGmaps_type = $s->myGmaps_type;

$page_title = __('Add a map to entry');

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

$post_id = !empty($_GET['post_id']) ?	$_GET['post_id'] : '';
$post_type = !empty($_GET['post_type']) ?	$_GET['post_type'] : '';
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

if ($post_id != '') {
	$meta =& $GLOBALS['core']->meta;
	$my_params['post_id'] = $_GET['post_id'];
	$my_params['no_content'] = true;
	$my_params['post_type'] = array('post','page');
				
	$rs = $core->blog->getPosts($my_params);
	while ($rs->fetch()) {
		$my_post_maps = $meta->getMetaStr($rs->post_meta,'map');
		
	}
	if ($rs->post_type == 'page') {
		$page_title = __('Add a map to page');
	}
	$my_post_maps_options = $meta->getMetaStr($rs->post_meta,'map_options');
	
	if ($my_post_maps_options) {
		$map_options = explode(",",$my_post_maps_options);
		$myGmaps_center = $map_options[0].','.$map_options[1];
		$myGmaps_zoom = $map_options[2];
		$myGmaps_type = $map_options[3];
	}
	
	if ($my_post_maps !='') {
		$maps_array = explode(",",$my_post_maps);
		$has_map = true;
		$page_title = __('Edit map');
	}
}

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;
$params['post_type'] = 'map';
$params['post_status'] = '1';

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
			'</tr>%s</table>';

			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';

			$blocks = explode('%s',$html_block);

			echo $blocks[0];
			
			global $core;
			$meta =& $GLOBALS['core']->meta;
			$my_params['post_id'] = $_GET['post_id'];
			$my_params['no_content'] = true;
			$my_params['post_type'] = array('post','page');
						
			$rs = $core->blog->getPosts($my_params);
			while ($rs->fetch()) {
				$my_post_maps = $meta->getMetaStr($rs->post_meta,'map');
			}			
			if ($my_post_maps !='') {
				$maps_array = explode(",",$my_post_maps);
			}

			while ($this->rs->fetch())
			{
				if (!isset($maps_array) || !in_array($this->rs->post_id,$maps_array)) {
					echo $this->postLine();
				}
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
			'<td class="maximal"><a href="plugin.php?p=myGmaps&amp;do=edit&amp;id='.$this->rs->post_id.'" title="'.__('Edit map element').' : '.html::escapeHTML($this->rs->post_title).'">'.html::escapeHTML($this->rs->post_title).'</a></td>'.
			'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt).'</td>'.
			'<td class="nowrap">'.$cat_title.'</td>'.
			'<td class="nowrap">'.$this->rs->user_id.'</td>'.
			'<td class="nowrap">'.__($meta_rs).'</td>'.
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

if (isset($_POST['addmap'])) {
  try {
    $entries = $_POST['entries'];
	$post_id = $_POST['post_id'];
	$myGmaps_center = $_POST['myGmaps_center'];
	$myGmaps_zoom = $_POST['myGmaps_zoom'];
	$myGmaps_type = $_POST['myGmaps_type'];
	$meta =& $GLOBALS['core']->meta;
	$meta->delPostMeta($post_id,'map');
	$meta->delPostMeta($post_id,'map_options');
	
	$entries = implode(',',$entries);
	foreach ($meta->splitMetaValues($entries) as $tag) {
		$meta->setPostMeta($post_id,'map',$tag);
	}
	$map_options = $myGmaps_center.','.$myGmaps_zoom.','.$myGmaps_type;
	$meta->setPostMeta($post_id,'map_options',$map_options);
	
	if ($_POST['post_type'] == 'page') {
		http::redirect('plugin.php?p=pages&act=page&id='.$post_id);
	} else {
		http::redirect(DC_ADMIN_URL.'post.php?id='.$post_id);
	}
	
	
  } catch (Exception $e) {
    $core->error->add($e->getMessage());
  }
  
} elseif (isset($_POST['updmap'])) {
  try {
    
	$post_id = $_POST['post_id'];
	$myGmaps_center = $_POST['myGmaps_center_upd'];
	$myGmaps_zoom = $_POST['myGmaps_zoom_upd'];
	$myGmaps_type = $_POST['myGmaps_type_upd'];
	$meta =& $GLOBALS['core']->meta;
	
	$meta->delPostMeta($post_id,'map_options');
	
	$map_options = $myGmaps_center.','.$myGmaps_zoom.','.$myGmaps_type;
	$meta->setPostMeta($post_id,'map_options',$map_options);
	
	if ($_POST['post_type'] == 'page') {
		http::redirect('plugin.php?p=pages&act=page&id='.$post_id);
	} else {
		http::redirect(DC_ADMIN_URL.'post.php?id='.$post_id);
	}
	
  } catch (Exception $e) {
    $core->error->add($e->getMessage());
  }
  
}

/* DISPLAY
-------------------------------------------------------- */
?>
<html>
	<head>
		<title><?php echo $page_title; ?></title>
		<?php
		echo
		dcPage::jsToolMan().
		dcPage::jsPageTabs($default_tab).
		dcPage::jsLoad(DC_ADMIN_URL.'?pf=myGmaps/js/_maps_list.js').
		dcPage::jsLoad(DC_ADMIN_URL.'?pf=myGmaps/js/_addmap_map.js');
		?>
        <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
		
		<style type="text/css">
        #map_canvas{height:400px;padding:3px;border:1px solid #999999;margin:-10px 0 1px 0; }
        </style>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
	<body class="popup">
<?php

if (!$core->error->flag())
{
	
	echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Google Maps').' &rsaquo; '.$page_title.'</h2>';
	//
	echo '<div class="multi-part" id="entries-list" title="'.__('Add elements').'">';
	
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
	__('Maps per page').'</label> '.
	$core->formNonce().
	'<input type="submit" value="'.__('filter').'" /></p>'.
	'<p>'.form::hidden(array('add_map_filters'),'myGmaps').
	form::hidden(array('post_id'),$post_id).
	form::hidden(array('p'),'myGmaps').'</p>'.
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
	
	'<p class="col right">'.__('Add selected map elements').' '.
	
	'<input type="submit" name="addmap" value="'.__('ok').'" /></p>'.
	'<p>'.form::hidden(array('post_id'),$post_id).
	form::hidden(array('post_type'),$post_type).
	form::hidden(array('user_id'),$user_id).
	form::hidden(array('cat_id'),$cat_id).
	form::hidden(array('status'),$status).
	form::hidden(array('month'),$month).
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('page'),$page).
	form::hidden(array('nb'),$nb_per_page).
	$core->formNonce().'</p>'.
	'</div>'
	);
	
	if (isset($maps_array)) {
		for ($i = 0; $i < sizeof($maps_array); ++$i) {
			echo '<p style="display:none">'.form::checkbox(array('entries[]'),$maps_array[$i],'true','','','').'</p>';
		}
	}
	
	echo
	'<p><input type="hidden" name="myGmaps_center" id="myGmaps_center" value="'.$myGmaps_center.'" />'.
	'<input type="hidden" name="myGmaps_zoom" id="myGmaps_zoom" value="'.$myGmaps_zoom.'" />'.
	'<input type="hidden" name="myGmaps_type" id="myGmaps_type" value="'.$myGmaps_type.'" /></p>'.
	'</form>';
	
	echo '</div>';
	
	echo '<div class="multi-part" id="settings" title="'.__('Settings').'">';
	
	
	$meta =& $GLOBALS['core']->meta;
	$my_params['post_id'] = $post_id;
	$my_params['no_content'] = true;
				
	$rs = $core->blog->getPosts($my_params);
	if(isset($post)) {
		$meta_rs = $meta->getMetaStr($post->post_meta,'map_options');
		if ($meta_rs) {
			$map_options = explode(",",$meta_rs);
			$myGmaps_center = $map_options[0].','.$map_options[1];
			$myGmaps_zoom = $map_options[2];
			$myGmaps_type = $map_options[3];
		}
	}
	
	echo
	'<form action="'.$p_url.'" method="post" id="map-options">'.
	'<fieldset><legend>'.__('Map parameters').'</legend>'.
	'<p>'.__('Choose map center, zoom level and map type.').'</p>'.
	'</fieldset>'.
	'<p class="area" id="map_canvas"></p>'.
	'<p><input type="hidden" name="myGmaps_center_upd" id="myGmaps_center_upd" value="'.$myGmaps_center.'" />'.
	'<input type="hidden" name="myGmaps_zoom_upd" id="myGmaps_zoom_upd" value="'.$myGmaps_zoom.'" />'.
	'<input type="hidden" name="myGmaps_type_upd" id="myGmaps_type_upd" value="'.$myGmaps_type.'" /></p>'.
	$core->formNonce();
	
	if (isset($has_map) && $has_map == true) {
		echo '<p>'.form::hidden('post_id',$post_id).form::hidden('post_type',$post_type).'<input type="submit" value="'.__('Save').'" name="updmap" /></p>';
	}
	
	echo '</form></div>';
}

dcPage::helpBlock('myGmapsadd');
?>
	</body>
</html>