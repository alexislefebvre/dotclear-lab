<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }



function setSettings() {
	global $core;
	$galleries_url_prefix = $core->blog->settings->gallery_galleries_url_prefix;
	$gallery_url_prefix = $core->blog->settings->gallery_gallery_url_prefix;
	$image_url_prefix = $core->blog->settings->gallery_image_url_prefix;
	//$images_url_prefix = $core->blog->settings->gallery_images_url_prefix;
	//$browser_url_prefix = $core->blog->settings->gallery_browser_url_prefix;
	$default_theme = $core->blog->settings->gallery_default_theme;
	$nb_images_per_page = $core->blog->settings->gallery_nb_images_per_page;
	$nb_galleries_per_page = $core->blog->settings->gallery_nb_images_per_page;
	$gallery_new_items_default = $core->blog->settings->gallery_new_items_default;
	$gallery_galleries_sort = $core->blog->settings->gallery_galleries_sort;
	$gallery_galleries_order = $core->blog->settings->gallery_galleries_order;
	$gallery_galleries_orderbycat = $core->blog->settings->gallery_galleries_orderbycat;
	$gallery_enabled = $core->blog->settings->gallery_enabled;

	$core->blog->settings->setNamespace('gallery');
	$core->blog->settings->put('gallery_galleries_url_prefix',$galleries_url_prefix);
	$core->blog->settings->put('gallery_gallery_url_prefix',$gallery_url_prefix);
	$core->blog->settings->put('gallery_image_url_prefix',$image_url_prefix);
	//$core->blog->settings->put('gallery_images_url_prefix',$images_url_prefix,'string','Filtered Images URL prefix');
	//$core->blog->settings->put('gallery_browser_url_prefix',$browser_url_prefix,'string','Browser URL prefix');
	$core->blog->settings->put('gallery_default_theme',$default_theme);
	$core->blog->settings->put('gallery_nb_images_per_page',$nb_images_per_page);
	$core->blog->settings->put('gallery_nb_galleries_per_page',$nb_galleries_per_page);
	$core->blog->settings->put('gallery_new_items_default',$gallery_new_items_default);
	$core->blog->settings->put('gallery_galleries_sort',$gallery_galleries_sort);
	$core->blog->settings->put('gallery_galleries_order',$gallery_galleries_order);
	$core->blog->settings->put('gallery_galleries_orderbycat',$gallery_galleries_orderbycat);
	$core->blog->settings->put('gallery_enabled',$gallery_enabled);
	http::redirect('plugin.php?p=gallery&m=options&upd=1');
}

$defaults=$core->blog->settings->gallery_new_items_default;
$c_nb_img=$core->blog->settings->gallery_nb_images_per_page;
$c_nb_gal=$core->blog->settings->gallery_nb_galleries_per_page;
$c_sort=$core->blog->settings->gallery_galleries_sort;
$c_order=$core->blog->settings->gallery_galleries_order;
$c_orderbycat=$core->blog->settings->gallery_galleries_orderbycat;
$c_gals_prefix=$core->blog->settings->gallery_galleries_url_prefix;
$c_gal_prefix=$core->blog->settings->gallery_gallery_url_prefix;
$c_img_prefix=$core->blog->settings->gallery_image_url_prefix;
$c_gal_themes_path=$core->blog->settings->gallery_themes_path;

