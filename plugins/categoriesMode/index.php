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
	<title><?php echo __('categoriesMode'); ?></title>
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('categoriesMode'); ?></h2>
<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>
<div id="categoriesmode_options">
	<form method="post" action="plugin.php">
	<fieldset>
		<legend><?php echo __('Plugin activation'); ?></legend>
		<p class="field">
		<label class=" classic"><?php echo form::checkbox('active', 1, $active); ?>&nbsp;
		<?php echo __('Enable categoriesMode');?>
		</label>
		</p>
	</fieldset>
		<p>
	<input type="hidden" name="p" value="categoriesMode" />
	<?php echo $core->formNonce(); ?>
	<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
	</p>
	</form>
</div>
</body>
</html>
