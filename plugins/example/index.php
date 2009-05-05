<?php 
/***** BEGIN LICENSE BLOCK *****
This program is free software. It comes without any warranty, to
the extent permitted by applicable law. You can redistribute it
and/or modify it under the terms of the Do What The Fuck You Want
To Public License, Version 2, as published by Sam Hocevar. See
http://sam.zoy.org/wtfpl/COPYING for more details.
	
Icon (icon.png) is from Silk Icons :
	http://www.famfamfam.com/lab/icons/silk/

***** END LICENSE BLOCK *****/

if (!defined('DC_CONTEXT_ADMIN')) {return;}

function line($line)
{
	return(' '.sprintf(
		__('(see line <strong>%s</strong> of the <strong>index.php</strong> file)'),
		$line).' ');
}

# default tab
$default_tab = 'administration';

if (!empty($_REQUEST['tab']))
{
	$default_tab = $_REQUEST['tab'];
}

$rs = $core->blog->getPosts(array(
	'order' => 'post_dt DESC',
	'limit' => 1
));

$post_link = $core->getPostAdminURL('post',$rs->post_id);

unset($rs);

# forms
# define_combo_values
$combo_values = array(
	# group values
	__('Numbers') => array(
		__('one') => 1,
		__('two') => 2
	),
	# simple value
	__('A text value') => 'a'
);

$combo_default_value = 2; 

# shortcut to settings
$settings =& $core->blog->settings;

# save the settings of the plugin in a namespace
$settings->setNameSpace('example');

