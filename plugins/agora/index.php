<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku , Tomtom and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

// Setting default parameters if missing configuration
if (is_null($core->blog->settings->agora_flag)) {
	try {
			$core->blog->settings->setNameSpace('agora');

			// Agora is not active by default
			$core->blog->settings->put('agora_flag',false,'boolean');
			$core->blog->triggerBlog();
			http::redirect(http::getSelfURI());
		}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

// Getting current parameters
$agora_flag		= (boolean)$core->blog->settings->agora_flag;
$agora_announce	= $core->blog->settings->agora_announce;

if ($agora_announce === null) {
	$agora_announce = __('<p class="message">Welcome to the Agora.</p>');
}

if (!empty($_POST['saveconfig']))
{
	try
	{
		$agora_flag = (empty($_POST['agora_flag']))?false:true;
		$agora_announce = $_POST['agora_announce'];

		if (empty($_POST['agora_announce'])) {
			throw new Exception(__('No agora announce.'));
		}

		$core->blog->settings->setNamespace('agora');
 		$core->blog->settings->put('agora_flag',$agora_flag,'boolean','Active the agora module');
		$core->blog->settings->put('agora_announce',$agora_announce,'string','Agora announce');

		$core->blog->triggerBlog();

		$msg = __('Configuration successfully updated.');
	}

	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
	<title><?php echo __('Agora'); ?></title>
</head>
<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &rsaquo; <?php echo __('Agora'); ?></h2>

<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>

<?php if (!empty($err)) echo '<p class="error">'.$err.'</p>'; ?>

<div id="agora_options">
	<form method="post" action="plugin.php">
		<fieldset>
			<legend><?php echo __('Plugin activation'); ?></legend>
				<div class="two-cols">
				<div class="col">
				<p class="field">
					<?php echo form::checkbox('agora_flag', 1, $agora_flag); ?>
					<label class=" classic" for="agora_flag"> <?php echo __('Enable Agora');?></label>
				</p>
				</div>

				</div>
		</fieldset>
		<fieldset class="constrained">
			<legend><?php echo __('Presentation options'); ?></legend>
				<p class="area"><label class="required" title="<?php echo __('Required field');?>">
					<?php echo __('Agora announce:');?>
					<?php echo form::textarea('agora_announce',30,4,html::escapeHTML($agora_announce)); ?>
				</label></p>
		</fieldset>

		<p><input type="hidden" name="p" value="agora" />
		<?php echo $core->formNonce(); ?>
		<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
	</form>
</div>
</body>
</html>
