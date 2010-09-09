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
$s_tpl_active = (boolean) $s->kutrl_tpl_active;
$s_admin_entry_default = (string) $s->kutrl_admin_entry_default;

$s_twit_onadmin = (boolean) $s->kutrl_twit_onadmin;
$s_twit_onpublic = (boolean) $s->kutrl_twit_onpublic;
$s_twit_ontpl = (boolean) $s->kutrl_twit_ontpl;
$s_twit_onwiki = (boolean) $s->kutrl_twit_onwiki;
$s_twit_post_msg = (string) $s->kutrl_twit_post_msg;
$s_twit_msg = (string) $s->kutrl_twit_msg;

$s_identica_login = (string) $s->kutrl_identica_login;
$s_identica_pass = (string) $s->kutrl_identica_pass;

$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
$img_green = '<img src="images/check-on.png" alt="ok" />';
$img_red = '<img src="images/check-off.png" alt="fail" />';


// Special Twitter
$has_tac = $has_registry = $has_access = $has_grant = false;
$has_tac = $core->plugins->moduleExists('TaC');
if ($has_tac) {

	try {
		// always
		$tac = new tac($core,'kUtRL',null);
		$has_registry = $tac->checkRegistry();
		
		// register plugin to tac
		if (!$has_registry) {
			$cur = $core->con->openCursor($core->prefix.'tac_registry');
			$cur->cr_id = 'kUtRL';
			$cur->cr_key = '6bmxAJkD2Gd0ymDUGoKng';
			$cur->cr_secret = 'hX7jIkP5XxAKOUz7GYHtmjXfEOdwLXQKRN42019FFs';
			$cur->cr_url_request = 'http://twitter.com/oauth/request_token';
			$cur->cr_url_access = 'http://twitter.com/oauth/access_token';
			$cur->cr_url_autorize = 'http://twitter.com/oauth/authorize';
			$cur->cr_url_authenticate = 'https://api.twitter.com/oauth/authenticate';
			
			$tac->addRegistry($cur);
			
			$has_registry = $tac->checkRegistry();
			
			if (!$has_registry) {
				throw new Exception(__('Failed to register plugin'));
			}
		}
		// test user
		$has_access = $tac->checkAccess();
		
		// request temp token
		if ($action == 'requesttwitter') {
			$url = $tac->requestAccess(DC_ADMIN_URL.'plugin.php?p=kUtRL&part=setting&action=granttwitter&section=setting-twitter');
			http::redirect($url);
		}
		
		// request final token
		if ($action == 'granttwitter') {
			$has_grant = $tac->grantAccess();
			
			if (!$has_grant) {
				$tac->cleanAccess();
			}
			http::redirect($p_url.'&part=setting&action=&section=setting-twitter');
		}
	}
	catch(Exception $e) {
		$has_registry = $has_access = $has_grant = false;
		$core->error->add($e->getMessage());
	}
}


