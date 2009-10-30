<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Typo plugin for Dotclear 2.
#
# Copyright (c) 2008 Franck Paul and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

// Setting default parameters if missing configuration
if (is_null($core->blog->settings->typo_active)) {
	try {
		$core->blog->settings->setNameSpace('typo');

		// Default state is active for entries content and inactive for comments
		$core->blog->settings->put('typo_active',true,'boolean');
		$core->blog->settings->put('typo_entries',true,'boolean');
		$core->blog->settings->put('typo_comments',false,'boolean');
		$core->blog->triggerBlog();
		http::redirect(http::getSelfURI());
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
} else {
	// Setting new 1.3 parameter if missing in configuration
	if (is_null($core->blog->settings->typo_entries)) {
		try {
			// Default state is active for entries content
			$core->blog->settings->setNameSpace('typo');
			$core->blog->settings->put('typo_entries',true,'boolean');
			$core->blog->triggerBlog();
			http::redirect(http::getSelfURI());
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
}

// Getting current parameters
$typo_active = (boolean)$core->blog->settings->typo_active;
$typo_entries = (boolean)$core->blog->settings->typo_entries;
$typo_comments = (boolean)$core->blog->settings->typo_comments;

// Saving new configuration
if (!empty($_POST['saveconfig'])) {
	try
	{
		$core->blog->settings->setNameSpace('typo');

		$typo_active = (empty($_POST['active']))?false:true;
		$typo_entries = (empty($_POST['entries']))?false:true;
		$typo_comments = (empty($_POST['comments']))?false:true;
		$core->blog->settings->put('typo_active',$typo_active,'boolean');
		$core->blog->settings->put('typo_entries',$typo_entries,'boolean');
		$core->blog->settings->put('typo_comments',$typo_comments,'boolean');
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
	<title><?php echo __('Typo'); ?></title>
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('Typo'); ?></h2>

<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>

<div id="typo_options">
	<form method="post" action="plugin.php">
	<fieldset>
		<legend><?php echo __('Plugin activation'); ?></legend>
		<p class="field">
			<?php echo form::checkbox('active', 1, $typo_active); ?>
			<label class="classic" for="active"><?php echo __('Enable Typo for this blog'); ?></label>
		</p>
	</fieldset>

	<fieldset>
		<legend><?php echo __('Options'); ?></legend>
		<p class="field">
			<?php echo form::checkbox('entries', 1, $typo_entries); ?>
			<label class="classic" for="entries"><?php echo __('Enable Typo for entries'); ?></label>
		</p>
		<p class="form-note"><?php echo __('Activating this option enforces typographic replacements in blog entries'); ?></p>
		<p class="field">
			<?php echo form::checkbox('comments', 1, $typo_comments); ?>
			<label class="classic" for="comments"><?php echo __('Enable Typo for comments'); ?></label>
		</p>
		<p class="form-note"><?php echo __('Activating this option enforces typographic replacements in blog comments (excluding trackbacks)'); ?></p>
	</fieldset>

	<p><input type="hidden" name="p" value="typo" />
	<?php echo $core->formNonce(); ?>
	<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
	</p>
	</form>
</div>

</body>
</html>
