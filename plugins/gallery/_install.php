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
$core->blog->settings->setNamespace('gallery');
putGlobalSetting('gallery_galleries_url_prefix','galleries','string','Gallery lists URL prefix');
putGlobalSetting('gallery_gallery_url_prefix','gallery','string','Galleries URL prefix');
putGlobalSetting('gallery_image_url_prefix','image','string','Images URL prefix');
putGlobalSetting('gallery_default_theme','default','string','Default theme to use');
putGlobalSetting('gallery_nb_images_per_page',24,'integer','Number of images per page');
putGlobalSetting('gallery_nb_galleries_per_page',10,'integer','Number of galleries per page');
putGlobalSetting('gallery_new_items_default','YYYYN','string','Default options for new items management');
putGlobalSetting('gallery_galleries_sort','date','string','Galleries list sort criteria');
putGlobalSetting('gallery_galleries_order','DESC','string','Galleries list sort order criteria');
putGlobalSetting('gallery_galleries_orderbycat',true,'boolean','Galleries list group by category');
putGlobalSetting('gallery_enabled',false,'boolean','Gallery plugin enabled');
putGlobalSetting('gallery_themes_path','plugins/gallery/default-templates','string','Gallery Themes path');

$core->setVersion('gallery',$this_version);

return true;
?>
