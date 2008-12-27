<?php
##licence_block##
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$your_name = '';
$your_firstname = '';

$##class_name## = new ##class_name##($core);

if (!empty($_POST['form_sent']))
{
	$your_name = !empty($_POST['your_name']) ? trim($_POST['your_name']) : '';
	$your_firstname = !empty($_POST['your_firstname']) ? trim($_POST['your_firstname']) : '';

	try
	{
		if (empty($your_name)) {
			throw new Exception(__('You must give a name.'));
		}

		if (!$##class_name##->compareName($your_name)) {
			throw new Exception(__('The name you give is different than the one in session.'));
		}
		elseif (!$##class_name##->compareFirstname($your_firstname)) {
			throw new Exception(__('The firstname you give is different than the one in session.'));
		}
		else {
			http::redirect($p_url.'&same=1');
		}
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
	<title><?php echo __('##plugin_name##'); ?></title>
</head>
<body>

<h2><?php echo __('##plugin_name##'); ?></h2>

<?php
if (!empty($_GET['same'])) {
	echo '<p class="message">'.__('The name and firstname that you have given are the same as those in session.').'</p>';
}
?>

<form action="plugin.php" method="post">

	<fieldset>
		<legend><?php echo __('User information') ?></legend>

		<p class="field"><label for="your_name" class="required" title="<?php echo __('Required field') ?>"><?php
		echo __('Name'); ?></label><?php echo form::field('your_name',60,255,html::escapeHTML($your_name)); ?></p>

		<p class="field"><label for="your_firstname"><?php echo __('Firstname'); ?></label>
		<?php echo form::field('your_firstname',60,255,html::escapeHTML($your_firstname)); ?></p>
	</fieldset>

	<p><?php echo form::hidden('form_sent', '1');
	echo form::hidden(array('p'),'##plugin_id##');
	echo $core->formNonce(); ?>
	<input type="submit" class="submit" value="<?php echo __('Send'); ?>" /></p>
</form>

</body>
</html>
