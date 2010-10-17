<?php
# ***** BEGIN LICENSE BLOCK *****
# 
# This program is free software. It comes without any warranty, to
# the extent permitted by applicable law. You can redistribute it
# and/or modify it under the terms of the Do What The Fuck You Want
# To Public License, Version 2, as published by Sam Hocevar. See
# http://sam.zoy.org/wtfpl/COPYING for more details.
# 
# Icon (icon.png) is from Silk Icons :
#  http://www.famfamfam.com/lab/icons/silk/
# 
# ***** END LICENSE BLOCK *****

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/admin');

$default_tab = 'tab-1';
 
if (isset($_REQUEST['tab'])) {
	$default_tab = $_REQUEST['tab'];
}

$settings =& $core->blog->settings;
 
$settings->setNameSpace('adminExample');
 
if (!empty($_POST['saveconfig']))
{
	$settings->put('adminexample_active',
		!empty($_POST['adminexample_active']),
		'boolean','Enable Admin Example');
 
	# redirect to the page, avoid conflicts with old settings
	http::redirect($p_url.'&tab=settings&saveconfig=1');
}

$settings->setNameSpace('system');

if (isset($_GET['saveconfig']))
{
	$msg = __('Configuration successfully updated.');
}

?><html>
<head>
	<title><?php echo(__('Admin example')); ?></title>
	<?php echo dcPage::jsPageTabs($default_tab); ?>
</head>
<body>
 
<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.
	__('Admin example'); ?></h2>

<?php if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';} ?>

<div class="multi-part" id="tab-1"
	title="<?php echo __('Tab 1'); ?>">
	<?php
	$combo_values = array(
		# a group of values
		# un groupe de valeurs
		__('Numbers') => array(
			__('one') => 1,
			__('two') => 2
		),
		# only  one value
		# une valeur seule
		__('Hello World!') => 'hello_world'
	);
	?>
	
	<fieldset>
		<legend><?php echo(__('Hello World!')); ?></legend>
		<p><label class="classic"><?php echo(
				form::checkbox('adminexample_active','1',
				$settings->adminexample_active).
				' '.__('Enable')); ?></label></p>
		
		<p><label><?php echo(__('Title:').
			form::combo('combo',$combo_values,'2')); ?>
			</label></p>
		<p class="form-note">
			<?php echo(__('Hello World!')); ?>
		</p>
	</fieldset>
	
	<fieldset>
		<legend><?php echo(__('Hello World!')); ?></legend>
		<p>
			<?php echo __('Title:'); ?>
			<label class="classic"><?php echo(
				form::radio(array('radio'),html::escapeHTML('1'),false).
				__('one')); ?></label>
			<label class="classic"><?php echo(
				form::radio(array('radio'),html::escapeHTML('2'),true).
				__('two')); ?></label>
		</p>
	</fieldset>
		
	

</div>

<div class="multi-part" id="settings"
	title="<?php echo __('Settings'); ?>">
	<form method="post" action="<?php echo($p_url); ?>">
		<p><label class="classic"><?php echo(
			form::checkbox('adminexample_active','1',
			$settings->adminexample_active).
			' '.__('Enable')); ?></label></p>
		<p class="form-note">
			<?php echo(__('Hello World!')); ?>
		</p>
		<p><?php echo $core->formNonce(); ?></p>
		<p><input type="submit" name="saveconfig"
		value="<?php echo __('Save configuration'); ?>" /></p>
	</form>
</div>

<div class="multi-part" id="columns"
	title="<?php echo __('Columns'); ?>">
	<h3><?php printf(__('%s columns'),2); ?></h3>
	<div class="two-cols">
		<div class="col">
			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec velit mi, in iaculis magna. Fusce nec tellus nec magna faucibus iaculis sed quis eros. Nulla sem orci, consectetur ullamcorper volutpat non, semper non purus.
		</div>
		<div class="col">
			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec velit mi, in iaculis magna. Fusce nec tellus nec magna faucibus iaculis sed quis eros. Nulla sem orci, consectetur ullamcorper volutpat non, semper non purus.
		</div>
	</div>
	
	<h3><?php printf(__('%s columns'),3); ?></h3>
	<div class="three-cols class">
		<div class="col">
			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec velit mi, in iaculis magna. Fusce nec tellus nec magna faucibus iaculis sed quis eros. Nulla sem orci, consectetur ullamcorper volutpat non, semper non purus.
		</div>
		<div class="col">
			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec velit mi, in iaculis magna. Fusce nec tellus nec magna faucibus iaculis sed quis eros. Nulla sem orci, consectetur ullamcorper volutpat non, semper non purus.
		</div>
		<div class="col">
			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec velit mi, in iaculis magna. Fusce nec tellus nec magna faucibus iaculis sed quis eros. Nulla sem orci, consectetur ullamcorper volutpat non, semper non purus.
		</div>
	</div>

</div>
 
</body>
</html>