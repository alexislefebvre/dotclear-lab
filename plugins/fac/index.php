<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of fac, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}
if (!$core->plugins->moduleExists('metadata')){return;}

dcPage::check('admin');

# Settings
$s =& $core->blog->settings;
$_active = (boolean) $s->fac_active;
$_public_limit = (integer) $s->fac_public_limit;
$_public_title = (string) $s->fac_public_title;
$_public_tpltypes = @unserialize($s->fac_public_tpltypes);
if (!is_array($_public_tpltypes)) $_public_tpltypes = array();

$types = array(
	__('home page') => 'default',
	__('post pages') => 'post',
	__('tags pages') => 'tag',
	__('archives pages') => 'archive',
	__('category pages') => 'category',
	__('entries feed') => 'feed'
);

# Settings
if (!empty($_POST['save']))
{
	$s->setNameSpace('fac');
	$s->put('fac_active',!empty($_POST['_active']));
	$s->put('fac_public_title',$_POST['_public_title']);
	$s->put('fac_public_limit',(integer) $_POST['_public_limit']);
	$s->put('fac_public_tpltypes',serialize($_POST['_public_tpltypes']));
	$s->setNameSpace('system');
	$core->blog->triggerBlog();

	http::redirect('plugin.php?p=fac&msg=settingdone');
}

# Vars
$img_green = '<img src="images/check-on.png" alt="ok" />';
$img_red = '<img src="images/check-off.png" alt="fail" />';
$msg = empty($_REQUEST['msg']) ? '' : $msg;

if ($msg == 'settingdone') {
	$msg = __('Configuration successfully saved');
}

echo '
<html><head><title>'.__('fac').' - '.__('Feed after content').'</title>'.dcPage::jsToolBar().'</head>
<body>
<h2>'.__('fac').' - '.__('Feed after content').'</h2>
'.$msg.'
<form method="post" action="plugin.php">
<div class="two-cols">
<div class="col">
<p><label class="classic">'.form::checkbox(array('_active'),'1',$_active).' '.__('Enable extension').'</label></p>
<p><label class="classic">'.__('Title:').'<br />'.
form::field(array('_public_title'),65,255,$_public_title).'</label></p>
<p><label class="classic">'.__('Number of feeds to show:').'<br />'.
form::field(array('_public_limit'),5,4,$_public_limit).'</label></p>
</div><div class="col">';

foreach($types as $k => $v)
{
	echo '
	<p><label class="classic">'.
	form::checkbox(array('_public_tpltypes[]'),$v,in_array($v,$_public_tpltypes)).' '.
	sprintf(__('Show feed on %s'),__($k)).'</label></p>';
}
echo '
</div></div>
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().form::hidden(array('p'),'fac').'
</p>
</form>
<fieldset><legend>'.__('help').'</legend>
<ul>
<li>'.__('Theme must have behavoir publicEntryAfterContent').'</li>
<li>'.__('Feeds are inserted after post content').'</li>
<li>'.__('Feeds are encapsuled in a html tag "div" of class "post-fac".').'</li>
<li>'.__('Title of the feed is in a html tag "h2" without class.').'</li>
<li>'.__('You can insert in feed title the name of the feed by using wildcard "%s".').'</li>
<li>'.__('Each line of a feed is in a html tag "li" without class.').'</li>
<li>'.__('To add feed to an entry edit this entry and put in sidebar the url of the feed.').'</li>
</lu>
</fieldset>
<hr class="clear"/>
<p class="right">fac - '.$core->plugins->moduleInfo('fac','version').'&nbsp;<img alt="fac" src="index.php?pf=fac/icon.png" /></p>
</body>
</html>';
?>