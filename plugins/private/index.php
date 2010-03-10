<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Private mode, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

// Getting current parameters
$private_flag = (boolean)$core->blog->settings->private_flag;
$private_conauto = (boolean)$core->blog->settings->private_conauto;
$blog_private_title = $core->blog->settings->blog_private_title;
$blog_private_msg = $core->blog->settings->blog_private_msg;

if (!empty($_POST['saveconfig']))
{
	try
	{
		$private_flag = (empty($_POST['private_flag']))?false:true;
		$private_conauto = (empty($_POST['private_conauto']))?false:true;
		$blog_private_title = $_POST['blog_private_title'];
		$blog_private_msg = $_POST['blog_private_msg'];
		$blog_private_pwd = md5($_POST['blog_private_pwd']);

		if (empty($_POST['blog_private_title'])) {
			throw new Exception(__('No page title.'));
		}

		if (empty($_POST['blog_private_msg'])) {
			throw new Exception(__('No private message.'));
		}

		$core->blog->settings->setNamespace('private');
 		$core->blog->settings->put('private_flag',$private_flag,'boolean','Protect your blog with a password');
 		$core->blog->settings->put('private_conauto',$private_conauto,'boolean','Allow automatic connection');
		$core->blog->settings->put('blog_private_title',$blog_private_title,'string','Private page title');
		$core->blog->settings->put('blog_private_msg',$blog_private_msg,'string','Private message');

		if (!empty($_POST['blog_private_pwd'])) {
			if ($_POST['blog_private_pwd'] != $_POST['blog_private_pwd_c']) {
				throw new Exception(__("Passwords don't match"));
			}
			$core->blog->settings->put('blog_private_pwd',$blog_private_pwd,'string','Private blog password');
		}

		$core->blog->triggerBlog();
		http::redirect($p_url.'&config=1');
	}

	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
if ($core->blog->settings->blog_private_pwd === null)
{
	$err = __('No password set.');
}
?>
<html>
<head>
	<title><?php echo __('Private mode'); ?></title>
	<?php echo dcPage::jsLoad('index.php?pf=private/js/config.js'); ?>
</head>
<body>
<?php 

if (isset($_GET['config'])) {
	echo '<p class="message">'.__('Configuration successfully updated.').'</p>';
}

if (!empty($err)) echo '<p class="error">'.$err.'</p>'; 

echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Private mode').'</h2>';

echo '<div id="private_options">'.
	'<form method="post" action="'.$p_url.'">'.
		'<fieldset>'.
			'<legend>'. __('Plugin activation').'</legend>'.
				'<p class="field">'.
					form::checkbox('private_flag', 1, $private_flag).
					'<label class=" classic" for="private_flag">'.__('Enable private mode').'</label>'.
				'</p>'.
		'</fieldset>'.
		'<fieldset id="misc_options">'.
			'<legend>'.__('Presentation options').'</legend>'.
				'<p><label class="required" title="'.__('Required field').'">'.
					__('New password:').
					form::password('blog_private_pwd',30,255).
				'</label></p>'.
				'<p><label class="required" title="'.__('Required field').'">'.
					__('Confirm password:').
					form::password('blog_private_pwd_c',30,255).
				'</label></p>'.
				'<p>'.
					form::checkbox('private_conauto', 1, $private_conauto).
					'<label class=" classic" for="private_conauto">'. __('Propose automatic connection to visitors').'</label>'.
				'</p>'.
				'<p class="form-note">'.
				__('With this option, the password could be stored in a cookie.').
				__('But it still remains a choice for the visitor.').
				'</p>'.
				'<p>'.sprintf(__('Don\'t forget to add a <a href="%s">widget</a> allowing disconnection from the blog.'),'plugin.php?p=widgets').'</p>'.
				'<p class="col"><label class="required" title="'.__('Required field').'">'.
					__('Private page title:').
					form::field('blog_private_title',20,255,html::escapeHTML($blog_private_title),'maximal').
				'</label></p>'.
				'<p class="area"><label class="required" title="'.__('Required field').'">'.
					__('Private message:').
					form::textarea('blog_private_msg',30,4,html::escapeHTML($blog_private_msg)).
				'</label></p>'.
		'</fieldset>'.

		'<p>'.form::hidden(array('p'),'private').
		$core->formNonce().
		'<input type="submit" name="saveconfig" value="'.__('Save configuration').'" /></p>'.
	'</form>'.
'</div>';
?>
</body>
</html>