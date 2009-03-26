<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of dayMode, a plugin for DotClear2.
# Copyright (c) 2006-2008 Pep and contributors. All rights
# reserved.
#
# This plugin is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This plugin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this plugin; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
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
