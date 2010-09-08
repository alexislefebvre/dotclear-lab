<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis, BG and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$active = (boolean) $s->zoneclearFeedServer_active;
$pub_active = (boolean) $s->zoneclearFeedServer_pub_active;
$post_status_new = (boolean) $s->zoneclearFeedServer_post_status_new;
$bhv_pub_upd = (integer) $s->zoneclearFeedServer_bhv_pub_upd;
$update_limit = (integer) $s->zoneclearFeedServer_update_limit;
if ($update_limit < 1) $update_limit = 10;
$post_full_tpl = @unserialize($s->zoneclearFeedServer_post_full_tpl);
if (!is_array($post_full_tpl)) $post_full_tpl = array();
$post_title_redir = @unserialize($s->zoneclearFeedServer_post_title_redir);
if (!is_array($post_title_redir)) $post_title_redir = array();
$feeduser = (string) $s->zoneclearFeedServer_user;

$identica_login = (string) $s->zoneclearFeedServer_identica_login;
$identica_pass = (string) $s->zoneclearFeedServer_identica_pass;
$identica_default_message = (string) $s->zoneclearFeedServer_identica_default_message;
$twitter_default_message = (string) $s->zoneclearFeedServer_twitter_default_message;

$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';

// Special Twitter
$has_tac = $has_registry = $has_access = $has_grant = false;
$has_tac = $core->plugins->moduleExists('TaC');
if ($has_tac) {

	try {
		// always
		$tac = new tac($core,'zoneclearFeedServer',null);
		$has_registry = $tac->checkRegistry();
		
		// register plugin to tac
		if (!$has_registry) {
			$cur = $core->con->openCursor($core->prefix.'tac_registry');
			$cur->cr_id = 'zoneclearFeedServer';
			$cur->cr_key = 'R1uiJxPNKWSg2ZruRpfmDA';
			$cur->cr_secret = 'tpdtvsdtiiDAV3SSdGsR2vh5E8z1Uu9Fnhbamx6ck';
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
			$url = $tac->requestAccess(DC_ADMIN_URL.'plugin.php?p=zoneclearFeedServer&part=setting&action=granttwitter&section=setting-twitter');
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
		$limit = abs((integer) $_POST['update_limit']);
		if ($limit < 1) $limit = 10;
		
		$s->put('zoneclearFeedServer_active',!empty($_POST['active']));
		$s->put('zoneclearFeedServer_pub_active',!empty($_POST['pub_active']));
		$s->put('zoneclearFeedServer_post_status_new',!empty($_POST['post_status_new']));
		$s->put('zoneclearFeedServer_bhv_pub_upd',(integer) $_POST['bhv_pub_upd']);
		$s->put('zoneclearFeedServer_update_limit',$limit);
		$s->put('zoneclearFeedServer_post_full_tpl',serialize($_POST['post_full_tpl']));
		$s->put('zoneclearFeedServer_post_title_redir',serialize($_POST['post_title_redir']));
		$s->put('zoneclearFeedServer_user',(string) $_POST['feeduser']);
		
		$s->put('zoneclearFeedServer_identica_login',(string) $_POST['identica_login']);
		if (!empty($_POST['identica_pass'])) {
			$s->put('zoneclearFeedServer_identica_pass',(string) $_POST['identica_pass']);
		}
		$s->put('zoneclearFeedServer_identica_default_message',(string) $_POST['identica_default_message']);
		if (isset($_POST['twitter_default_message'])) {
			$s->put('zoneclearFeedServer_twitter_default_message',(string) $_POST['twitter_default_message']);
		}

		//todo save twitter settings
		
		$core->blog->triggerBlog();
		
		http::redirect($p_url.'&part=setting&msg='.$action.'&section='.$section);
	}
	catch (Exception $e) 
	{
		$core->error->add($e->getMessage());
	}
}

$combo_admins = $zc->getAllBlogAdmins();
$combo_pubupd = array(
	__('disable') => 0,
	__('before display') => 1,
	__('after display') => 2,
	__('through Ajax') => 3
);
$combo_status = array(
	__('unpublished') => 0,
	__('published') => 1
);

$pub_page_url = $core->blog->url.$core->url->getBase('zoneclearFeedsPage');

echo '
<html>
<head><title>'.__('Feeds server').'</title>'.$header.
dcPage::jsColorPicker().
dcPage::jsLoad('index.php?pf=zoneclearFeedServer/js/setting.js').
"<script type=\"text/javascript\">\n//<![CDATA[\n".
dcPage::jsVar('jcToolsBox.prototype.section',$section).
"\n//]]>\n</script>\n".
'</head>
<body>
<h2>'.html::escapeHTML($core->blog->name).
' &rsaquo; <a href="'.$p_url.'&amp;part=feeds">'.__('Feeds').'</a>'.
' &rsaquo; '.__('Settings').
' - <a class="button" href="'.$p_url.'&amp;part=feed">'.__('New feed').'</a>'.
'</h2>'.$msg.'
<form id="setting-form" method="post" action="'.$p_url.'">

<fieldset id="setting-plugin"><legend>'. __('Plugin activation').'</legend>
<p class="field"><label>'.
form::checkbox(array('active'),'1',$active).
__('Enable plugin').'</label></p>
</fieldset>

<fieldset id="setting-option"><legend>'. __('General rules').'</legend>
<div class="two-cols"><div class="col">
<p class="field"><label>'.
__('Status of new posts:').'<br />'.
form::combo(array('post_status_new'),$combo_status,$post_status_new).'</label></p>
<p class="field"><label>'.
__('Owner of entries created by zoneclearFeedServer:').'<br />'.
form::combo(array('feeduser'),$combo_admins,$feeduser).'</label></p>
<p class="field"><label>'.
__('Update feeds on public side:').'<br />'.
form::combo(array('bhv_pub_upd'),$combo_pubupd,$bhv_pub_upd).'</label></p>
<p class="field"><label>'.
__('Number of feeds to update at one time:').'<br />'.
form::field('update_limit',6,4,$update_limit).'</label></p>
<p class="field"><label>'.
form::checkbox(array('pub_active'),'1',$pub_active).
__('Enable public page').'</label></p>
</div><div class="col">
<h3>'.__('Information').'</h3>
<ul>
<li>'.__('A writable cache folder is required to use this extension.').'</li>
<li>'.__('If you set a large number of feeds to update at one time, this may cause a timeout error. We recommand to keep it to one.').'</li>
<li>'.__('If you use cron script, you can disable public update.').'</li>
<li>'.sprintf(__('If active, a public list of feeds are available at "%s".'),'<a href="'.$pub_page_url.'">'.$pub_page_url.'</a>').'</li>
<li>'.__('In order to do update through Ajax, your theme must have behavior publicHeadContent.').'</li>
</ul>
</div></div>
</fieldset>

<fieldset id="setting-identica"><legend>'.__('Identi.ca').'</legend>
<div class="two-cols"><div class="col">
<h3>'.__('Identi.ca account').'</h3>
<p><label class="classic">'.__('Login:').'<br />'.
form::field('identica_login',50,255,$identica_login,'',2).'
</label></p>
<p><label class="classic">'.__('Password:').'<br />'.
form::password('identica_pass',50,255,'','',2).'
</label></p>
<p class="form-note">'.__('Type a password only to change old one.').'</p>
<h3>'.__('Message').'</h3>
<p><label class="classic">'.__('Text:').'<br />'.
form::field('identica_default_message',50,255,$identica_default_message,'',2).'
</label></p>
</div><div class="col">
<ul>
<li>'.__('Send automatically message to Identi.ca on new post only if status of new post is "pusblished".').'</li>
<li>'.__('Leave empty "ident" to not use this feature.').'</li>
<li>'.__('For message, use wildcard: %posttitle%, %postlink%, %postauthor%, %posttweeter%, %sitetitle%, %sitelink%').'</li>
</ul>';
if (!$has_tac) {
	echo '<p>'.__('To use a Twitter account you must install plugin called "TaC"').'</p>';
}
echo '
</div></div>
</fieldset>';

if ($has_tac) {
	echo '
	<fieldset id="setting-twitter"><legend>'.__('Twitter').'</legend>
	<div class="two-cols"><div class="col">';

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

	echo '
	<h3>'.__('Message').'</h3>
	<p><label class="classic">'.__('Text:').'<br />'.
	form::field('twitter_default_message',50,255,$twitter_default_message,'',2).'
	</label></p>
	</div><div class="col">
	<ul>
	<li>'.__('Send automatically message to Twitter on new post only if status of new post is "pusblished".').'</li>
	<li>'.__('Leave empty "ident" to not use this feature.').'</li>
	<li>'.__('For message, use wildcard: %posttitle%, %postlink%, %postauthor%, %posttweeter%, %sitetitle%, %sitelink%').'</li>
	</ul>
	</div></div>
	</fieldset>
	';
}

echo '
<fieldset id="setting-display"><legend>'. __('Display').'</legend>
<div class="two-cols"><div class="col">
<h3>'.__('Entries').'</h3>
<p>'.__('Show full content on:').'</p>';

foreach($zc->getPublicUrlTypes($core) as $k => $v)
{
	echo 
	'<p class="field"><label>'.
	form::checkbox(array('post_full_tpl[]'),$v,in_array($v,$post_full_tpl)).
	__($k).'</label></p>';
}
echo '
</div><div class="col">
<h3>'.__('Entries title').'</h3>
<p>'.__('Redirect to original post on:').'</p>';

foreach($zc->getPublicUrlTypes($core) as $k => $v)
{
	echo 
	'<p class="field"><label>'.
	form::checkbox(array('post_title_redir[]'),$v,in_array($v,$post_title_redir)).
	__($k).'</label></p>';
}
echo '
</div></div>
</fieldset>

<div class="clear">
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('p'),'zoneclearFeedServer').
form::hidden(array('part'),'setting').
form::hidden(array('action'),'savesetting').'
</p></div>
</form>';

dcPage::helpBlock('zoneclearFeedServer');
echo $footer.'</body></html>';
?>