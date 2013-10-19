<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Freshy2, a theme for Dotclear.
# Original WP Theme from Julien de Luca
# (http://www.jide.fr/francais/)
#
# Copyright (c) 2008-2013
# Bruno Hondelatte dsls@morefnu.org
# Pierre Van Glabeke contact@brol.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) exit;

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');
require dirname(__FILE__).'/lib/class.freshy2.config.php';

$core->blog->settings->addNamespace('freshy2');
$freshy2_settings =& $core->blog->settings->freshy2;

$config = new freshy2Config($core);

$sidebar_combo = array(__('None') => 'none',__('Navigation sidebar') =>'nav', __('Extra sidebar')=>'extra');
$menu_combo = array(
	__('simpleMenu') => 'simplemenu',
	__('menuFreshy or menu (Adjaya)') => 'freshymenu'
);
$images = $config->getHeaderImages();
if (!empty($_POST))
{
	$config->custom_theme = $_POST['freshy_custom'];
	$config->top_image = $_POST['freshy_top_image'];
	$config->menu = (isset($_POST['menu']) && $_POST['menu'] == "freshymenu") ? "freshymenu" : "simplemenu";
  $config->menu;
	if (!isset($images[$config->top_image])) {
		$config->top_image = 'default';
	}
	$config->left_sidebar = $_POST['left_sidebar'];
	$config->right_sidebar = $_POST['right_sidebar'];
	$config->store();
	$core->blog->triggerBlog();

 dcPage::success(__('Theme configuration has been successfully updated.'));
}
$custom_themes_combo = $config->getCustomThemes();
$current_custom_theme = $config->custom_theme;
$current_top_image = $config->top_image;
$left_sidebar = $config->left_sidebar;
$right_sidebar = $config->right_sidebar;
$menu = $config->menu;
$has_freshy_menu = $core->plugins->moduleExists('menuFreshy') || $core->plugins->moduleExists('menu');

echo'<style type="text/css" media="screen">';
include dirname(__FILE__).'/lib/admin_style.css';
echo '</style>';

# Options display
echo '<div class="fieldset"><h4>'.__('Preferences').'</h4>';
echo
'<p><label for="freshy_custom">'.__('Custom theme: ').' '.
form::combo('freshy_custom',$config->getCustomThemes(),$current_custom_theme).'</label></p>';
echo '</div>';
if ($has_freshy_menu) {
	echo '<div class="fieldset"><h4>'.__('Menus').'</h4>'.
		'<p><label for="menu">'.__('Menu: ').form::combo('menu',$menu_combo,$menu).'</label></p>'.
		'</div>';
}

echo '<div class="fieldset"><h4>'.__('Sidebars').'</h4>'.
	'<p><label for="left_sidebar">'.__('Left sidebar: ').form::combo('left_sidebar',$sidebar_combo,$left_sidebar).'</label></p>'.
	'<p><label for="right_sidebar">'.__('Right sidebar: ').form::combo('right_sidebar',$sidebar_combo,$right_sidebar).'</label>';
if (!$has_freshy_menu) {
	echo form::hidden('menu','simplemenu');
}
echo '</p>'.
	'</div>';

echo '<div class="fieldset clearfix"><h4>'.__('Top Image').'</h4>';
$nb_img = count($images);
$nb_img_by_col = 1+($nb_img-$nb_img%3)/3;
echo '<div id="imgheaders">';
echo '<p>'.form::radio(array('freshy_top_image','default'),'default',$current_top_image=='default').__('Use custom theme default header').'</p>';
$count=0;
echo '<div class="three-cols"><div class="col"><ul>';
foreach ($images as $ref => $image) {
	if ($count != 0 && $count%$nb_img_by_col==0)
		echo '</ul></div><div class="col"><ul>';
	echo '<li>'.form::radio(array('freshy_top_image',$ref),$ref,($current_top_image==$ref)?1:0).'<img src="'.$image['thumb'].'" alt="'.$ref.'" /></li>';
	$count++;
}
echo '</ul></div></div></div>';
echo '</div>';
?>