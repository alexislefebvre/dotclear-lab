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
require_once dirname(__FILE__).'/class.dc.gallery.integration.php';

$this_version = $core->plugins->moduleInfo('gallery','version');
$installed_version = $core->getVersion('gallery');
if (version_compare($installed_version,$this_version,'>=')) {
	return;
}
 # Settings compatibility test
if (!version_compare(DC_VERSION,'2.2alpha','>=')) {
	$core->blog->settings->addNamespace('gallery');
	$gallery_settings =& $core->blog->settings->gallery;
	$system_settings =& $core->blog->settings->system;
} else {
	$core->blog->settings->setNamespace('gallery');
	$gallery_settings =& $core->blog->settings;
	$system_settings =& $core->blog->settings;
}

function putGlobalSetting($id,$value,$type=null,$label=null,$value_change=true) {
	global $core,$gallery_settings;
	$old_value = $gallery_settings->get($id);
	if ($old_value === null)
		$gallery_settings->put($id,$value,$type,$label,$value_change,true);
	else
		$gallery_settings->put($id,$old_value,$type,$label,$value_change,true);
}
$themes_re = "#(.*)themes$#";
if (preg_match($themes_re,$system_settings->themes_path)) {
	$gal_default_themes_path = preg_replace("#(.*)themes$#","$1plugins/gallery/default-templates",$system_settings->themes_path);
} else {
	$gal_default_themes_path = 'plugins/gallery/default-templates';
}

putGlobalSetting('gallery_galleries_url_prefix','galleries','string','Gallery lists URL prefix');
putGlobalSetting('gallery_gallery_url_prefix','gallery','string','Galleries URL prefix');
putGlobalSetting('gallery_image_url_prefix','image','string','Images URL prefix');
putGlobalSetting('gallery_default_theme','simple','string','Default theme to use');
putGlobalSetting('gallery_default_integ_theme','sameasgal','string','Default theme to use (integration mode)');
putGlobalSetting('gallery_nb_images_per_page',24,'integer','Number of images per page');
putGlobalSetting('gallery_nb_galleries_per_page',10,'integer','Number of galleries per page');
putGlobalSetting('gallery_new_items_default','YYYYN','string','Default options for new items management');
putGlobalSetting('gallery_galleries_sort','date','string','Galleries list sort criteria');
putGlobalSetting('gallery_galleries_order','DESC','string','Galleries list sort order criteria');
putGlobalSetting('gallery_galleries_orderbycat',true,'boolean','Galleries list group by category');
putGlobalSetting('gallery_enabled',false,'boolean','Gallery plugin enabled');
putGlobalSetting('gallery_adv_items',false,'boolean','Gallery items advanced interface');
putGlobalSetting('gallery_themes_path',$gal_default_themes_path,'string','Gallery Themes path');
putGlobalSetting('gallery_entries_include_galleries',false,'boolean','Include selected galeries in Entries tpl');
putGlobalSetting('gallery_entries_include_images',false,'boolean','Include selected images in Entries tpl');
putGlobalSetting('gallery_admin_items_sortby','post_dt','string','Administration items tab ordering (chose from : post_dt,post_title,cat_title,user_id,post_status,post_selected)');
putGlobalSetting('gallery_admin_items_order','desc','string','Administration items tab ordering (chose from : asc,desc)');
putGlobalSetting('gallery_admin_gals_sortby','post_dt','string','Administration galleries tab ordering (chose from : post_dt,post_title,cat_title,user_id,post_status,post_selected');
putGlobalSetting('gallery_admin_gals_order','desc','string','Administration galleries tab ordering (chose from : asc,desc)');
putGlobalSetting('gallery_max_ajax_requests',5,'integer','Maximum of simultaneous Ajax requests');

if (function_exists('json_encode')) {
putGlobalSetting('gallery_supported_modes',json_encode(dcGalleryIntegration::$default_supported_modes),'string','Gallery supported integration modes');
}
$core->setVersion('gallery',$this_version);

if ($gallery_settings->gallery_default_theme == 'default') {
	$gallery_settings->put('gallery_default_theme','simple','string','Default theme to use', true, true);
}

return true;
?>
