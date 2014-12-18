<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of authorMode, a plugin for DotClear2.
#
# Copyright (c) 2003 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$page_title = __('authorMode');

# Url de base
$p_url = 'plugin.php?p=authorMode';

$active      = $core->blog->settings->authormode->authormode_active;
$url_author  = $core->blog->settings->authormode->authormode_url_author;
$url_authors = $core->blog->settings->authormode->authormode_url_authors;
$posts_only  = $core->blog->settings->authormode->authormode_default_posts_only;
$alpha_order = $core->blog->settings->authormode->authormode_default_alpha_order;

if (!empty($_POST['saveconfig'])) {
	try
	{
		$core->blog->settings->addNameSpace('authormode');
		
		$active = (empty($_POST['active']))?false:true;
		if (trim($_POST['url_author']) == '') {
			$url_author = 'author';
		} else {
			$url_author = text::str2URL(trim($_POST['url_author']));
		}
		if (trim($_POST['url_authors']) == '') {
			$url_authors = 'authors';
		} else {
			$url_authors = text::str2URL(trim($_POST['url_authors']));
		}
		$posts_only  = (empty($_POST['posts_only']))?false:true;
		$alpha_order = (empty($_POST['alpha_order']))?false:true;
		
		$core->blog->settings->authormode->put('authormode_active',$active,'boolean');
		$core->blog->settings->authormode->put('authormode_url_author',$url_author,'string');
		$core->blog->settings->authormode->put('authormode_url_authors',$url_authors,'string');
		$core->blog->settings->authormode->put('authormode_default_posts_only',$posts_only,'boolean');
		$core->blog->settings->authormode->put('authormode_default_alpha_order',$alpha_order,'boolean');
		$core->blog->triggerBlog();
		
		$msg = __('Configuration successfully updated.');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
  <title><?php echo $page_title; ?></title>
</head>

<body>
<?php

	echo dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));
		
if (!empty($msg)) {
  dcPage::success($msg);
}
?>

<div id="authormode_options">
	<form method="post" action="plugin.php">
	<div class="fieldset">
		<h4><?php echo __('Plugin activation'); ?></h4>
		<p class="field">
		<label class="classic"><?php echo form::checkbox('active', 1, $active); ?>&nbsp;
		<?php echo __('Enable authorMode');?>
		</label>
		</p>
	</div>
	<div class="fieldset">
		<h4><?php echo __('Advanced options'); ?></h4>
		<h5><?php echo __('URLs prefixes'); ?></h5>
		<p><label class="classic"><?php echo __('URL author : '); ?>
		<?php echo form::field('url_author', 60, 255, $url_author); ?>
		</label></p>
		<p><label class="classic"><?php echo __('URL authors : '); ?>
		<?php echo form::field('url_authors', 60, 255, $url_authors); ?>
		</label></p>
		<h5><?php echo __('List options'); ?></h5>
		<p><label class="classic"><?php echo form::checkbox('posts_only', 1, $posts_only); ?>&nbsp;
		<?php echo __('List only authors of standard posts'); ?>
		</label></p>
		<p><label class="classic"><?php echo form::checkbox('alpha_order', 1, $alpha_order); ?>&nbsp;
		<?php echo __('Sort list (alphabetical order)'); ?>
		</label></p>
	</div>
	<p>
		<input type="hidden" name="p" value="authorMode" />
		<?php echo $core->formNonce(); ?>
		<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
	</p>
	</form>
</div>
<?php dcPage::helpBlock('authorMode'); ?>
</body>
</html>