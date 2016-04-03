<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Versions Manager, a plugin for Dotclear 2
# Copyright 2010-2015 Moe (http://gniark.net/)
#
# Versions Manager is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Versions Manager is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {exit;}

$page_title = __('Versions manager');

require_once(dirname(__FILE__).'/php-xhtml-table/class.table.php');
require_once(dirname(__FILE__).'/inc/lib.versionsManager.php');

$msg = (string)'';

if (isset($_POST['submit']))
{
	if (count($_POST['v']) > 0)
	{
		foreach ($_POST['v'] as $k => $v)
		{
			if (isset($v['delete']) && ((int) $v['delete'] == 1))
			{
				versionsManager::delete_version($k);
			}
			else
			{
				versionsManager::update_version($k,$v['version']);
			}
		}
		
		http::redirect($p_url.'&updated=1');
	}
}

if (isset($_GET['updated']))
{
	$msg = __('Versions updated or deleted.');
}

?>
<html>
<head>
	<title><?php echo $page_title; ?></title>
	<!-- from /dotclear/plugins/widgets -->
	<script type="text/javascript">
	//<![CDATA[
		<?php echo 
		dcPage::jsVar('dotclear.msg.confirm_delete_versions',
		__('Are you sure you want to delete versions?')); ?>
		$(document).ready(function() {
			$('.checkboxes-helpers').each(function() {
				dotclear.checkboxesHelpers(this);
			});
			$('input[name="submit"]').click(function() {
				if ($('.delete_versions:checked').length > 0)
				{
					return window.confirm(dotclear.msg.confirm_delete_versions);
				}
			});
		});
	//]]>
	</script>
</head>
<body>

<?php echo dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));

if (!empty($msg)) {
  dcPage::success($msg);
}
?>
	
	<div id="versions">
		<p class="warn"><?php echo(__('Deleting the version of a plugin will reinstall it if the plugin has an install process.')); ?></p>
		<form method="post" action="<?php echo($p_url); ?>">
			<?php echo(versionsManager::versions()); ?>
			<p class="checkboxes-helpers"></p>
			<p><?php echo($core->formNonce()); ?></p>
			<p><input type="submit" name="submit"
				value="<?php echo(__('Perform selected actions')); ?>" /></p>
		</form>
	</div>

</body>
</html>