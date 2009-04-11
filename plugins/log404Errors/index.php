<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Log 404 Errors, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
#
# Log 404 Errors is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Log 404 Errors is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

require_once(dirname(__FILE__).'/lib.log404Errors.php');

$tab = 'report';

$msg = (string)'';

$settings =& $core->blog->settings;

$settings->setNameSpace('log404errors');

# actions
if (!empty($_POST['saveconfig']))
{
	# Enable Log 404 Errors
	$settings->put('log404errors_active',
		(!empty($_POST['log404errors_active'])),'boolean',
		'Enable Log 404 Errors');
	
	http::redirect($p_url.'&saveconfig=1');
}
elseif (isset($_POST['drop']))
{
	try
	{
		log404Errors::drop();
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	$msg = __('Errors log has been deleted.');
}

if (isset($_GET['saveconfig']))
{
	$msg = __('Configuration successfully updated.');
	$tab = 'settings';
}

?>
<html>
<head>
  <title><?php echo __('Errors 404'); ?></title>
  <?php echo dcPage::jsPageTabs($tab); ?>
  <script type="text/javascript">
  //<![CDATA[
  	<?php echo dcPage::jsVar('dotclear.msg.confirm_404Errors_drop',
  	__('Are you sure you want to delete the 404 errors ?')); ?>
  	$(function() {
			$('input[@name="drop"]').click(function() {
				return window.confirm(dotclear.msg.confirm_404Errors_drop);
			});
	});
  //]]>
  </script>
</head>
<body>

	<h2><?php echo(__('Errors 404')); ?></h2>

	<?php 
		if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}
		if (!$settings->log404errors_active)
		{
			echo('<p class="message">'.__('The 404 errors are not logged.').'</p>');
		}
	?>

	<?php  ?>
	
	<div class="multi-part" id="report" title="<?php echo __('Summary'); ?>">
		<table class="clear" summary="<?php echo(__('Errors 404')); ?>">
			<caption><?php echo(__('404 errors')); ?></caption>
			<thead>
				<tr>
					<th><?php echo(__('count')); ?></th>
					<th><acronym title="Uniform Resource Locator">URL</acronym></th>
				</tr>
			</thead>
			<tbody>
				<?php log404Errors::show(true); ?>
			</tbody>
		</table>

		<form method="post" action="<?php echo(http::getSelfURI()); ?>">
			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="drop" value="<?php echo __('drop'); ?>" /></p>
		</form>
	</div>

	<div class="multi-part" id="errors" title="<?php echo __('Errors'); ?>">
		<table class="clear" summary="<?php echo(__('Errors 404')); ?>">
			<caption><?php echo(__('404 errors')); ?></caption>
			<thead>
				<tr>
					<th><?php echo(__('id')); ?></th>
					<th><acronym title="Uniform Resource Locator">URL</acronym></th>
					<th><?php echo(__('Date')); ?></th>
					<th><?php echo(__('referrer')); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php log404Errors::show(); ?>
			</tbody>
		</table>
		<form method="post" action="<?php echo(http::getSelfURI()); ?>">
			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="drop" value="<?php echo __('drop'); ?>" /></p>
		</form>
	</div>
	
	<div class="multi-part" id="settings" title="<?php echo __('Settings'); ?>">
		<form method="post" action="<?php echo http::getSelfURI(); ?>">
			<fieldset>
				<legend><?php echo(__('Settings')); ?></legend>
				<p>
					<?php echo(form::checkbox('log404errors_active',1,
						$settings->log404errors_active)); ?>
					<label class="classic" for="log404errors_active">
					<?php echo(__('Log 404 errors')); ?></label>
				</p>
		
				<p><?php echo $core->formNonce(); ?></p>
				<p><input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
			</fieldset>
		</form>
	</div>

</body>
</html>