try
{
	if (!empty($_POST['saveconfig']))
	{
		$settings->put('setting_active',
			!empty($_POST['setting_active']),
			'boolean','Enable Example');
		
		# redirect to the page, avoid conflicts with old settings
		http::redirect($p_url.'&tab=settings&saveconfig=1');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

if (isset($_GET['saveconfig']))
{
	$msg = __('Configuration successfully updated.');
}

?>
<html>
<head>
	<title><?php echo(__('Example')); ?></title>
	<?php echo dcPage::jsPageTabs($default_tab); ?>
	<script type="text/javascript">
	//<![CDATA[
		<?php echo(dcPage::jsVar('dotclear.msg.confirm_saveconfig',
  	__('Are you sure you want to save the configuration?'))); ?>
	  $(document).ready(function () {
	  	/* display an alert when clicking on the button */
	  	$('input[@name="saveconfig"]').click(function() {
				return window.confirm(dotclear.msg.confirm_saveconfig);
			});
		});
	//]]>
  </script>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.
		__('Example'); ?></h2>
	
	<?php if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';} ?>
	
	<?php 
	if ($default_tab == 'fake-tab')
	{
		echo('<a href="'.$p_url.'&amp;tab=administration"'.
			'class="multi-part">'.__('Administration').'</a>'.
			'<a href="'.$p_url.'&amp;tab=public"'.
			'class="multi-part">'.__('Public').'</a>'.
			'<a href="'.$p_url.'&amp;tab=widget"'.
			'class="multi-part">'.__('Widget').'</a>'.
			'<a href="'.$p_url.'&amp;tab=settings"'.
			'class="multi-part">'.__('Settings').'</a>'.
			'<a href="'.$p_url.'&amp;tab=sql"'.
			'class="multi-part">'.__('SQL Queries').'</a>');
		
		echo('<div class="multi-part" id="fake-tab"'.
			'title="'.__('A fake tab').'">'.
			'<p>'.line(__LINE__).'</p>'.
			'<p>'.__('Hello World!').'</p>'.
			'</div>');
	}
	else
	{ ?>
	
	<div class="multi-part" id="administration"
		title="<?php echo __('Administration'); ?>">
		<p>
			<?php echo(__('If you edit an entry, you will see a new text in the right menu, this is due to a behavior, declared in the <strong>_admin.php</strong> file.').
			' <a href="'.$post_link.'">'.__('example').'</a>'); ?>
		</p>
		<p>
			<?php echo(__('We can call the <code>example::HelloWorld()</code> function, defined in the <strong>lib.example.php</strong> file:')); ?>
			<?php echo(example::HelloWorld().line(__LINE__)); ?>
		</p>
		
		<h3><?php echo(__('Forms')); ?></h3>
		
		<h4><?php echo(__('Combo').line(__LINE__)); ?></h4>
		<p><label><?php echo(__('Title:').
			form::combo(
				# name and id
				'combo',
				# combo values, search "define_combo_values" in this file
				$combo_values,
				# default value, search "define_combo_values" in this file
				$combo_default_value)); ?>
			</label></p>
		<p class="form-note">
			<?php echo(__('Here is a form note.').line(__LINE__)); ?>
		</p>
		
		<h4><?php echo(__('Radio').line(__LINE__)); ?></h4>
		<p>
			<label class="classic"><?php echo(
				form::radio(
					# name
					array('radio'),
					# value
					html::escapeHTML('1'),
					# checked ?
					false
				).
				__('one')); ?></label>
			<label class="classic"><?php echo(
				form::radio(array('radio'),html::escapeHTML('2'),false).
				__('two')); ?></label>
			<label class="classic"><?php echo(
				form::radio(array('radio'),html::escapeHTML('checked'),
					true).__('Checked by default')); ?></label>
			<label class="classic"><?php echo(
				form::radio(array('radio'),html::escapeHTML('false'),
					false,'','',
					# disabled ?
					true).__('Disabled')); ?></label>
		</p>
		
		<h4><?php echo(__('Checkbox').line(__LINE__)); ?></h4>
		<p>
			<label class="classic">
				<?php echo(form::checkbox(
					# name and id
					'checkbox_one',
					# value
					'1',
					# checked ?
					false
				).
					' '.__('one')); ?>
			</label>
			<label class="classic">
				<?php echo(form::checkbox(
					# name and id
					'checkbox_two',
					# value
					'2',
					# checked ?
					false
				).
					' '.__('two')); ?>
			</label>
			<label class="classic">
				<?php echo(form::checkbox('checked_checkbox','1',true).
					' '.__('Checked by default checkbox')); ?>
			</label>
			<label class="classic">
				<?php echo(form::checkbox('disabled_checkbox','1',false,
					'','',true).
					' '.__('Disabled checkbox')); ?>
			</label>
		</p>
		
		<h4><?php echo(__('Field').line(__LINE__)); ?></h4>
		<p><label><?php echo(__('Title:').
			form::field(
				# name and id
				'field',
				# size
				40,
				# max length
				255,
				# default value
				__('default value'))); ?></label></p>
		
		<p><label><?php echo(__('A field with the <code>maximal</code> class:').
			form::field(
				# name and id
				'field_maximal',
				# size
				40,
				# max length
				255,
				# default value
				__('default value'),
				# CSS class
				'maximal')); ?></label></p>
		
		<h4><?php echo(__('Password').line(__LINE__)); ?></h4>
		<p><label><?php echo(__('Title:').
			form::password(
				# name and id
				'password',
				# size
				20,
				# max length
				255,
				# default value
				__('default value'))); ?></label></p>
		
		<h4><?php echo(__('Textarea').line(__LINE__)); ?></h4>
		<p class="area"><label><?php echo(__('Title:').
			form::textarea(
				# name and id
				'textarea',
				# columns
				80,
				# rows
				20,
				# default value
				__('default value'))); ?></label></p>
		
		<h4><?php echo(__('Hidden').line(__LINE__)); ?></h4>
		<p><?php echo(form::hidden(
			# name
			'hidden',
			# value
			__('value'))); ?></p>
		<p><?php echo(__('(see the source of the page)')); ?></p>
		
		<h4><?php echo(__('Button').line(__LINE__)); ?></h4>
		<p><input type="submit" name="saveconfig"
			value="<?php echo __('Save configuration'); ?>" />
			<input type="submit" name="send"
			value="<?php echo __('Send'); ?>" /></p>
		
		<h4><?php echo(__('Fieldset').line(__LINE__)); ?></h4>
		
		<fieldset>
			<legend><?php echo(__('Fieldset legend')); ?></legend>
			<h4><?php echo(__('Combo')); ?></h4>
			<p><label><?php echo(__('Title:').
				form::combo('fieldset_combo',$combo_values,
				$combo_default_value)); ?>
			</label></p>
			<p><label><?php echo(__('Title:').
				form::field('fieldset_field',30,255,
				__('default value'))); ?></label></p>
			<input type="submit" name="send"
				value="<?php echo __('Send'); ?>" />
		</fieldset>
		
		<h3><?php echo(__('Columns')); ?></h3>
		
		<p><?php printf(__('We can use the %1$s CSS class to have %2$s columns:'),
			'<code>two-cols</code>',__('two'));
			echo(line(__LINE__)); ?></p>
		
		<div class="two-cols">
			<div class="col">
				<?php echo(__('Hello World!')); ?>
			</div>
			<div class="col">
				<?php echo(__('Hello World!')); ?>
			</div>
		</div>
		
		<p><?php printf(__('We can use the %1$s CSS class to have %2$s columns:'),
			'<code>three-cols</code>',__('three'));
			echo(line(__LINE__)); ?></p>
		
		<div class="three-cols class">
			<div class="col">
				<?php echo(__('Hello World!')); ?>
			</div>
			<div class="col">
				<?php echo(__('Hello World!')); ?>
			</div>
			<div class="col">
				<?php echo(__('Hello World!')); ?>
			</div>
		</div>
	</div>
	
	<div class="multi-part" id="public"
		title="<?php echo __('Public'); ?>">
		<p>
			<?php printf(__('URL of the %s page, defined in the <strong>_prepend.php</strong> file:'),
				__('Example')); ?>
			<code><?php echo($core->blog->url.
				$core->url->getBase('example')); ?></code>
		</p>
		<p><a href="<?php echo($core->blog->url.
			$core->url->getBase('example')); ?>">
			<?php printf(__('View the %s page'),
				__('Example')); ?></a></p>
		<p><a href="<?php echo($core->blog->url.
			$core->url->getBase('example')); ?>/hello">
			<?php printf(__('View the %s page with an argument in its URL'),
				__('Example')); ?></a></p>
	</div>
	
	<div class="multi-part" id="widget"
		title="<?php echo __('Widget'); ?>">
		<p>
			<?php echo(__('An example widget is available on the widgets page.').' '.
				__('It is defined in the <strong>_widget.php</strong> file.')); ?>
		</p>
	</div>
	
	<div class="multi-part" id="settings"
		title="<?php echo __('Settings'); ?>">
		<p><?php echo(line(__LINE__)); ?></p>
		<form method="post" action="<?php echo($p_url); ?>">
			<fieldset>
				<legend><?php echo(__('General settings')); ?></legend>
				<p>
					<?php echo(
					form::checkbox('setting_active',1,
						$settings->setting_active)); ?>
					<label class="classic" for="setting_active">
					<?php echo(__('Enable this setting')); ?>
					</label>
				</p>
			</fieldset>
			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="saveconfig"
				value="<?php echo __('Save configuration'); ?>" /></p>
		</form>
	</div>
	
	<div class="multi-part" id="sql"
		title="<?php echo __('SQL Queries'); ?>">
		<p>
			<?php echo(__('Title of the last entry:')); ?>
			<?php echo(example::LastPostTitle().line(__LINE__)); ?>
		</p>
	</div>
	
	<a href="<?php echo($p_url.'&amp;tab=fake-tab'); ?>"
			class="multi-part"><?php echo(__('A fake tab')); ?></a>
	
	<?php } ?>
	
	<div id="help" title="<?php echo __('Help'); ?>">
		<div class="help-content">
			<h2><?php echo(__('Help').line(__LINE__)); ?></h2>
			<p><?php echo(__('Hello World!')); ?></p>
		</div>
	</div>

</body>
</html>