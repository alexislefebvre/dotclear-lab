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
		
		$settings = new dcSettings($core, null);
		$settings->subscription->put('blogs_folder_path', $blogs_folder_path, 'string', 'Blogs storage folder path', true, true);
		$settings->subscription->put('dotclear_folder_path', $dotclear_folder_path, 'string', 'Dotclear folder path', true, true);
				
		$core->blog->triggerBlog(); //still needed ?
	
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

$settings = new dcSettings($core, null);

echo
'<form action="'.$p_url.'" method="post" enctype="multipart/form-data">'.
'<fieldset><legend>'.__('Paths').'</legend>'.
'<p>'.__('Set absolute paths for Doctclear installation and blogs storage folder.').'</p>'.
'<p><label>'.__('Dotclear path:').' '.form::field('dotclear_folder_path',40,300,$settings->subscription->dotclear_folder_path).'</label></p>'.
'<p><label>'.__('Blogs path:').' '.form::field('blogs_folder_path',40,300,$settings->subscription->blogs_folder_path).'</label></p>'.
'</fieldset>';

echo 
'<p><input type="submit" value="'.__('save').'" />'.$core->formNonce().'</p></form>';

echo '<p>'.sprintf(__('Don\'t forget to add a <a href="%s">widget</a> linking to your subscription	 page.'),'plugin.php?p=widgets').'</p>';
?>
</body>
</html>