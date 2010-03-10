<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Mystique Config plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# This file is hugely inspired from blowupConfig admin page
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

require dirname(__FILE__).'/../mystiqueConfig/lib/class.mystique.config.php';

$can_write_images = mystiqueConfig::canWriteImages();
$admin_url=parse_url(DC_ADMIN_URL);
$crossdomain = ($admin_url['scheme']."://".$admin_url['host']) != http::getHost();
$mystique_base = array(
	"bg_color" => "#000000",
);

$mystique_user = $core->blog->settings->mystique->mystique_style;

if (($mystique_user === null) ||($mystique_user=='')) {
	$core->blog->settings->addNamespace('mystique');
	$core->blog->settings->mystique->put('mystique_style','','string','Mystique custom style',true);
}
$mystique_user = @unserialize($mystique_user);
if (!is_array($mystique_user)) {
	$mystique_user = array();
}
$mystique_user = array_merge($mystique_base,$mystique_user);

$color_radio = array("green","blue","red","grey");

$layout_radio = array(
	"col-1" => array(
		'desc' => __('no sidebar')),
	"col-2-left" => array(
		'desc' => __('1 sidebar, left side'), 
		'dims' => array('fluid' =>'30', 'fixed' =>'310')),
	"col-2-right" => array(
		'desc' => __('1 sidebar, right side'),
		'dims' => array('fluid' =>'70', 'fixed' =>'630')),
	"col-3-left" => array(
		'desc' => __('2 sidebars, both on left side'), 
		'dims' => array('fluid' =>'25;50', 'fixed' =>'230;460')),
	"col-3-right" => array(
		'desc' => __('2 sidebars, both on right side'), 
		'dims' => array('fluid' =>'50;75', 'fixed' =>'480;710')),
	"col-3" => array(
		'desc' => __('2 sidebars, left and right sides'), 
		'dims' => array('fluid' =>'25;75', 'fixed' =>'230;710'))
);

