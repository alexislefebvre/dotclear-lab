<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of xiti, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2015 JC Denis and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$page_title = __('XITI');

dcPage::check('amdin');

$combo_image = array(
	__('original') => 0,
	__('all black') => 1,
	__('blue and black border') => 2,
	__('red on black') => 3,
	__('all yellow') => 4,
	__('Red text on yellow') => 5,
	__('Red and yellow') => 6,
	__('Red and black border') => 7,
	__('Green and black border') => 8
);

$core->blog->settings->addNamespace('xiti');
$xiti_active = (boolean) $core->blog->settings->xiti->xiti_active;
$xiti_serial = (string) $core->blog->settings->xiti->xiti_serial;
$xiti_footer = (boolean) $core->blog->settings->xiti->xiti_footer;
$xiti_image = (integer) $core->blog->settings->xiti->xiti_image;

if (isset($_POST['xiti_save']))
{
	try
	{
		$core->blog->settings->xiti->put('xiti_active',!empty($_POST['xiti_active']));
		$core->blog->settings->xiti->put('xiti_serial',!empty($_POST['xiti_serial']) ? $_POST['xiti_serial'] : '');
		$core->blog->settings->xiti->put('xiti_footer',!empty($_POST['xiti_footer']));
		$core->blog->settings->xiti->put('xiti_image',isset($_POST['xiti_image']) ? (integer) $_POST['xiti_image'] : 0);
		
		$core->blog->triggerBlog();
		
		http::redirect('plugin.php?p=xiti&done=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

echo '
<html><head><title>'.$page_title.'</title></head>
<body>';
echo dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));
if (!empty($_REQUEST['done'])) {
  dcPage::success(__('Configuration successfully updated'));
};

echo '<form method="post" action="plugin.php">
<div class="fieldset"><h4>'.__('Settings').'</h4>
<p><label class="classic">'.form::checkbox('xiti_active','1',$xiti_active).__('Enable XITI').'</label></p>
<p><label>'.__('Your XITI account number:').' '.form::field('xiti_serial',30,255,html::escapeHTML($xiti_serial)).'</label></p>
<p><label>'.__('Image style:').' '.form::combo('xiti_image',$combo_image,$xiti_image).'</label></p>
<p><label class="classic">'.form::checkbox('xiti_footer','1',$xiti_footer).__('Add to theme footer').'</label></p>
</div>
<p><input type="submit" name="xiti_save" value="'.__('Save').'" />'.$core->formNonce().form::hidden(array('p'),'xiti').'</p>
</form>
<br class="clear"/>
<p class="right">
xiti - '.$core->plugins->moduleInfo('xiti','version').'&nbsp;
<img alt="'.__('XITI').'" src="index.php?pf=xiti/icon.png" />
</p>';
echo dcPage::helpBlock('xiti');
echo '</body></html>';