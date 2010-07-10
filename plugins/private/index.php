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

// Getting current settings
$s = privateSettings($core);
$private_flag = (boolean)$s->private_flag;
$private_conauto_flag = (boolean)$s->private_conauto_flag;
$private_page_title = $s->private_page_title;
$private_page_message = $s->private_page_message;

if (!empty($_POST['saveconfig']))
{
	try
	{
		$private_flag = (empty($_POST['private_flag']))?false:true;
		$private_conauto_flag = (empty($_POST['private_conauto_flag']))?false:true;
		$private_page_title = $_POST['private_page_title'];
		$private_page_message = $_POST['private_page_message'];
		$blog_private_pwd = md5($_POST['blog_private_pwd']);

 		$s->put('private_flag',$private_flag,'boolean','Private mode activation flag');
 		$s->put('private_conauto_flag',$private_conauto_flag,'boolean','Private mode automatic connection option');
		$s->put('private_page_title',$private_page_title,'string','Private mode public page title');
		$s->put('private_page_message',$private_page_message,'string','Private mode public welcome message');

		if (!empty($_POST['blog_private_pwd'])) 
		{
			if ($_POST['blog_private_pwd'] != $_POST['blog_private_pwd_c']) 
			{
				$core->error->add(__("Passwords don't match"));
			}
			else 
			{
				$s->put('blog_private_pwd',$blog_private_pwd,'string','Private blog password');
			}
		}

	}

	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	
	if (!$core->error->flag()) 
	{
		$core->blog->triggerBlog();
		http::redirect($p_url.'&config=1');
	}
}

if ($s->blog_private_pwd === null)
{
	$err = __('No password set.');
}
?>
<html>
<head>
	<title><?php echo __('Private mode'); ?></title>
	<?php echo  dcPage::jsToolBar().
	dcPage::jsLoad('index.php?pf=private/js/config.js'); ?>
</head>
<body>
<?php

if (isset($_GET['config'])) {
	echo '<p class="message">'.__('Configuration successfully updated.').'</p>';
}

echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Private mode').'</h2>';

echo 
'<div id="private_options">
<form method="post" action="'.$p_url.'">
<fieldset>
<legend>'. __('Plugin activation').'</legend>
<p class="field">'.
form::checkbox('private_flag', 1, $private_flag).
'<label class="classic" for="private_flag"> '.
__('Enable private mode').'</label>
</p>
</fieldset>
<fieldset>
<legend>'.__('Presentation options').'</legend>
<div class="two-cols">
<div class="col">';
if (!empty($err)) echo '<p class="error">'.$err.'</p>'; 
echo
'<p><label class="required" for="blog_private_pwd" title="'.__('Required field').'">'.
__('New password:').
form::password('blog_private_pwd',30,255).
'</label></p>'.
'<p><label class="required" for="blog_private_pwd_c" title="'.__('Required field').'">'.
__('Confirm password:').
form::password('blog_private_pwd_c',30,255).
'</label></p>
<p>'.
form::checkbox('private_conauto_flag', 1, $private_conauto_flag).
'<label class="classic" for="private_conauto_flag">'. __('Propose automatic connection to visitors').'</label>'.
'</p>'.
'<p class="form-note">'.
__('With this option, the password could be stored in a cookie.').
'<br />'.
__('But it still remains a choice for the visitor.').
'</p>
<p>'.sprintf(__('Don\'t forget to add a <a href="%s">widget</a> allowing disconnection from the blog.'),'plugin.php?p=widgets').
'</p></div>
<div class="col">
<p class=""><label>'.
__('Title:').'</label>'.
form::field('private_page_title',20,255,html::escapeHTML($private_page_title),'maximal').
'</p>
<p class=""><label>'.
__('Message:').'</label>'.
form::textarea('private_page_message',30,4,html::escapeHTML($private_page_message),'maximal').
'</p>
</div></div>
</fieldset>
<p>'.form::hidden(array('p'),'private').
$core->formNonce().
'<input type="submit" name="saveconfig" value="'.__('save').'" />
</p>
</form>
</div>';
?>
</body>
</html>