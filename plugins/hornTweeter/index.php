<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of hornTweeter, a plugin for Dotclear 2.
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

$msg = '';
$action = empty($_REQUEST['action']) ? '' : $_REQUEST['action'];

# Read settings
$core->blog->settings->addNamespace('hornTweeter');
$s = $core->blog->settings->hornTweeter;
$active = (boolean) $s->active;
$api_url = $s->api_url;
if (null === $api_url) {
	$api_url = 'http://is.gd/api.php?';
}
$post_msg = $s->post_msg;
if (null === $post_msg) {
	$post_msg = '#news "%title%" %url% %tags%';
}
$post_auto = (boolean) $s->post_auto;
$comment_msg = $s->comment_msg;
if (null === $comment_msg) {
	$comment_msg = '#comment by %user% on "%title%" %url%';
}
$comment_auto = (boolean) $s->comment_auto;

# TaC script
$has_registry = $has_access = $has_grant = false;
$has_tac = $core->plugins->moduleExists('TaC');
if ($has_tac) {
	try {
		// always
		$tac = new tac($core,'hornTweeter',null);
		$has_registry = $tac->checkRegistry();
		
		// register plugin to tac
		if (!$has_registry) {
			$cur = $core->con->openCursor($core->prefix.'tac_registry');
			$cur->cr_id = 'hornTweeter';
			$cur->cr_key = '1vilUN2qgBClZO7o05rw7Q';
			$cur->cr_secret = 'uvSUzQDaV3FDZ0zeVvOZGdhv7BeDMZaA4htiytdI';
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
			$url = $tac->requestAccess(DC_ADMIN_URL.'plugin.php?p=hornTweeter&action=granttwitter');
			http::redirect($url);
		}
		
		// request final token
		if ($action == 'granttwitter') {
			$has_grant = $tac->grantAccess();
			
			if (!$has_grant) {
				$tac->cleanAccess();
			}
			http::redirect($p_url);
		}
		
		// clean access
		if ($action == 'cleantwitter') {
			$tac->cleanAccess();
			http::redirect($p_url);
		}
	}
	catch(Exception $e) {
		$active = $has_registry = $has_access = $has_grant = false;
		$core->error->add($e->getMessage());
	}
}

if (!$has_access) {
	$active = false;
	$s->put('active',$active,'boolean');
}
elseif ($action == 'savesetting') {
	try {
		$s->put('active',!empty($_POST['active']),'boolean');
		$s->put('api_url',$_POST['api_url'],'string');
		$s->put('post_msg',$_POST['post_msg'],'string');
		$s->put('post_auto',!empty($_POST['post_auto']),'boolean');
		$s->put('comment_msg',$_POST['comment_msg'],'string');
		$s->put('comment_auto',!empty($_POST['comment_auto']),'boolean');
		//$core->blog->triggerBlog();
		
		http::redirect($p_url.'&msg='.$action);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}


# Display
echo '
<html><head><title>'.__('Horn tweeter').'</title></head>
<body>
<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Horn tweeter').'</h2>';

if (!empty($_GET['msg'])) {
	echo '<p class="message">'.__('Configuration successfully updated').'</p>';
}

if (!$has_tac) {
	echo '<p>'.__('To use this extension you must install plugin called "TaC"').'</p>';
}
else {
	if ($has_access) {
		echo '
		<form id="setting-form" method="post" action="plugin.php">
		<fieldset id="setting-plugin"><legend>'.__('Extension').'</legend>
		<p><label class="classic">'.
		form::checkbox(array('active'),'1',$active).' '.
		__('Enable extension on this blog').'</label></p>
		<p><label class="classic">'.__('URL of shortener API:').'<br />'.
		form::field('api_url',50,255,$api_url,'',2).'
		</label></p>
		<p class="form-note">'.__('API must be compliant with is.gd API').'</p>
		</fieldset>';
	}

	echo '<fieldset id="setting-twitter"><legend>'.__('Twitter account').'</legend>';

	if (!$has_access) {
		echo '
		<p><a href="'.$p_url.
		'&amp;action=requesttwitter'.
		'"><img src="index.php?pf=TaC/img/tac_light.png" alt="Sign in with Twitter"/></a></p>';
	}
	else {
		
		$user = $tac->get('account/verify_credentials');
		$content = $tac->get('account/rate_limit_status');
		
		echo '
		<ul>
		<li>'.sprintf(__('Your are connected as "%s"'),$user->screen_name).'</li>
		<li>'.sprintf(__('It remains %s API hits'),$content->remaining_hits).'</li>
		<li><a href="'.$p_url.'&amp;action=cleantwitter">'.__('Disconnect and clean access').'</a></li>
		</ul>';
	}
	echo '</fieldset>';
	
	if ($has_access) {
		echo '
		<fieldset id="setting-post"><legend>'.__('Entries').'</legend>
		<p><label class="classic">'.__('Message for entries:').'<br />'.
		form::field('post_msg',50,255,html::escapeHTML($post_msg),'',2).'
		</label></p>
		<p class="form-note">'.sprintf(__('You can use wildcards like %s'),'%blog%, %title%, %url%, %author%, %tags%').'</p>
		<p><label class="classic">'.
		form::checkbox(array('post_auto'),'1',$post_auto).' '.
		__('Send tweet when an entry is marked as published').'</label></p>
		</fieldset>
		
		<fieldset id="setting-comment"><legend>'.__('Comments').'</legend>
		<p><label class="classic">'.__('Message for comments:').'<br />'.
		form::field('comment_msg',50,255,html::escapeHTML($comment_msg),'',2).'
		</label></p>
		<p class="form-note">'.sprintf(__('You can use wildcards like %s'),'%blog%, %user%, %title%, %url%').'</p>
		<p><label class="classic">'.
		form::checkbox(array('comment_auto'),'1',$comment_auto).' '.
		__('Send tweet when a comment is published.').'</label></p>
		</fieldset>
		
		<div class="clear">
		<p><input type="submit" name="save" value="'.__('save').'" />'.
		$core->formNonce().
		form::hidden(array('p'),'hornTweeter').
		form::hidden(array('action'),'savesetting').'
		</p></div>
		</form>';
	}
}

echo '
<br class="clear"/>
<p class="right">
hornTweeter - '.$core->plugins->moduleInfo('hornTweeter','version').'&nbsp;
<img alt="hornTweeter" src="index.php?pf=hornTweeter/icon.png" />
</p>';
dcPage::helpBlock('hornTweeter');
echo '</body></html>';
?>