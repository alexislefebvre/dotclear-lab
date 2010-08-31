<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of sofa, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!$core->auth->check('admin',$core->blog->id)) { return; }

# Init
$p_url = 'plugin.php?p='.basename(dirname(__FILE__));

# Save
if (isset($_POST['save'])) {
	try {
		$core->blog->settings->sofa->put('enable',$_POST['enable']);
		$core->blog->settings->sofa->put('css',$_POST['css']);
		http::redirect($p_url.'&upd=1');
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# ---------------
# Display
echo
'<html>'.
'<head>'.
	'<title>'.__('Filters & sort').'</title>'.
'</head>'.
'<body>';

if (isset($_GET['upd']) && $_GET['upd'] === '1') {
	echo '<p class="message">'.__('Configuration has been successfully saved').'</p>';
}

echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Filters & sort').'</h2>'.
'<form action="'.$p_url.'" method="post">'.
'<p class="field">'.
	'<label class="classic" for="enable">'.__('Enable sofa').'</label>'.
	form::checkbox('enable',1,$core->blog->settings->sofa->enable).
'</p>'.
'<p>'.
	'<label>'.__('Custom CSS absolute URL:').' '.
	form::field('css',30,255,$core->blog->settings->sofa->css).
	'</label>'.
'</p>'.
'<p class="form-note">'.__('Leave blank to disable this feature.').'</p>'.
'<p><input type="submit" name="save" value="'.__('ok').'" /></p>'.
$core->formNonce().
'</form>'.
'</body>'.
'</html>';

?>