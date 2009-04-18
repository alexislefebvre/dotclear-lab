<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifications, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zesntyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if ($core->blog->settings->notifications_config) {
	$config = unserialize($core->blog->settings->notifications_config);
}
else {
	throw new Exception(__('Impossible to load notification configuration'));
}
if (isset($_POST['saveconfig'])) {
	$config['posts'] = html::escapeHTML($_POST['posts']);
	$config['categories'] = html::escapeHTML($_POST['categories']);
	$config['comments'] = html::escapeHTML($_POST['comments']);
	$config['trackbacks'] = html::escapeHTML($_POST['trackbacks']);
	$config['404'] = html::escapeHTML($_POST['404']);
	$config['sticky_new'] = html::escapeHTML($_POST['sticky_new']);
	$config['sticky_upd'] = html::escapeHTML($_POST['sticky_upd']);
	$config['sticky_del'] = html::escapeHTML($_POST['sticky_del']);
	$config['sticky_msg'] = html::escapeHTML($_POST['sticky_msg']);
	$config['sticky_err'] = html::escapeHTML($_POST['sticky_err']);
	$config['position'] = $_POST['position'];
	$config['display_time'] = html::escapeHTML($_POST['display_time']);
	$config['refresh_time'] = html::escapeHTML($_POST['refresh_time']);
	$config['autoclean'] = html::escapeHTML($_POST['autoclean']);
	try {
		$core->blog->settings->setNamespace('notifications');
		$core->blog->settings->put('notifications_config',serialize($config),'string');
		$core->blog->triggerBlog();
		$msg = __('Configuration successfully updated.');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$combo_data = array(
	__('Top - Left') => 'top-left',
	__('Top - Right') => 'top-right',
	__('Bottom - Left') => 'bottom-left',
	__('Bottom - Right') => 'bottom-right',
	__('Center') => 'center'
);

?>

<html>
<head>
<title><?php echo __('Notifications'); ?></title>
</head>

<body>
<h2><?php echo __('Notifications'); ?></h2>

<?php if (!empty($msg)) : echo '<p class="message">'.$msg.'</p>'; endif; ?>

<form method="post" action="plugin.php">

<fieldset>
<legend><?php echo __('Blog notifications'); ?></legend>
<p class="field">
	<?php echo form::checkbox('posts',1,$config['posts']); ?>
	<label class="classic" for="posts"><?php echo __('Enable posts notifications'); ?></label>
</p>
<p class="field">
	<?php echo form::checkbox('categories',1,$config['categories']); ?>
	<label class="classic" for="categories"><?php echo __('Enable categories notifications'); ?></label>
</p>
<p class="field">
	<?php echo form::checkbox('comments',1,$config['comments']); ?>
	<label class="classic" for="comments"><?php echo __('Enable comments notifications'); ?></label>
</p>
<p class="field">
	<?php echo form::checkbox('trackbacks',1,$config['trackbacks']); ?>
	<label class="classic" for="trackbacks"><?php echo __('Enable trackbacks notifications'); ?></label>
</p>
<p class="field">
	<?php echo form::checkbox('404',1,$config['404']); ?>
	<label class="classic" for="404"><?php echo __('Enable 404 errors notifications'); ?></label>
</p>
</fieldset>

<fieldset>
<legend><?php echo __('Notifications options'); ?></legend>
<p class="field">
	<?php echo form::checkbox('sticky_new',1,$config['sticky_new']); ?>
	<label class="classic" for="sticky_new"><?php echo __('Sticky new notifications'); ?></label>
</p>
<p class="field">
	<?php echo form::checkbox('sticky_upd',1,$config['sticky_upd']); ?>
	<label class="classic" for="sticky_upd"><?php echo __('Sticky update notifications'); ?></label>
</p>
<p class="field">
	<?php echo form::checkbox('sticky_del',1,$config['sticky_del']); ?>
	<label class="classic" for="sticky_del"><?php echo __('Sticky delete notifications'); ?></label>
</p>
<p class="field">
	<?php echo form::checkbox('sticky_msg',1,$config['sticky_msg']); ?>
	<label class="classic" for="sticky_msg"><?php echo __('Sticky message notifications'); ?></label>
</p>
<p class="field">
	<?php echo form::checkbox('sticky_err',1,$config['sticky_err']); ?>
	<label class="classic" for="sticky_err"><?php echo __('Sticky error notifications'); ?></label>
</p>
<p class="field">
	<?php echo form::combo('position',$combo_data,$config['position']); ?>
	<label class="classic" for="position"><?php echo __('Position of notifications'); ?></label>
</p>
<p class="field">
	<?php echo form::field('display_time',30,255,$config['display_time']); ?>
	<label class="classic" for="display_time"><?php echo __('Time to display notifications (second)'); ?></label>
</p>
<p class="field">
	<?php echo form::field('refresh_time',30,255,$config['refresh_time']); ?>
	<label class="classic" for="refresh_time"><?php echo __('Time beetween each request (second)'); ?></label>
</p>
</fieldset>
<fieldset>
<legend><?php echo __('Notifications maintenance'); ?></legend>
<p class="field">
	<?php echo form::checkbox('autoclean',1,$config['autoclean']); ?>
	<label class="classic" for="autoclean"><?php echo __('Auto-clean notifications'); ?></label>
</p>
</fieldset>

<p><input type="hidden" name="p" value="notifications" />
<?php echo $core->formNonce(); ?>
<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
</p>
</form>

</body>
</html>