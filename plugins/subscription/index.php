<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Subscription, a plugin for Dotclear.
# 
# Copyright (c) 2010 Marc Vachette
# marc.vachette@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------


$core->blog->settings->setNamespace('subscription');

//Liste des SuperUsers (pour affichage des mails)
$ulist = array();
$users = $core->getBlogPermissions($core->blog->id,$core->auth->isSuperAdmin());		
foreach ($users as $id => $uinfos) {		
	$rows = $core->getUser($id)->rows();
	foreach ($rows as $user) {
		if (!empty($user['user_email'])) {
			$ulist[$user['user_email']] = $user['user_email'];
		}
	}
}


if(isset($_POST['notify_mail_adress'])) {
	try {
		$blogs_folder_path 		= $_POST['blogs_folder_path'];
		$dotclear_folder_path 	= $_POST['dotclear_folder_path'];
		$notify_mail_adress 	= $_POST['notify_mail_adress'];
		
		if (empty($_POST['blogs_folder_path'])) {
			throw new Exception(__('No blogs folder path.'));
		}
		
		$core->blog->settings->setNamespace('subscription');
		
		$core->blog->settings->put('blogs_folder_path', $blogs_folder_path, 'string', 'Blogs storage folder path', true, true);
		$core->blog->settings->put('dotclear_folder_path', $dotclear_folder_path, 'string', 'Dotclear folder path', true, true);
		$core->blog->settings->put('notify_mail_adress', $notify_mail_adress	, 'string', 'Email address for notification', true, true);
		
		$core->blog->triggerBlog();
	
		http::redirect($p_url.'&upd=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

?>

<html>
<head>
	<title><?php echo __('Subscription settings'); ?></title>
	
</head>
<body>

<?php
echo '<h2>'.__('Subscription settings').'</h2>';

if (!empty($_GET['upd'])) {
	echo '<p class="message">'.__('Setting have been successfully updated.').'</p>';
}

echo
'<form action="'.$p_url.'" method="post" enctype="multipart/form-data">'.
'<fieldset><legend>'.__('Paths').'</legend>'.
'<p>'.__('Set absolute paths for Doctclear installation and blogs storage folder.').'</p>'.
'<p><label>'.__('Dotclear path:').' '.form::field('dotclear_folder_path',40,300,$core->blog->settings->dotclear_folder_path).'</label></p>'.
'<p><label>'.__('Blogs path:').' '.form::field('blogs_folder_path',40,300,$core->blog->settings->blogs_folder_path).'</label></p>'.
'</fieldset>'.
'<fieldset><legend>'.__('Email adress for notification').'</legend>'.
'<p><label>'.__('Adress to notify blog creation to administrator').
form::combo('notify_mail_adress',$ulist, $core->blog->settings->notify_mail_adress).
'</label></p>'.

'</fieldset>';

/*
echo 
'<fieldset><legend>'.__('Presentation options').'</legend>'.
'<p><label class="required" title="'.__('Required field').'">'.__('Page title:').' '.
form::field('cm_page_title',30,256,html::escapeHTML($page_title)).
'</label></p>'.
'<p class="area"><label>'.__('Form caption:').' '.
form::textarea('cm_form_caption',30,2,html::escapeHTML($form_caption)).
'</label></p>'.
'<p class="area"><label class="required" title="'.__('Required field').'">'.__('Confirmation message:').' '.
form::textarea('cm_msg_success',30,2,html::escapeHTML($msg_success)).
'</label></p>'.
'<p class="area"><label class="required" title="'.__('Required field').'">'.__('Error message:').' '.
form::textarea('cm_msg_error',30,2,html::escapeHTML($msg_error)).
'</label></p>'.
'<p class="form-note">'.__('"%s" is the error message.').'</p>'.
'</fieldset>';
*/

echo 
'<p><input type="submit" value="'.__('save').'" />'.$core->formNonce().'</p></form>';

echo '<p>'.sprintf(__('Don\'t forget to add a <a href="%s">widget</a> linking to your subscription	 page.'),'plugin.php?p=widgets').'</p>';
?>
</body>
</html>