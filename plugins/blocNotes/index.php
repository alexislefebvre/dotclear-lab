<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Bloc-Notes.
# Copyright 2007 Moe (http://gniark.net/)
#
# Bloc-Notes is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Bloc-Notes is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://txfx.net/code/wordpress/subscribe-to-comments/
#
# ***** END LICENSE BLOCK *****

	if (!defined('DC_CONTEXT_ADMIN')) { exit; }

	try
	{
		if (!empty($_POST['saveconfig']))
		{
			$core->blog->settings->setNameSpace('blocnotes');
			# Bloc-Notes' text
			$core->blog->settings->put('blocNotes_text',
				$_POST['blocNotes_text'],'text','Bloc-Notes\' text');
			http::redirect($p_url.'&saveconfig=1');
		}
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}

	if (isset($_GET['saveconfig']))
	{
		$msg = __('Configuration successfully updated.');
	}

?>
<html>
<head>
	<title><?php echo __('Bloc-Notes'); ?></title>
	<?php echo dcPage::jsPageTabs($default_tab); ?>
	<style type="text/css">
		textarea {width:100%;}
	</style>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &gt '.__('Bloc-Notes'); ?></h2>

	<?php 
		if (!empty($msg)) {echo '<div class="message">'.$msg.'</div><p></p>';}
	?>

	<div id="settings" title="<?php echo __('settings'); ?>">
		<form method="post" action="<?php echo http::getSelfURI(); ?>">
			<fieldset>
				<legend><?php echo(__('Text')); ?></legend>
				<p class="field">
					<?php echo(blocNotes::adminPostForm()); ?>
				</p>
			</fieldset>

			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
		</form>
	</div>

</body>
</html>