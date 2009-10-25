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
$core->gallery= new dcGallery($core);
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
	$dirs_combo['/'.$v->relname] = $v->relname;
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
	$combo_action[__('Remove image-post')] = 'removeimgpost';
	$combo_action[__('add tags')] = 'tags';
}
$combo_action[__('change category')] = 'category';
if ($core->auth->check('admin',$core->blog->id)) {
	$combo_action[__('change author')] = 'author';
}
/*if ($core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('delete')] = 'delete';
}*/


$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$nb_per_page =  30;
if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	if ($nb_per_page != $_GET['nb']) {
		$show_filters = true;
	}
	$nb_per_page = (integer) $_GET['nb'];
}

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;

# --BEHAVIOR-- adminPostsActionsCombo
/*$core->callBehavior('adminPostsActionsCombo',array(&$combo_action));*/
$default_tab ='gal_list';

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
  <link rel="stylesheet" type="text/css" href="index.php?pf=gallery/admin_css/items_adv.css" />
  <script type="text/javascript" src="index.php?pf=gallery/js/_items_adv.js"></script>
</head>
<body>

<?php

echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; '.__('Galleries').' &gt; '.__('Entries').'</h2>';
echo '<p><a href="plugin.php?p=gallery" class="multi-part">'.__('Galleries').'</a></p>';
echo '<div class="multi-part" id="gal_list" title="'.__('Images').'">';

?>
<div id="items-section">

	<div id="items-list">
		<div id="items-header">
			<h2>Filters</h2>
		</div>
		<div id="all-items">
		</div>
		<div style="clear: both;"></div>
		<div id="items-footer">
			<p>Pagination</p>
		</div>
</div>
</div>
<div id="side-menu">
	<div class="menu-section" id="img-details"><h2>Image</h2>
		<div id="thumb-container">
			<img id="details-thumb" src="images/media/image.png" />
		</div>
		<p><strong>Title :</strong><span id="details-title"></span></p>
	</div>
	<div class="menu-section"><h2>Operations</h2>
		<p>Title :</p>
	</div>
	</div>
</div>
<?php 
echo '<p><a href="plugin.php?p=gallery&amp;m=newitems" class="multi-part">'.__('Manage new items').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=options" class="multi-part">'.__('Options').'</a></p>';
if ($core->auth->isSuperAdmin())
	echo '<p><a href="plugin.php?p=gallery&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
?>
</body>
</html>