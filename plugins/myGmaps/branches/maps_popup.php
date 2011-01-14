<?php

$post_id		= isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : null;
$page		= !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page	= 30;

if (is_null($post_id)) {
	$core->error->add(__('Post ID is missing'));
}

if (!$core->error->flag())
{
	$maps = $core->meta->getMetadata(array(
		'meta_type' => 'map',
		'post_id' => $post_id
	));
	
	# Bind map elements
	if (isset($_POST['bind'])) {
		try {
			$core->meta->delPostMeta($post_id,'map');
			foreach ($_POST['entries'] as $id) {
				$core->meta->setPostMeta($post_id,'map',$id);
			}	
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

	/* Get posts
	-------------------------------------------------------- */
	foreach ($filters as $k => $v) {
		${$k} = array_key_exists($k,$_GET) ? $_GET[$k] : $v;
	}
	
	$params = array();
	$exclude = array();
	
	while ($maps->fetch()) {
		array_push($exclude,$maps->meta_id);
	}
	if (count($exclude) > 0) {
		$params['sql'] = 'AND post_id not '.$core->con->in($exclude);
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
		'<title>'.__('Google Map').'</title>'.
		dcPage::jsLoad('http://maps.google.com/maps/api/js?sensor=false').
		dcPage::jsLoad('index.php?pf=myGmaps/js/myGmaps.js').
		dcPage::jsLoad('index.php?pf=myGmaps/js/_maps_post.js').
		'<link type="text/css" rel="stylesheet" href="'.DC_ADMIN_URL.'?pf=myGmaps/css/style.css" />'.
		'<script type="text/javascript">'.
		'$(function() {'.
		myGmapsUtils::getMapDataJS($exclude).
		'})'.
		'</script>'.
	'</head>'.
	'<body>'.
	'<h2>'.
		html::escapeHTML($core->blog->name).' &rsaquo; '.
		'<a href="'.$p_url.'">'.__('Google Maps').'</a> &rsaquo; '.
		__('Bind map elements to an entry').
	'</h2>';


	if (!$show_filters) {
		echo 
		dcPage::jsLoad('js/filter-controls.js').
		'<p><a id="filter-control" class="form-control" href="#">'.
		__('Filters').'</a></p>';
	}
	echo
	'<form action="'.$p_url.'" method="get" id="filters-form">'.
	form::hidden(array('p'),'myGmaps').
	form::hidden(array('go'),'maps_popup').
	form::hidden(array('post_id'),$post_id).
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
	'</div>'.
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	'</form>';
	
	# Show map elements
	$post_list->display($page,$nb_per_page,$p_url,
	'<form action="'.$p_url.'&amp;go=maps_popup&amp;post_id='.$post_id.'" method="post" id="form-entries">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.
	form::hidden('post_id',$post_id).
	'<input type="submit" name="bind" value="'.__('Bind selected map elements').'" /></p>'.
	$core->formNonce().
	'</div>'.
	'</form>'
	);
	
	echo
	'<div class="area" id="map_canvas"></div>';
}

echo
'</body>'.
'</html>';
?>
