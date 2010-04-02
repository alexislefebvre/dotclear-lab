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

require dirname(__FILE__).'/class.dc.gallery.integration.php';

$integ = new dcGalleryIntegration ($core); 

function setSettings() {
	global $core;
	$galleries_url_prefix = $core->gallery->settings->gallery_galleries_url_prefix;
	$gallery_url_prefix = $core->gallery->settings->gallery_gallery_url_prefix;
	$image_url_prefix = $core->gallery->settings->gallery_image_url_prefix;
	//$images_url_prefix = $core->gallery->settings->gallery_images_url_prefix;
	//$browser_url_prefix = $core->gallery->settings->gallery_browser_url_prefix;
	$default_theme = $core->gallery->settings->gallery_default_theme;
	$default_integ_theme = $core->gallery->settings->gallery_default_integ_theme;
	$nb_images_per_page = $core->gallery->settings->gallery_nb_images_per_page;
	$nb_galleries_per_page = $core->gallery->settings->gallery_nb_galleries_per_page;
	$gallery_new_items_default = $core->gallery->settings->gallery_new_items_default;
	$gallery_galleries_sort = $core->gallery->settings->gallery_galleries_sort;
	$gallery_galleries_order = $core->gallery->settings->gallery_galleries_order;
	$gallery_galleries_orderbycat = $core->gallery->settings->gallery_galleries_orderbycat;
	$gallery_entries_include_galleries = $core->gallery->settings->gallery_entries_include_galleries;
	$gallery_entries_include_images = $core->gallery->settings->gallery_entries_include_images;
	$gallery_enabled = $core->gallery->settings->gallery_enabled;

	$core->gallery->settings->put('gallery_galleries_url_prefix',$galleries_url_prefix);
	$core->gallery->settings->put('gallery_gallery_url_prefix',$gallery_url_prefix);
	$core->gallery->settings->put('gallery_image_url_prefix',$image_url_prefix);
	//$core->gallery->settings->put('gallery_images_url_prefix',$images_url_prefix,'string','Filtered Images URL prefix');
	//$core->gallery->settings->put('gallery_browser_url_prefix',$browser_url_prefix,'string','Browser URL prefix');
	$core->gallery->settings->put('gallery_default_theme',$default_theme);
	$core->gallery->settings->put('gallery_default_integ_theme',$default_integ_theme);
	$core->gallery->settings->put('gallery_nb_images_per_page',$nb_images_per_page);
	$core->gallery->settings->put('gallery_nb_galleries_per_page',$nb_galleries_per_page);
	$core->gallery->settings->put('gallery_new_items_default',$gallery_new_items_default);
	$core->gallery->settings->put('gallery_galleries_sort',$gallery_galleries_sort);
	$core->gallery->settings->put('gallery_galleries_order',$gallery_galleries_order);
	$core->gallery->settings->put('gallery_galleries_orderbycat',$gallery_galleries_orderbycat);
	$core->gallery->settings->put('gallery_entries_include_images',$gallery_entries_include_images);
	$core->gallery->settings->put('gallery_entries_include_galleries',$gallery_entries_include_galleries);
	$core->gallery->settings->put('gallery_enabled',$gallery_enabled);
	http::redirect('plugin.php?p=gallery&m=options&upd=1');
}

$defaults=$core->gallery->settings->gallery_new_items_default;
$c_nb_img=$core->gallery->settings->gallery_nb_images_per_page;
$c_nb_gal=$core->gallery->settings->gallery_nb_galleries_per_page;
$c_sort=$core->gallery->settings->gallery_galleries_sort;
$c_order=$core->gallery->settings->gallery_galleries_order;
$c_orderbycat=$core->gallery->settings->gallery_galleries_orderbycat;
$c_gals_prefix=$core->gallery->settings->gallery_galleries_url_prefix;
$c_gal_prefix=$core->gallery->settings->gallery_gallery_url_prefix;
$c_img_prefix=$core->gallery->settings->gallery_image_url_prefix;
$c_admin_gals_sortby=$core->gallery->settings->gallery_admin_gals_sortby;
$c_admin_gals_order=$core->gallery->settings->gallery_admin_gals_order;
$c_admin_items_sortby=$core->gallery->settings->gallery_admin_items_sortby;
$c_admin_items_order=$core->gallery->settings->gallery_admin_items_order;
$c_default_theme=$core->gallery->settings->gallery_default_theme;
$c_default_integ_theme=$core->gallery->settings->gallery_default_integ_theme;

