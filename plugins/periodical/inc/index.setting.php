<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of periodical, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$sortby_combo = array(
	__('Create date') => 'post_creadt',
	__('Date') => 'post_dt',
	__('ID') => 'post_id'
);
$order_combo = array(
	__('Descending') => 'desc',
	__('Ascending') => 'asc'
);

$s_active = (boolean) $s->periodical_active;
$s_upddate = (boolean) $s->periodical_upddate;
$s_updurl = (boolean) $s->periodical_updurl;
$e_order = (string) $s->periodical_pub_order;
$e_order = explode(' ',$e_order);
$s_sortby = in_array($e_order[0],$sortby_combo) ?
	$e_order : 'post_dt';
$s_order = isset($e_order[1]) && strtolower($e_order[1]) == 'desc' ? 'desc' : 'asc';

// Messengers
$s_statusnet_msg = (string) $s->periodical_statusnet_msg;
$s_statusnet_login = (string) $s->periodical_statusnet_login;
$s_statusnet_pass = (string) $s->periodical_statusnet_pass;

// Special Twitter
$has_registry = $has_access = $has_grant = false;
$has_tac = $core->plugins->moduleExists('TaC');
if ($has_tac) {

	try {
		// always
		$tac = new tac($core,'periodical',null);
		$has_registry = $tac->checkRegistry();
		
		// register plugin to tac
		if (!$has_registry) {
			$cur = $core->con->openCursor($core->prefix.'tac_registry');
			$cur->cr_id = 'periodical';
			$cur->cr_key = 'UTeOUVbT7YVvRhUXnmEmw';
			$cur->cr_secret = 'AslLDTcpXsGT1k7V4WiYAWos1dK6kv7UboTGUV0Wo';
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
			$url = $tac->requestAccess(DC_ADMIN_URL.'plugin.php?p=periodical&part=setting&action=granttwitter&section=setting-twitter');
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
	try
	{
		$s->put('periodical_active',!empty($_POST['s_active']));
		$s->put('periodical_upddate',!empty($_POST['s_upddate']));
		$s->put('periodical_updurl',!empty($_POST['s_updurl']));
		$s->put('periodical_pub_order',$_POST['s_sortby'].' '.$_POST['s_order']);
		
		$s->put('periodical_statusnet_msg',(string) $_POST['s_statusnet_msg']);
		$s->put('periodical_statusnet_login',(string) $_POST['s_statusnet_login']);
		if (!empty($_POST['s_statusnet_pass'])) {
			$s->put('periodical_statusnet_pass',(string) $_POST['s_statusnet_pass']);
		}
		
		$core->blog->triggerBlog();
		
		http::redirect($p_url.'&part=setting&msg='.$action.'&section='.$section);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

echo '
<html><head><title>'.__('Periodical').'</title>'.
dcPage::jsLoad('index.php?pf=periodical/js/main.js').
'<script type="text/javascript">'."\n//<![CDATA[\n".
dcPage::jsVar('jcToolsBox.prototype.text_wait',__('Please wait')).
dcPage::jsVar('jcToolsBox.prototype.section',$section).
"\n//]]>\n</script>\n".'
</head>
<body>
<h2>'.
html::escapeHTML($core->blog->name).
' &rsaquo; <a href="'.$p_url.'&amp;part=periods">'.__('Periodical').'</a>'.
' &rsaquo; '.__('Settings').
' - <a class="button" href="'.$p_url.'&amp;part=addperiod">'.__('New period').'</a>'.
'</h2>'.$msg.'

<form id="setting-form" method="post" action="plugin.php">

<fieldset id="setting-plugin"><legend>'.__('Extension').'</legend>
<p class="field"><label>'.
form::checkbox(array('s_active'),'1',$s_active).' '.
__('Enable extension').'</label></p>
</fieldset>

<fieldset id="setting-date"><legend>'.__('Dates of published entries').'</legend>
<p class="field"><label>'.
form::checkbox(array('s_upddate'),'1',$s_upddate).' '.
__('Update post date').'</label></p>
<p class="field"><label>'.
form::checkbox(array('s_updurl'),'1',$s_updurl).' '.
__('Update post url').'</label></p>
</fieldset>

<fieldset id="setting-order"><legend>'.__('Order of publication of entries').'</legend>
<p class="field"><label>'.__('Order by:').
form::combo('s_sortby',$sortby_combo,$s_sortby).'</label></p>
<p class="field"><label>'.__('Sort:').
form::combo('s_order',$order_combo,$s_order).'</label></p>
</fieldset>

<fieldset id="setting-twitter"><legend>'. __('Messenger').'</legend>
<div class="two-cols"><div class="col">
<h3>'.__('Identi.ca account').'</h3>
<p><label class="classic">'.__('Login:').'<br />'.
form::field('s_statusnet_login',50,255,$s_statusnet_login,'',2).'
</label></p>
<p><label class="classic">'.__('Password:').'<br />'.
form::password('s_statusnet_pass',50,255,'','',2).'
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
form::field('s_statusnet_msg',50,255,$s_statusnet_msg,'',2).'
</label></p>
</div><div class="col">
<ul>
<li>'.__('Send automatically message to tweeter when entry is published').'</li>
<li>'.__('Leave empty "ident" to not use this feature.').'</li>
<li>'.__('For message, use wildcard: %posttitle%, %posturl%, %shortposturl%, %postauthor%, %sitetitle%, %siteurl%, %shortsiteurl%').'</li>
</ul>
</div></div>
</fieldset>

<div class="clear">
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('p'),'periodical').
form::hidden(array('part'),'setting').
form::hidden(array('action'),'savesetting').
form::hidden(array('section'),$section).'
</p></div>
</form>';
?>