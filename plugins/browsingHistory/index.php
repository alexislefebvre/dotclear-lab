<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of browsingHistory, a plugin for Dotclear 2.
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

$core->blog->settings->addNamespace('browsingHistory');

$mem_time = (integer) $core->blog->settings->browsingHistory->mem_time;
$lastn = (integer) $core->blog->settings->browsingHistory->lastn;
$more_css = (string) $core->blog->settings->browsingHistory->more_css;
$img_size = (string) $core->blog->settings->browsingHistory->img_size;
$on_footer = (boolean) $core->blog->settings->browsingHistory->on_footer;

$img_sizes = array(
	__('original') => 'o',
	__('medium') => 'm',
	__('small') => 's',
	__('thumbnail') => 't',
	__('square') => 'sq'
);

if (isset($_POST['save']))
{
	try
	{
		$mem_time = abs((integer) $_POST['mem_time']);
		if (!$mem_time) $mem_time = 604800;
		$lastn = abs((integer) $_POST['lastn']);
		if (!$lastn) $lastn = 5;
		$more_css = $_POST['more_css'];
		$img_size = $_POST['img_size'];
		$on_footer = !empty($_POST['on_footer']);
		
		$core->blog->settings->browsingHistory->put('mem_time',$mem_time,'string');
		$core->blog->settings->browsingHistory->put('lastn',$lastn,'integer');
		$core->blog->settings->browsingHistory->put('more_css',$more_css,'string');
		$core->blog->settings->browsingHistory->put('img_size',$img_size,'string');
		$core->blog->settings->browsingHistory->put('on_footer',$on_footer,'boolean');
		
		$core->blog->triggerBlog();
		
		http::redirect('plugin.php?p=browsingHistory&done=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

echo '
<html><head><title>'.__('Browsing history').'</title></head>
<body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Browsing history').'</h2>'.
(!empty($_REQUEST['done']) ? '<p class="message">'.__('Configuration successfully updated').'</p>' : '').'
<fieldset><legend>'.__('Settings').'</legend>
<form method="post" action="plugin.php">

<p><label class="classic">'.__('Duration of backup of browsing history: (in seconds)').'<br />'.
form::field('mem_time',7,64,$mem_time).'</label></p>

<p><label class="classic">'.
form::checkbox('on_footer','1',$on_footer).__('Add to theme footer').'</label></p>

<p><label class="classic">'.__('Size of first image:').'<br />'.
form::combo('img_size',$img_sizes,$img_size).'</label></p>

<p><label class="classic">'.__('Number of historical to be displayed:').'<br />'.
form::field('lastn',7,64,$lastn).'</label></p>

<p><label class="classic">'.__('Additionnal style sheet:').' '.
form::textarea(array('more_css'),164,10,$more_css,'maximal').'</label></p>

<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().form::hidden(array('p'),'browsingHistory').'</p>
</form>
</fieldset>
<br class="clear"/>
<p class="right">
browsingHistory - '.$core->plugins->moduleInfo('browsingHistory','version').'&nbsp;
<img alt="'.__('Browsing history').'" src="index.php?pf=browsingHistory/icon.png" />
</p>';
dcPage::helpBlock('browsingHistory');
echo '</body></html>';
?>