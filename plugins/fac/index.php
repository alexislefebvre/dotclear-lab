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

dcPage::check('admin');

# Settings
$core->blog->settings->addNamespace('fac');
$s_active = (boolean) $core->blog->settings->fac->fac_active;
$s_defaultfeedtitle = (string) $core->blog->settings->fac->fac_defaultfeedtitle;
$s_showfeeddesc = (integer) $core->blog->settings->fac->fac_showfeeddesc;
$s_public_tpltypes = @unserialize($core->blog->settings->fac->fac_public_tpltypes);
if (!is_array($s_public_tpltypes)) $s_public_tpltypes = array();
$s_formats = @unserialize($core->blog->settings->fac->fac_formats);
if (!is_array($s_formats)) $s_formats = array();

$action = isset($_POST['action']) ? $_POST['action'] : '';
$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
$types = array(
	__('home page') => 'default',
	__('post pages') => 'post',
	__('tags pages') => 'tag',
	__('archives pages') => 'archive',
	__('category pages') => 'category',
	__('entries feed') => 'feed'
);

# Settings
if ($action == 'savesetting')
{
	$new_s_formats = array();
	foreach($_POST['s_formats'] as $uid => $f)
	{
		if (empty($f['name'])) continue;
		$new_s_formats[$uid] = $f;
	}

	$core->blog->settings->fac->put('fac_active',!empty($_POST['s_active']));
	$core->blog->settings->fac->put('fac_public_tpltypes',serialize($_POST['s_public_tpltypes']));
	$core->blog->settings->fac->put('fac_formats',serialize($new_s_formats));
	$core->blog->settings->fac->put('fac_defaultfeedtitle',(string) $_POST['s_defaultfeedtitle']);
	$core->blog->settings->fac->put('fac_showfeeddesc',!empty($_POST['s_showfeeddesc']));
	
	$core->blog->triggerBlog();
	
	http::redirect('plugin.php?p=fac&section='.$section.'&msg='.$action);
}

# Messages
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$msg_list = array(
	'savesetting' => __('Configuration successfully saved')
);

echo '
<html><head><title>'.__('fac').' - '.__('Feed after content').'</title>'.
dcPage::jsToolBar().
dcPage::jsLoad('index.php?pf=fac/js/fac.js').
'<script type="text/javascript">'."\n//<![CDATA[\n".
dcPage::jsVar('jcToolsBox.prototype.text_wait',__('Please wait')).
dcPage::jsVar('jcToolsBox.prototype.section',$section).
"\n//]]>\n</script>\n".
'</head>
<body>
<h2>'.__('fac').' - '.__('Feed after content').'</h2>';

if (isset($msg_list[$msg]))
{
	echo sprintf('<p class="message">%s</p>',$msg_list[$msg]);
}

echo
'<form method="post" action="'.$p_url.'" id="setting-form">

<fieldset id="plugin"><legend>'. __('Plugin activation').'</legend>
<p class="field"><label>'.
form::checkbox(array('s_active'),'1',$s_active).
__('Enable extension').'</label></p>
</fieldset>

<fieldset id="feed"><legend>'. __('Feed').'</legend>
<div class="two-cols"><div class="col">
<p class="field"><label>'.__('Default title').' *<br />'.
form::field(array('s_defaultfeedtitle'),65,255,$s_defaultfeedtitle).'</label></p>
<p class="field"><label>'.
form::checkbox(array('s_showfeeddesc'),'1',$s_showfeeddesc).
__('Show description of feed').'</label></p>
</div><div class="col">
<h3>'.__('Format').'</h3>
<p>* '.__('Use %T to insert title of feed.').'</p>
</div></div>
</fieldset>

<fieldset id="entries"><legend>'. __('Entries').'</legend>
<h3>'.__('Preconfiguration').'</h3>
<table>
<thead><tr>
<th>'.__('Name').'</th>
<th>'.__('Date format').' *</th>
<th>'.__('Entries limit').'</th>
<th>'.__('Title format').' **</th>
<th>'.__('Over title format').' **</th>
<th>'.__('Maximum length of title').' ***</th>
<th>'.__('Show description of entries').'</th>
<th>'.__('Maximum length of description').' ***</th>
<th>'.__('Remove html of description').'</th>
<th>'.__('Show content of entries').'</th>
<th>'.__('Maximum length of content').' ***</th>
<th>'.__('Remove html of content').'</th>
</tr></thead>
<tbody>';

