<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin feedburner for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

# Initialisation des variables
$nb_per_page 		= 5;
$p_url				= 'plugin.php?p=feedburner';
$page				= !empty($_GET['page']) ? (integer)$_GET['page'] : 1;
$default_tab		= !empty($_GET['tab']) ? trim(html::escapeHTML($_GET['tab'])) : 'feeds';
$feeds				= unserialize($core->blog->settings->feedburner_feeds);

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
	$feeds['rss'] = $feeds['rss'] != $_POST['rss'] ? $_POST['rss'] : $feeds['rss'];
	$feeds['rssco'] = $feeds['rssco'] != $_POST['rssco'] ? $_POST['rssco'] : $feeds['rssco'];
	$feeds['atom'] = $feeds['atom'] != $_POST['atom'] ? $_POST['atom'] : $feeds['atom'];
	$feeds['atomco'] = $feeds['atomco'] != $_POST['atomco'] ? $_POST['atomco'] : $feeds['atomco'];
	$core->blog->settings->setNamespace('feedburner');
	$core->blog->settings->put(
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

/**
 * Returns feedburner API's errors
 *
 * @param	array	errors
 *
 * @return	string
 */
function getErrors($errors)
{
	$res = '';
	
	foreach ($errors as $k => $v) {
		$res .= '<h3>'.sprintf(__('Error %1$s : %2$s'),$k,utf8_encode($v)).'</h3>';
	}
	
	return $res;
}

/**
 * Returns plugin messages
 */
function getMsg()
{
	if (isset($_POST['save'])) {
		echo '<p class="static-msg">'.__('Setup saved').'</p>';
	}
}

?>
<html>
<head>
	<title><?php echo __('Feedburner statistics'); ?></title>
	<?php echo dcPage::jsModal(); ?>
	<?php echo dcPage::jsPageTabs($default_tab); ?>
	<?php echo dcPage::jsLoad(DC_ADMIN_URL.'?pf=feedburner/_feedburner.js'); ?>
	<style type="text/css">@import '<?php echo DC_ADMIN_URL; ?>index.php?pf=feedburner/style.css';</style>
</head>
<body>
<h2><?php echo __('Feedburner statistics'); ?></h2>
<p><?php echo __('View your feedburner statistics directly in Dotclear'); ?></p>

<?php getMsg(); ?>

<!-- Feeds configuration -->
<div id="feeds" class="multi-part" title="<?php echo __('Feeds configuration'); ?>">
	<?php feedburnerUi::feedsTable($feeds,$p_url); ?>
</div>

<!-- Feed statistics -->
<div id="stats" class="multi-part" title="<?php echo __('Feed statistics'); ?>">
	<?php feedburnerUi::statsForm($feeds,$p_url); ?>
	<?php feedburnerUi::statsView(); ?>
</div>

</body>
</html>
