<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Popularity Contest.
# Copyright 2007 Moe (http://gniark.net/)
#
# Popularity Contest is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Popularity Contest is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

	$msg = (string)'';
	$errors = array();
	$tab = 'popularityContest';

	$hidden_plugins = array();
	if (strlen($core->blog->settings->popularityContest_hidden_plugins) > 0)
	{
		$hidden_plugins = unserialize(base64_decode($core->blog->settings->popularityContest_hidden_plugins));
	}

	require_once(dirname(__FILE__).'/class.table.php');
	require_once(dirname(__FILE__).'/class.popularityContest.php');

	$popularityContest_time_interval = 
		(is_int($core->blog->settings->popularityContest_time_interval)) ? 
			$core->blog->settings->popularityContest_time_interval : (24*3600);
	$popularityContest_sent = false;

	if (!empty($_POST['saveconfig']))
	{
		try
		{
			$core->blog->settings->setNameSpace('popularitycontest');
			# Time interval in seconds between sends to Popularity Contest
			$popularityContest_time_interval = (empty($_POST['popularityContest_time_interval']))?604800:abs($_POST['popularityContest_time_interval']);
			$core->blog->settings->put('popularityContest_time_interval',
				$popularityContest_time_interval,'integer','Time interval in seconds between sends to Popularity Contest',true,true);
			# Hide plugins
			$popularityContest_hidden_plugins = (!empty($_POST['hidden_plugins']))?base64_encode(serialize($_POST['hidden_plugins'])):base64_encode(serialize(array('')));
			$core->blog->settings->put('popularityContest_hidden_plugins',
				$popularityContest_hidden_plugins,'text','Hidden plugins',true,true);
			$hidden_plugins = $_POST['hidden_plugins'];

			$msg = __('Configuration successfully updated.');
			$tab = 'settings';
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}

	# actions
	if (isset($_POST['popularityContest_send']))
	{
		popularityContest::send($msg,$errors);
		$popularityContest_sent = true;
	}
?>
<html>
<head>
  <title><?php echo __('Popularity Contest'); ?></title>
  <?php 
  		echo(dcPage::jsPageTabs($tab).
  		dcPage::jsLoad('js/_posts_list.js').
  		dcPage::jsLoad('js/filter-controls.js'));
  ?>
  <style type="text/css">
  <!--
  	label {display:inline;}
  -->
  </style>
</head>
<body>

	<h2><?php echo __('Popularity Contest'); ?></h2>

	<?php 
		if (!empty($msg)) {echo '<div class="message">'.$msg.'</div>';}
		if (!empty($errors)) {echo '<div class="error"><strong>'.__('Errors:').'</strong><ul><li>'.implode('</li><li>',$errors).'</li></ul></div>';}

		if (!$popularityContest_sent)
		{
			if (is_int($core->blog->settings->popularityContest_last_report))
			{
				printf('<h3>'.__('Last successful report: %s ago').'</h3>',popularityContest::getDiff(time()-$core->blog->settings->popularityContest_last_report));
			}
			if (is_int($core->blog->settings->popularityContest_last_try))
			{
				printf('<h3>'.__('Last try: %s ago').'</h3>',popularityContest::getDiff(time()-$core->blog->settings->popularityContest_last_try));
			}
		}
	?>

	<div class="multi-part" id="popularityContest" title="<?php echo __('Popularity Contest'); ?>">
		<p><?php echo(__('This plugin only send the following informations to Dotclear Popularity Contest:')); ?></p>
		<ul>
			<?php 
			$infos = array(
				__('the names of installed plugins'),
				sprintf(__('the value of md5(DC_ADMIN_URL) (%s) identify the Dotclear installation with an unique and anonym hash'),'<strong>'.md5(DC_ADMIN_URL).'</strong>'),
				sprintf(__('the Dotclear version (%s)'),'<strong>'.DC_VERSION.'</strong>')
			);
			foreach ($infos as $k)
			{
				echo('<li>'.$k.'</li>');
			}
			?>
		</ul>

		<p><?php echo(__('In the settings, you can hide some plugins and they will be ignored by Dotclear Popularity Contest.')); ?></p>
		<p><a href="http://popcon.gniark.net/"><?php echo(__('Click here to see results.')); ?></a></p>
		<form method="post" action="<?php echo(http::getSelfURI()); ?>">
			<p><input type="submit" name="popularityContest_send" value="<?php echo __('Send a report to Dotclear Popularity Contest'); ?>" /></p>
			<p><?php echo $core->formNonce(); ?></p>
		</form>
		<h2><?php echo(__('Plugins:')); ?></h2>
		<?php echo(popularityContest::getPluginsTable()); ?>
	</div>

	<div class="multi-part" id="settings" title="<?php echo __('settings'); ?>">
		<form method="post" action="<?php echo(http::getSelfURI()); ?>">
			<fieldset>
				<legend><?php echo(__('Popularity Contest')); ?></legend>
				<p class="field">
					<label for="popularityContest_time_interval"><?php echo(__('Send a report:')); ?></label>
					<?php echo(form::combo('popularityContest_time_interval',popularityContest::getComboOptions(),$popularityContest_time_interval)); ?>
				</p>
			</fieldset>

			<h2><?php echo(__('Hide plugins:')); ?></h2>
			<?php echo(popularityContest::getPluginsTable(true)); ?>
			<p class="col checkboxes-helpers"></p>
			<p><input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
			<p><?php echo $core->formNonce(); ?></p>
		</form>
	</div>

</body>
</html>