if ($default_part == 'setting' && $action == 'savesetting')
{
	try {
		$s_active = isset($_POST['s_active']);
		$s_admin_service = $_POST['s_admin_service'];
		$s_tpl_service = $_POST['s_tpl_service'];
		$s_wiki_service = $_POST['s_wiki_service'];
		$s_limit_to_blog = isset($_POST['s_limit_to_blog']);
		$s_tpl_passive = isset($_POST['s_tpl_passive']);
		$s_tpl_active = isset($_POST['s_tpl_active']);
		$s_admin_entry_default = isset($_POST['s_admin_entry_default']);
		
		$s_twit_onadmin = isset($_POST['s_twit_onadmin']);
		$s_twit_onpublic = isset($_POST['s_twit_onpublic']);
		$s_twit_ontpl = isset($_POST['s_twit_ontpl']);
		$s_twit_onwiki = isset($_POST['s_twit_onwiki']);
		$s_twit_post_msg = $_POST['s_twit_post_msg'];
		
		$s->put('kutrl_active',$s_active);
		$s->put('kutrl_admin_service',$s_admin_service);
		$s->put('kutrl_tpl_service',$s_tpl_service);
		$s->put('kutrl_wiki_service',$s_wiki_service);
		$s->put('kutrl_limit_to_blog',$s_limit_to_blog);
		$s->put('kutrl_tpl_passive',$s_tpl_passive);
		$s->put('kutrl_tpl_active',$s_tpl_active);
		$s->put('kutrl_admin_entry_default',$s_admin_entry_default);
		
		$s->put('kutrl_twit_onadmin',$s_twit_onadmin);
		$s->put('kutrl_twit_onpublic',$s_twit_onpublic);
		$s->put('kutrl_twit_ontpl',$s_twit_ontpl);
		$s->put('kutrl_twit_onwiki',$s_twit_onwiki);
		$s->put('kutrl_twit_post_msg',$s_twit_post_msg);
		$s->put('kutrl_twit_msg',$s_twit_msg);
		
		$s->put('kutrl_identica_login',(string) $_POST['s_identica_login']);
		if (!empty($_POST['s_identica_pass'])) {
			$s->put('kutrl_identica_pass',(string) $_POST['s_identica_pass']);
		}
		
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
<p><label class="classic">'.
form::checkbox(array('s_active'),'1',$s_active).
__('Enable plugin').'</label></p>
</fieldset>

<fieldset id="setting-option"><legend>'. __('General rules').'</legend>
<p><label class="classic">'.
form::checkbox(array('s_limit_to_blog'),'1',$s_limit_to_blog).
__('Limit short link to current blog').'</label></p>
<p class="form-note">'.__('Only link started with this blog URL could be shortened.').'</p>
<p><label class="classic">'.
form::checkbox(array('s_tpl_passive'),'1',$s_tpl_passive).
__('Passive mode').'</label></p>
<p class="form-note">'.__('If this extension is disabled and the passive mode is enabled, "kutrl" tags (like EntryKurl) will display long urls instead of nothing on templates.').'</p>
<p><label class="classic">'.
form::checkbox(array('s_tpl_active'),'1',$s_tpl_active).
__('Active mode').'</label></p>
<p class="form-note">'.__('If the active mode is enabled, all know default template tags (like EntryURL) will display short urls instead of long ones on templates.').'</p>
<p><label class="classic">'.
form::checkbox(array('s_admin_entry_default'),'1',$s_admin_entry_default).
__('Create short link for new entries').'</label></p>
<p class="form-note">'.__('This can be changed on page of creation/edition of an entry.').'</p>
</fieldset>

<fieldset id="setting-service"><legend>'. __('Default services').'</legend>
<p><label>';
if (!empty($msg) && isset($core->kutrlServices[$s_admin_service])) {
	$o = new $core->kutrlServices[$s_admin_service]($core);
	echo $o->testService() ? $img_green : $img_red;
}
echo '&nbsp;'.__('Administration:').'<br />'.
form::combo(array('s_admin_service'),$services_combo,$s_admin_service).'
</label></p>
<p class="form-note">'.__('Service to use in this admin page and on edit page of an entry.').'</p>
<p><label>';
if (!empty($msg) && isset($core->kutrlServices[$s_tpl_service])) {
	$o = new $core->kutrlServices[$s_tpl_service]($core);
	echo $o->testService() ? $img_green : $img_red;
}
echo '&nbsp;'.__('Templates:').'<br />'.
form::combo(array('s_tpl_service'),$ext_services_combo,$s_tpl_service).'
</label></p>
<p class="form-note">'.__('Shorten links automatically when using template value like "EntryKutrl".').'</p>
<p><label>';
if (!empty($msg) && isset($core->kutrlServices[$s_wiki_service])) {
	$o = new $core->kutrlServices[$s_wiki_service]($core);
	echo $o->testService() ? $img_green : $img_red;
}
echo '&nbsp;'.__('Contents:').'<br />'.
form::combo(array('s_wiki_service'),$ext_services_combo,$s_wiki_service).'
</label></p>
<p class="form-note">'.__('Shorten links automatically found in contents using wiki synthax.').'</p>
</fieldset>

<fieldset id="setting-twitter"><legend>'. __('Messenger').'</legend>
<div class="two-cols"><div class="col">
<h3>'.__('Identi.ca account').'</h3>
<p><label class="classic">'.__('Login:').'<br />'.
form::field('s_identica_login',50,255,$s_identica_login,'',2).'
</label></p>
<p><label class="classic">'.__('Password:').'<br />'.
form::password('s_identica_pass',50,255,'','',2).'
</label></p>
<p class="form-note">'.__('Type a password only to change old one.').'</p>';

if (!$has_tac) {
	echo '<p>'.__('To use a Twitter account you must install plugin called "TaC"').'</p>';
}
else {
	echo '<h3>'.__('Twitter account').'</h3>';

	if (!$has_access) {
		echo '
		<p><a href="'.$p_url.
		'&amp;part=setting&amp;action=requesttwitter&amp;section=setting-twitter'.
		'"><img src="index.php?pf=TaC/img/tac_light.png" alt="Sign in with Twitter"/></a></p>';
	}
	else {
		$user = $tac->get('account/verify_credentials');
		$content = $tac->get('account/rate_limit_status');
		
		echo '
		<ul>
		<li>'.sprintf(__('Your are connected as "%s"'),$user->screen_name).'</li>
		<li>'.sprintf(__('It remains %s API hits'),$content->remaining_hits).'</li>
		<li><a href="'.$p_url.'&amp;part=setting&amp;action=cleantwitter&amp;section=setting-twitter">'.__('Disconnect and clean access').'</a></li>
		</ul>';
	}
}

echo '
<h3>'.__('Message').'</h3>
<p><label class="classic">'.__('Text:').'<br />'.
form::field('s_twit_msg',50,255,$s_twit_msg,'',2).'
</label></p>
<p class="form-note">'.__('Use wildcard %L for short URL, %B for blog name, %U for user name.').'</p>
<p><label class="classic">'.__('Entry message:').'<br />'.
form::field('s_twit_post_msg',50,255,$s_twit_post_msg,'',2).'
</label></p>
<p class="form-note">'.__('This is a special message that can be used on admin enrty page, use wildcard %T for entry title, %L for short URL, %B for blog name, %U for user name.').'</p>
</div><div class="col">
<h3>'.__('Activation').'</h3>
<p>'.__('Send message when short url is created on:').'</p>
<p><label class="classic">'.
form::checkbox(array('s_twit_onadmin'),'1',$s_twit_onadmin).
__('administration form').'</label></p>
<p><label class="classic">'.
form::checkbox(array('s_twit_onpublic'),'1',$s_twit_onpublic).
__('public form').'</label></p>
<p><label class="classic">'.
form::checkbox(array('s_twit_ontpl'),'1',$s_twit_ontpl).
__('template').'</label></p>
<p><label class="classic">'.
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