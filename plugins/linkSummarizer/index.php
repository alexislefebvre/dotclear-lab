<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Link Summarizer.
# Copyright 2009 Moe (http://gniark.net/)
#
# Link Summarizer is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Link Summarizer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://wordpress.org/extend/plugins/link-summarizer/
#
# http://mac.partofus.org/macpress/2007/08/02/wordpress-plugin-link-summarizer/
# http://mac.partofus.org/macpress/forum/english-support-forum/dotclear-plugin/page-1/post-6/#p6
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$settings =& $core->blog->settings;

$settings->setNameSpace('linksummarizer');

try
{
	if (!empty($_POST['saveconfig']))
	{
		# Enable Link Summarizer
		$settings->put('linksummarizer_active',
			(!empty($_POST['linksummarizer_active'])),'boolean',
			'Enable Link Summarizer');
		
		# Display Link Summarizer only in post context
		$settings->put('linksummarizer_only_post',
			(!empty($_POST['linksummarizer_only_post'])),'boolean',
			'Display Link Summarizer only in post context');	
		
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
	<title><?php echo __('Link Summarizer'); ?></title>
</head>
<body>

	<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.
		__('Link Summarizer'); ?></h2>
	
	<form method="post" action="<?php echo http::getSelfURI(); ?>">
		<p>
			<?php echo(form::checkbox('linksummarizer_active',1,
				$settings->linksummarizer_active)); ?>
			<label class="classic" for="linksummarizer_active">
			<?php echo(__('Display links in posts after the posts')); ?></label>
		</p>
		
		<p>
			<?php echo(form::checkbox('linksummarizer_only_post',1,
				$settings->linksummarizer_only_post)); ?>
			<label class="classic" for="linksummarizer_only_post">
			<?php echo(__('Display the link summary only in post context')); ?></label>
		</p>
		
		<p><?php echo $core->formNonce(); ?></p>
		<p><input type="submit" name="saveconfig"
			value="<?php echo __('Save configuration'); ?>" /></p>
	</form>
	
	<div id="help" title="<?php echo __('Help'); ?>">
		<div class="help-content">
			<h2><?php echo(__('Help')); ?></h2>
			<p><?php printf(__('Inspired by <a href="%1$s">%2$s</a>'),
				'http://wordpress.org/extend/plugins/link-summarizer/',
				__('Link Summarizer for WordPress')); ?></p>
		</div>
	</div>

</body>
</html>