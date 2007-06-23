<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2003-2007 dcTeam and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$active      = $core->blog->settings->authormode_active;
$url_author  = $core->blog->settings->authormode_url_author;
$url_authors = $core->blog->settings->authormode_url_authors;
$posts_only  = $core->blog->settings->authormode_default_posts_only;
$alpha_order = $core->blog->settings->authormode_default_alpha_order;

if (!empty($_POST['saveconfig'])) {
	try
	{
		$core->blog->settings->setNameSpace('authormode');

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

		$core->blog->settings->put('authormode_active',$active,'boolean');
		$core->blog->settings->put('authormode_url_author',$url_author,'string');
		$core->blog->settings->put('authormode_url_authors',$url_authors,'string');
		$core->blog->settings->put('authormode_default_posts_only',$posts_only,'boolean');
		$core->blog->settings->put('authormode_default_alpha_order',$alpha_order,'boolean');
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
	<title><?php echo __('authorMode'); ?></title>
</head>

<body>
<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt; <?php echo __('authorMode'); ?></h2>
<?php if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; ?>
<div id="authormode_options">
	<form method="post" action="plugin.php">
	<fieldset>
		<legend><?php echo __('Plugin activation'); ?></legend>
		<p class="field">
		<label class=" classic"><?php echo form::checkbox('active', 1, $active); ?>&nbsp;
		<?php echo __('Enable authorMode');?>
		</label>
		</p>
	</fieldset>
	<fieldset>
		<legend><?php echo __('Advanced options'); ?></legend>
		<h3><?php echo __('URLs prefixes'); ?></h3>
		<p><label class=" classic"><?php echo __('URL author : '); ?>
		<?php echo form::field('url_author', 60, 255, $url_author); ?>
		</label></p>
		<p><label class=" classic"><?php echo __('URL authors : '); ?>
		<?php echo form::field('url_authors', 60, 255, $url_authors); ?>
		</label></p>
		<h3><?php echo __('List options'); ?></h3>
		<p><label class=" classic"><?php echo form::checkbox('posts_only', 1, $posts_only); ?>&nbsp;
		<?php echo __('List only authors of standard posts'); ?>
		</label></p>
		<p><label class=" classic"><?php echo form::checkbox('alpha_order', 1, $alpha_order); ?>&nbsp;
		<?php echo __('Sort list (alphabetical order)'); ?>
		</label></p>
	</fieldset>
	<p>
		<input type="hidden" name="p" value="authorMode" />
		<input type="submit" name="saveconfig" value="<?php echo __('Save configuration'); ?>" />
	</p>
	</form>
</div>
</body>
</html>
