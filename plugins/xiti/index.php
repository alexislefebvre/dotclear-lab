<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of xiti, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

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
<html><head><title>'.__('XITI').'</title></head>
<body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('XITI').'</h2>'.
(!empty($_REQUEST['done']) ? '<p class="message">'.__('Configuration successfully updated').'</p>' : '').'
<fieldset><legend>'.__('Settings').'</legend>
<form method="post" action="plugin.php">
<p><label class="classic">'.form::checkbox('xiti_active','1',$xiti_active).__('Enable XITI').'</label></p>
<p><label>'.__('Your XITI account number:').form::field('xiti_serial',30,255,html::escapeHTML($xiti_serial)).'</label></p>
<p><label>'.__('Image style:').form::combo('xiti_image',$combo_image,$xiti_image).'</label></p>
<p><label class="classic">'.form::checkbox('xiti_footer','1',$xiti_footer).__('Add to theme footer').'</label></p>
<p><input type="submit" name="xiti_save" value="'.__('save').'" />'.$core->formNonce().form::hidden(array('p'),'xiti').'</p>
</form>
</fieldset>
<fieldset><legend>'.__('Help').'</legend>
<ul>
<li>'.__('In order to add XITI to your theme footer, theme must have sysBehavoir "publicAfterContent", commonly in template file "_footer.html"').'</li>
<li>'.__('You can use instead the XITI widget but this plugin must be enabled and well configured here.').'</li>
<li>'.__('In footer template, XITI is encapsuled in a "div" tag of class "xiti-footer".').'</li>
<li>'.__('In widget, XITI is encapsuled in a "div" tag of class "xiti-widget".').'</li>
</ul>
</fieldset>
<br class="clear"/>
<p class="right">
xiti - '.$core->plugins->moduleInfo('xiti','version').'&nbsp;
<img alt="'.__('XITI').'" src="index.php?pf=xiti/icon.png" />
</p></body></html>';
?>