if (!empty($_POST))
{
	try
	{
		$layout=isset($layout_radio[$_POST['layout']])?$_POST['layout']:'col-1';
		$color_scheme=in_array($_POST['color-scheme'],$color_radio)?$_POST['color-scheme']:"green";
		$width_type = ($_POST['width-type']=='fixed')?'fixed':'fluid';
		$mystique_user['column_widths'] = preg_match("#[0-9;]*#",$_POST['column_widths'])?$_POST['column_widths']:0;
		$mystique_user['bg_color'] = mystiqueConfig::adjustColor($_POST['bg-color']);
		$core->blog->settings->addNamespace('mystique');
		$core->blog->settings->mystique->put('mystique_style',serialize($mystique_user));
		$core->blog->settings->mystique->put('mystique_color_scheme',$color_scheme);
		$core->blog->settings->mystique->put('mystique_layout',$layout);
		$core->blog->settings->mystique->put('mystique_width_type',$width_type);
		$core->blog->triggerBlog();
		http::redirect($p_url.'&upd=1');
		exit;
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>


?>
<html>
<head>
  <title><?php echo __('Mystique configuration'); ?></title>
  <?php echo dcPage::jsLoad('index.php?pf=mystiqueConfig/js/jquery.dependClass.js'); ?>
  <?php echo dcPage::jsLoad('index.php?pf=mystiqueConfig/js/jquery.slider.js'); ?>
  <?php echo dcPage::jsLoad('index.php?pf=mystiqueConfig/js/config_layout.js'); ?>
  <?php echo dcPage::jsColorPicker(); ?>
  <?php echo dcPage::jsPageTabs("layout"); ?>
  <link rel='stylesheet' href='index.php?pf=mystiqueConfig/css/admin.css' type='text/css' media='all' />
  <script type="text/javascript">
  //<![CDATA[
  <?php
  echo dcPage::jsVar('dotclear.mystique_public_url',mystiqueConfig::imagesURL());
  echo dcPage::jsVar('dotclear.msg.predefined_styles',__('Predefined styles'));
  echo dcPage::jsVar('dotclear.msg.apply_code',__('Apply code'));
	echo 'var layout_info = {';
	$l = array();
	foreach ($layout_radio as $k => $v) {
		if (isset($v['dims'])) {
			$l[] = "'".$k."': {'fluid':'".$v['dims']['fluid']."', 'fixed':'".$v['dims']['fixed']."'}";
		}
	}
	echo join(",",$l)."};\n";
	echo "var dynamic_preview = ".($crossdomain?'false':'true').";\n";
  ?>
  //]]>
  </script>
</head>

<body>
<?php
echo
'<h2>'.html::escapeHTML($core->blog->name).
' &rsaquo; <a href="blog_theme.php">'.__('Blog aspect').'</a> &rsaquo; '.__('Mystique configuration').'</h2>'.
'<p><a class="back" href="blog_theme.php">'.__('back').'</a></p>';

echo '<div id="layout" class="multi-part" title="'.__('Layout').'">';
?>

<form id="theme_config" action="<?php echo $p_url; ?>" method="post" enctype="multipart/form-data">
<fieldset><legend><?php echo __('Preview'); ?></legend>


<div id="theme-preview">
<?php if ($crossdomain) {
	echo '<div class="error"><strong>'.__('Warning:').'</strong>'.
	__('Admin URL is not on the same domain as blog URL. Dynamic preview is disabled').
	'</div>';
}
?>
<iframe id="previewframe" name="previewframe" src="<?php echo $core->blog->url.'mystiquePreview#home'; ?>"></iframe>
</div>
</fieldset>
		
<fieldset><legend><?php echo __('Layout options'); ?></legend>
<?php

$layout=$core->blog->settings->mystique->mystique_layout;
$color_scheme="green";
$width_type=$core->blog->settings->mystique->mystique_width_type;

echo '<p id="layout-settings" style="float:left; padding-top: 2em;"><label><strong>'.__('Sidebars layout').': </strong></label></p><div id="sidebars" class="radioimg" >';
foreach ($layout_radio as $k => $v) {
	$selected = ($layout == $k)?' selected':'';
	echo '<div class="layout-box"><p><label id="layout-'.$k.'" class="icon'.$selected.'"><span class="desc">'.$v['desc'].'</span></label>'.
		form::radio(array('layout'),$k, $layout == $k).'</p></div>';
}
echo "</div>";
echo '<p style="clear: both;float:left; padding-top: 2em;" id="color-settings"><label><strong>'.__('Color Scheme').':&nbsp;</strong></label></p><div class="radioimg">';
foreach ($color_radio as $k) {
	$selected = ($color_scheme == $k)?' selected':'';
	echo '<div class="layout-box"><p><label id="color-'.$k.'" class="icon'.$selected.'"></label>'.
		form::radio(array('color-scheme'),$k, $color_scheme == $k).'</p></div>';
}
echo "</div>";

echo '<p style="clear: both;"><label class="classic"><strong>'.__('Page width type').' : </strong></label>'.
		form::radio(array('width-type'),"fixed", $width_type == "fixed").' '.__('Fixed layout').' '.
		form::radio(array('width-type'),"fluid", $width_type == "fluid").' '.__('Fluid layout').' '.
		'</p>';

if ($layout != 'col-1') {
	$slider_value = $layout_radio[$layout]['dims'][$width_type];
	$slider_hide='';
} else {
	$slider_value = '0';
	$slider_hide=' style="display: none;"';
}
echo '<div id="dimension-control"'.$slider_hide.'><p><strong>'.__('Column widths :').'</strong></p>'.
	'<p style="padding-top:1em;"><span id="slider">'.
	form::hidden("column_widths",$mystique_user['column_widths']).
	'</span></p></div>';

echo
'<p class="field"><label><strong>'.__('Background color').'</strong> : '.
form::field('bg-color',7,7,$mystique_user['bg_color'],'colorpicker').'</label></p>';
	
echo
'<p class="clear"><input type="submit" value="'.__('save').'" />'.
$core->formNonce().'</p>';
	
echo
	'</fieldset></form></div>'.
	'<p><a href="plugin.php?p=mystiqueConfig&amp;m=config" class="multi-part">'.__('Settings').'</a></p>';
?>

</body>
</html>