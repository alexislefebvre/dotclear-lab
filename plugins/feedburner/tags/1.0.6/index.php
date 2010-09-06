<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of feedburner, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# Initialisation des variables
$nb_per_page 		= 5;
$p_url			= 'plugin.php?p=feedburner';
$page			= !empty($_GET['page']) ? (integer)$_GET['page'] : 1;
$default_tab		= !empty($_GET['tab']) ? trim(html::escapeHTML($_GET['tab'])) : 'feeds';
$feeds			= unserialize($core->blog->settings->feedburner->feedburner_feeds);

# Sert les fichiers de amchart
if (isset($_GET['file'])) {
	$file = dirname(__FILE__).'/inc/amstock/'.$_GET['file'];
	if (file_exists($file)) {
		$ext = strrchr($_GET['file'],'.');
		switch($ext) {
			case ".swf": header('Content-Type: application/x-shockwave-flash');
				break;
			case ".js": header('Content-Type: application/x-javascript');
				break;
			case ".php": require $file;
				exit;
				break;
			default: http::head(404,'Not Found');
				exit;
				break;
		}
		http::cache(array_merge(array($file),get_included_files()));
		readfile($file);
		exit;
	}
}

# Enregistrement de la configuration des flux
if (!empty($_POST['save'])) {
	$feeds['rss2'] = $feeds['rss2'] != $_POST['rss2'] ? $_POST['rss2'] : $feeds['rss2'];
	$feeds['rss2_comments'] = $feeds['rss2_comments'] != $_POST['rss2_comments'] ? $_POST['rss2_comments'] : $feeds['rss2_comments'];
	$feeds['atom'] = $feeds['atom'] != $_POST['atom'] ? $_POST['atom'] : $feeds['atom'];
	$feeds['atom_comments'] = $feeds['atom_comments'] != $_POST['atom_comments'] ? $_POST['atom_comments'] : $feeds['atom_comments'];
	$core->blog->settings->addNamespace('feedburner');
	$core->blog->settings->feedburner->put(
		'feedburner_feeds',
		serialize($feeds)
	);
}

$fb = new feedburner($core);

# Sert le csv pour les statistiques
if (isset($_GET['data'])) {
	$id = html::escapeHTML($_GET['id']);
	$fb->check($id);
	$fb->getCsv();
}

echo
'<html>'.
'<head>'.
	'<title>'.__('Feedburner statistics').'</title>'.
	dcPage::jsModal().
	dcPage::jsPageTabs($default_tab).
	dcPage::jsLoad('index.php?pf=feedburner/js/_feedburner.js').
	'<link rel="stylesheet" href="index.php?pf=feedburner/style.css" type="text/css" />'.
'</head>'.
'<body>';

# Message
if (isset($_POST['save'])) {
	echo '<p class="static-msg">'.__('Setup saved').'</p>';
}

echo
'<h2>'.$core->blog->name.' &rsaquo; '.__('Feedburner statistics').'</h2>'.
'<p>'.__('View your feedburner statistics directly in Dotclear').'</p>'.
'<!-- Feeds configuration -->'.
'<div id="feeds" class="multi-part" title="'.__('Feeds configuration').'">';
feedburnerUi::feedsTable($feeds,$p_url);

echo
'</div>'.
'<!-- Feed statistics -->'.
'<div id="stats" class="multi-part" title="'.__('Feed statistics').'">';
feedburnerUi::statsForm($feeds,$p_url);
feedburnerUi::statsView();

echo
'</div>'.
'</body>'.
'</html>';

?>