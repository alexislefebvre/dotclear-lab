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
$s =& facSettings($core);
$s_active = (boolean) $s->fac_active;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';

$s_defaultfeedtitle = (string) $s->fac_defaultfeedtitle;
$s_showfeeddesc = (integer) $s->fac_showfeeddesc;
$s_dateformat = (string) $s->fac_dateformat;
$s_lineslimit = (integer) $s->fac_lineslimit;
$s_linestitletext = (string) $s->fac_linestitletext;
$s_linestitleover = (string) $s->fac_linestitleover;
$s_linestitlelength = (integer) $s->fac_linestitlelength;
$s_showlinesdescription = (integer) $s->fac_showlinesdescription;
$s_linesdescriptionlength = (integer) $s->fac_linesdescriptionlength;
$s_linesdescriptionnohtml = (boolean) $s->fac_linesdescriptionnohtml;
$s_showlinescontent = (integer) $s->fac_showlinescontent;
$s_linescontentlength = (integer) $s->fac_linescontentlength;
$s_linescontentnohtml = (boolean) $s->fac_linescontentnohtml;

$s_public_tpltypes = @unserialize($s->fac_public_tpltypes);
if (!is_array($s_public_tpltypes)) $s_public_tpltypes = array();

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
	$s->put('fac_active',!empty($_POST['s_active']));
	$s->put('fac_public_tpltypes',serialize($_POST['s_public_tpltypes']));
	$s->put('fac_defaultfeedtitle',(string) $_POST['s_defaultfeedtitle']);
	$s->put('fac_showfeeddesc',!empty($_POST['s_showfeeddesc']));
	$s->put('fac_dateformat',(string) $_POST['s_dateformat']);
	$s->put('fac_lineslimit',(integer) $_POST['s_lineslimit']);
	$s->put('fac_linestitletext',(string) $_POST['s_linestitletext']);
	$s->put('fac_linestitleover',(string) $_POST['s_linestitleover']);
	$s->put('fac_linestitlelength',(integer) $_POST['s_linestitlelength']);
	$s->put('fac_showlinesdescription',!empty($_POST['s_showlinesdescription']));
	$s->put('fac_linesdescriptionlength',(integer) $_POST['s_linesdescriptionlength']);
	$s->put('fac_linesdescriptionnohtml',!empty($_POST['s_linesdescriptionnohtml']));
	$s->put('fac_showlinescontent',!empty($_POST['s_showlinescontent']));
	$s->put('fac_linescontentlength',(integer) $_POST['s_linescontentlength']);
	$s->put('fac_linescontentnohtml',!empty($_POST['s_linescontentnohtml']));
	if (version_compare(DC_VERSION,'2.1.6','<=')) { 
		$s->setNamespace('system'); 
	}
	$core->blog->triggerBlog();

	http::redirect('plugin.php?p=fac&section='.$section.'&msg='.$action);
}

# Messages
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$msg_list = array(
	'savesetting' => __('Configuration successfully saved')
);
if (isset($msg_list[$msg])) {
	$msg = sprintf('<p class="message">%s</p>',$msg_list[$msg]);
} else { $msg = ''; }

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
<h2>'.__('fac').' - '.__('Feed after content').'</h2>
'.$msg.'
<form method="post" action="'.$p_url.'" id="setting-form">


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
<div class="two-cols"><div class="col">
<p class="field"><label>'.__('Date format').' *<br />'.
form::field(array('s_dateformat'),20,255,$s_dateformat).'</label></p>
<p class="field"><label>'.__('Entries limit').'<br />'.
form::field(array('s_lineslimit'),5,4,$s_lineslimit).'</label></p>
<p class="field"><label>'.__('Title format').' **<br />'.
form::field(array('s_linestitletext'),20,255,$s_linestitletext).'</label></p>
<p class="field"><label>'.__('Over title format').' **<br />'.
form::field(array('s_linestitleover'),20,255,$s_linestitleover).'</label></p>
<p class="field"><label>'.__('Maximum length of title').' ***<br />'.
form::field(array('s_linestitlelength'),5,4,$s_linestitlelength).'</label></p>
<p class="field"><label>'.
form::checkbox(array('s_showlinesdescription'),'1',$s_showlinesdescription).
__('Show description of entries').'</label></p>
<p class="field"><label>'.__('Maximum length of description').' ***<br />'.
form::field(array('s_linesdescriptionlength'),5,4,$s_linesdescriptionlength).'</label></p>
<p class="field"><label>'.
form::checkbox(array('s_linesdescriptionnohtml'),'1',$s_linesdescriptionnohtml).
__('Remove html of description').'</label></p>
<p class="field"><label>'.
form::checkbox(array('s_showlinescontent'),'1',$s_showlinescontent).
__('Show content of entries').'</label></p>
<p class="field"><label>'.__('Maximum length of content').' ***<br />'.
form::field(array('s_linescontentlength'),5,4,$s_linescontentlength).'</label></p>
<p class="field"><label>'.
form::checkbox(array('s_linescontentnohtml'),'1',$s_linescontentnohtml).
__('Remove html of content').'</label></p>
</div><div class="col">
<h3>'.__('Format').'</h3>
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
</div></div>
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