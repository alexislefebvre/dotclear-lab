<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 Osku, Tomtom and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

// Getting current parameters
$s =& $core->blog->settings->agora;
$agora_flag		= (boolean)$s->agora_flag;
$community_flag 	= (boolean)$s->community_flag;
$wiki_flag		= (boolean)$s->wiki_flag;
$user_desc 		= (boolean)$s->user_desc;
$modify_pseudo 	= (boolean)$s->modify_pseudo;
$new_post			= (boolean)$s->new_post;
// Comments or messages : 
$full_flag		= (boolean)$s->full_flag;
$entry_excerpt		= (boolean)$s->entry_excerpt;
$content_status	= $s->content_status;
$content_syntax 	= $s->content_syntax;
$register_flag 	= (boolean)$s->register_flag;
$register_modo		= (boolean)$s->register_modo;
$recover_flag 		= (boolean)$s->recover_flag;
$private_flag		= (boolean)$s->private_flag;
$register_modo		= (boolean)$s->register_modo;
$content_syntax	= $s->content_syntax;
$content_status	= (integer) $s->content_status;
$avatar			= (integer) $s->avatar;
$modo_links		= (boolean) $s->modo_links;

// Tweaks
$empty_category 	= (boolean)$s->empty_category;
$trig_date 		= (boolean)$s->trig_date;
$nb_message_per_feed = $s->nb_message_per_feed;

$has_category = !$core->blog->getCategories()->isempty();

foreach ($core->getFormaters() as $v) {
	$formaters_combo[$v] = $v;
}

foreach ($core->blog->getAllPostStatus() as $k => $v) {
	$status_combo[$v] = $k;
}

$avatar_combo = array( __('Disabled') => 0, __('Manual display') => 1, __('Automatic display') => 2);

$redir_url = $p_url.'&act=options';


