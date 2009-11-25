<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom and contributors
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$p_url		= 'plugin.php?p=multiToc';
$settings		= unserialize($core->blog->settings->multitoc_settings);

if (!empty($_POST['save'])) {
	$settings = array(
		'cat' => array(
			'enable' => $_POST['enable_cat'],
			'order_group' => $_POST['order_group_cat'],
			'display_nb_entry' => $_POST['display_nb_entry_cat'],
			'order_entry' => $_POST['order_entry_cat'],
			'display_date' => $_POST['display_date_cat'],
			'format_date' => trim(html::escapeHTML($_POST['format_date_cat'])),
			'display_author' => $_POST['display_author_cat'],
			'display_cat' => $_POST['display_cat_cat'],
			'display_nb_com' => $_POST['display_nb_com_cat'],
			'display_nb_tb' => $_POST['display_nb_tb_cat'],
			'display_tag' => $_POST['display_tag_cat']
		),
		'tag' => array(
			'enable' => $_POST['enable_tag'],
			'order_group' => $_POST['order_group_tag'],
			'display_nb_entry' => $_POST['display_nb_entry_tag'],
			'order_entry' => $_POST['order_entry_tag'],
			'display_date' => $_POST['display_date_tag'],
			'format_date' => trim(html::escapeHTML($_POST['format_date_tag'])),
			'display_author' => $_POST['display_author_tag'],
			'display_cat' => $_POST['display_cat_tag'],
			'display_nb_com' => $_POST['display_nb_com_tag'],
			'display_nb_tb' => $_POST['display_nb_tb_tag'],
			'display_tag' => $_POST['display_tag_tag']
		),
		'alpha' => array(
			'enable' => $_POST['enable_alpha'],
			'order_group' => $_POST['order_group_alpha'],
			'display_nb_entry' => $_POST['display_nb_entry_alpha'],
			'order_entry' => $_POST['order_entry_alpha'],
			'display_date' => $_POST['display_date_alpha'],
			'format_date' => trim(html::escapeHTML($_POST['format_date_alpha'])),
			'display_author' => $_POST['display_author_alpha'],
			'display_cat' => $_POST['display_cat_alpha'],
			'display_nb_com' => $_POST['display_nb_com_alpha'],
			'display_nb_tb' => $_POST['display_nb_tb_alpha'],
			'display_tag' => $_POST['display_tag_alpha']
		),
		'post' => array(
			'enable' => $_POST['enable_post']
		)
	);
	$core->blog->settings->setNameSpace('multiToc');
	$core->blog->settings->put('multitoc_settings',
		serialize($settings),'string',
		'multiToc settings');
	http::redirect($p_url.'&saveconfig=1');
}

function infoMessages()
{
	$res = '';

	# Plugins install message
	if (!empty($_GET['saveconfig'])) {
		$res .= '<div class="message"><p>'.
		__('Setup saved').
		'</p></div>';
	}
	
	echo $res;	
}

function getSetting($type,$value)
{
	global $settings;
	
	return isset($settings[$type][$value]) ? $settings[$type][$value] : '';
}

?>

<html>
<head>
	<title><?php echo __('Tables of content'); ?></title>
</head>
<body>
<h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.__('Tables of content'); ?></h2>
<?php infoMessages(); ?>
<form method="post" action="<?php echo $p_url; ?>">
<?php multiTocUi::form('post'); ?>
<?php multiTocUi::form('cat'); ?>
<?php multiTocUi::form('tag'); ?>
<?php multiTocUi::form('alpha'); ?>
<?php echo $core->formNonce(); ?>
<p><input name="save" value="<?php echo __('Save setup'); ?>" type="submit" /></p>
</form>
</body>
</html>