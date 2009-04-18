<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zesntyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!$core->auth->isSuperAdmin()) { return; }

# Var initialisation
$p_url 		= 'plugin.php?p=dcCron';
$page		= isset($_GET['page']) ? html::escapeHTML($_GET['page']) : 1;
$nid			= isset($_POST['nid']) ? html::escapeHTML($_POST['nid']) : '';
$nb_per_page	= 20;

# Save tasks
if (isset($_POST['save'])) {
	$nid = html::escapeHTML($_POST['nid']);
	$interval = html::escapeHTML($_POST['interval']);
	$callback = array(
		html::escapeHTML($_POST['class']),
		html::escapeHTML($_POST['function'])
	);
	$first_run = !empty($_POST['first_run']) ? strtotime(html::escapeHTML($_POST['first_run'])) : null;
	$old = isset($_POST['old']) ? html::escapeHTML($_POST['old']) : '';
	if ($nid != $old && $core->blog->dcCron->taskExists($old)) {
		$core->blog->dcCron->del(array($old));
	}
	$msg = empty($old) ? sprintf(__('Task : %s have been successfully created'),$nid) : sprintf(__('Task : %s have been successfully edited'),$nid);
	$msg = $core->blog->dcCron->put($nid,$interval,$callback,$first_run) ? $msg : '';
}
# Delete tasks
if (isset($_POST['delete'])) {
	$nids = $_POST['nids'];
	$msg = $core->blog->dcCron->del($nids) ? __('All Tasks selected have been deleted successfully') : '';
}
# Disable tasks
if (isset($_POST['disable'])) {
	$nid = html::escapeHTML($_POST['nid']);
	$msg = $core->blog->dcCron->disable($nid) ? sprintf(__('Task : %s have been successfully disabled'),$nid) : '';
}
# Enable tasks
if (isset($_POST['enable'])) {
	$nid = html::escapeHTML($_POST['nid']);
	$msg = $core->blog->dcCron->enable($nid) ? sprintf(__('Task : %s have been successfully enabled'),$nid) : '';
}

# Gets enabled tasks & prepares display object
$et_rs = $core->blog->dcCron->getEnabledTasks();
$et_nb = count($et_rs);
$et_s_rs = staticRecord::newFromArray($et_rs);
$et_list = new dcCronEnableList($core,$et_s_rs,$et_nb);
# Gets disabled tasks & prepares display object
$dt_rs = $core->blog->dcCron->getDisabledTasks();
$dt_nb = count($dt_rs);
$dt_s_rs = staticRecord::newFromArray($dt_rs);
$dt_list = new dcCronDisableList($core,$dt_s_rs,$dt_nb);

# Adds errors display
foreach ($core->blog->dcCron->getErrors() as $k => $v) {
	$core->error->add($v);
}

# Construct line
$line = html::escapeHTML($core->blog->name).' &rsaquo; ';
$line .= sprintf(
	(isset($_POST['edit']) || isset($_GET['add']) ?
	'<a href="%2$s">%1$s</a> &rsaquo; ' :
	'%1$s - '),__('dcCron'),$p_url);
$line .= sprintf(
	(!isset($_POST['edit']) && !isset($_GET['add']) ?
	'<a class="button" href="%2$s">%1$s</a>' :
	'%1$s'),sprintf(
		isset($_POST['edit']) ?
		__('Edit task') :
		__('New task')
	),$p_url.'&amp;add=go');

?>
<html>
<head>
	<title><?php echo __('dcCron'); ?></title>
	<?php echo dcPage::jsDatePicker(); ?>
	<?php echo dcPage::jsLoad(DC_ADMIN_URL.'?pf=dcCron/js/dccron.min.js'); ?>
	<script type="text/javascript">
	//<![CDATA[
	<?php echo dcPage::jsVar('dotclear.msg.confirm_delete_task',__('Are you sure you want to delete these tasks?')); ?>
	//]]>
	</script>
	<style type="text/css">@import '<?php echo DC_ADMIN_URL; ?>?pf=dcCron/style.min.css';</style>
</head>

<body>
<h2><?php echo $line; ?></h2>

<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>

<?php if (isset($_GET['add'])) : ?>
	<h3><?php echo __('New task'); ?></h3>
	<form action="<?php echo $p_url; ?>" method="post">
	<p class="field">
		<label class="classic" for="nid"><?php echo __('Task id'); ?></label>
		<?php echo form::field('nid',40,255,''); ?>
	</p>
	<p class="field">
		<label class="classic" for="class"><?php echo __('Class name'); ?></label>
		<?php echo form::field('class',40,255,''); ?>
	</p>
	<p class="field">
		<label class="classic" for="function"><?php echo __('Function name'); ?></label>
		<?php echo form::field('function',40,255,''); ?>
	</p>
	<p class="field">
		<label class="classic" for="interval"><?php echo __('Interval'); ?></label>
		<?php echo form::field('interval',40,255,''); ?>
		<span id="convert"></span>
	</p>
	<p class="field">
		<label class="classic" for="first_run"><?php echo __('First run'); ?></label>
		<?php echo form::field('first_run',20,255,''); ?>
		<span id="convert"></span>
	</p>
	<p>
	<?php echo $core->formNonce(); ?>
	<input class="save" name="save" value="<?php echo __('Add task'); ?>" type="submit" />
	</p>
	</form>
<?php elseif (isset($_POST['edit'])) : ?>
	<h3><?php echo __('Task edit'); ?></h3>
	<form action="<?php echo $p_url; ?>" method="post">
	<p class="field">
		<label class="classic" for="nid"><?php echo __('Task id'); ?></label>
		<?php echo form::field('nid',40,255,$et_rs[$nid]['id']); ?>
	</p>
	<p class="field">
		<label class="classic" for="class"><?php echo __('Class name'); ?></label>
		<?php echo form::field('class',40,255,$et_rs[$nid]['callback'][0]); ?>
	</p>
	<p class="field">
		<label class="classic" for="function"><?php echo __('Function name'); ?></label>
		<?php echo form::field('function',40,255,$et_rs[$nid]['callback'][1]); ?>
	</p>
	<p class="field">
		<label class="classic" for="interval"><?php echo __('Interval (in second)'); ?></label>
		<?php echo form::field('interval',40,255,$et_rs[$nid]['interval']); ?>
		<span id="convert"></span>
	</p>
	<p class="field">
		<label class="classic" for="first_run"><?php echo __('First run'); ?></label>
		<?php echo form::field('first_run',20,255,($et_rs[$nid]['last_run'] == 0 ? date('Y-m-j H:i',$et_rs[$nid]['first_run']) : '')); ?>
		<span id="convert"></span>
	</p>
	<p>
	<?php echo form::hidden('old',$et_rs[$nid]['id']); ?>
	<?php echo $core->formNonce(); ?>
	<input class="save" name="save" value="<?php echo __('Save configuration'); ?>" type="submit" />
	</p>
	</form>
<?php else : ?>
	<h3><?php echo $et_nb > 0 ? __('Planned tasks') : __('No tasks planned'); ?></h3>
	<?php if ($et_nb > 0) : ?>
		<?php $et_list->display($page,$nb_per_page,$p_url); ?>
	<?php endif; ?>
	<?php echo $dt_nb > 0 ? '<h3>'.__('Disabled tasks').'</h3>' : ''; ?>
	<?php if ($dt_nb > 0) : ?>
		<?php $dt_list->display($p_url); ?>
	<?php endif; ?>
<?php endif; ?>

</body>
</html>