if (!empty($_POST['saveconfig']))
{
	try
	{
		$agora_flag 		= empty($_POST['agora_flag']) ? false : true;
		$private_flag 		= empty($_POST['private_flag']) ? false : true;
		$register_flag 	= empty($_POST['register_flag']) ? false : true;
		$register_modo 	= empty($_POST['register_modo']) ? false : true;
		$recover_flag 		= empty($_POST['recover_flag']) ? false : true;
		$community_flag 	= empty($_POST['community_flag']) ? false : true;
		$wiki_flag 		= empty($_POST['wiki_flag']) ? false : true;
		$user_desc 		= empty($_POST['user_desc']) ? false : true;
		$modify_pseudo 	= empty($_POST['modify_pseudo']) ? false : true;
		$new_post			= empty($_POST['new_post']) ? false : true;
		$full_flag		= empty($_POST['full_flag']) ? false : true;
		$empty_category 	= empty($_POST['empty_category']) ? false : true;
		$modify_pseudo 	= empty($_POST['modify_pseudo']) ? false : true;
		$entry_excerpt 	= empty($_POST['entry_excerpt']) ? false : true;
		$content_status 	= (integer) $_POST['content_status'];
		$content_syntax	= $_POST['content_syntax'];
		$trig_date 		=  empty($_POST['trig_date']) ? false : true;
		$avatar			= (integer) $_POST['avatar'];
		$modo_links		= empty($_POST['modo_links']) ? false : true;

		$nb_message_per_feed = abs((integer) $_POST['nb_message_per_feed']);
		if ($nb_message_per_feed <= 1) { $nb_message_per_feed = 1; }

 		$s->put('agora_flag',$agora_flag,'boolean','Agora activation flag');
		$s->put('full_flag',$full_flag,'boolean','Messages or comments schema');
		$s->put('private_flag',$private_flag,'boolean','Agora private flag');
		$s->put('register_flag',$register_flag,'boolean','Agora - register new user flag');
		$s->put('register_modo',$register_modo,'boolean','Agora - registration moderation');
		if ($core->auth->allowPassChange()) {
			$s->put('recover_flag',$recover_flag,'boolean','Agora - recover password');
		}
		$s->put('community_flag',$community_flag,'boolean','Community flag - people, profile');
		$s->put('wiki_flag',$wiki_flag,'boolean','Wiki flag - public entries edition');
		if ($has_category) {
			$s->put('empty_category',$empty_category,'boolean','Agora - empty category');
		}
		$s->put('user_desc',$user_desc,'boolean','Agora -users can change description');
		$s->put('modify_pseudo',$modify_pseudo,'boolean','Agora -users can change pseudo');
		$s->put('avatar',$avatar,'integer','Handle a simple avatar mechanism for users');

		$s->put('entry_excerpt',$entry_excerpt,'boolean','Agora entry excerpt flag');
		$s->put('content_status',$content_status,'integer','Agora all new content default status');
		$s->put('content_syntax',$content_syntax,'string','Agora new content syntax globally defined');
		$s->put('new_post',$new_post,'boolean','Accept new posts from registered user');
		$s->put('trig_date',$trig_date,'boolean','New message change published post date');
		$s->put('modo_links',$modo_links,'boolean','Display moderation links');

		$s->put('nb_message_per_feed',$nb_message_per_feed,'integer','Number of messages on feeds');

		if (!empty($_FILES['default_avatar']) 
			&& !empty($_FILES['default_avatar']['name']) 
			&& mediaAgora::checkType($_FILES['default_avatar']))
		{
			$core->agora->media->uploadFile($_FILES['default_avatar']['tmp_name'],'avatar.jpg',null,false,true);
		}

		$core->blog->triggerBlog();
		http::redirect($redir_url.'&msg=save');

	}

	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
if (isset($_REQUEST['msg']))
{
	$msg = __('Configuration successfully updated.');
}
?>
<html>
<head>
	<title><?php echo __('agora:config'); ?></title>
<?php echo
  dcPage::jsToolBar().
  dcPage::jsLoad('index.php?pf=agora/js/_options.js');
?>
</head>
<body>
<?php
if (!empty($msg)) echo '<p class="message">'.$msg.'</p>';

echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; <span class="page-title">'.__('agora:config').'</span></h2>';

echo '<div id="agora_options">
<form method="post" action="'.$redir_url.'" enctype="multipart/form-data">
<fieldset>
<legend>'.__('Main settings').'</legend>
<p class="field">'.
form::checkbox('agora_flag', 1, $agora_flag).
'<label class=" classic" for="agora_flag">'.__('Enable agora:').'</label>
</p>
<p class="form-note warn">'.__('When you activate agora, you enable new widgets and all options below.').'</p>
<p class="field">'.
form::checkbox('full_flag', 2, $full_flag).
'<label class=" classic" for="agora_flag">'.__('Messages system:').'</label>
</p>
<p class="form-note info">'.__('Messages system replaces comments system for answering entries. It requires user authentication.').'</p>
<p class="field">'.
form::checkbox('register_flag', 1, $register_flag).
'<label class=" classic" for="register_flag">'.__('Registration:').'</label>
</p>
<p class="field">'.
form::checkbox('register_modo', 1, $register_modo).
'<label class=" classic" for="register_modo">'.__('Registration moderation:').'</label>
</p>
<p class="form-note info">'.__('A confirmation by administrators is required for new subscriptions.').'</p>
<p class="field">'.
form::checkbox('new_post', 1, $new_post).
'<label class=" classic" for="new_post">'.__('New entry:').'</label>
<p class="form-note info">'.__('Users can can post.').'</p>
</p>
</fieldset>
<fieldset>
<legend>'.__('User settings').'</legend>';
if ($core->auth->allowPassChange()) {
	echo '<p class="field">'.
	form::checkbox('recover_flag', 1, $recover_flag).
	'<label class=" classic" for="recover_flag">'.__('Password recovery:').'</label>
	</p>';
}
echo '<p class="field">'.
form::checkbox('modify_pseudo', 1, $modify_pseudo).
'<label class=" classic" for="modify_pseudo">'.__('Nickname edition:').'</label>
</p>
<p class="field">'.
form::checkbox('user_desc', 1, $user_desc).
'<label class=" classic" for="user_desc">'.__('Description edition:').'</label>
</p>';
if (mediaAgora::canWriteImages(true)) {
	echo '
	<p class="field">'.
	form::combo('avatar', $avatar_combo, $avatar).
	'<label class=" classic" for="avatar">'.__('Avatars:').'</label>
	</p>
	<p id="uploader" class="field"><label for="default_avatar">'.__('Default avatar:').'</label>'.
	'<input type="file" name="default_avatar" id="default_avatar" size="30" />'.
	'</p>';
	if (mediaAgora::defaultAvatarExists()) {
		echo '<p class="field"><img class="right" 
			src="'.mediaAgora::imagesURL().'/.avatar_t.jpg" 
			title="'.__('Default avatar').'"
			alt="'.html::escapeHTML(__('Default avatar')).'" /></p>';
	}
}
echo 
'</fieldset>
<fieldset>
<legend>'.__('Miscellaneous settings').'</legend>';
echo '
<p class="field">'.
form::checkbox('entry_excerpt', 1, $entry_excerpt).
'<label class=" classic" for="entry_excerpt">'.__('Entry excerpt:').'</label>
</p>
<p class="field">'.
form::combo('content_syntax', $formaters_combo, $content_syntax).
'<label class=" classic" for="content_syntax">'.__('Text formating:').'</label>
</p>
<p class="field">'.
form::combo('content_status', $status_combo, $content_status).
'<label class=" classic" for="content_syntax">'.__('New content status:').'</label>
</p>
<p class="form-note info">'.__('Does not apply to comments. It is default status of the entries and messages posted by new users. You can change for each y user later.').'</p>
<p class="field">'.
form::checkbox('private_flag', 1, $private_flag).
'<label class=" classic" for="private_flag">'.__('Global authentication:').'</label>
</p>
<p class="form-note info">'.__('Global authentication forces visitors to log in to read the blog.').'</p>';
if ($has_category) {
echo '<p class="field">'.
form::checkbox('empty_category', 1, $empty_category).
'<label class=" classic" for="empty_category">'.__('Uncategorized entries:').'</label>
</p>';}
echo'
<p class="field">'.
form::checkbox('community_flag', 1, $community_flag).
'<label class=" classic" for="community_flag">'.__('Community mode:').'</label>
</p>
<p class="form-note info">'.__("It enables 'Connected users' widget, member list and profile URLs.").'</p>
<p class="field">'.
form::checkbox('trig_date', 1, $trig_date).
'<label class=" classic" for="trig_date">'.__('Forum mode:').'</label>
</p>
<p class="form-note info">'.__("When a published message is added to an entry, its published date is updated.").'</p>
<p class="field">'.
form::checkbox('wiki_flag', 1, $wiki_flag).
'<label class=" classic" for="wiki_flag">'.__('Wiki mode:').'</label>
</p>
<p class="form-note info">'.__("Every authenticated user can edit any entry.").'</p>
<p><label class="classic">'.sprintf(__('Display %s messages per feed'),
form::field('nb_message_per_feed',2,3,$nb_message_per_feed)).
'</label></p>
<p class="field">'.
form::checkbox('modo_links', 1, $modo_links).
'<label class=" classic" for="modo_links">'.__('Moderations links:').'</label>
</p>
<p class="form-note info">'.__("It displays automatically moderation links on your blog.").'</p>
<p>'.form::hidden(array('p'),'agora').
$core->formNonce().
'<input type="submit" name="saveconfig" value="'.__('Save').'" /></p>
</fieldset>
</form>
</div>';
?>
</body>
</html>
