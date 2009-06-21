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

if ($odt_behavior === null) {
	$odt_behavior = "";
}
if ($odt_import_images === null) {
	$odt_import_images = true;
}

if (isset($_POST["save"])) {
	# modifications
	try {
		$odt_behavior = $_POST["odt_behavior"];
		$odt_import_images = !empty($_POST["odt_import_images"]);

		$core->blog->settings->setNameSpace('odt');
		$core->blog->settings->put('odt_behavior',$odt_behavior,'string');
		$core->blog->settings->put('odt_import_images',$odt_import_images,'boolean');
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

		<p><input type="submit" name="save"
		          value="<?php echo __('Save'); ?>" /></p>
	</form>
 
<?php dcPage::helpBlock('odt');?>
</body>
</html>
