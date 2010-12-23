<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom and contributors
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$p_url	= 'plugin.php?p=multiToc';
$settings	= unserialize($core->blog->settings->multiToc->multitoc_settings);

if (!empty($_POST['save']))
{
	$types = array('cat','tag','alpha','post');
	
	foreach ($types as $type) {
		foreach ($settings[$type] as $k => $v) {
			$settings[$type][$k] = '';
		}
	}
	
	foreach ($_POST as $k => $v) {
		if (preg_match('#^('.implode('|',$types).')_(.*)$#',$k,$match)) {
			$settings[$match[1]][$match[2]] = $v;
		}
	}
	
	$core->blog->settings->multiToc->put('multitoc_settings',serialize($settings));
	http::redirect($p_url.'&upd=1');
}

function getSetting($type,$value)
{
	global $settings;
	
	return isset($settings[$type][$value]) ? $settings[$type][$value] : '';
}


echo
'<html>'.
'<head>'.
	'<title>'.__('Tables of content').'</title>'.
'</head>'.
'<body>'.
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Tables of content').'</h2>';

# Information message
if (!empty($_GET['upd'])) {
	echo
	'<p class="message">'.
	__('Configuration has been saved successfully').
	'</p>';
}

echo
'<form method="post" action="'.$p_url.'">'.
	multiTocUi::form('post').
	multiTocUi::form('cat').
	multiTocUi::form('tag').
	multiTocUi::form('alpha').
	$core->formNonce().
'<p><input name="save" value="'.__('Save').'" type="submit" /></p>'.
'</form>'.
'</body>'.
'</html>';

?>