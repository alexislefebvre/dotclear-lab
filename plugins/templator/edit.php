<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of templator a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

try
{
	try
	{
		if (!empty($_GET['edit'])) {
			$name = rawurldecode($_GET['edit']);
			$file = $core->templator->getSourceContent($name);
		} 
	}
	catch (Exception $e)
	{
		$file = $file_default;
		throw $e;
	}
	# Write file
	if (!empty($_POST['write']))
	{
		$file['c'] = $_POST['file_content'];
		$core->templator->writeTpl($file['f'],$file['c']);
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
?>
<html>
<head>
	<title><?php echo __('Templator'); ?></title>
	<link rel="stylesheet" type="text/css" href="index.php?pf=templator/style/style.css" />
	<script type="text/javascript">
	//<![CDATA[
	<?php echo dcPage::jsVar('dotclear.msg.saving_document',__("Saving document...")); ?>
	<?php echo dcPage::jsVar('dotclear.msg.document_saved',__("Document saved")); ?>
	<?php echo dcPage::jsVar('dotclear.msg.error_occurred',__("An error occurred:")); ?>
	//]]>
	</script>
	<?php echo dcPage::jsLoad('index.php?pf=templator/js/script.js');?>
</head>

<body>
<?php
echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; <a href="'.$p_url.'">'.__('Supplementary templates').'</a> &rsaquo; '.__('Edit the template').'</h2>';

if (($file['c'] !== null))
{
	echo
	'<div id="file-templator">'.
	'<form id="file-form" action="'.$p_url.'&amp;edit='.$name.'" method="post">'.
	'<fieldset><legend>'.__('File editor').'</legend>'.
	'<p>'.sprintf(__('Editing file %s'),'<strong>'.$file['f']).'</strong></p>'.
	'<p>'.form::textarea('file_content',72,30,html::escapeHTML($file['c']),'maximal','',!$file['w']).'</p>';

	if ($file['w'])
	{
		echo
		'<p><input type="submit" name="write" value="'.__('save').'" accesskey="s" /> '.
		$core->formNonce().
		form::hidden(array('file_id'),html::escapeHTML($file['f'])).
		'</p>';
		

	}
	else
	{
		echo '<p>'.__('This file is not writable. Please check your files permissions.').'</p>';
	}

	echo
	'</fieldset></form></div>';
}

?>
</body>
</html>