if (!empty($_POST['enable_plugin'])) {
	$core->gallery->settings->put('gallery_enabled',true,'boolean');
	setSettings();
	http::redirect('plugin.php?p=gallery');
} elseif (!empty($_POST['disable_plugin'])) {
	$core->gallery->settings->put('gallery_enabled',false,'boolean');
	setSettings();
	http::redirect('plugin.php?p=gallery');
} elseif (!empty($_POST['save_item_defaults'])) {
	$items_default=array();
	$items_default[]=empty($_POST['delete_orphan_media'])?"N":"Y";
	$items_default[]=empty($_POST['delete_orphan_items'])?"N":"Y";
	$items_default[]=empty($_POST['create_media'])?"N":"Y";
	$items_default[]=empty($_POST['create_items'])?"N":"Y";
	$items_default[]=empty($_POST['create_items_for_new_media'])?"N":"Y";
	$items_default[]=empty($_POST['update_ts'])?"N":"Y";

	$gallery_new_items_default=implode('',$items_default);
	$core->gallery->settings->put('gallery_new_items_default',$gallery_new_items_default,'string','Default options for new items management');
	$defaults=$gallery_new_items_default;
	http::redirect('plugin.php?p=gallery&m=options&upd=1');
} elseif (!empty($_POST['save_general'])) {
	$c_sort = !empty($_POST['galleries_sort'])?$_POST['galleries_sort']:$c_sort;
	$c_order = !empty($_POST['galleries_order'])?$_POST['galleries_order']:$c_order;
	$c_orderbycat = empty($_POST['galleries_orderbycat'])?0:1;
	$c_nb_img = !empty($_POST['nb_images'])?(integer)$_POST['nb_images']:$c_nb_img;
	$c_nb_gal = !empty($_POST['nb_galleries'])?(integer)$_POST['nb_galleries']:$c_nb_gal;
	$c_admin_gals_sortby = !empty($_POST['admin_gals_sortby'])?$_POST['admin_gals_sortby']:$c_admin_gals_sortby;
	$c_admin_gals_order = !empty($_POST['admin_gals_order'])?$_POST['admin_gals_order']:$c_admin_gals_order;
	$c_admin_items_sortby = !empty($_POST['admin_items_sortby'])?$_POST['admin_items_sortby']:$c_admin_items_sortby;
	$c_admin_items_order = !empty($_POST['admin_items_order'])?$_POST['admin_items_order']:$c_admin_items_order;
	$c_default_theme = !empty($_POST['default_theme'])?$_POST['default_theme']:$c_default_theme;
	$c_default_integ_theme = !empty($_POST['default_integ_theme'])?$_POST['default_integ_theme']:$c_default_integ_theme;
	$core->gallery->settings->put('gallery_nb_images_per_page',$c_nb_img);
	$core->gallery->settings->put('gallery_nb_galleries_per_page',$c_nb_gal);
	$core->gallery->settings->put('gallery_galleries_sort',$c_sort);
	$core->gallery->settings->put('gallery_galleries_order',$c_order);
	$core->gallery->settings->put('gallery_galleries_orderbycat',$c_orderbycat);
	$core->gallery->settings->put('gallery_admin_gals_sortby',$c_admin_gals_sortby);
	$core->gallery->settings->put('gallery_admin_gals_order',$c_admin_gals_order);
	$core->gallery->settings->put('gallery_admin_items_sortby',$c_admin_items_sortby);
	$core->gallery->settings->put('gallery_admin_items_order',$c_admin_items_order);
	$core->gallery->settings->put('gallery_default_theme',$c_default_theme);
	$core->gallery->settings->put('gallery_default_integ_theme',$c_default_integ_theme);
	$core->blog->triggerBlog();
	http::redirect('plugin.php?p=gallery&m=options&upd=1');
} elseif (!empty($_POST['save_integration'])) {
	$modes = $integ->getModes();
	foreach ($modes as $k=>$v) {
		$prefix = 'c_integ_'.$k; 
		$img = !empty($_POST[$prefix.'_img'])?$_POST[$prefix.'_img']:'none';
		$gal = !empty($_POST[$prefix.'_gal'])?$_POST[$prefix.'_gal']:'none';
		$integ->setMode($k,$img,$gal);
	}
	$integ->save();
	$core->emptyTemplatesCache();
	$core->blog->triggerBlog();
	http::redirect('plugin.php?p=gallery&m=options&upd=1');

} elseif (!empty($_POST['save_advanced'])) {
	$c_gals_prefix = !empty($_POST['galleries_prefix'])?$_POST['galleries_prefix']:$c_gals_prefix;
	$c_gal_prefix = !empty($_POST['gallery_prefix'])?$_POST['gallery_prefix']:$c_gal_prefix;
	$c_img_prefix = !empty($_POST['images_prefix'])?$_POST['images_prefix']:$c_img_prefix;
	$core->gallery->settings->put('gallery_galleries_url_prefix',$c_gals_prefix);
	$core->gallery->settings->put('gallery_gallery_url_prefix',$c_gal_prefix);
	$core->gallery->settings->put('gallery_image_url_prefix',$c_img_prefix);
	$core->blog->triggerBlog();
	http::redirect('plugin.php?p=gallery&m=options&upd=1');
}

