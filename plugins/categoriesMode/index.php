<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of categoriesMode, a plugin for Dotclear 2.
#
# Copyright (c) 2007-2011 Adjaya and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$page_title = __('categoriesMode');

# Url de base
$p_url = 'plugin.php?p=categoriesMode';

if ($core->blog->settings->categoriesmode->categoriesmode_active === null) {
	try
	{
		// Default settings
		$core->blog->settings->addNameSpace('categoriesmode');
		$core->blog->settings->categoriesmode->put('categoriesmode_active',false,'boolean');

		http::redirect($p_url);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

$active = $core->blog->settings->categoriesmode->categoriesmode_active;

if (!empty($_POST['saveconfig'])) {
	try
	{
		$core->blog->settings->addNameSpace('categoriesmode');

		$active = (empty($_POST['active'])) ? false : true;
		$core->blog->settings->categoriesmode->put('categoriesmode_active',$active,'boolean');
		$core->blog->triggerBlog();

		$msg = __('Configuration successfully updated.');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
	<title><?php echo $page_title; ?></title>
</head>

<body>
<?php
	echo dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));

if (!empty($msg)) {
  dcPage::success($msg);
}
?>

<div id="categoriesmode_options">
	<form method="post" action="plugin.php">
	<div class="fieldset">
		<h4><?php echo __('Plugin activation'); ?></h4>
		<p class="field">
		<label class="classic"><?php echo form::checkbox('active', 1, $active); ?>&nbsp;
		<?php echo __('Enable categoriesMode');?>
		</label>
		</p>
	</div>
		<p>
	<input type="hidden" name="p" value="categoriesMode" />
	<?php echo $core->formNonce(); ?>
	<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
	</p>
	</form>
</div>
</body>
</html>