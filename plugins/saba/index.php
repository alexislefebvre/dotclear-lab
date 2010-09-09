<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of saba, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('amdin');

$core->blog->settings->addNamespace('saba');

$active = (boolean) $core->blog->settings->saba->active;

if (isset($_POST['save']))
{
	try
	{
		$active = !empty($_POST['active']);
		
		$core->blog->settings->saba->put('active',$active,'boolean','Enable extension');
		
		$core->blog->triggerBlog();
		
		http::redirect('plugin.php?p=saba&done=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

echo '
<html><head><title>'.__("Search across blog's archive").'</title></head>
<body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__("Search across blog's archive").'</h2>'.
(!empty($_REQUEST['done']) ? '<p class="message">'.__('Configuration successfully updated').'</p>' : '').'
<fieldset><legend>'.__('Settings').'</legend>
<form method="post" action="plugin.php">

<p><label class="classic">'.
form::checkbox('active','1',$active).__('Enable extension').'</label></p>

<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().form::hidden(array('p'),'saba').'</p>
</form>
</fieldset>
<br class="clear"/>
<p class="right">
<i>&ldquo; Saba (pronounced /ˈseɪbə/) is the smallest island of the Netherlands Antilles. &rdquo;</i><br />
saba - '.$core->plugins->moduleInfo('saba','version').'&nbsp;
<img alt="'.__("Search across blog's archive").'" src="index.php?pf=saba/icon.png" />
</p>';
dcPage::helpBlock('saba');
echo '</body></html>';
?>