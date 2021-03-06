<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$cm_recipients		= $core->blog->settings->contactme->cm_recipients;
$cm_subject_prefix	= $core->blog->settings->contactme->cm_subject_prefix;
$cm_page_title		= $core->blog->settings->contactme->cm_page_title;
$cm_form_caption	= $core->blog->settings->contactme->cm_form_caption;
$cm_msg_success		= $core->blog->settings->contactme->cm_msg_success;
$cm_msg_error		= $core->blog->settings->contactme->cm_msg_error;
$cm_use_antispam	= $core->blog->settings->contactme->cm_use_antispam;

$antispam_enabled = $core->plugins->moduleExists('antispam');

if ($cm_page_title === null) {
	$cm_page_title = __('Contact me');
}

if ($cm_form_caption === null) {
	$cm_form_caption = __('<p>You can use the following form to send me an e-mail.</p>');
}

if ($cm_msg_success === null) {
	$cm_msg_success = __('<p style="color:green"><strong>Thank you for your message.</strong></p>');
}

if ($cm_msg_error === null) {
	$cm_msg_error = __('<p style="color:red"><strong>An error occured:</strong> %s</p>');
}

if (isset($_POST['cm_recipients']))
{
	try
	{
		$cm_recipients = $_POST['cm_recipients'];
		$cm_subject_prefix = $_POST['cm_subject_prefix'];
		$cm_page_title = $_POST['cm_page_title'];
		$cm_form_caption = $_POST['cm_form_caption'];
		$cm_msg_success = $_POST['cm_msg_success'];
		$cm_msg_success = $_POST['cm_msg_success'];
		$cm_msg_error = $_POST['cm_msg_error'];
		
		if (empty($_POST['cm_page_title'])) {
			throw new Exception(__('No page title.'));
		}
		
		if (empty($_POST['cm_msg_success'])) {
			throw new Exception(__('No success message.'));
		}
		
		if (empty($_POST['cm_msg_error'])) {
			throw new Exception(__('No error message.'));
		}
		
		$cm_r = explode(',',$cm_recipients);
		$cm_r2 = array();
		
		foreach ($cm_r as $v)
		{
			$v = trim($v);
			if (empty($v)) {
				continue;
			}
			if (!text::isEmail($v)) {
				throw new Exception(sprintf(__('%s is not a valid e-mail address.'),html::escapeHTML($v)));
			}
			$cm_r2[] = $v;
		}
		$cm_recipients = implode(', ',$cm_r2);
		
		# Everything's fine, save options
		$core->blog->settings->addNamespace('contactme');
		$core->blog->settings->contactme->put('cm_recipients',$cm_recipients,'string','ContactMe recipients');
		$core->blog->settings->contactme->put('cm_subject_prefix',$cm_subject_prefix,'string','ContactMe subject prefix');
		$core->blog->settings->contactme->put('cm_page_title',$cm_page_title,'string','ContactMe page title');
		$core->blog->settings->contactme->put('cm_form_caption',$cm_form_caption,'string','ContactMe form caption');
		$core->blog->settings->contactme->put('cm_msg_success',$cm_msg_success,'string','ContactMe success message');
		$core->blog->settings->contactme->put('cm_msg_error',$cm_msg_error,'string','ContactMe error message');
		
		if ($antispam_enabled) {
			$core->blog->settings->contactme->put('cm_use_antispam',!empty($_POST['cm_use_antispam']),'boolean','ContactMe should use comments spam filter');
		}
		
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
	<title><?php echo __('Contact me'); ?></title>
</head>

<body>
<?php
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Contact me').'</h2>';

if (!empty($_GET['upd'])) {
	dcPage::message(__('Setting have been successfully updated.'));
}

echo
'<form action="'.$p_url.'" method="post">'.
'<fieldset><legend>'.__('E-Mail settings').'</legend>'.
'<p><label for="cm_recipients">'.__('Comma separated recipients list:').'</label> '.
form::field('cm_recipients',30,512,html::escapeHTML($cm_recipients),'maximal').'</p>'.
'<p class="form-note">'.__('Empty recipients list to disable contact page.').'</p>'.
'<p><label for="cm_subject_prefix">'.__('E-Mail subject prefix:').'</label> '.
form::field('cm_subject_prefix',30,128,html::escapeHTML($cm_subject_prefix)).'</p>'.
'<p class="form-note">'.__('This will be prepend to e-mail subject').'</p>';

# Antispam options
if ($antispam_enabled)
{
	echo
	'<p>'.form::checkbox('cm_use_antispam',1,(boolean) $cm_use_antispam).
	' <label for="cm_use_antispam" class="classic">'.__('Use comments spam filter').'</label></p>';
}

echo
'</fieldset>'.

'<fieldset><legend>'.__('Presentation options').'</legend>'.
'<p><label for="cm_page_title" class="required" title="'.__('Required field').'"><abbr title="'.__('Required field').'">*</abbr> '.
__('Page title:').'</label> '.
form::field('cm_page_title',30,256,html::escapeHTML($cm_page_title)).
'</p>'.
'<p class="area"><label for="cm_form_caption">'.__('Form caption:').'</label> '.
form::textarea('cm_form_caption',30,2,html::escapeHTML($cm_form_caption)).
'</p>'.
'<p class="area"><label for="cm_msg_success" class="required" title="'.__('Required field').'"><abbr title="'.__('Required field').'">*</abbr> '.
__('Confirmation message:').'</label> '.
form::textarea('cm_msg_success',30,2,html::escapeHTML($cm_msg_success)).
'</p>'.
'<p class="area"><label for="cm_msg_error" class="required" title="'.__('Required field').'"><abbr title="'.__('Required field').'">*</abbr> '.
__('Error message:').'</label> '.
form::textarea('cm_msg_error',30,2,html::escapeHTML($cm_msg_error)).
'</p>'.
'<p class="form-note">'.__('"%s" is the error message.').'</p>'.
'</fieldset>'.

'<p>'.$core->formNonce().'<input type="submit" value="'.__('save').'" /></p>'.
'</form>';

echo '<p>'.sprintf(__('Don\'t forget to add a <a href="%s">widget</a> linking to your contact page.'),'plugin.php?p=widgets').'</p>';
?>
</body>
</html>