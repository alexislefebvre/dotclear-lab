<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of webOptimizer,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Peck and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

require_once(dirname(__FILE__).'/class.dc.weboptimizer.php');
$default_tab = 'index';

if (!empty($_POST['optimize'])) {
	$type = $_POST['type'];
	foreach($_POST[$type] as $file) {
		$fobj = new dcWebOptimizer($file);
		try {
			$fobj->optimizeFile();
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		} 
	}
}

$estimate = array();
if (!empty($_POST['estimate'])) {
	$type = $_POST['type'];
	foreach($_POST[$type] as $file) {
		array_push($estimate, $file);
	}
}

if (!empty($_POST['restore'])) {
	$type = $_POST['type'];
	foreach($_POST[$type] as $file) {
		$fobj = new dcWebOptimizer($file);
		try {
			$fobj->restoreBackup();
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
}

/*
	$settings =& $core->blog->settings;
	$keep_comments = $settings->compress_keep_comments;
	$create_backup_every_time = $settings->compress_create_backup_every_time;
	$text_beginning = $settings->compress_text_beginning;

	if (!empty($_POST['saveconfig']))
	{
		try
		{
			$settings->setNameSpace('compress');
			# keep comments
			$keep_comments = (!empty($_POST['compress_keep_comments']));
			$settings->put('compress_keep_comments',$keep_comments,'boolean',
				'Keep comments when compressing');
			# create backup every time
			$create_backup_every_time = (!empty($_POST['compress_create_backup_every_time']));
			$settings->put('compress_create_backup_every_time',
				$create_backup_every_time,'boolean',
				'Create an unique backup of CSS file every time a CSS backup file is compressed');
			# text beginning
			$text_beginning = $_POST['compress_text_beginning'];
			$settings->put('compress_text_beginning',$text_beginning,'text',
				'Text to include at the beginning of the compressed file');

			http::redirect($p_url.'&saveconfig=1&tab=settings');
		}
		catch (Exception $e)
		{
m
			$core->error->add($e->getMessage());
		}
	}

*/
?>
<html>
<head>
	<title><?php echo __('webOptimizer'); ?></title>
 	<?php echo dcPage::jsPageTabs($default_tab); ?>
<!--link rel="stylesheet" type="text/css" href="index.php?pf=optimizer/style.css" title="Optimizer" /-->
	<script type="text/javascript" src="/index.php?pf=webOptimizer/checkAll.js"></script>
</head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name)." &gt; ".__('webOptimizer'); ?></h2>

<?php if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';} ?>

<div class="multi-part" id="index" title="<?php echo __('webOptimizer'); ?>">
	<h2><?php echo(__('Documentation')); ?></h2>
	<h3><?php echo(__('CSS and JavaScript')); ?></h3>
	<p><?php echo(__('Compression use minify to reduce size, removing unused elements such as whitespaces and comments.')); ?></p>
	<p><?php echo(__('The original file is kept in a backup file (.bak.css or .bak.js).')); ?></p>
	<p><?php echo(__('Compression is always done on the original file so you can edit it and hit rcompress again.')); ?></p>
	<p><?php echo(__('Estimate gives you an estimated ratio but do not actualy comrpess the file.')); ?></p>
	<p><?php echo(__('Restore replaces the original file to its original place.')); ?></p>

	<h3><?php echo(__('Translations')); ?></h3>
	<p><?php echo(__('PO files are compiled into PHP to accelerate page creation. It gives a 20% speedup for me. Be careful, it can be more if you have many untranslaated PO files, it can be less if you don\'t have plugins (files provided with dotclear are already compiled).')); ?></p>
	<p><?php echo(__('The original po file is left untouched, compression always create a new .lang.php from it.')); ?></p>
	 <p><?php echo(__('Restore removes the .lang.php file an leave the po file untouched.')); ?></p>

	<h3><?php echo(__('Apache')); ?></h3>
	<p><?php echo(__('You can optimize client cache using apache statements so that they don\'t download data too often.')); ?></p>
	<p><?php echo(__('You can optimize transfered data using mod_deflate.')); ?></p>
</div>

<?php 
	$dir = dirname(dirname(path::real(dirname(__FILE__))));
	dcWebOptimizer::fileTab($core, $dir, "css", __("Compress CSS"), $estimate);
	dcWebOptimizer::fileTab($core, $dir, "js", __("Compress JavaScripts"), $estimate);
	dcWebOptimizer::fileTab($core, $dir, "po", __("Compile translations"), $estimate);
?>

<div class="multi-part" id="apache" title="<?php echo __('apache'); ?>">
	<p>Things that will go to .htaccess to optimize client cache</p>
</div>

</body>
</html>
