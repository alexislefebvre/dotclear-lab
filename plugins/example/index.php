<?php 
/***** BEGIN LICENSE BLOCK *****
Copyright (c) 2009, <Dotclear Lab Contributors>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:

1. Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above
copyright notice, this list of conditions and the following
disclaimer in the documentation and/or other materials provided
with the distribution.
3. The name of the author may not be used to endorse or promote
products derived from this software without specific prior written
permission.

THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS
OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	
Icon (icon.png) is from Silk Icons :
	http://www.famfamfam.com/lab/icons/silk/

***** END LICENSE BLOCK *****/

if (!defined('DC_CONTEXT_ADMIN')) {return;}

function line($line)
{
	return(' '.sprintf(
		__('(line %s of the <strong>index.php</strong> file)'),
		$line).' ');
}

# default tab
$default_tab = 'administration';

if (!empty($_REQUEST['tab']))
{
	switch ($_REQUEST['tab'])
	{
		case 'settings' :
			$default_tab = 'settings';
			break;
	}
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
	
	<div class="multi-part" id="administration"
		title="<?php echo __('Administration'); ?>">
		<p>
			<?php echo(__('If you edit an entry, you will see a new text in the right menu, this is due to a behavior, declared in the <strong>_admin.php</strong> file.').
			' <a href="'.$post_link.'">'.__('example').'</a>'); ?>
		</p>
		<p>
			<?php echo(__('We can call the <code>example::HelloWorld()</code> function, defined in the <strong>lib.example.php</strong> file:')); ?>
			<?php echo(example::HelloWorld().line(__LINE__)); ?></p>
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
				form::combo('combo',$combo_values,
				$combo_default_value)); ?>
			</label></p>
			<p><label><?php echo(__('Title:').
				form::field('field',30,255,
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

</body>
</html>