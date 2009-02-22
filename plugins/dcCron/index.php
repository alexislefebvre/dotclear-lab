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
$id			= isset($_POST['id']) ? html::escapeHTML($_POST['id']) : '';
$nb_per_page	= 20;

if (isset($_POST['delete'])) {
	$core->blog->dcCron->del(array($id));
	$msg = sprintf(__('Task : %s have been deleted successfully'),$id);
}
if (isset($_POST['save'])) {
	$nid = html::escapeHTML($_POST['nid']);
	$interval = html::escapeHTML($_POST['interval']);
	$callback = array(
		html::escapeHTML($_POST['class']),
		html::escapeHTML($_POST['function'])
	);
	$msg = $core->blog->dcCron->put($nid,$interval,$callback) ? sprintf(__('Task : %s have been edited successfully'),$nid) : '';
}

# Gets all tasks & prepares display object
$t_rs = $core->blog->dcCron->getTasks();
$t_nb = count($t_rs);
$t_s_rs = staticRecord::newFromArray($t_rs);
$t_list = new dcCronList($core,$t_s_rs,$t_nb);

foreach ($core->blog->dcCron->getErrors() as $k => $v) {
	$core->error->add($v);
}

?>
<html>
<head>
<title><?php echo __('dcCron'); ?></title>
</head>

<body>
<h2><?php echo __('dcCron'); ?></h2>

<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>

<?php if (isset($_POST['edit'])) : ?>
	<h3><?php echo __('Task edit'); ?></h3>
	<form action="<?php echo $p_url; ?>" method="post">
	<p class="field">
		<label class="classic" for="nid"><?php echo __('Task id'); ?></label>
		<?php echo form::field('nid',40,255,$t_rs[$id]['id']); ?>
	</p>
	<p class="field">
		<label class="classic" for="class"><?php echo __('Class name'); ?></label>
		<?php echo form::field('class',40,255,$t_rs[$id]['callback'][0]); ?>
	</p>
	<p class="field">
		<label class="classic" for="function"><?php echo __('Function name'); ?></label>
		<?php echo form::field('function',40,255,$t_rs[$id]['callback'][1]); ?>
	</p>
	<p class="field">
		<label class="classic" for="interval"><?php echo __('Interval (in second)'); ?></label>
		<?php echo form::field('interval',40,255,$t_rs[$id]['interval']); ?>
		<span id="convert"></span>
	</p>
	<p>
	<?php echo $core->formNonce(); ?>
	<input class="save" name="save" value="<?php echo __('Save configuration'); ?>" type="submit" />
	</p>
	</form>
<?php else : ?>
	<h3><?php echo $t_nb > 0 ? __('Planned tasks') : __('No tasks planned'); ?></h3>
	<?php if ($t_nb > 0) : ?>
		<?php $t_list->display($page,$nb_per_page,$p_url); ?>
	<?php endif; ?>
<?php endif; ?>
</body>
</html>