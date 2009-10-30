<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of threadedComments, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------


# Get settings
$threading_active = $core->blog->settings->threading_active;
$threading_indent = $core->blog->settings->threading_indent;
$threading_max_levels = $core->blog->settings->threading_max_levels;
$threading_switch_text = $core->blog->settings->threading_switch_text;

if ($threading_indent === null) {
	$threading_indent = 25;
}
if ($threading_max_levels === null) {
	$threading_max_levels = 5;
}
if ($threading_switch_text === null) {
	$threading_switch_text = __('Sort by thread');
}

if (isset($_POST["save"])) {
	# modifications
	try {
		$threading_active = !empty($_POST["threading_active"]);
		$threading_indent = intval(str_replace("px","",$_POST["threading_indent"]));
		$threading_max_levels = intval($_POST["threading_max_levels"]);
		$threading_switch_text = $_POST["threading_switch_text"];

		if (empty($_POST['threading_indent'])) {
			throw new Exception(__('No indentation value.'));
		}
		if ($threading_indent == 0) {
			throw new Exception(__('Wrong indentation value.'));
		}
		if (empty($_POST['threading_max_levels'])) {
			throw new Exception(__('No maximum indentation level.'));
		}
		if ($threading_max_levels == 0) {
			throw new Exception(__('Wrong maximum indentation level value.'));
		}
		
		$core->blog->settings->setNameSpace('threadedComments');
		$core->blog->settings->put('threading_active',$threading_active,'boolean');
		$core->blog->settings->put('threading_indent',$threading_indent,'integer');
		$core->blog->settings->put('threading_max_levels',$threading_max_levels,'integer');
		$core->blog->settings->put('threading_switch_text',$threading_switch_text,'string');
		$core->blog->settings->setNameSpace('system');

		http::redirect($p_url.'&upd=1');

	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
	<title><?php echo(__('Threaded comments')); ?></title>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.
		__('Threaded comments'); ?></h2>
 
	<?php
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Settings have been successfully updated.').'</p>';
	}
	?>

	<form method="post" action="<?php echo($p_url); ?>">
		<p><?php echo $core->formNonce(); ?></p>

		<p><label class="classic"><?php 
			echo(form::checkbox('threading_active', 1,
			    (boolean) $threading_active).' '.
			    __('Allow threaded view')); ?></label></p>

		<p><label class="classic"><?php echo(__('Indentation width (in px):').
				" ".form::field('threading_indent',3,255,
				$threading_indent)); ?>px</p>

		<p><label class="classic"><?php echo(__('Maximum indentation level:').
				" ".form::field('threading_max_levels',3,255,
				$threading_max_levels)); ?></p>

		<p><label><?php echo(__('Switch text:').
				form::field('threading_switch_text',40,255,
				$threading_switch_text)); ?></p>

		<p><input type="submit" name="save"
		          value="<?php echo __('Save'); ?>" /></p>
	</form>
 
<?php dcPage::helpBlock('threadedComments');?>
</body>
</html>