<?php

$post_id		= isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : null;
$page		= isset($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page	= 30;

if (is_null($post_id)) {
	$core->error->add(__('Post ID is missing'));
}

if (!$core->error->flag())
{
	$post_type = array();
	$post_types = $core->getPostTypes();
	
	$meta = array(
		'center' => array($core->blog->settings->myGmaps->center),
		'zoom' => array($core->blog->settings->myGmaps->zoom),
		'map_type' => array($core->blog->settings->myGmaps->map_type)
	);
	
	foreach ($post_types as $k => $v) {
		array_push($post_type,$k);
	}
	
	$post = $core->blog->getPosts(array('post_id' => $post_id, 'post_type' => $post_type));
	
	$redir = sprintf(
		(array_key_exists($post->post_type,$post_types) ? $post_types[$post->post_type]['admin_url'] : ''),
		$post_id
	);
	
	$post_meta = $core->meta->getMetaArray($post->post_meta);
	foreach ($meta as $k => $v) {
		if (array_key_exists($k,$post_meta)) {
			$meta[$k] = $post_meta[$k];
		}
	}
	foreach ($meta as $k => $v) {
		if (array_key_exists($k,$_POST)) {
			$meta[$k] = array($_POST[$k]);
		}
	}
	
	# Bind map elements
	if (isset($_POST['bind'])) {
		try {
			foreach ($_POST['entries'] as $id) {
				$core->meta->delPostMeta($post_id,'map',$id);
				$core->meta->setPostMeta($post_id,'map',$id);
			}
			http::redirect(sprintf('%1$s&go=maps_post&post_id=%2$s&upd=%3$s',$p_url,$post_id,1));
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
	# Unbind map elements
	if (isset($_POST['unbind'])) {
		try {
			foreach ($_POST['entries'] as $id) {
				$core->meta->delPostMeta($post_id,'map',$id);
			}
			http::redirect(sprintf('%1$s&go=maps_post&post_id=%2$s&upd=%3$s',$p_url,$post_id,2));
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
	# Save map config
	if (isset($_POST['save'])) {
		try {
			foreach ($meta as $k => $v) {
				$core->meta->delPostMeta($post_id,$k);
				$core->meta->setPostMeta($post_id,$k,$v[0]);
			}
			http::redirect(sprintf('%1$s&go=maps_post&post_id=%2$s&upd=%3$s',$p_url,$post_id,3));
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
	$bound = array();
	
	$maps = $core->meta->getMetadata(array(
		'meta_type' => 'map',
		'post_id' => $post_id
	));
	
	while ($maps->fetch()) {
		array_push($bound,$maps->meta_id);
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
		$post_list = new adminMyGmapsList($core,$posts,$posts->count(),$bound);
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}

	/* DISPLAY
	-------------------------------------------------------- */	
	echo
	'<html>'.
	'<head>'.
		'<title>'.__('Google Map').'</title>'.
		dcPage::jsLoad('index.php?pf=myGmaps/js/_maps_post.js').
		myGmapsUtils::jsCommon().
		myGmapsUtils::jsData($bound).
		'<link type="text/css" rel="stylesheet" href="'.DC_ADMIN_URL.'?pf=myGmaps/css/style.css" />'.
	'</head>'.
	'<body>';
	
	if (isset($_GET['upd'])) {
		$msg = '';
		if ($_GET['upd'] === '1') {
			$msg = __('Selected map elements have been successfully bound');
		}
		if ($_GET['upd'] === '2') {
			$msg = __('Selected map elements have been successfully unbound');
		}
		if ($_GET['upd'] === '3') {
			$msg = __('Map configuration has been successfully saved');
		}
		echo sprintf('<p class="message">%s</p>',$msg);
	}
	
	echo
	'<h2>'.
		html::escapeHTML($core->blog->name).' &rsaquo; '.
		'<a href="'.$redir.'">'.sprintf(
			__('%s %s'),__($post->post_type),
			sprintf('<q>%s</q>',$post->post_title)
		).'</a> &rsaquo; '.
		__('Bind map elements').
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
	form::hidden(array('go'),'maps_post').
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
	'<form action="'.sprintf('%1$s&amp;go=maps_post',$p_url).'" method="post" id="form-entries">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.
	form::hidden('post_id',$post_id).
	'<input type="submit" name="bind" value="'.__('Bind selected map elements').'" />'.
	'<input type="submit" name="unbind" value="'.__('Unbind selected map elements').'" /></p>'.
	$core->formNonce().
	'</div>'.
	'</form>'
	);
	
	echo
	'<div class="area" id="map_canvas"></div>';
	
	echo
	'<form action="'.sprintf('%1$s&amp;go=maps_post',$p_url).'" method="post">'.
	'<p>';
	foreach ($meta as $k => $v) {
		echo form::hidden($k,$v[0]);
	}
	echo
	form::hidden(array('scrollwheel'),($core->blog->settings->myGmaps->scrollwheel ? 'true' : 'false')).
	form::hidden(array('post_id'),$post_id).
	$core->formNonce().
	'<input type="submit" value="'.__('Auto fit').' (a)" '.
	'accesskey="a" name="autofit" /> '.
	'<input type="submit" value="'.__('Save map configuration (center, zoom, type)').' (s)" '.
	'accesskey="s" name="save" /> '.
	'</p>'.
	'</form>';
	
	echo
	'<p class="area" id="map-details-area" >'.
		'<label for="map-details">'.__('Map details:').'</label>'.
		'<div id="map-details"></div>'.
	'</p>';
}

echo
'</body>'.
'</html>';
?>