$integ_gal_combo = array(__('No integration') => 'none', __('All galleries') => 'all', __('Selected galleries') => 'selected');
$integ_img_combo = array(__('No integration') => "none", __('All images') => 'all', __('Selected images') => 'selected');
$sortby_combo = array(
__('Date') => 'post_dt',
__('Title') => 'post_title',
__('Category') => 'cat_title',
__('Author') => 'user_id',
__('Status') => 'post_status',
__('Selected') => 'post_selected'
);

$integrations = array(
__("Home") => array('field' => 'home', 'img' => 'none','gal' => 'none'),
__("Category") => array('field' => 'home', 'img' => 'none','gal' => 'none'),
__("Tags") => array('field' => 'home', 'img' => 'none','gal' => 'none'),
__("Tag") => array('field' => 'home', 'img' => 'none','gal' => 'none'),
__("Archives") => array('field' => 'home', 'img' => 'none','gal' => 'none'),
__("Search") => array('field' => 'home', 'img' => 'none','gal' => 'none')
);

$themes = $core->gallery->getThemes();
$themes_integ = $themes;
$themes_integ[__('same as gallery theme')] = 'sameasgal';
if (strlen($defaults)<6) 
	$defaults="YYYYYY";
$c_delete_orphan_media=($defaults{0} == "Y");
$c_delete_orphan_items=($defaults{1} == "Y");
$c_create_media=($defaults{2} == "Y");
$c_create_items=($defaults{3} == "Y");
$c_create_items_for_new_media=($defaults{4} == "Y");
$c_update_ts=($defaults{5} == "Y");
?>
<html>
<head>
  <title><?php echo __('Options'); ?></title>
  <?php echo dcPage::jsPageTabs("options");
  ?>
</head>
<body>

<?php
if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Options have been successfully updated.').'</p>';
}
if ($core->error->flag()) {
	echo
	'<div class="error"><strong>'.__('Errors:').'</strong>'.
	$core->error->toHTML().
	'</div>';
}

echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; '.__('Galleries').' &gt; '.__('Options').'</h2>';
echo '<p><a href="plugin.php?p=gallery" class="multi-part">'.__('Galleries').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=items" class="multi-part">'.__('Images').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=newitems" class="multi-part">'.__('Manage new items').'</a></p>';
echo '<div class="multi-part" id="options" title="'.__('Options').'">';

$sort_combo = array(__('Title') => 'title',
	__('Selected entries') => 'selected',
	__('Author') => 'author',
	__('Date') => 'date'
);
$order_combo = array(__('Ascending') => 'asc',
	__('Descending') => 'desc' );
if (is_null($core->gallery->settings->gallery_enabled) || !$core->gallery->settings->gallery_enabled) {
	$public_ok = is_dir($core->blog->public_path);

	echo '<form action="plugin.php" method="post" id="enable_form">'.
		'<fieldset><legend>'.__('Plugin Activation').'</legend>';
	if (!$public_ok) {
		echo '<p>'.sprintf(__("Directory %s does not exist."),$core->blog->public_path).'</p>'.
		'<p>'.__('The plugin cannot be enabled. Please check in your about:config that public_path points to an existing directory.').'</p>'; 
	} else {
		echo '<p>'.__('The plugin is not enabled for this blog yet. Click below to enable it').'</p>'.
			'<p><input type="submit" name="enable_plugin" value="'.__('Enable plugin').'" />'.
			form::hidden('p','gallery').
			form::hidden('m','options').$core->formNonce()."</p>";
	}
	echo '</fieldset></form>';
} else {
	echo '<form action="plugin.php" method="post" id="disable_form">'.
		'<fieldset><legend>'.__('Plugin Activation').'</legend>';
	echo '<p><input type="submit" name="disable_plugin" value="'.__('Disable plugin for this blog').'" />'.
		form::hidden('p','gallery').
		form::hidden('m','options').$core->formNonce().'</p>';
	echo '</fieldset></form>';

	echo '<form action="plugin.php" method="post" id="actions_form">'.
		'<fieldset><legend>'.__('General options').'</legend>'.
		'<h3>'.__('Public-side options').'</h3>'.
		'<p><label class=" classic">'. __('Number of galleries per page').' : '.
		form::field('nb_galleries', 4, 4, $c_nb_gal).
		'</label></p>'.
		'<p><label class=" classic">'. __('Number of images per page').' : '.
		form::field('nb_images', 4, 4, $c_nb_img).
		'</label></p>'.
		'<p><label class=" classic">'. __('Galleries list sort by').' : '.
		form::combo('galleries_sort', $sort_combo, $c_sort).
		'</label></p>'.
		'<p><label class=" classic">'. __('Galleries list order').' : '.
		form::combo('galleries_order', $order_combo, $c_order).
		'</label></p>'.
		'<p><label class=" classic">'. __('Group galeries by category').' : '.
		form::checkbox('galleries_orderbycat', 1, $c_orderbycat).
		'</label></p>'.
		'<p><label class=" classic">'. __('Default gallery theme').' : '.
		form::combo('default_theme', $themes, $c_default_theme).
		'</label></p>'.
		'<p><label class=" classic">'. __('Default gallery theme when integrated').' : '.
		form::combo('default_integ_theme', $themes_integ, $c_default_integ_theme).
		'</label></p>'.
		'<h3>'.__('Administration-side options').'</h3>'.
		'<p><label class=" classic">'. __('Galleries list sort by').' : '.
		form::combo('admin_gals_sortby', $sortby_combo, $c_admin_gals_sortby).
		'</label></p>'.
		'<p><label class=" classic">'. __('Galleries list order').' : '.
		form::combo('admin_gals_order', $order_combo, $c_admin_gals_order).
		'</label></p>'.
		'<p><label class=" classic">'. __('Images list sort by').' : '.
		form::combo('admin_items_sortby', $sortby_combo, $c_admin_items_sortby).
		'</label></p>'.
		'<p><label class=" classic">'. __('Images list order').' : '.
		form::combo('admin_items_order', $order_combo, $c_admin_items_order).
		'</label></p>'.
		form::hidden('p','gallery').
		form::hidden('m','options').$core->formNonce().
		'<input type="submit" name="save_general" value="'.__('Save').'" />'.
		'</fieldset></form>';

	echo '<form action="plugin.php" method="post" id="default_form">'.
		'<fieldset><legend>'.__('New Items default options').'</legend>'.
		'<p><label class="classic">'.form::checkbox('delete_orphan_media',1,$c_delete_orphan_media).
		__('Delete orphan media').'</label></p>'.
		'<p><label class="classic">'.form::checkbox('delete_orphan_items',1,$c_delete_orphan_items).
		__('Delete orphan items').'</label></p>'.
		'<p><label class="classic">'.form::checkbox('create_media',1,$c_create_media).
		__('Create media in database').'</label></p>'.
		'<p><label class="classic">'.form::checkbox('create_items',1,$c_create_items).
		__('Create image-post associated to media').'</label></p> '.
		'<p><label class="classic">'.form::checkbox('create_items_for_new_media',1,$c_create_items_for_new_media).
		__('Create post-image for each new media').'</label></p> '.
		'<p><label class="classic">'.form::checkbox('update_ts',1,$c_update_ts).
		__('Set post date to image exif date').'</label></p> '.
		form::hidden('p','gallery').
		form::hidden('m','options').$core->formNonce().
		'<input type="submit" name="save_item_defaults" value="'.__('Save').'" />'.
		'</fieldset></form>';

	echo '<form action="plugin.php" method="post" id="integration_form">'.
		'<fieldset><legend>'.__('Integration options').'</legend>'.
		'<p>'.__('Several blog pages display lists of entries. This section enables to include images and/or galleries inside these lists').'</p>'.
		'<p>'.__('You can choose either to display all galleries/images or only galleries/images that have the selected state.').'</p>'.
		'<table class="clear"><tr>'.
		'<th>'.__('Type').'</th>'.
		'<th>'.__('Images').'</th>'.
		'<th>'.__('Galleries').'</th>'.
		'</tr>';
		$modes = $integ->getModes();
		foreach ($modes as $k=>$v) {
		echo '<tr><td>'.$k.'</td>'.
		'<td>'.form::combo('c_integ_'.$k.'_img',$integ_img_combo,$v['img']).'</td>'.
		'<td>'.form::combo('c_integ_'.$k.'_gal',$integ_gal_combo,$v['gal']).'</td></tr>';
		}
		echo '</table>'.
		form::hidden('p','gallery').
		form::hidden('m','options').$core->formNonce().
		'<input type="submit" name="save_integration" value="'.__('Save').'" />'.
		'</fieldset></form>';

	echo '<form action="plugin.php" method="post" id="advanced_form">'.
		'<fieldset><legend>'.__('Advanced options').'</legend>'.
		'<p>'.__('All the following values will define default URLs for gallery items').'</p>'.
		'<p><label class=" classic">'. __('Galleries URL prefix').' : '.
		form::field('galleries_prefix', 60, 255, $c_gals_prefix).
		'</label></p>'.
		'<p><label class=" classic">'. __('Gallery URL prefix').' : '.
		form::field('gallery_prefix', 60, 255, $c_gal_prefix).
		'</label></p>'.
		'<p><label class=" classic">'. __('Image URL prefix').' : '.
		form::field('images_prefix', 60, 255, $c_img_prefix).
		'</label></p>'.
		form::hidden('p','gallery').
		form::hidden('m','options').$core->formNonce().
		'<input type="submit" name="save_advanced" value="'.__('Save').'" />'.
		'</fieldset></form>';
}

?>

</div>
<?php
if ($core->auth->isSuperAdmin())
	echo '<p><a href="plugin.php?p=gallery&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
?>
</body>
</html>