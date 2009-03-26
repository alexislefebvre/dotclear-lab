<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dayMode, a plugin for Dotclear 2.
#
# Copyright (c) 2006-2009 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

if ($core->blog->settings->daymode_active === null) {
	try
	{
		// Default settings
		$core->blog->settings->setNameSpace('daymode');
		$core->blog->settings->put('daymode_active',false,'boolean');

		http::redirect($p_url);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

$active = $core->blog->settings->daymode_active;

if (!empty($_POST['saveconfig'])) {
	try
	{
		$core->blog->settings->setNameSpace('daymode');

		$active = (empty($_POST['active']))?false:true;
		$core->blog->settings->put('daymode_active',$active,'boolean');
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
	<title><?php echo __('dayMode'); ?></title>
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('dayMode'); ?></h2>
<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>
<div id="daymode_options">
	<form method="post" action="plugin.php">
	<fieldset>
		<legend><?php echo __('Plugin activation'); ?></legend>
		<p class="field">
		<label class=" classic"><?php echo form::checkbox('active', 1, $active); ?>&nbsp;
		<?php echo __('Enable dayMode');?>
		</label>
		</p>
	</fieldset>
	<input type="hidden" name="p" value="dayMode" />
	<?php echo $core->formNonce(); ?>
	<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
	</form>
</div>
</body>
</html>