if (!empty($_POST['enable_plugin'])) {
	$core->blog->settings->setNamespace('gallery');
	$core->blog->settings->put('gallery_enabled',true,'boolean');
	setSettings();
	http::redirect('plugin.php?p=gallery');
} elseif (!empty($_POST['disable_plugin'])) {
	$core->blog->settings->setNamespace('gallery');
	$core->blog->settings->put('gallery_enabled',false,'boolean');
	setSettings();
	http::redirect('plugin.php?p=gallery');
} elseif (!empty($_POST['save_item_defaults'])) {
	$items_default=array();
	$items_default[]=empty($_POST['delete_orphan_media'])?"N":"Y";
	$items_default[]=empty($_POST['delete_orphan_items'])?"N":"Y";
	$items_default[]=empty($_POST['scan_media'])?"N":"Y";
	$items_default[]=empty($_POST['create_posts'])?"N":"Y";
	$items_default[]=empty($_POST['create_thumbs'])?"N":"Y";
	$items_default[]=empty($_POST['update_ts'])?"N":"Y";

	$gallery_new_items_default=implode('',$items_default);
	$core->blog->settings->setNamespace('gallery');
	$core->blog->settings->put('gallery_new_items_default',$gallery_new_items_default,'string','Default options for new items management');
	$defaults=$gallery_new_items_default;
	http::redirect('plugin.php?p=gallery&m=options&upd=1');
} elseif (!empty($_POST['save_general'])) {
	$c_sort = !empty($_POST['galleries_sort'])?$_POST['galleries_sort']:$c_sort;
	$c_order = !empty($_POST['galleries_order'])?$_POST['galleries_order']:$c_order;
	$c_orderbycat = empty($_POST['galleries_orderbycat'])?0:1;
	$c_nb_img = !empty($_POST['nb_images'])?(integer)$_POST['nb_images']:$c_nb_img;
	$c_nb_gal = !empty($_POST['nb_galleries'])?(integer)$_POST['nb_galleries']:$c_nb_gal;
	$core->blog->settings->setNamespace('gallery');
	$core->blog->settings->put('gallery_nb_images_per_page',$c_nb_img,'integer','Number of images per page');
	$core->blog->settings->put('gallery_nb_galleries_per_page',$c_nb_gal,'integer','Number of galleries per page');
	$core->blog->settings->put('gallery_galleries_sort',$c_sort,'string','Galleries list sort criteria');
	$core->blog->settings->put('gallery_galleries_order',$c_order,'string','Galleries list sort order criteria');
	$core->blog->settings->put('gallery_galleries_orderbycat',$c_orderbycat,'boolean','Galleries list group by category');
	$core->blog->triggerBlog();
	http::redirect('plugin.php?p=gallery&m=options&upd=1');
} elseif (!empty($_POST['save_advanced'])) {
	$c_gals_prefix = !empty($_POST['galleries_prefix'])?$_POST['galleries_prefix']:$c_gals_prefix;
	$c_gal_prefix = !empty($_POST['gallery_prefix'])?$_POST['gallery_prefix']:$c_gal_prefix;
	$c_img_prefix = !empty($_POST['images_prefix'])?$_POST['images_prefix']:$c_img_prefix;
	$c_gal_themes_path = !empty($_POST['themes_path'])?$_POST['themes_path']:$c_img_prefix;
	$core->blog->settings->setNamespace('gallery');
	$core->blog->settings->put('gallery_galleries_url_prefix',$c_gals_prefix,'string','Gallery lists URL prefix');
	$core->blog->settings->put('gallery_gallery_url_prefix',$c_gal_prefix,'string','Galleries URL prefix');
	$core->blog->settings->put('gallery_image_url_prefix',$c_img_prefix,'string','Images URL prefix');
	$core->blog->settings->put('gallery_themes_path',$c_gal_themes_path,'string','Gallery Themes path');
	$core->blog->triggerBlog();
	http::redirect('plugin.php?p=gallery&m=options&upd=1');
}



$c_delete_orphan_media=($defaults{0} == "Y");
$c_delete_orphan_items=($defaults{1} == "Y");
$c_scan_media=($defaults{2} == "Y");
$c_create_posts=($defaults{3} == "Y");
$c_create_thumbs=($defaults{4} == "Y");
$c_update_ts=($defaults{5} == "Y");
?>
<html>
<head>
  <title><?php echo __('Gallery Items'); ?></title>
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
if (is_null($core->blog->settings->gallery_enabled) || !$core->blog->settings->gallery_enabled) {
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
		form::hidden('p','gallery').
		form::hidden('m','options').$core->formNonce().
		'<input type="submit" name="save_general" value="'.__('Save').'" />'.
		'</fieldset></form>';

	echo '<form action="plugin.php" method="post" id="default_form">'.
		'<fieldset><legend>'.__('New Items default options').'</legend>'.
		'<p><label class="classic">'.form::checkbox('delete_orphan_media',1,$c_delete_orphan_media).
		__('Delete orphan media').'</label></p>'.
		'<p><label class="classic">'.form::checkbox('delete_orphan_items',1,$c_delete_orphan_items).
		__('Delete orphan image-posts').'</label></p>'.
		'<p><label class="classic">'.form::checkbox('scan_media',1,$c_scan_media).
		__('Scan dir for new media').'</label></p>'.
		'<p><label class="classic">'.form::checkbox('create_posts',1,$c_create_posts).
		__('Create image-posts for media in dir').'</label></p> '.
		'<p><label class="classic">'.form::checkbox('create_thumbs',1,$c_create_thumbs).
		__('Create missing thumbnails').'</label></p> '.
		'<p><label class="classic">'.form::checkbox('update_ts',1,$c_update_ts).
		__('Set post date to image exif date').'</label></p> '.
		form::hidden('p','gallery').
		form::hidden('m','options').$core->formNonce().
		'<input type="submit" name="save_item_defaults" value="'.__('Save').'" />'.
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
		'<p><label class=" classic">'. __('Gallery themes path').' : '.
		form::field('themes_path', 60, 255, $c_gal_themes_path).
		'</label></p>'.
		form::hidden('p','gallery').
		form::hidden('m','options').$core->formNonce().
		'<input type="submit" name="save_advanced" value="'.__('Save').'" />'.
		'</fieldset></form>';
}

?>

</div>
</body>
</html>
