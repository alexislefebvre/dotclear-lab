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

$page_title = __('Link Summarizer');

# Everything's fine, save options
$core->blog->settings->addNamespace('linksummarizer');
		
try
{
	if (!empty($_POST['saveconfig']))
	{
		# Enable Link Summarizer
		$core->blog->settings->linksummarizer->put('linksummarizer_active',
			(!empty($_POST['linksummarizer_active'])),'boolean',
			'Enable Link Summarizer');
		
		# Display Link Summarizer only in post context
		$core->blog->settings->linksummarizer->put('linksummarizer_only_post',
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
	<title><?php echo $page_title; ?></title>
</head>
<body>
<?php
	echo dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));
if (!empty($_GET['saveconfig'])) {
  dcPage::success(__('Configuration successfully updated.'));
};
?>

	<form method="post" action="<?php echo http::getSelfURI(); ?>">
	<div class="fieldset">
		<h4><?php echo __('Configuration'); ?></h4>
		<p>
			<?php echo(form::checkbox('linksummarizer_active',1,
				$core->blog->settings->linksummarizer->linksummarizer_active)); ?>
			<label class="classic" for="linksummarizer_active">
			<?php echo(__('Activate the plugin (display a summary of links contained in the Notes after the entries)')); ?></label>
		</p>


<?php
if ($core->blog->settings->linksummarizer->linksummarizer_active) {
	echo

		'<p>'.
			(form::checkbox('linksummarizer_only_post',1,$core->blog->settings->linksummarizer->linksummarizer_only_post)).
			'<label class="classic" for="linksummarizer_only_post">'.
			__('Display the links summary only in post context').'</label>'.
		'</p>';
}
?>
  </div>
		<p><?php echo $core->formNonce(); ?></p>
		<p><input type="submit" name="saveconfig"
			value="<?php echo __('Save'); ?>" /></p>
	</form>
</body>
</html>