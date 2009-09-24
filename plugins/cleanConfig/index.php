<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of clean:config.
# Copyright 2007 Moe (http://gniark.net/)
#
# clean:config is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# clean:config is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {exit;}

	require_once(dirname(__FILE__).'/php-xhtml-table/class.table.php');
	require_once(dirname(__FILE__).'/class.cleanconfig.php');

	$default_tab = 'blog_settings';

	$msg = (string)'';
	
	# actions
	$limit = $_POST['limit'];
	if ((isset($_POST['delete'])) AND (($limit == 'blog') OR ($limit == 'global')))
	{
		if (count($_POST['settings']) == 0)
		{
			$msg = '<p>'.__('No settings deleted.').'</p>';
			$default_tab = $limit.'_settings';
		}
		else
		{
			foreach ($_POST['settings'] as $setting)
			{
				cleanconfig::delete($setting,$limit);
			}
			$msg = '<p>'.(($limit == 'blog') ? __('Deleted blog settings :') : __('Deleted global settings :')).'</p><ul><li>'.
				implode('</li><li>',$_POST['settings']).'</li></ul>';
			$default_tab = $limit.'_settings';
		}
	}
	elseif (isset($_POST['delete_versions']))
	{
		if (count($_POST['versions']) == 0)
		{
			$msg = '<p>'.__('No versions deleted.').'</p>';
			$default_tab = 'versions';
		}
		else
		{
			foreach ($_POST['versions'] as $k)
			{
				cleanconfig::delete_version($k);
			}
			$msg = '<p>'.__('Deleted versions:').'</p><ul><li>'.
				implode('</li><li>',$_POST['versions']).'</li></ul>';
			$default_tab = 'versions';
		}
	}

?>
<html>
<head>
	<title><?php echo __('clean:config'); ?></title>
	<?php echo dcPage::jsPageTabs($default_tab); ?>
	<style type="text/css">
		.ns-name { background: #ccc; color: #000; padding-top: 0.3em; padding-bottom: 0.3em; font-size: 1.1em; }
	</style>
	<!-- from /dotclear/plugins/widgets -->
	<script type="text/javascript">
	//<![CDATA[
		<?php echo dcPage::jsVar('dotclear.msg.confirm_cleanconfig_delete',
		__('Are you sure you want to delete settings?')).
		dcPage::jsVar('dotclear.msg.confirm_cleanconfig_delete_versions',
		__('Are you sure you want to delete versions?')); ?>
		$(document).ready(function() {
			$('.checkboxes-helpers').each(function() {
				dotclear.checkboxesHelpers(this);
			});
			$('input[name="delete"]').click(function() {
				return window.confirm(dotclear.msg.confirm_cleanconfig_delete);
			});
			$('input[name="delete_versions"]').click(function() {
				return window.confirm(dotclear.msg.confirm_cleanconfig_delete_versions);
			});
			$('td[class="ns-name"]').css({ cursor:"pointer" });
			$('td[class="ns-name"]').click(function() {
				$("."+$(this).children().filter("strong").text()).toggleCheck();
				return false;
			});
		});
	//]]>
	</script>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('clean:config'); ?></h2>

	<?php if (!empty($msg)) {echo '<div class="message">'.$msg.'</div>';} ?>

	<div class="multi-part" id="blog_settings" title="<?php echo __('blog settings'); ?>">
		<?php echo(cleanconfig::settings('blog')); ?>
	</div>

	<div class="multi-part" id="global_settings" title="<?php echo __('global settings'); ?>">
		<?php echo(cleanconfig::settings('global')); ?>
	</div>

	<div class="multi-part" id="versions" title="<?php echo __('versions'); ?>">
		<p><?php echo(__('Deletting the version of a module will reinstall it if the module has an install process.')); ?></p>
		<?php echo(cleanconfig::versions()); ?>
	</div>

</body>
</html>