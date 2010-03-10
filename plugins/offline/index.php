<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Offline mode, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

// Getting current parameters
$blog_off_flag = (boolean)$core->blog->settings->blog_off_flag;
$blog_off_ip_ok = $core->blog->settings->blog_off_ip_ok;
$blog_off_page_title = $core->blog->settings->blog_off_page_title;
$blog_off_msg = $core->blog->settings->blog_off_msg;
$myip = $_SERVER['REMOTE_ADDR'];

if (!empty($_POST['saveconfig']))
{
	try
	{
		$blog_off_flag = (empty($_POST['blog_off_flag']))?false:true;
		$blog_off_ip_ok = $_POST['blog_off_ip_ok'];
		$blog_off_page_title = $_POST['blog_off_page_title'];
		$blog_off_msg = $_POST['blog_off_msg'];

		if (empty($_POST['blog_off_page_title'])) {
			throw new Exception(__('No page title.'));
		}

		if (empty($_POST['blog_off_msg'])) {
			throw new Exception(__('No maintenance message.'));
		}

		$core->blog->settings->setNamespace('offline');
 		$core->blog->settings->put('blog_off_flag',$blog_off_flag,'boolean');
		$core->blog->settings->put('blog_off_ip_ok',$blog_off_ip_ok,'string','Authorized IP');
		$core->blog->settings->put('blog_off_page_title',$blog_off_page_title,'string','Maintenance page title');
		$core->blog->settings->put('blog_off_msg',$blog_off_msg,'string','Maintenance message');

		$core->blog->triggerBlog();

		$msg = __('Configuration successfully updated.');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
	<title><?php echo __('Offline mode'); ?></title>
	<?php echo dcPage::jsLoad('index.php?pf=offline/js/config.js'); ?>
</head>
<body>
<?php 
if (!empty($msg)) echo '<p class="message">'.$msg.'</p>'; 

echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Offline mode').'</h2>';

echo '<div id="offline_options">'.
	'<form method="post" action="'.$p_url.'">'.
		'<fieldset>'.
			'<legend>'.__('Plugin activation').'</legend>'.
				'<p class="field">'.
					form::checkbox('blog_off_flag', 1, $blog_off_flag).
						'<label class="classic" for="blog_off_flag">'.__('Enable Offline mode').'</label>'.
				'</p>'.
		'</fieldset>'.
		'<fieldset id="misc_options">'.
			'<legend>'.__('Options').'</legend>'.
				'<p><label class="required" title="'.__('Required field').'">'.
					__('Offline title:').
					form::field('blog_off_page_title',30,256,html::escapeHTML($blog_off_page_title)).
				'</label></p>'.
				'<p class="area"><label class="required" title="'.__('Required field').'">'.
					__('Offline message:').
					form::textarea('blog_off_msg',30,2,html::escapeHTML($blog_off_msg)).
				'</label></p>'.
				'<h3>'.__('IP restricted access').'</h3>'.
				'<p class="classic">'.__('My own IP is:&nbsp;').'<strong>'.$myip.'</strong></p>'.
				'<p><label class="classic">'.
					__('Authorized IP:&nbsp;').
					form::field('blog_off_ip_ok',20,39,html::escapeHTML($blog_off_ip_ok)).
				'</label></p>'.
				'<p class="form-note">'.__('With this option, a visitor having this IP can access the website.').'</p>'.
		'</fieldset>'.
		'<p>'.form::hidden(array('p'),'offline').
		$core->formNonce().
		'<input type="submit" name="saveconfig" value="'.__('Save configuration').'" /></p>'.
	'</form>'.
'</div>';
?>
</body>
</html>