<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of autoBackup, a plugin for Dotclear.
# 
# Copyright (c) 2008 k-net, Franck
# http:www.k-netweb.net/
# http://www.franck-paul.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http:#www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# Need to be a super admin to access this plugin
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$action	= !empty($_REQUEST['action']) ? $_REQUEST['action'] : null;
$config	= autoBackup::getConfig();
$tab		= isset($_POST['tab']) ? html::escapeHTML($_POST['tab']) : 'status';

if (($action == 'save') && !empty($_POST['saveconfig'])) {
	# Saving new configuration

	$config['importexportclasspath'] = $_POST['importexportclasspath'];
	$config['backup_onfile'] = isset($_POST['backup_onfile']);
	$config['backup_onemail'] = isset($_POST['backup_onemail']);
	$config['backup_onfile_repository'] = $_POST['backup_onfile_repository'];
	$config['backup_onfile_compress_gzip'] = isset($_POST['backup_onfile_compress_gzip']);
	$config['backup_onfile_deleteprev'] = isset($_POST['backup_onfile_deleteprev']);
	$config['backup_onemail_adress'] = $_POST['backup_onemail_adress'];
	$config['backup_onemail_compress_gzip'] = isset($_POST['backup_onemail_compress_gzip']);
	$config['backup_onemail_header_from'] = $_POST['backup_onemail_header_from'];
	$config['backuptype'] = $core->auth->isSuperAdmin() && $_POST['backuptype'] == 'full' ? 'full' : 'blog';
	$config['backupblogid'] = $core->blog->id;
	$config['interval'] = (int) $_POST['interval'];

	try
	{
		autoBackup::setConfig($config);
		$msg = __('Configuration successfully updated.');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}

} elseif ($action == 'run_asap') {
	# Run backup as soon as possible
	try
	{
		$config['backup_asap'] = true;

		autoBackup::setConfig($config);
		$msg = __('Backup will run as soon as possible.');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
<title><?php echo __('Auto Backup'); ?></title>
	<?php echo dcPage::jsPageTabs($tab); ?>
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('Auto Backup'); ?></h2>

<?php 
# Display message if any
if (!empty($msg)) echo '<p class="message">'.$msg.'</p>';

# Set export type
$backuptypes = $core->auth->isSuperAdmin() ? array(__('All content export') => 'full', __('Blog export') => 'blog') : array(__('Blog export') => 'blog');

# Set export interval list
$intervals = array(
	__('disable') =>     0,
	'6 '.__('hours') =>  3600*6,
	'12 '.__('hours') => 3600*12,
	'24 '.__('hours') => 3600*24,
	'2 '.__('days') =>   3600*24*2,
	'7 '.__('days') =>   3600*24*7,
	'14 '.__('days') =>  3600*24*14,
	);
	
# Set format date
$date_format = $core->blog->settings->date_format.' - '.$core->blog->settings->time_format;
# Add custom interval if any
if (!in_array($config['interval'], array(0, 3600*6, 3600*12, 3600*24, 3600*24*2, 3600*24*7, 3600*24*14))) {
	$intervals[$config['interval'].' '.__('seconds')] = $config['interval'];
}
?>

<div id="status" title="<?php echo __('Status'); ?>" class="multi-part">
	<h3><?php echo __('Current status'); ?></h3>
	<p><?php echo ($config['backup_running'] ? '<strong>'.__('Backup is currently running...').'</strong>' : __('No backup is running.')); ?></p>
	<p><?php echo ($config['backup_asap'] ? '<strong>'.__('The next backup will occur as soon as possible').'</strong>' : __('The next backup will occur on normal schedule')); ?></p>
	
	<h3><?php echo __('Last backups'); ?></h3>

	<p><?php echo __('Last backup on file:'); ?>&nbsp;
	<?php echo ($config['backup_onfile_last']['date'] > 0 ? dt::str($date_format,$config['backup_onfile_last']['date']) : '<em>'.__('never').'</em>'); ?><br />
	<?php echo __('File name:'); ?>&nbsp;<abbr title="<?php echo html::escapeHTML($config['backup_onfile_last']['file']); ?>">
	<?php echo  html::escapeHTML(basename($config['backup_onfile_last']['file'])); ?></abbr></p>

	<p><?php echo __('Last backup by email:'); ?>&nbsp;
	<?php echo ($config['backup_onemail_last']['date'] > 0 ? dt::str($date_format,$config['backup_onemail_last']['date']) : '<em>'.__('never').'</em>'); ?></p>

	<h3><?php echo __('Next backup'); ?></h3>

	<p><?php echo __('Next scheduled backup on file:'); ?>&nbsp;
	<?php 
	if ($config['backup_onfile'] && ($config['interval'] > 0)) {
		# Backup on file activated
		if ($config['backup_asap'] || ($config['backup_onfile_last']['date'] == 0)) {
			# Backup ASAP or never done
			echo __('as soon as possible');
		} else {
			# Normal schedule
			echo dt::str($date_format,$config['backup_onfile_last']['date'] + $config['interval']);
		}
	} else {
		# No backup on file or extension not activated
		echo '<em>'.__('never').'</em>';
	}
	?>
	</p>
	
	<p><?php echo __('Next scheduled backup by email:') ?>&nbsp;
		<?php 
		if ($config['backup_onemail'] && ($config['interval'] > 0)) {
			# Backup on file activated
			if ($config['backup_asap'] || ($config['backup_onemail_last']['date'] == 0)) {
				# Backup ASAP or never done
				echo __('as soon as possible');
			} else {
				# Normal schedule
				echo dt::str($date_format,$config['backup_onemail_last']['date'] + $config['interval']);
			}
		} else {
			# No backup on file or extension not activated
			echo '<em>'.__('never').'</em>';
		}
		?>
	</p>

	<form method="post" action="plugin.php">
	<p><input type="hidden" name="p" value="autoBackup" />
	<?php echo $core->formNonce(); ?>
	<?php echo form::hidden(array('action'),'run_asap'); ?>
	<input type="submit" name="runbackup" value="<?php echo __('Run backup as soon as possible'); ?>" />
	</p>

	</form>
</div>

<div id="settings" title="<?php echo __('Settings'); ?>" class="multi-part">

	<p><?php __('Auto Backup allows you to create backups automatically and often.') ?><br />
		<?php __('It uses the Import/Export plugin to work.') ?></p>

	<form method="post" action="plugin.php">
	<fieldset>
		<legend><?php echo __('Plugin configuration'); ?></legend>
		<p class="field">
			<label class="classic" for="importexportclasspath"><?php echo __('Import/Export plugin class path:'); ?></label>
			<?php echo form::field('importexportclasspath',40,255,$config['importexportclasspath']); ?>
			<?php echo (is_file($config['importexportclasspath']) ? '' : '<span style="color:#C00"><strong>'.__('Warning: this file doesn\'t exist!').'</strong></span>'); ?>
		</p>
	</fieldset>

	<fieldset>
		<legend><?php echo __('General options'); ?></legend>
		<p class="field">
			<label class="classic" for="backuptype"><?php echo __('Backup type:'); ?></label>
			<?php echo form::combo('backuptype',$backuptypes,$config['backuptype']); ?>
		</p>
		<p class="field">
			<label class="classic" for="interval"><?php echo __('Create a new backup every:'); ?></label>
			<?php echo form::combo('interval',$intervals,$config['interval']); ?>
		</p>
	</fieldset>

	<fieldset>
		<legend><?php echo __('File options'); ?></legend>
		<p class="field">
			<?php echo form::checkbox('backup_onfile',1,$config['backup_onfile']) ?>
			<label class="classic" for="backup_onfile"><strong><?php echo __('Backup on file'); ?></strong></label>
		</p>
		<p class="field">
			<label class="classic" for="backup_onfile_repository"><?php echo __('Repository path:'); ?></label>
			<?php echo form::field('backup_onfile_repository',40,255,$config['backup_onfile_repository']); ?>
		</p>
		<p class="field">
			<?php echo form::checkbox('backup_onfile_compress_gzip',1,$config['backup_onfile_compress_gzip']); ?>
			<label class="classic" for="backup_onfile_compress_gzip"><?php echo __('Compress data with gzip'); ?></label>
		</p>
		<p class="field">
			<?php echo form::checkbox('backup_onfile_deleteprev',1,$config['backup_onfile_deleteprev']); ?>
			<label class="classic" for="backup_onfile_deleteprev"><?php echo __('After creating the backup file, delete the previous one.'); ?></label>
		</p>
	</fieldset>

	<fieldset>
		<legend><?php echo __('Mail options'); ?></legend>
		<p class="field">
			<?php echo form::checkbox('backup_onemail',1,$config['backup_onemail']); ?>
			<label class="classic" for="backup_onemail"><strong><?php echo __('Backup by email'); ?></strong></label>
		</p>
		<p class="field">
			<label class="classic" for="backup_onemail_adress"><?php echo __('Email address:'); ?></label>
			<?php echo form::field('backup_onemail_adress',30,255,$config['backup_onemail_adress']); ?>
		</p>
		<p class="field">
			<?php echo form::checkbox('backup_onemail_compress_gzip',1,$config['backup_onemail_compress_gzip']); ?>
			<label class="classic" for="backup_onemail_compress_gzip"><?php echo __('Compress data with gzip'); ?></label>
		</p>
		<p class="field">
			<label class="classic" for="backup_onemail_header_from"><?php echo __('Email <em>From</em> header:'); ?></label>
			<?php echo form::field('backup_onemail_header_from',30,255,$config['backup_onemail_header_from']); ?>
		</p>
	</fieldset>

	<p><input type="hidden" name="p" value="autoBackup" />
	<input type="hidden" name="tab" value="settings" />
	<?php echo $core->formNonce(); ?>
	<?php echo form::hidden(array('action'),'save'); ?>
	<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
	</p>
	</form>

</div>

<div id="about" title="<?php echo __('About'); ?>" class="multi-part">
	<h2 style="background: url(index.php?pf=autoBackup/icon.png) no-repeat 0 0.25em; padding: 5px 0 5px 22px; margin-left: 20px;"><?php echo __('Auto Backup'); ?></h2>
	<ul style="list-style: none; line-height: 30px; font-weight: bold;">
		<li><?php echo __('Created by'); ?> : <a href="http://www.k-netweb.net/">k-net</a></li>
		<li><?php echo __('Help, support and sources'); ?> : <a href="http://lab.dotclear.org/wiki/plugin/autoBackup">http://lab.dotclear.org/wiki/plugin/autoBackup</a></li>
	</ul>
</div>

</body>
</html>