<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2010 Gaetan Guillard and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$dsm_flag  = $core->blog->settings->dcsitemap->dsm_flag;
$dsm_title = $core->blog->settings->dcsitemap->dsm_title;

if ($dsm_title === null) {
	$dsm_title = __('Site map');
}

if (isset($_POST['dsm_flag']))
{
	try
	{
		$dsm_flag  = $_POST['dsm_flag'];
		$dsm_title = $_POST['dsm_title'];
		
		if (empty($_POST['dsm_title'])) {
			throw new Exception(__('No page title.'));
		}

		# Everything's fine, save options
		$core->blog->settings->addNamespace('dcsitemap');
		$core->blog->settings->dcsitemap->put('dsm_flag',$dsm_flag,'boolean','dcSiteMap plugin status');
		$core->blog->settings->dcsitemap->put('dsm_title',$dsm_title,'string','dcSiteMap page title');
		
		$core->blog->triggerBlog();
		http::redirect($p_url.'&upd=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
	<title><?php echo __('Site map'); ?></title>
</head>

<body>
<?php
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Site map').'</h2>';

if (!empty($_GET['upd'])) {
	echo '<p class="message">'.__('Setting have been successfully updated.').'</p>';
}

echo
'<form action="'.$p_url.'" method="post">'.
'<fieldset><legend>'.__('Plugin activation').'</legend>'.
'<p><label class="classic">'.form::checkbox('dsm_flag',1,(boolean) $dsm_flag).
' '.__('Enable dcSiteMap').'</label></p>'.
'</fieldset>'.
'<fieldset><legend>'.__('Presentation options').'</legend>'.
'<p><label class="required" title="'.__('Required field').'">'.__('Page title:').' '.
form::field('dsm_title',30,256,html::escapeHTML($dsm_title)).
'</label></p>'.
'</fieldset>'.
'<p>'.$core->formNonce().'<input type="submit" value="'.__('save').'" /></p>'.
'</form>';

dcPage::helpBlock('dcSiteMap');
?>
</body>
</html>