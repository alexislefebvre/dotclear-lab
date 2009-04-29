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

# default tab
$default_tab = 'settings';

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
		http::redirect($p_url.'&saveconfig=1');
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
	
	<div class="multi-part" id="administration"
		title="<?php echo __('Administration'); ?>">
		<p>
			<?php echo(__('If you edit an entry, you will see a new text in the right menu, this is due to a behavior, declared in the <strong>_admin.php</strong> file.')); ?>
		</p>
		<p>
			<?php echo(__('We can call the <code>example::HelloWorld()</code> function, defined in the <strong>lib.example.php</strong> file:')); ?>
			<?php echo(example::HelloWorld()); ?>
		</p>
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

</body>
</html>