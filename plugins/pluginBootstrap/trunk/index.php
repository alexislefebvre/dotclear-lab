<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of pluginBootstrap,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Vincent Garnier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$plug_name = __('My new plugin');
$plug_description = __('A plugin that does nothing yet.');
$plug_author = $core->auth->getInfo('user_cn');
$plug_version = '0.1';

$plug_licence = 'gpl2';

$has_admin = true;
$has_public = false;

if (!empty($_POST['p_add']))
{
	$plug_name = !empty($_POST['plug_name']) ? trim($_POST['plug_name']) : '';
	$plug_version = !empty($_POST['plug_version']) ? trim($_POST['plug_version']) : '';
	$plug_description = !empty($_POST['plug_description']) ? trim($_POST['plug_description']) : '';
	$plug_author = !empty($_POST['plug_author']) ? trim($_POST['plug_author']) : '';

	$plug_licence = !empty($_POST['plug_licence']) ? trim($_POST['plug_licence']) : '';

	$has_admin = isset($_POST['has_admin']) ? true : false;
	$has_public = isset($_POST['has_public']) ? true : false;

	try
	{
		if (empty($plug_name)) {
			throw new Exception(__('You must give a name for the plugin.'));
		}

		if (empty($plug_version)) {
			throw new Exception(__('You must give a number version for the plugin.'));
		}

		$bootstraper = new pluginBootstrap(
			$plug_name,
			$plug_description,
			$plug_author,
			$plug_version,
			$plug_licence,
			$has_admin,
			$has_public
		);

		$bootstraper->templates_dir = dirname(__FILE__).'/templates';
		$bootstraper->plugins_root = DC_PLUGINS_ROOT;

		$bootstraper->build();

		http::redirect($p_url.'&added=1');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$licences_list = pluginBootstrap::getLicencesList(true);
$licences_list['other'] = __('Other');

?>
<html>
<head>
	<title><?php echo __('Plugin Bootstrap'); ?></title>
</head>
<body>
<h2><?php echo __('Plugin Bootstrap'); ?></h2>

<?php
if (!empty($_GET['added'])) {
	echo '<p class="message">'.__('Plugin successfully bootstraped.').'</p>';
}
?>

<form action="plugin.php" method="post">
	<fieldset>
		<legend><?php echo __('Definition') ?></legend>

		<p class="field"><label for="plug_name" class="required" title="<?php echo __('Required field') ?>"><?php
		echo __('Name'); ?></label><?php echo form::field('plug_name',60,255,html::escapeHTML($plug_name)); ?></p>

		<p class="field"><label for="plug_version" class="required" title="<?php echo __('Required field') ?>"><?php
		echo __('Version'); ?></label><?php echo form::field('plug_version',10,255,html::escapeHTML($plug_version)); ?></p>

		<p class="field"><label for="plug_description"><?php echo __('Description'); ?></label>
		<?php echo form::field('plug_description',60,255,html::escapeHTML($plug_description)); ?></p>

		<p class="field"><label for="plug_author"><?php echo __('Author'); ?></label>
		<?php echo form::field('plug_author',60,255,html::escapeHTML($plug_author)); ?></p>
	</fieldset>

	<fieldset>
		<legend><?php echo __('Options') ?></legend>

		<p class="field"><label for="plug_licence"><?php echo __(Licence) ?></label>
		<?php echo form::combo('plug_licence',$licences_list,$plug_licence) ?></p>

		<p><label class="classic"><?php echo form::checkbox('has_admin',1,$has_admin) ?>
		<?php echo __('With admin page') ?></label></p>

		<p><label class="classic"><?php echo form::checkbox('has_public',1,$has_public) ?>
		<?php echo __('With public page') ?></label></p>
	</fieldset>

	<p><?php echo form::hidden('p_add', '1');
	echo form::hidden(array('p'),'pluginBootstrap');
	echo $core->formNonce(); ?>
	<input type="submit" class="submit" value="<?php echo __('Create'); ?>" /></p>
</form>

</body>
</html>
