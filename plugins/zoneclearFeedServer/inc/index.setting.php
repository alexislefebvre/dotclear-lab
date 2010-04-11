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
$post_status_new = (boolean) $s->zoneclearFeedServer_post_status_new;
$dis_pub_upd = (boolean) $s->zoneclearFeedServer_dis_pub_upd;
$update_limit = (integer) $s->zoneclearFeedServer_update_limit;
if ($update_limit < 2) $update_limit = 10;
$post_full_tpl = @unserialize($s->zoneclearFeedServer_post_full_tpl);
if (!is_array($post_full_tpl)) $post_full_tpl = array();
$post_title_redir = @unserialize($s->zoneclearFeedServer_post_title_redir);
if (!is_array($post_title_redir)) $post_title_redir = array();
$feeduser = (string) $s->zoneclearFeedServer_user;

$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';

if ($default_part == 'setting' && $action == 'savesetting')
{
	try {
		$limit = abs((integer) $_POST['update_limit']);
		if ($limit < 2) $limit = 10;
		$s->put('zoneclearFeedServer_active',!empty($_POST['active']));
		$s->put('zoneclearFeedServer_post_status_new',!empty($_POST['post_status_new']));
		$s->put('zoneclearFeedServer_dis_pub_upd',!empty($_POST['dis_pub_upd']));
		$s->put('zoneclearFeedServer_update_limit',$limit);
		$s->put('zoneclearFeedServer_post_full_tpl',serialize($_POST['post_full_tpl']));
		$s->put('zoneclearFeedServer_post_title_redir',serialize($_POST['post_title_redir']));

		$core->blog->triggerBlog();

		http::redirect($p_url.'&part=setting&msg='.$action.'&section='.$section);
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$combo_admins = $zc->getAllBlogAdmins();

echo '
<html>
<head><title>'.__('Zoneclear feed server').'</title>'.$header.
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
<p class="field"><label>'.
form::checkbox('post_status_new',1,$post_status_new).
__('Publish new feed posts').'</label></p>
<p class="field"><label>'.
__('Number of feeds to update at one time:').'<br />'.
form::field('update_limit',6,4,$update_limit).'</label></p>
<p class="form-note">'.sprintf(__('There is a limit of %s seconds between two series of updates.'),$zc->timer).'</p>
<p class="field"><label>'.
__('Owner of entries created by zoneclearFeedServer:').'<br />'.
form::combo(array('feeduser'),$combo_admins,$feeduser).'</label></p>
<p class="field"><label>'.
form::checkbox('dis_pub_upd',1,$dis_pub_upd).
__('Disable public update').'</label></p>
<p class="form-note">'.__('If you use cron script, you can disable public update.').'</p>
</fieldset>

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