<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Plugins Page.
# Copyright 2007 Moe (http://gniark.net/)
#
# Plugins Page is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Plugins Page is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

	require_once(dirname(__FILE__).'/php-xhtml-table/class.table.php');
	require_once(dirname(__FILE__).'/class.pluginsPage.php');

	$pluginsPage_active = $core->blog->settings->pluginsPage_active;
	$pluginsPage_show_icons = $core->blog->settings->pluginsPage_show_icons;
	$pluginsPage_style = $core->blog->settings->pluginsPage_style;
	$pluginsPage_url = $core->blog->settings->pluginsPage_url;
	if (empty($pluginsPage_url)) {$pluginsPage_url = 'plugins';}

	$msg = (string)'';
	$error = (string)'';
	$tab = 'pluginsPage';

	if (!empty($_POST['saveconfig']))
	{
		try
		{
			$core->blog->settings->setNameSpace('pluginspage');
			# Activate Plugins Page
			$pluginsPage_active = (empty($_POST['pluginsPage_active']))?false:true;
			$core->blog->settings->put('pluginsPage_active',$pluginsPage_active,'boolean','Activate Plugins Page');
			# Show icons
			$pluginsPage_show_icons = (empty($_POST['pluginsPage_show_icons']))?false:true;
			$core->blog->settings->put('pluginsPage_show_icons',$pluginsPage_show_icons,'boolean','Show icons');
			# Style of Plugins Page
			$pluginsPage_style = $_POST['pluginsPage_style'];
			files::putContent(dirname(__FILE__).'/template/plugins.css',$pluginsPage_style);
			# URL
			$pluginsPage_url = (!empty($_POST['pluginsPage_url']) ? $_POST['pluginsPage_url'] : 'plugins');
			$core->blog->settings->put('pluginsPage_url',$pluginsPage_url,'text','URL');
			# Hide plugins
			$pluginsPage_hidden_plugins = (!empty($_POST['hidden_plugins']))?base64_encode(serialize($_POST['hidden_plugins'])):base64_encode(serialize(array('')));
			$core->blog->settings->put('pluginsPage_hidden_plugins',
				$pluginsPage_hidden_plugins,'test','Hidden plugins');
			$hidden_plugins = $_POST['hidden_plugins'];

			$core->blog->triggerBlog();

			http::redirect($p_url.'&saveconfig=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}

	# define $link
	$core->url->register($pluginsPage_url,$pluginsPage_url,'^'.$pluginsPage_url.'(/.+)?$',array('pluginsPage','showPage'));
	$link = $core->blog->url.$core->url->getBase($pluginsPage_url);

	if (!empty($_POST['deletecache']))
	{
		try {
			pluginsPage::deleteCache();
			$core->blog->triggerBlog();
			http::redirect($p_url.'&cachedeleted=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}

	if (isset($_GET['saveconfig']))
	{
		$msg = __('Configuration successfully updated.');
		$tab = 'settings';
	}
	else if (isset($_GET['cachedeleted']))
	{
		$msg = __('Cache of Plugins Page has been successfully deleted.');
	}
?>
<html>
<head>
  <title><?php echo __('Plugins Page'); ?></title>
  <?php 
  		echo(dcPage::jsPageTabs($tab));
  ?>
  <style type="text/css">
  <!--
  	label {display:inline;}
  -->
  </style>
  <script type="text/javascript">
	//<![CDATA[
		$(document).ready(function() {
			$('.checkboxes-helpers').each(function() {
			dotclear.checkboxesHelpers(this);
			});
		});
	//]]>
	</script>
  <link rel="stylesheet" type="text/css" href="index.php?pf=pluginsPage/template/plugins.css" media="screen" />
</head>
<body>

	<h2><?php echo __('Plugins Page'); ?></h2>

	<?php 
		if (!empty($msg)) {echo '<div class="message">'.$msg.'</div>';}
		if (!empty($error)) {echo '<div class="error"><strong>'.__('Error:').'</strong> '.$error.'</div>';}
	?>

	<div class="multi-part" id="pluginsPage" title="<?php echo __('Plugins Page'); ?>">
		<h2><?php 
			if ($pluginsPage_active) {
				echo('<a href="'.$link.'">'.__('See Plugins Page').'</a>.');
			} else {
				echo(__('Plugins Page is not active.'));
			}
		?></h2>
		<?php if ($pluginsPage_active) { ?>
		<h3><?php echo(__('Links:')); ?></h3>
		<p><code><?php echo($link); ?></code></p>
		<p><code><?php echo(htmlentities('<a href="'.$link.'">'.__('See Plugins Page').'</a>.')); ?></code></p>
		<p><code><?php echo('['.__('See Plugins Page').'|'.$link.']'); ?></code></p>
		<?php } ?>
		<?php if (file_exists(pluginsPage::getCacheFile())) { ?>
		<form method="post" action="<?php echo(http::getSelfURI()); ?>">
			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="deletecache" value="<?php echo __('Delete the cache of Plugins Page'); ?>" /></p>
		</form>
		<?php } ?>
		<h2><?php echo(__('Preview:')); ?></h2>
		<?php echo(pluginsPage::getPluginsTable()); ?>
	</div>

	<div class="multi-part" id="settings" title="<?php echo __('settings'); ?>">
		<form method="post" action="<?php echo(http::getSelfURI()); ?>">
			<fieldset>
				<legend><?php echo(__('Plugins Page')); ?></legend>
				<p class="field"><?php echo(form::checkbox('pluginsPage_active',1,$pluginsPage_active).'&nbsp;<label for="pluginsPage_active">'.__('Activate Plugins Page').'</label>'); ?></p>
				<p class="field"><?php echo(form::checkbox('pluginsPage_show_icons',1,$pluginsPage_show_icons).'&nbsp;<label for="pluginsPage_show_icons">'.__('Show icons').'</label>'); ?></p>
				<p><label class=" classic"><?php echo(__('URL :').' '.form::field('pluginsPage_url', 60, 255, $pluginsPage_url)); ?></label></p>

				<h3><label for="pluginsPage_style"><?php echo(__('Style of Plugins Page:')); ?></label></h3>
				<p><?php echo(__('CSS file:').' '.dirname(__FILE__).'/template/plugins.css'); ?></p>
				<p><?php echo(form::textarea('pluginsPage_style',80,20,html::escapeHTML(file_get_contents(dirname(__FILE__).'/template/plugins.css')))); ?></p>
			</fieldset>
			<h2><?php echo(__('Hide plugins:')); ?></h2>
			<?php echo(pluginsPage::getPluginsTable(false,true)); ?>
			<p class="col checkboxes-helpers"></p>
			<p><?php echo $core->formNonce(); ?></p>
			<p><input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" /></p>
		</form>
	</div>

</body>
</html>