<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# This file manage settings of kUtRL (called from index.php)

if (!defined('DC_CONTEXT_ADMIN')){return;}

$s_active = (boolean) $s->kutrl_active;
$s_admin_service = (string) $s->kutrl_admin_service;
$s_tpl_service = (string) $s->kutrl_tpl_service;
$s_wiki_service = (string) $s->kutrl_wiki_service;
$s_limit_to_blog = (boolean) $s->kutrl_limit_to_blog;
$s_tpl_passive = (boolean) $s->kutrl_tpl_passive;
$s_admin_entry_default = (string) $s->kutrl_admin_entry_default;

$s_twit_onadmin = (boolean) $s->kutrl_twit_onadmin;
$s_twit_onpublic = (boolean) $s->kutrl_twit_onpublic;
$s_twit_ontpl = (boolean) $s->kutrl_twit_ontpl;
$s_twit_onwiki = (boolean) $s->kutrl_twit_onwiki;

$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
$img_green = '<img src="images/check-on.png" alt="ok" />';
$img_red = '<img src="images/check-off.png" alt="fail" />';

if ($default_part == 'setting' && $action == 'savesetting')
{
	try {
	
		$s_active = isset($_POST['s_active']);
		$s_admin_service = $_POST['s_admin_service'];
		$s_tpl_service = $_POST['s_tpl_service'];
		$s_wiki_service = $_POST['s_wiki_service'];
		$s_limit_to_blog = isset($_POST['s_limit_to_blog']);
		$s_tpl_passive = isset($_POST['s_tpl_passive']);
		$s_admin_entry_default = isset($_POST['s_admin_entry_default']);

		$s_twit_onadmin = isset($_POST['s_twit_onadmin']);
		$s_twit_onpublic = isset($_POST['s_twit_onpublic']);
		$s_twit_ontpl = isset($_POST['s_twit_ontpl']);
		$s_twit_onwiki = isset($_POST['s_twit_onwiki']);

		$s->put('kutrl_active',$s_active);
		$s->put('kutrl_admin_service',$s_admin_service);
		$s->put('kutrl_tpl_service',$s_tpl_service);
		$s->put('kutrl_wiki_service',$s_wiki_service);
		$s->put('kutrl_limit_to_blog',$s_limit_to_blog);
		$s->put('kutrl_tpl_passive',$s_tpl_passive);
		$s->put('kutrl_admin_entry_default',$s_admin_entry_default);

		$s->put('kutrl_twit_onadmin',$s_twit_onadmin);
		$s->put('kutrl_twit_onpublic',$s_twit_onpublic);
		$s->put('kutrl_twit_ontpl',$s_twit_ontpl);
		$s->put('kutrl_twit_onwiki',$s_twit_onwiki);

		# Save libDcTwitter settings
		kutrlLibDcTwitter::adminAction('kUtRL');

		$core->blog->triggerBlog();

		http::redirect($p_url.'&part=setting&msg='.$action.'&section='.$section);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$services_combo = array();
foreach($core->kutrlServices as $service_id => $service)
{
	$o = new $service($core);
	$services_combo[__($o->name)] = $o->id;
}
$ext_services_combo = array_merge(array(__('disabled')=>''),$services_combo);
$lst_services_combo = array_merge(array('-'=>''),$services_combo);

echo '
<html>
<head><title>kUtRL, '.__('Links shortener').'</title>'.$header.
dcPage::jsLoad('index.php?pf=kUtRL/js/setting.js').
"<script type=\"text/javascript\">\n//<![CDATA[\n".
dcPage::jsVar('jcToolsBox.prototype.section',$section).
"\n//]]>\n</script>\n".
'</head>
<body>
<h2>kUtRL'.
' &rsaquo; <a href="'.$p_url.'&amp;part=links">'.__('Links').'</a>'.
' &rsaquo; '.__('Settings').
' - <a class="button" href="'.$p_url.'&amp;part=link">'.__('New link').'</a>'.
'</h2>'.$msg.'
<form id="setting-form" method="post" action="'.$p_url.'">

<fieldset id="setting-plugin"><legend>'. __('Plugin activation').'</legend>
<p class="field"><label>'.
form::checkbox(array('s_active'),'1',$s_active).
__('Enable plugin').'</label></p>
</fieldset>

<fieldset id="setting-option"><legend>'. __('General rules').'</legend>
<p class="field"><label>'.
form::checkbox(array('s_limit_to_blog'),'1',$s_limit_to_blog).
__('Limit short link to current blog').'</label></p>
<p class="form-note">'.__('Only link started with this blog URL could be shortened.').'</p>
<p class="field"><label>'.
form::checkbox(array('s_tpl_passive'),'1',$s_tpl_passive).
__('Passive mode').'</label></p>
<p class="form-note">'.__('If this extension is disabled and the passive mode is enabled, "kutrl" tags will display long urls instead of nothing on templates.').'</p>
<p class="field"><label>'.
form::checkbox(array('s_admin_entry_default'),'1',$s_admin_entry_default).
__('Create short link for new entries').'</label></p>
<p class="form-note">'.__('This can be changed on page of creation/edition of an entry.').'</p>
</fieldset>

<fieldset id="setting-service"><legend>'. __('Default services').'</legend>
<p class="field"><label>';
if (!empty($msg) && isset($core->kutrlServices[$s_admin_service])) {
	$o = new $core->kutrlServices[$s_admin_service]($core);
	echo $o->testService() ? $img_green : $img_red;
}
echo '&nbsp;'.__('Administration:').
form::combo(array('s_admin_service'),$services_combo,$s_admin_service).'
</label></p>
<p class="form-note">'.__('Service to use in this admin page and on edit page of an entry.').'</p>
<p class="field"><label>';
if (!empty($msg) && isset($core->kutrlServices[$s_tpl_service])) {
	$o = new $core->kutrlServices[$s_tpl_service]($core);
	echo $o->testService() ? $img_green : $img_red;
}
echo '&nbsp;'.__('Templates:').
form::combo(array('s_tpl_service'),$ext_services_combo,$s_tpl_service).'
</label></p>
<p class="form-note">'.__('Shorten links automatically when using template value like "EntryKutrl".').'</p>
<p class="field"><label>';
if (!empty($msg) && isset($core->kutrlServices[$s_wiki_service])) {
	$o = new $core->kutrlServices[$s_wiki_service]($core);
	echo $o->testService() ? $img_green : $img_red;
}
echo '&nbsp;'.__('Contents:').
form::combo(array('s_wiki_service'),$ext_services_combo,$s_wiki_service).'
</label></p>
<p class="form-note">'.__('Shorten links automatically found in contents using wiki synthax.').'</p>
</fieldset>

<fieldset id="setting-twitter"><legend>'. __('Twitter').'</legend>
<div class="two-cols"><div class="col">';

# libDcTwitter settings form
kutrlLibDcTwitter::adminForm('kUtRL');

echo '
<p class="form-note">'.__('Use wildcard %L for short URL, %B for blog name, %U for user name.').'</p>
</div><div class="col">
<h3>'.__('Activation').'</h3>
<p>'.__('Send message when short url is created on:').'</p>
<p class="field"><label>'.
form::checkbox(array('s_twit_onadmin'),'1',$s_twit_onadmin).
__('administration form').'</label></p>
<p class="field"><label>'.
form::checkbox(array('s_twit_onpublic'),'1',$s_twit_onpublic).
__('public form').'</label></p>
<p class="field"><label>'.
form::checkbox(array('s_twit_ontpl'),'1',$s_twit_ontpl).
__('template').'</label></p>
<p class="field"><label>'.
form::checkbox(array('s_twit_onwiki'),'1',$s_twit_onwiki).
__('content').'</label></p>
</div></div>
</fieldset>

<div class="clear">
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('p'),'kUtRL').
form::hidden(array('part'),'setting').
form::hidden(array('action'),'savesetting').'
</p></div>
</form>';
dcPage::helpBlock('kUtRL');
echo $footer.'</body></html>';
?>