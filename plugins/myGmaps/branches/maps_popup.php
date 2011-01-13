<?php

$post_id		= isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : null;
$page		= !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page	= 30;

if (is_null($post_id)) {
	$core->error->add(__('Post ID is missing'));
}

# Get map elements
try {
	$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
	$params['no_content'] = true;
	$params['post_type'] = 'map';
	$posts = $core->blog->getPosts($params);
	$counter = $core->blog->getPosts($params,true);
	$post_list = new adminMyGmapsList($core,$posts,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

echo
'<html>'.
'<head>'.
	'<title>'.__('Google Map').'</title>'.
	dcPage::jsLoad('index.php?pf=myGmaps/js/_maps_popup.js').
'</head>'.
'<body>'.
'<h2>'.
	html::escapeHTML($core->blog->name).' &rsaquo; '.
	'<a href="'.$p_url.'">'.__('Google Maps').'</a> &rsaquo; '.
	__('Bind map elements to an entry').
'</h2>';

if (!$core->error->flag()) {
	# Show map elements
	$post_list->display($page,$nb_per_page,$p_url,
	'<form action="'.$p_url.'&amp;go=maps_popup&amp;popup=1" method="post" id="form-entries">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.
	form::hidden('post_id',$post_id).
	'<input type="submit" value="'.__('Bind selected map elements').'" /></p>'.
	$core->formNonce().
	'</div>'.
	'</form>'
	);
}

echo
'</body>'.
'</html>';
?>
