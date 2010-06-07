<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Popularity Contest, a plugin for Dotclear 2
# Copyright (C) 2007,2009,2010 Moe (http://gniark.net/)
#
# Popularity Contest is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Popularity Contest is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {exit;}

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/admin');

$settings =& $core->blog->settings;

$msg = '';
$tab = 'popularityContest';

$time_interval_last_try =
	$_SERVER['REQUEST_TIME'] - $settings->popularityContest_last_report;
$can_send_report = ($time_interval_last_try >= (30*60));

$hidden_plugins = array();
if (strlen($settings->popularityContest_hidden_plugins) > 0)
{
	$hidden_plugins = unserialize(base64_decode($settings->popularityContest_hidden_plugins));
}
if (!is_array($hidden_plugins)) {$hidden_plugins = array();}

require_once(dirname(__FILE__).'/php-xhtml-table/class.table.php');
require_once(dirname(__FILE__).'/inc/lib.popularityContest.php');

$popularityContest_time_interval = 
	(is_int($settings->popularityContest_time_interval)) ? 
		$settings->popularityContest_time_interval : (24*3600);
$popularityContest_sent = false;

# actions
if (!empty($_POST['saveconfig']))
{
	try
	{
		$settings->setNameSpace('popularitycontest');
		# Time interval in seconds between sends to Popularity Contest
		$popularityContest_time_interval =
			(empty($_POST['popularityContest_time_interval']))
			? 604800:abs($_POST['popularityContest_time_interval']);
		$settings->put('popularityContest_time_interval',
			$popularityContest_time_interval,'integer',
			'Time interval in seconds between submissions to Popularity Contest',
			true,true);
		# Hide plugins
		$popularityContest_hidden_plugins =
			(!empty($_POST['hidden_plugins'])) 
			? base64_encode(serialize($_POST['hidden_plugins']))
			: base64_encode(serialize(array('')));
		$settings->put('popularityContest_hidden_plugins',
			$popularityContest_hidden_plugins,'text','Hidden plugins',
			true,true);
		$hidden_plugins = $_POST['hidden_plugins'];

		http::redirect($p_url.'&saveconfig=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
elseif (isset($_POST['send_report']))
{
	if (!$can_send_report)
	{
		http::redirect($p_url.'&wait=1');
	}
	elseif (popularityContest::send() === true)
	{
		http::redirect($p_url.'&report_sent=1');
	}
	else
	{
		http::redirect($p_url.'&wait=1');
	}
}

if (isset($_GET['saveconfig']))
{
	$msg = __('Configuration successfully updated.');
	$tab = 'settings';
}
elseif (isset($_GET['report_sent']))
{
	$msg = __('Report successfully sent.');
}
elseif (isset($_GET['wait']))
{
	$core->error->add(sprintf(
		__('please wait %s before sending a report'),
		popularityContest::getDiff((30*60)- $time_interval_last_try)));
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
  	/*tr:hover {background:#eee none;}*/
		.icon {text-align:center;}
		.default {background:transparent url(/images/template/default.png) repeat;}
		.popularityContest {background:transparent url(/images/template/popularityContest.png) repeat;}
  </style>
</head>
<body>

<h2><?php echo __('Popularity Contest'); ?></h2>

<?php 
	if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}
	
	if (is_int($settings->popularityContest_last_report))
	{
		printf('<h3>'.__('Last successful report: %s ago').'</h3>',
			popularityContest::getDiff(
			$_SERVER['REQUEST_TIME']-$settings->popularityContest_last_report));
	}
	if (is_int($settings->popularityContest_last_try))
	{
		printf('<h3>'.__('Last try: %s ago').'</h3>',
			popularityContest::getDiff(
			$_SERVER['REQUEST_TIME']-$settings->popularityContest_last_try));
	}
?>

<div class="multi-part" id="popularityContest"
	title="<?php echo __('Popularity Contest'); ?>">
	<p><?php echo(__('This plugin only send the following informations to Dotclear Popularity Contest:')); ?></p>
	<ul>
		<?php 
		$infos = array(
			__('the names of installed and activated plugins'),
			sprintf(__('the value of md5(DC_ADMIN_URL) (%s) identify the Dotclear installation with an unique and anonym hash'),
				'<strong>'.md5(DC_ADMIN_URL).'</strong>'),
			sprintf(__('the Dotclear version (%s)'),
				'<strong>'.DC_VERSION.'</strong>')
		);
		foreach ($infos as $k)
		{
			echo('<li>'.$k.'</li>');
		}
		?>
	</ul>

	<p><?php echo(__('In the settings, you can hide some plugins and they will be ignored by Dotclear Popularity Contest.')); ?></p>
	<form method="post" action="<?php echo(http::getSelfURI()); ?>">
		<p><input type="submit" name="send_report"value="<?php echo
			__('Send a report to Dotclear Popularity Contest'); ?>" /></p>
		<p><?php echo $core->formNonce(); ?></p>
	</form>
	<h3><?php echo(__('Installed plugins:')); ?></h3>
	<?php echo(popularityContest::getPluginsTable()); ?>
</div>

<div class="multi-part" id="settings" title="<?php echo __('settings'); ?>">
	<form method="post" action="<?php echo(http::getSelfURI()); ?>">
		<fieldset>
			<legend><?php echo(__('Popularity Contest')); ?></legend>
			<p>
				<label for="popularityContest_time_interval">
					<?php echo(__('Send a report:')); ?>
				<?php echo(form::combo('popularityContest_time_interval',
				 popularityContest::getComboOptions(),
				 $popularityContest_time_interval)); ?>
				</label>
			</p>
		</fieldset>

		<h2><?php echo(__('Hide plugins:')); ?></h2>
		<?php echo(popularityContest::getPluginsTable(true)); ?>
		<p class="col checkboxes-helpers"></p>
		<p><?php echo $core->formNonce(); ?></p>
		<p><input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
	</form>
</div>

<div class="multi-part" id="results" title="<?php echo __('results'); ?>">
	<p><a href="http://popcon.gniark.net/"><?php echo(__('Click here to see results.')); ?></a></p>
	<h3><?php echo(__('Plugins:')); ?></h3>
	<?php echo(popularityContest::getResultsTable()); ?>
</div>

</body>
</html>