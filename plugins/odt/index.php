<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of odt, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) {return;}

require_once(dirname(__FILE__)."/inc/lib.odt.utils.php");

# Get settings
$odt_behavior = $core->blog->settings->odt_behavior;
$odt_import_images = $core->blog->settings->odt_import_images;
$odt_img_width = $core->blog->settings->odt_img_width;
$odt_img_height = $core->blog->settings->odt_img_height;

if ($odt_behavior === null) {
	$odt_behavior = "";
}
if ($odt_import_images === null) {
	$odt_import_images = true;
}
if ($odt_img_width === null) {
	$odt_img_width = "8cm";
}
if ($odt_img_height === null) {
	$odt_img_height = "6cm";
}

if (isset($_POST["save"])) {
	# modifications
	try {
		$odt_behavior = $_POST["odt_behavior"];
		$odt_import_images = !empty($_POST["odt_import_images"]);
		$odt_img_width = trim($_POST["odt_img_width"]);
		if (is_numeric($odt_img_width)) $odt_img_width .= "cm";
		$odt_img_height = trim($_POST["odt_img_height"]);
		if (is_numeric($odt_img_height)) $odt_img_height .= "cm";

		$core->blog->settings->setNameSpace('odt');
		$core->blog->settings->put('odt_behavior',$odt_behavior,'string');
		$core->blog->settings->put('odt_import_images',$odt_import_images,'boolean');
		$core->blog->settings->put('odt_img_width',$odt_img_width,'string');
		$core->blog->settings->put('odt_img_height',$odt_img_height,'string');
		$core->blog->settings->setNameSpace('system');

		http::redirect($p_url.'&upd=1');

	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$behavior_choices = array(
	__("disabled") => "",
	__("bottom") => "bottom",
	__("top") => "top"
);

?>
<html>
<head>
	<title><?php echo(__('ODT export')); ?></title>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.
		__('ODT export'); ?></h2>
 

	<?php

	if (!odtUtils::checkConfig()) {
		echo '<p class="error">'.__('Configuration problem, see help for details').'</p>';
	}

	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Settings have been successfully updated.').'</p>';
	}
	?>

	<fieldset><legend><?php echo __('Installation'); ?></legend>

		<p><?php echo __("To add an ODT export link, you have three options:"); ?></p>
		<ul>
			<li><?php echo __("add a new presentation widget called \"ODT export\","); ?></li>
			<li><?php echo __("activate the export link in the form below,"); ?></li>
			<li><?php echo __("add the following tag in your template:"); ?> <code>{{tpl:ODT}}</code></li>
		</ul>

		<p><?php echo __("Warning, the blog posts <em>must</em> be valid XHTML for the plugin to work properly. Remember to use the \"XHTML validation\" link below the post edition area."); ?></p>

	</fieldset>
	<fieldset><legend><?php echo __('Configuration'); ?></legend>

		<form method="post" action="<?php echo($p_url); ?>">
			<p><?php echo $core->formNonce(); ?></p>

			<p><label class="classic"><?php echo(__('Export button:')." ".
					form::combo('odt_behavior',$behavior_choices,
					html::escapeHTML($odt_behavior))); ?>
			</label></p>

			<p><label class="classic"><?php 
				echo(form::checkbox('odt_import_images', 1,
				    (boolean) $odt_import_images).' '.
				    __('Import remote images')); ?></label></p>

			<p><label class="classic"><?php echo(__('Default image size:')." ".
					form::field('odt_img_width', 5, 10,
					html::escapeHTML($odt_img_width)).' x '.
					form::field('odt_img_height', 5, 10,
					html::escapeHTML($odt_img_height))); ?>
			</label></p>

			<p><input type="submit" name="save"
					value="<?php echo __('Save'); ?>" /></p>
		</form>

	</fieldset>
	<fieldset><legend><?php echo __('About'); ?></legend>

		<p><?php echo __("This plugin has been written by Aurelien Bompard.");
		         echo " ";
		         echo __("Feel free to <a href=\"http://aurelien.bompard.org/contact\" onclick=\"window.open(this.href);return false;\">contact me</a> if you encounter issues with it.");
		         echo " ";
			    echo __("You can also <a href=\"http://aurelien.bompard.org/post/2009/06/05/Export-des-billets-Dotclear-en-ODT\" onclick=\"window.open(this.href);return false;\">read the post</a> (in french) introducing it."); ?></p>

	</fieldset>
 
<?php dcPage::helpBlock('odt');?>
</body>
</html>
