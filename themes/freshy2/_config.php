<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2008 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) exit;

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');
require dirname(__FILE__).'/lib/class.freshy2.config.php';

$config = new freshy2Config($core);

$sidebar_combo = array(__('None') => 'none',__('Navigation sidebar') =>'nav', __('Extra sidebar')=>'extra');
$custom_themes_combo = $config->getCustomThemes();
$images = $config->getHeaderImages();
$current_custom_theme = $core->blog->settings->freshy2_custom;
$current_top_image = $core->blog->settings->freshy2_top_image;
$left_sidebar = $core->blog->settings->freshy2_sidebar_left;
$right_sidebar = $core->blog->settings->freshy2_sidebar_right;
if ($current_custom_theme == null) {
	$current_custom_theme = 'default';
	$current_top_image = 'default';
	$left_sidebar = 'none';
	$right_sidebar = 'nav';
}
if (!empty($_POST))
{
	$current_custom_theme = $_POST['freshy_custom'];
	$current_top_image = $_POST['freshy_top_image'];
	if (!isset($images[$current_top_image])) {
		$current_top_image = 'default';
	}
	$left_sidebar = $_POST['left_sidebar'];
	$right_sidebar = $_POST['right_sidebar'];
	$core->blog->settings->setNamespace('themes');
	$core->blog->settings->put('freshy2_custom',$current_custom_theme,'string');
	$core->blog->settings->put('freshy2_top_image',$current_top_image,'string');
	$core->blog->settings->put('freshy2_sidebar_left',$left_sidebar,'string');
	$core->blog->settings->put('freshy2_sidebar_right',$right_sidebar,'string');
	$core->blog->triggerBlog();
}
echo'<style type="text/css" media="screen">';
include dirname(__FILE__).'/lib/admin_style.css';
echo '</style>';
echo '<script type="text/javascript" src="js/_blog_theme.js"></script>';

# Options display
echo '<fieldset><legend>'.__('Preferences').'</legend>';
echo
'<p class="field"><label>'.__('Custom theme:').' '.
form::combo('freshy_custom',$config->getCustomThemes(),$current_custom_theme).'</label></p>';
echo '</fieldset>';

echo '<fieldset><legend>'.__('Sidebars').'</legend>'.
	"<p>".__('Left sidebar')." : ".form::combo('left_sidebar',$sidebar_combo,$left_sidebar)."</p>".
	"<p>".__('Right sidebar')." : ".form::combo('right_sidebar',$sidebar_combo,$right_sidebar)."</p>".
	'</fieldset>';

echo '<fieldset><legend>'.__('Top Image').'</legend>';
$nb_img = count($images);
$nb_img_by_col = 1+($nb_img-$nb_img%3)/3;
echo '<div id="imgheaders">';
echo "<p>".form::radio(array('freshy_top_image','default'),'default',$current_top_image=='default').__('Use custom theme default header')."</p>";
$count=0;
echo '<div class="three-cols"><div class="col"><ul>';
foreach ($images as $ref => $image) {
	if ($count != 0 && $count%$nb_img_by_col==0)
		echo '</ul></div><div class="col"><ul>';
	echo '<li>'.form::radio(array('freshy_top_image',$ref),$ref,$current_top_image==$ref).'<img src="'.$image['thumb'].'" alt="'.$ref.'" /></li>';
	$count++;
}
echo '</ul></div></div>';
echo '</fieldset>';
?>
