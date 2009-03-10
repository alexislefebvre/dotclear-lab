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

if (!defined('DC_CONTEXT_ADMIN')) exit;
global $core;

$this_version = $core->plugins->moduleInfo('gallery','version');
$installed_version = $core->getVersion('gallery');
if (version_compare($installed_version,$this_version,'>=')) {
	return;
}
 
function putGlobalSetting($id,$value,$type=null,$label=null,$value_change=true) {
	global $core;
	$old_value = $core->blog->settings->get($id);
	if ($old_value === null)
		$core->blog->settings->put($id,$value,$type,$label,$value_change,true);
	else
		$core->blog->settings->put($id,$old_value,$type,$label,$value_change,true);
}
$themes_re = "#(.*)themes$#";
if (preg_match($themes_re,$core->blog->settings->themes_path)) {
	$gal_default_themes_path = preg_replace("#(.*)themes$#","$1plugins/gallery/default-templates",$core->blog->settings->themes_path);
} else {
	$gal_default_themes_path = 'plugins/gallery/default-templates';
}
$core->blog->settings->setNamespace('gallery');
putGlobalSetting('gallery_galleries_url_prefix','galleries','string','Gallery lists URL prefix');
putGlobalSetting('gallery_gallery_url_prefix','gallery','string','Galleries URL prefix');
putGlobalSetting('gallery_image_url_prefix','image','string','Images URL prefix');
putGlobalSetting('gallery_default_theme','simple','string','Default theme to use');
putGlobalSetting('gallery_nb_images_per_page',24,'integer','Number of images per page');
putGlobalSetting('gallery_nb_galleries_per_page',10,'integer','Number of galleries per page');
putGlobalSetting('gallery_new_items_default','YYYYN','string','Default options for new items management');
putGlobalSetting('gallery_galleries_sort','date','string','Galleries list sort criteria');
putGlobalSetting('gallery_galleries_order','DESC','string','Galleries list sort order criteria');
putGlobalSetting('gallery_galleries_orderbycat',true,'boolean','Galleries list group by category');
putGlobalSetting('gallery_enabled',false,'boolean','Gallery plugin enabled');
#putGlobalSetting('gallery_adv_items',false,'boolean','Gallery items advanced interface');
putGlobalSetting('gallery_themes_path',$gal_default_themes_path,'string','Gallery Themes path');
putGlobalSetting('gallery_entries_include_galleries',false,'boolean','Include selected galeries in Entries tpl');
putGlobalSetting('gallery_entries_include_images',false,'boolean','Include selected images in Entries tpl');
putGlobalSetting('gallery_admin_items_sortby','post_dt','string','Administration items tab ordering (chose from : post_dt,post_title,cat_title,user_id,post_status,post_selected');
putGlobalSetting('gallery_admin_items_order','desc','string','Administration items tab ordering (chose from : asc,desc');
putGlobalSetting('gallery_admin_gals_sortby','post_dt','string','Administration galleries tab ordering (chose from : post_dt,post_title,cat_title,user_id,post_status,post_selected');
putGlobalSetting('gallery_admin_gals_order','desc','string','Administration galleries tab ordering (chose from : asc,desc');

$core->setVersion('gallery',$this_version);

if ($core->blog->settings->gallery_default_theme == 'default') {
	$core->blog->settings->put('gallery_default_theme','simple','string','Default theme to use', true, true);
}

return true;
?>
