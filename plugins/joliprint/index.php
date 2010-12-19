<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of joliprint, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('admin');

# Requests
$action = isset($_POST['action']) ? $_POST['action'] : '';
$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';

# Vars
$api_url = 'http://api.joliprint.com/';

$pages = array(
	__('home page') => 'default',
	__('post pages') => 'post',
	__('tags pages') => 'tag',
	__('archives pages') => 'archive',
	__('category pages') => 'category',
	__('entries feed') => 'feed'
);
if ($core->plugins->moduleExists('muppet'))
{
	$muppet_pages = muppet::getPostTypes();
	
	foreach($muppet_pages as $k => $v)
	{
		$n = sprintf(__('"%s" pages from extension muppet'),$v['name']);
		$pages[$n] = $k;
	}
}
$places = array(
	__('Before') => 'before',
	__('After') => 'after',
	__('Both') => 'both'
);

# Read settings
$core->blog->settings->addNamespace('joliprint');
$s = $core->blog->settings->joliprint;

$active = (boolean) $s->active;
$btn_place = (string) $s->btn_place;
if (!in_array($btn_place,$places)) $btn_place = 'after';
$btn_button = (string) $s->btn_button;
if (!in_array($btn_button,joliprint::buttons())) $btn_button = 'joliprint-button.png';
$btn_server = (string) $s->btn_server;
if (!in_array($btn_server,joliprint::servers())) $btn_server = 'eu.joliprint.com';
$btn_text = (string) $s->btn_text;
$btn_css = $s->btn_css;
if (null === $btn_css) $btn_css = "div.postjoliprint { clear:both; } \ndiv.postjoliprint a.joliprint { float:right; margin: 2px; } ";
$btn_pages = @unserialize($s->btn_pages);
if (null === $btn_pages) $btn_pages = array();
if (!is_array($btn_pages)) $btn_pages = array('post');

# Save settings
if ($action == 'savesetting')
{
	try
	{
		$active = !empty($_POST['active']);
		$btn_place = (string) $_POST['btn_place'];
		$btn_button = (string) $_POST['btn_button'];
		$btn_server = (string) $_POST['btn_server'];
		$btn_text = (string) $_POST['btn_text'];
		$btn_css = (string) $_POST['btn_css'];
		$btn_pages = $_POST['btn_pages'];
		
		$s->put('active',$active);
		$s->put('btn_place',$btn_place);
		$s->put('btn_button',$btn_button);
		$s->put('btn_server',$btn_server);
		$s->put('btn_text',$btn_text);
		$s->put('btn_css',$btn_css);
		$s->put('btn_pages',serialize($btn_pages));
		
		$core->blog->triggerBlog();
		
		http::redirect('plugin.php?p=joliprint&section='.$section.'&msg='.$action);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Messages
$msg_list = array(
	'savesetting' => __('Configuration successfully saved')
);

# Display
echo '
<html><head><title>'.__('Joliprint').'</title>'.
dcPage::jsToolBar().
dcPage::jsLoad('index.php?pf=joliprint/js/joliprint.js').
'<script type="text/javascript">'."\n//<![CDATA[\n".
dcPage::jsVar('jcToolsBox.prototype.text_wait',__('Please wait')).
dcPage::jsVar('jcToolsBox.prototype.section',$section).
"\n//]]>\n</script>\n".
'</head>
<body>
<h2>'.__('Joliprint').'</h2>';

if (isset($msg_list[$msg]))
{
	echo sprintf('<p class="message">%s</p>',$msg_list[$msg]);
}

echo
'<form method="post" action="'.$p_url.'" id="setting-form">

<fieldset id="plugin"><legend>'. __('Plugin activation').'</legend>
<p class="field"><label>'.
form::checkbox(array('active'),'1',$active).
__('Enable extension').'</label></p>
</fieldset>

<fieldset id="button"><legend>'. __('Button').'</legend>
<h4>'.__('Picture:').'</h4>';

foreach(joliprint::buttons() as $k => $v)
{
	echo '
	<p><label class="classic">'.
	form::radio(array('btn_button'),$v,($btn_button == $v)).
	'<img src="'.$api_url.joliprint::$api_res.$v.'" alt="'.$k.'" /></label></p>';
}
echo '
<h4>'.__('Text:').'</h4>
<p><label class="classic">'.
form::field(array('btn_text'),20,255,$btn_text).'
</label></p>
<p class="form-note">'.__('This text is placed after icon only and on mouseover.').'</p>
<h4>'.__('Country:').'</h4>
<p><label class="classic">'.
form::combo(array('btn_server'),joliprint::servers(),$btn_server).'
</label></p>
</fieldset>

<fieldset id="entry"><legend>'. __('Entries').'</legend>
<h4>'.__('Show on posts from:').'</h4>';

foreach($pages as $k => $v)
{
	echo '
	<p><label class="classic">'.
	form::checkbox(array('btn_pages[]'),$v,in_array($v,$btn_pages)).
	$k.'</label></p>';
}
echo '
<h4>'.__('Place:').'</h4>
<p><label class="classic">'.
form::combo(array('btn_place'),$places,$btn_place).'
</label></p>
<h4>'.__('Additionnal style sheet:').'</h4>
<p><label class="classic">'.
form::textarea(array('btn_css'),164,10,$btn_css,'maximal').'</label></p>
<p class="form-note">'.__('This button is placed in HTML tag "div" of class "postjoliprint".').'</p>
</fieldset>

<div class="clear">
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('p'),'joliprint').
form::hidden(array('action'),'savesetting').
form::hidden(array('section'),$section).'
</p></div>
</form>';
dcPage::helpBlock('joliprint');
echo '
<hr class="clear"/><p class="right">
Joliprint - '.$core->plugins->moduleInfo('joliprint','version').'&nbsp;
<img alt="'.__('Joliprint').'" src="index.php?pf=joliprint/icon.png" />
</p>
</body>
</html>';
?>