foreach($s_formats as $uid => $f)
{
	if (empty($f['name'])) continue;
	$name = empty($f['name']) ? '' : $f['name'];
	$dateformat = empty($f['dateformat']) ? '' : $f['dateformat'];
	$lineslimit = empty($f['lineslimit']) ? '' : $f['lineslimit'];
	$linestitletext = empty($f['linestitletext']) ? '' : $f['linestitletext'];
	$linestitleover = empty($f['linestitleover']) ? '' : $f['linestitleover'];
	$linestitlelength = empty($f['linestitlelength']) ? '' : $f['linestitlelength'];
	$showlinesdescription = empty($f['showlinesdescription']) ? 0 : 1;
	$linesdescriptionlength = empty($f['linesdescriptionlength']) ? '' : $f['linesdescriptionlength'];
	$linesdescriptionnohtml = empty($f['linesdescriptionnohtml']) ? 0 : 1;
	$showlinescontent = empty($f['showlinescontent']) ? 0 : 1;
	$linescontentlength = empty($f['linescontentlength']) ? '' : $f['linescontentlength'];
	$linescontentnohtml = empty($f['linescontentnohtml']) ? 0 : 1;
	
	echo '
	<tr>
	<td>'.form::field(array('s_formats['.$uid.'][name]'),20,255,$name).'</td>
	<td>'.form::field(array('s_formats['.$uid.'][dateformat]'),20,255,$dateformat).'</td>
	<td>'.form::field(array('s_formats['.$uid.'][lineslimit]'),5,4,$lineslimit).'</td>
	<td>'.form::field(array('s_formats['.$uid.'][linestitletext]'),20,255,$linestitletext).'</td>
	<td>'.form::field(array('s_formats['.$uid.'][linestitleover]'),20,255,$linestitleover).'</td>
	<td>'.form::field(array('s_formats['.$uid.'][linestitlelength]'),5,4,$linestitlelength).'</td>
	<td>'.form::checkbox(array('s_formats['.$uid.'][showlinesdescription]'),'1',$showlinesdescription).'</td>
	<td>'.form::field(array('s_formats['.$uid.'][linesdescriptionlength]'),5,4,$linesdescriptionlength).'</td>
	<td>'.form::checkbox(array('s_formats['.$uid.'][linesdescriptionnohtml]'),'1',$linesdescriptionnohtml).'</td>
	<td>'.form::checkbox(array('s_formats['.$uid.'][showlinescontent]'),'1',$showlinescontent).'</td>
	<td>'.form::field(array('s_formats['.$uid.'][linescontentlength]'),5,4,$linescontentlength).'</td>
	<td>'.form::checkbox(array('s_formats['.$uid.'][linescontentnohtml]'),'1',$linescontentnohtml).'</td>
	</tr>';
}
$uid = uniqid();
echo '
<tr>
<td>'.form::field(array('s_formats['.$uid.'][name]'),20,255,'').'</td>
<td>'.form::field(array('s_formats['.$uid.'][dateformat]'),20,255,'').'</td>
<td>'.form::field(array('s_formats['.$uid.'][lineslimit]'),5,4,'5').'</td>
<td>'.form::field(array('s_formats['.$uid.'][linestitletext]'),20,255,'%T').'</td>
<td>'.form::field(array('s_formats['.$uid.'][linestitleover]'),20,255,'%D').'</td>
<td>'.form::field(array('s_formats['.$uid.'][linestitlelength]'),5,4,'150').'</td>
<td>'.form::checkbox(array('s_formats['.$uid.'][showlinesdescription]'),'1',0).'</td>
<td>'.form::field(array('s_formats['.$uid.'][linesdescriptionlength]'),5,4,'350').'</td>
<td>'.form::checkbox(array('s_formats['.$uid.'][linesdescriptionnohtml]'),'1',1).'</td>
<td>'.form::checkbox(array('s_formats['.$uid.'][showlinescontent]'),'1',0).'</td>
<td>'.form::field(array('s_formats['.$uid.'][linescontentlength]'),5,4,'350').'</td>
<td>'.form::checkbox(array('s_formats['.$uid.'][linescontentnohtml]'),'1',1).'</td>
</tr>
</tbody></table>

<h3>'.__('Format').'</h3>
<p>'.__('In dorder to delete a configuration, leave its name empty').'</p>
<p>* '.__('Use Dotclear date format or leave empty to use default date format of blog.').'</p>
<p>** '.__('Format of "Title", "Over title" can be:').'</p>
<ul>
<li>%D : '.__('Date').'</li>
<li>%T : '.__('Title').'</li>
<li>%A : '.__('Author').'</li>
<li>%E : '.__('Description').'</li>
<li>%C : '.__('Content').'</li>
</ul>
<p>*** '.__('Leave empty for no limit.').'</p>

</fieldset>

<fieldset id="display"><legend>'. __('Display').'</legend>
<p>'.__('Show on:').'</p>';

foreach($types as $k => $v)
{
	echo '
	<p class="field"><label>'.
	form::checkbox(array('s_public_tpltypes[]'),$v,in_array($v,$s_public_tpltypes)).
	sprintf(__($k)).'</label></p>';
}
echo '
</fieldset>

<fieldset id="info"><legend>'. __('Information').'</legend>
<div class="two-cols"><div class="col">
<h3>'.__('Theme').'</h3>
<ul>
<li>'.__('Theme must have behavoir publicEntryAfterContent').'</li>
<li>'.__('Feeds are inserted after post content').'</li>
</ul>
<h3>'.__('Add a feed to an entry').'</h3>
<ul>
<li>'.__('To add feed to an entry edit this entry and put in sidebar the url of the feed.').'</li>
</lu>
</div><div class="col">
<h3>'.__('Structure').'</h3>
<pre>'.html::escapeHTML('
<div class="post-fac">
<h3>'.__('Title of feed').'</h3>
<p>'.__('Description of feed').'</p>
<dl>
<dt>'.__('Title of entry').'</dt>
<dd>'.__('Description of entry').'</dd>
</dl>
</div>
').'</pre>
</div></div>
</fieldset>

<div class="clear">
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('p'),'fac').
form::hidden(array('action'),'savesetting').
form::hidden(array('section'),$section).'
</p></div>
</form>';
dcPage::helpBlock('fac');
echo '
<hr class="clear"/><p class="right">
fac - '.$core->plugins->moduleInfo('fac','version').'&nbsp;
<img alt="'.__('Feed after content').'" src="index.php?pf=fac/icon.png" />
</p>
</body>
</html>';
?>