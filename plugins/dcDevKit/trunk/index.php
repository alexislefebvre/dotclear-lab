<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcDevKit, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom, Dsls and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

# Help links
$links = array(
	'general' => array(
		__('Lab') => 'http://lab.dotclear.org',
		__('Dotclear trac') => 'http://dev.dotclear.org/2.0/',
		__('Dotclear technical documentation') => 'http://dev.dotclear.org/code/2/',
		__('Dotaddict tips') => 'http://tips.dotaddict.org',
	),
	'plugins' => array(
		__('Plugin documentation') => 'http://fr.dotclear.org/documentation/2.0/resources/plugins',
		__('Dotaddict plugins') => 'http://plugins.dotaddict.org'
	),
	'themes' => array(
		__('Theme documentation') => 'http://fr.dotclear.org/documentation/2.0/resources/themes',
		__('Dotaddict themes') => 'http://themes.dotaddict.org'
	)
);

# Init
$devkit	= new dcDevKit($core);
$id		= isset($_GET['id']) ? $_GET['id'] : null;
$tab		= isset($_REQUEST['tab']) ? $_REQUEST['tab'] : null;

# Module test
if (!is_null($id)) {
	if (!$devkit->hasModule($id)) {
		$id = null;
		$core->error->add(__('Module does no exists'));
	}
}

# ------------
# Display
echo
'<html>'.
'<head>'.
	'<title>'.__('Developers kit').'</title>'.
	'<link rel="stylesheet" type="text/css" href="index.php?pf=dcDevKit/style.css" />'.
	(!is_null($id) ? dcPage::jsPageTabs((!is_null($tab) ? $tab : '')) : '').
'</head>'.
'<body>'.
'<h2>'.
html::escapeHTML($core->blog->name).' &rsaquo; '.
sprintf((!is_null($id) ? '<a href="%2$s">%1$s</a> &rsaquo; ' : '%1$s'),__('Developers kit'),$p_url).
sprintf((!is_null($id) ? '%s' : ''),$devkit->getModule($id)->name).
'</h2>';

# Dashboard
if (is_null($id)) {
	foreach ($devkit->getModules() as $id => $module) {
		echo
		'<div class="devkit-module">'.
		sprintf('<a href="%3$s"><img src="%1$s" alt="%2$s" title="%2$s" /></a>',$module->icon,$module->name,$module->guiURL()).
		'<p>'.$module->guiLink().'</p>'.
		'</div>';
	}
	echo '<div id="devkik-links" class="three-cols">';
	foreach ($links as $section => $list) {
		$res = $title = '';
		foreach ($list as $label => $url) {
			$res .= sprintf('<li><a href="%2$s">%1$s</a></li>',$label,$url);
		}
		if ($res !== '') {
			if ($section === 'themes') {
				$title =  __('Theme links');
			}
			elseif ($section === 'plugins') {
				$title =  __('Plugin links');
			}
			if ($section === 'general') {
				$title =  __('General links');
			}
			echo
			'<div class="col">'.
				'<h2>'.$title.'</h2>'.
				'<ul>'.$res.'</ul>'.
			'</div>';
		}
	}
	echo '</div>';
}
# Module page
else {
	echo
	'<p>'.$devkit->getModule($id)->description.'</p>'.
	$devkit->getModule($id)->gui($devkit->getModule($id)->guiURL());
}

echo
'</body>'.
'</html>';

?>