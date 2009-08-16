<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of postWidgetText a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

if (!$core->auth->isSuperAdmin()) return;

$img_green = '<img alt="" src="index.php?pf=postWidgetText/inc/img/green.png" />';
$img_red = '<img alt="" src="index.php?pf=postWidgetText/inc/img/red.png" />';

$understand = isset($_POST['understand']) ? $_POST['understand'] : 0;
$delete_table = isset($_POST['delete_table']) ? $_POST['delete_table'] : 0;
$delete_settings = isset($_POST['delete_settings']) ? $_POST['delete_settings'] : 0;
$set = $understand || $delete_table || $delete_settings ? true : false;

echo '
<html>
 <head>
  <title>'.__('Post widget text').'</title>
 </head>
 <body>
<h2>'.
	html::escapeHTML($core->blog->name).' &rsaquo; '.
	__('Post widget text').' &rsaquo; '.
	__('Uninstall').'
</h2>
<div id="uninstall">';

if (!empty($_POST['validate']) && $set) {
	try {
		if (1 != $understand)
			throw new Exception(__('You must check warning in order to delete plugin.'));
		if (1 == $delete_table)
			postWidgetTextInstall::delTable($core);
		if (1 == $delete_settings)
			postWidgetTextInstall::delSettings($core);

		postWidgetTextInstall::delVersion($core);
		postWidgetTextInstall::delModule($core);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
	if (!$core->error->flag())
		http::redirect('plugins.php?removed=1');
}

if (!empty($_POST['uninstall']) && $set && 1 == $understand) {
	echo '
	<p>'.__('In order to properly uninstall this plugin, you must specify the actions to perform').'</p>
	<form method="post" action="'.$p_url.'">
	<h2>'.__('Validate').'</h2>
	<p>
	<label class=" classic">'.($understand ? $img_green : $img_red).
		__('You understand that if you delete this plugin, the other plugins that use there table and class will no longer work.').'</label><br />
	<label class=" classic">'.$img_green.
		__('Delete plugin files').'</label><br />
	<label class=" classic">'.($delete_table ? $img_green : $img_red).
		__('Delete plugin database table').'</label><br />
	<label class=" classic">'.($delete_settings ? $img_green : $img_red).
		__('Delete plugin settings').'</label><br />
	</p>
	<p>'.
	form::hidden(array('p'),'postWidgetText').
	form::hidden(array('understand'),$understand).
	form::hidden(array('delete_table'),$delete_table).
	form::hidden(array('delete_settings'),$delete_settings).
	$core->formNonce().'
	<input type="submit" name="validate" value="'.__('Uninstall').'" />
	<input type="submit" name="back" value="'.__('Back').'" /></p>
	</form>';

} else {
	if (!empty($_POST['uninstall']) && 1 != $understand)
		$core->error->add(__('You must check warning in order to delete plugin.'));

	echo '
	<p>'.__('In order to properly uninstall this plugin, you must specify the actions to perform').'</p>
	<form method="post" action="'.$p_url.'">
	<h2>'.__('Uninstall "postWidgetText" plugin').'</h2>
	<p>
	<label class=" classic">'.form::checkbox(array('understand'),1,$understand).
	__('You understand that if you delete this plugin, the other plugins that use there table and class will no longer work.').'</label><br />
	<label class=" classic">'.form::checkbox(array('delete_table'),1,$delete_table).
	__('Delete plugin database table').'</label><br />
	<label class=" classic">'.form::checkbox(array('delete_settings'),1,$delete_settings).
	__('Delete plugin settings').'</label><br />
	</p><p>'.
	form::hidden('p','postWidgetText').
	$core->formNonce().'
	<input type="submit" name="uninstall" value="'.__('Uninstall').'" /></p>
	</form>';
}
echo '
  </div>
 </body>
</html>';
?>