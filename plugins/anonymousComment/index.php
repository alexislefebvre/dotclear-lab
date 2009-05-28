<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of anonymousComment, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------


# Get settings
$anonymous_active = $core->blog->settings->anonymous_active;
$anonymous_name = $core->blog->settings->anonymous_name;
$anonymous_email = $core->blog->settings->anonymous_email;

if ($anonymous_name === null) {
	$anonymous_name = __('Anonymous');
}
if ($anonymous_email === null) {
	$anonymous_email = "anonymous@example.com";
}

if (isset($_POST["save"])) {
	# modifications
	try {
		$anonymous_active = !empty($_POST["anonymous_active"]);
		$anonymous_name = $_POST["anonymous_name"];
		$anonymous_email = $_POST["anonymous_email"];

		if (empty($_POST['anonymous_name'])) {
			throw new Exception(__('No name.'));
		}
		if (empty($_POST['anonymous_email'])) {
			throw new Exception(__('No email.'));
		}
		
		$core->blog->settings->setNameSpace('anonymousComment');
		$core->blog->settings->put('anonymous_active',$anonymous_active,'boolean');
		$core->blog->settings->put('anonymous_name',$anonymous_name,'string');
		$core->blog->settings->put('anonymous_email',$anonymous_email,'string');
		$core->blog->settings->setNameSpace('system');

		http::redirect($p_url.'&upd=1');

	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
	<title><?php echo(__('Anonymous comments')); ?></title>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.
		__('Anonymous comments'); ?></h2>
 
	<?php
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Settings have been successfully updated.').'</p>';
	}
	?>

	<form method="post" action="<?php echo($p_url); ?>">
		<p><?php echo $core->formNonce(); ?></p>

		<p><label class="classic"><?php 
			echo(form::checkbox('anonymous_active', 1,
			    (boolean) $anonymous_active).' '.
			    __('Allow anonymous comments')); ?></label></p>

		<p><label><?php echo(__('Replacement name:').
				form::field('anonymous_name',40,255,
				$anonymous_name)); ?></p>

		<p><label><?php echo(__('Replacement email:').
				form::field('anonymous_email',40,255,
				$anonymous_email)); ?></p>

		<p><input type="submit" name="save"
		          value="<?php echo __('Save'); ?>" /></p>
	</form>
 
<?php dcPage::helpBlock('anonymousComment');?>
</body>
</html>
