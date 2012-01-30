<?php
/* BEGIN LICENSE BLOCK
This file is part of Contact, a plugin for Dotclear.

K-net
Pierre Van Glabeke

Licensed under the GPL version 2.0 license.
A copy of this license is available in LICENSE file or at
http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
END LICENSE BLOCK */
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$errors = array();
$infos = array();
			
$adminaccess = $core->blog->settings->get('contact_adminaccess');
$adminaccess = $adminaccess === null ? 0 : $adminaccess;

if (($adminaccess >= 1 && !$core->auth->check('admin',$core->blog->id))
|| ($adminaccess >= 2 && !$core->auth->isSuperAdmin())) exit;


$recipients = unserialize($core->blog->settings->contact->contact_recipients);
$recipients = is_array($recipients) ? $recipients : array();
$formconfig = unserialize(str_replace('\\n', "\n", str_replace('\\r', "\r", $core->blog->settings->contact->contact_formconfig)));

$gd2installed = function_exists('imagecreatetruecolor');

if (empty($formconfig)) {
	$core->blog->settings->addNamespace('contact');
	$core->blog->settings->contact->put('contact_recipients','','string');
#	$core->blog->settings->contact->put('contact_formconfig','a:26:{s:9:"pagetitle";s:12:"Contact - %N";s:10:"h2_caption";s:7:"Contact";s:8:"infotext";s:168:"<p><strong>N\'hésitez pas à me contacter !<br />\r\nRemplissez le formulaire et un email me sera automatiquement envoyé.</strong><br />\r\n<br />\r\n* Champ obligatoire</p>";s:17:"recipient_caption";s:14:"Destinataire :";s:12:"name_enabled";b:1;s:13:"name_required";b:1;s:12:"name_caption";s:7:"* Nom :";s:13:"email_enabled";b:1;s:14:"email_required";b:1;s:13:"email_caption";s:9:"* Email :";s:15:"subject_enabled";b:1;s:16:"subject_required";b:0;s:15:"subject_caption";s:7:"Sujet :";s:13:"body_required";b:1;s:12:"body_caption";s:11:"* Message :";s:12:"file_enabled";b:1;s:12:"file_caption";s:15:"Pièce jointe :";s:15:"preview_enabled";b:1;s:15:"preview_caption";s:14:"Prévisualiser";s:12:"send_caption";s:7:"Envoyer";s:13:"emailsenttext";s:74:"<p><strong>Votre message a bien été envoyé.<br />\r\nMerci !</strong></p>";s:6:"h2text";s:7:"Contact";s:16:"antispam_enabled";b:'.($gd2installed ? '1' : '0').';s:16:"antispam_caption";s:28:"Recopier le code anti-spam :";s:17:"mimemailcompliant";b:1;s:8:"mimemail";b:1;}','string');
	$core->blog->settings->contact->put('contact_formconfig','a:27:{s:9:"pagetitle";s:12:"Contact - %N";s:10:"h2_caption";s:7:"Contact";s:8:"infotext";s:168:"<p><strong>N\'hésitez pas à me contacter !<br />\r\nRemplissez le formulaire et un email me sera automatiquement envoyé.</strong><br />\r\n<br />\r\n* Champ obligatoire</p>";s:17:"recipient_caption";s:14:"Destinataire :";s:12:"name_enabled";b:1;s:13:"name_required";b:1;s:12:"name_caption";s:7:"* Nom :";s:13:"email_enabled";b:1;s:14:"email_required";b:1;s:13:"email_caption";s:9:"* Email :";s:15:"subject_enabled";b:1;s:16:"subject_required";b:0;s:15:"subject_caption";s:7:"Sujet :";s:13:"body_required";b:1;s:12:"body_caption";s:11:"* Message :";s:12:"file_enabled";b:1;s:12:"file_caption";s:15:"Pièce jointe :";s:15:"preview_enabled";b:1;s:15:"preview_caption";s:14:"Prévisualiser";s:12:"send_caption";s:7:"Envoyer";s:13:"emailsenttext";s:74:"<p><strong>Votre message a bien été envoyé.<br />\r\nMerci !</strong></p>";s:6:"h2text";s:7:"Contact";s:16:"antispam_enabled";b:'.($gd2installed ? '1' : '0').';s:16:"antispam_caption";s:28:"Recopier le code anti-spam :";s:17:"mimemailcompliant";b:1;s:8:"mimemail";b:1;s:11:"additionnal";a:1:{i:0;a:4:{s:4:"type";s:4:"text";s:7:"caption";s:10:"Site web :";s:3:"ini";s:7:"http://";s:0:"";N;}}}','string');
	$core->blog->triggerBlog();
	http::redirect($p_url);
}

if (!$gd2installed && $formconfig['antispam_enabled']) {
	$formconfig['antispam_enabled'] = false;
	$core->blog->settings->addNamespace('contact');
	$core->blog->settings->contact->put('contact_formconfig',str_replace("\n", '\\n', str_replace("\r", '\\r', serialize($formconfig))),'string');
	$core->blog->triggerBlog();
}

if ($core->blog->id != 'default' && !file_exists(dirname(__FILE__).'/contact.'.$core->blog->id.'.html')) {
	if (!copy(dirname(__FILE__).'/contact.default.html', dirname(__FILE__).'/contact.'.$core->blog->id.'.html')) {
		$errors[] = __('The plugin file <ins>contact.'.$core->blog->id.'.html</ins> is not writable');
	}
}

if (isset($_POST['adminaccess_update']) && $core->auth->isSuperAdmin()) {
	$adminaccess = (integer) $_POST['adminaccess'];
	$core->blog->settings->addNamespace('contact');
	$core->blog->settings->contact->put('contact_adminaccess',$adminaccess,'integer',null,true,true);
	$core->blog->triggerBlog();
} elseif (!empty($_POST['recipients_update'])) {
	$_POST['recipients_recname'] = str_replace('	', ' ', $_POST['recipients_recname']);
	if (!empty($_POST['recipients_action']) && $_POST['recipients_action'] == 'add') {
		if (!empty($_POST['recipients_recname']) && !empty($_POST['recipients_recemail'])) {
			if (ereg('([A-Za-z0-9]|-|_|\.)([A-Za-z0-9]|-|_|\.)*@([A-Za-z0-9]|-|_|\.)([A-Za-z0-9]|-|_|\.)*\.([A-Za-z0-9]|-|_|\.)([A-Za-z0-9]|-|_|\.)([A-Za-z0-9]|-|_|\.)*', $_POST['recipients_recemail'])) {
				$recipients[] = array('email' => $_POST['recipients_recemail'], 'name' => $_POST['recipients_recname']);
			} else {
				$errors[] = __('The given email is invalid.');
			}
		}
	} elseif (!empty($_POST['recipients_action']) && $_POST['recipients_action'] == 'edit') {
		if (!empty($_POST['recipients_recid']) && !empty($_POST['recipients_recname']) && !empty($_POST['recipients_recemail'])) {
			if (ereg('([A-Za-z0-9]|-|_|\.)([A-Za-z0-9]|-|_|\.)*@([A-Za-z0-9]|-|_|\.)([A-Za-z0-9]|-|_|\.)*\.([A-Za-z0-9]|-|_|\.)([A-Za-z0-9]|-|_|\.)([A-Za-z0-9]|-|_|\.)*', $_POST['recipients_recemail'])) {
				foreach ($recipients as $k => $v) {
					if ($v['email'] == $_POST['recipients_recid']) {
						$recipients[$k] = array('email' => $_POST['recipients_recemail'], 'name' => $_POST['recipients_recname']);
						break;
					}
				}
			} else {
				$errors[] = __('The given email is invalid.');
			}
		}
	} elseif (!empty($_POST['recipients_action']) && $_POST['recipients_action'] == 'delete') {
		if (!empty($_POST['recipients_recid'])) {
			foreach ($recipients as $k => $v) {
				if ($v['email'] == $_POST['recipients_recid']) {
					unset($recipients[$k]);
					$recipients = array_values($recipients);
					break;
				}
			}
		}
	}
	
	$core->blog->settings->addNamespace('contact');
	$core->blog->settings->contact->put('contact_recipients',serialize($recipients),'string');
	$core->blog->triggerBlog();
} elseif (!empty($_POST['recipients_autofill'])) {
	$recipients = array();
	
	$rs = $core->getUsers();
	while ($rs->fetch()) {
		$permissions = $core->getUserPermissions($rs->user_id);
		foreach ($permissions as $k => $v) {
			if ($k == $core->blog->id && count($v['p']) > 0 && $rs->user_email != '') {
				$recipients[] = array('email' => $rs->user_email, 'name' => dcUtils::getUserCN($rs->user_id, $rs->user_name, $rs->user_firstname, $rs->user_displayname));
			}
		}
	}
	
	$core->blog->settings->addNamespace('contact');
	$core->blog->settings->contact->put('contact_recipients',serialize($recipients),'string');
	$core->blog->triggerBlog();
} elseif (isset($_POST['formconfig_update'])) {
	$formconfig['pagetitle'] = $_POST['formconfig']['pagetitle'];
	$formconfig['h2text'] = $_POST['formconfig']['h2text'];
	$formconfig['infotext'] = $_POST['formconfig']['infotext'];
	$formconfig['recipient_caption'] = $_POST['formconfig']['recipient_caption'];
	$formconfig['name_enabled'] = isset($_POST['formconfig']['name_enabled']);
	$formconfig['name_required'] = isset($_POST['formconfig']['name_required']);
	$formconfig['name_caption'] = $_POST['formconfig']['name_caption'];
	$formconfig['email_enabled'] = isset($_POST['formconfig']['email_enabled']);
	$formconfig['email_required'] = isset($_POST['formconfig']['email_required']);
	$formconfig['email_caption'] = $_POST['formconfig']['email_caption'];
	$formconfig['subject_enabled'] = isset($_POST['formconfig']['subject_enabled']);
	$formconfig['subject_required'] = isset($_POST['formconfig']['subject_required']);
	$formconfig['subject_caption'] = $_POST['formconfig']['subject_caption'];
	
	$formconfig['additionnal'] = array();
	if (!empty($_POST['formconfig']['additionnal'])) {
		foreach ($_POST['formconfig']['additionnal'] as $k => $v) {
			if (isset($v['dontdelete'])) {
				$formconfig['additionnal'][$k] = array();
				$formconfig['additionnal'][$k]['type'] = $v['type'];
				$formconfig['additionnal'][$k]['caption'] = $v['caption'];
				$formconfig['additionnal'][$k]['ini'] = $v['type'] == 'text' ? $v['ini'] : ($v['type'] == 'select' ? (int) $v['ini'] : ($v['type'] == 'checkbox' ? !empty($v['ini']) : ''));
				//$formconfig['additionnal'][$k][''] = $v[''];
			}
		}
	}
	if (isset($_POST['formconfig']['additionnal_add'])) {
		$formconfig['additionnal'][] = array('type' => 'text', 'caption' => 'Nouveau champ :', 'ini' => '');
	}
	
	$formconfig['body_required'] = isset($_POST['formconfig']['body_required']);
	$formconfig['body_caption'] = $_POST['formconfig']['body_caption'];
	$formconfig['file_enabled'] = isset($_POST['formconfig']['file_enabled']);
	$formconfig['file_caption'] = $_POST['formconfig']['file_caption'];
	$formconfig['antispam_enabled'] = isset($_POST['formconfig']['antispam_enabled']);
	$formconfig['antispam_caption'] = $_POST['formconfig']['antispam_caption'];
	$formconfig['preview_enabled'] = isset($_POST['formconfig']['preview_enabled']);
	$formconfig['preview_caption'] = $_POST['formconfig']['preview_caption'];
	$formconfig['send_caption'] = $_POST['formconfig']['send_caption'];
	$formconfig['emailsenttext'] = $_POST['formconfig']['emailsenttext'];
	$formconfig['mimemail'] = isset($_POST['formconfig']['mimemail']);

	$core->blog->settings->addNamespace('contact');
	$core->blog->settings->contact->put('contact_formconfig',str_replace("\n", '\\n', str_replace("\r", '\\r', serialize($formconfig))),'string');
	$core->blog->triggerBlog();
	
	$infos[] = __('Configuration successfully updated');
} elseif (isset($_POST['adapttpl_try']) && !empty($_POST['adapttpl_sourcetpl'])) {
	if (file_exists($core->blog->themes_path.'/'.$_POST['adapttpl_sourcetpl'].'/post.html')) {
		if (is_readable($core->blog->themes_path.'/'.$_POST['adapttpl_sourcetpl'].'/post.html')) {
			$fc = file_get_contents($core->blog->themes_path.'/'.$_POST['adapttpl_sourcetpl'].'/post.html');
			$formcode = file_get_contents(dirname(__FILE__).'/contact.html.in');
			$fc = preg_replace('/<div id="main">[\S\s]*<div id="sidebar">/', '<div id="main">'."\n".'<div id="content">'."\n\n".$formcode."\n\n".'</div>'."\n".'</div>'."\n\n".'<div id="sidebar">', $fc);
			
			$fc = preg_replace('/<title>.*<\/title>/', '<title>{{tpl:ContactPageTitle}}</title>', $fc);
			$fc = preg_replace('/<tpl:Entry((?!<tpl)[a-zA-Z0-9\s_:="<>{}\[\]\.\/\\-])*<\/tpl:Entry((?!<)[a-zA-Z0-9_-])*>/', '', $fc);
			$fc = preg_replace('/{{tpl:Entry[a-zA-Z0-9\s_:-="]*}}/', '', $fc);
			
			if ((file_exists(dirname(__FILE__).'/contact.'.$core->blog->id.'.html') && is_writable(dirname(__FILE__).'/contact.'.$core->blog->id.'.html')) || is_writable(dirname(__FILE__))) {
				$fp = fopen(dirname(__FILE__).'/contact.'.$core->blog->id.'.html', 'w');
				fputs($fp, $fc);
				fclose($fp);
				
				$infos[] = __('Template adapted');
			} else {
				$errors[] = __('The plugin file <ins>contact.'.$core->blog->id.'.html</ins> is not writable');
			}
		} else {
			$errors[] = __('The theme file <ins>post.html</ins> is not readable');
		}
	} else {
		$errors[] = __('Cannot adapt page to this template');
	}
}

$part = empty($_GET['part']) ? 'general' : ($_GET['part'] == 'recipients' ? 'recipients' : ($_GET['part'] == 'display' ? 'display' : 'general'));
?>
<html>
<head>
<title><?php echo __('Contact'); ?></title>
  <script type="text/javascript">
  //<![CDATA[
	function recipients_form_add() {
		var recipients_recname = prompt('<?php echo html::escapeJS(__('Name :')); ?>', '');
		if (recipients_recname != '' && recipients_recname != undefined) {
			var recipients_recemail = prompt('<?php echo html::escapeJS(__('Email :')); ?>', '');
			if (recipients_recemail != '' && recipients_recemail != undefined) {
				document.getElementById('recipients_form').recipients_action.value = 'add';
				document.getElementById('recipients_form').recipients_recname.value = recipients_recname;
				document.getElementById('recipients_form').recipients_recemail.value = recipients_recemail;
				document.getElementById('recipients_form').submit();
			}
		}
	}
	function recipients_form_delete(recipients_recid) {
		if (confirm('<?php echo html::escapeJS(__('Are you sure you want to delete this recipient ?')); ?>')) {
			document.getElementById('recipients_form').recipients_action.value = 'delete';
			document.getElementById('recipients_form').recipients_recid.value = recipients_recid;
			document.getElementById('recipients_form').submit();
		}
	}
	function recipients_form_edit(recipients_recid, recipientname, recipientemail) {
		var recipients_recname = prompt('<?php echo html::escapeJS(__('Name :')); ?>', recipientname);
		if (recipients_recname != '' && recipients_recname != undefined) {
			var recipients_recemail = prompt('<?php echo html::escapeJS(__('Email :')); ?>', recipientemail);
			if (recipients_recemail != '' && recipients_recemail != undefined) {
				document.getElementById('recipients_form').recipients_action.value = 'edit';
				document.getElementById('recipients_form').recipients_recid.value = recipients_recid;
				document.getElementById('recipients_form').recipients_recname.value = recipients_recname;
				document.getElementById('recipients_form').recipients_recemail.value = recipients_recemail;
				document.getElementById('recipients_form').submit();
			}
		}
	}
	function recipients_autofill() {
		document.getElementById('recipients_form').recipients_update.value = '';
		document.getElementById('recipients_form').recipients_autofill.value = 'true';
		document.getElementById('recipients_form').submit();
	}
  //]]>
  </script>
<?php
if (!empty($_GET['part'])) {
	$part = $_GET['part'] == 'template' ? 'template' :
		($_GET['part'] == 'display' ? 'display' :
			($_GET['part'] == 'recipients' ? 'recipients' :
				'general'));
} else {
	$part = 'general';
}
echo dcPage::jsPageTabs($part);
?>
</head>

<body>
<?php echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; '.'<span class="page-title">'.__('Contact').' - '.$core->plugins->moduleInfo('contact','version').'</span></h2>';

if (!empty($errors)) {
	echo '<div class="error">';
	foreach ($errors as $e) {
		echo $e.'<br />';
	}
	echo '</div>';
}

echo '<div id="general" title="'.__('General').'" class="multi-part">'.

'<h3>'.__('Information').'</h3>'.
'<p>'.
__('Contact is a plugin to add and personalize easily a contact form to your blog.').
'</p>'.

'<p>&nbsp;</p>'.
'<h3>'.__('Use').'</h3>'.
'<p>'.__('Your contact page is installed and available at :').'<br />'.
' <a href="'.$core->blog->url.'contact">'.$core->blog->url.'contact</a></p>'.
'<p>'.__('To make it available for your visitors, you have to active the <em>Contact</em> widget by dragging it into the navigation sidebar from the').
' <a href="plugin.php?p=widgets">'.__('the widget page').'</a> '.__('and click on the &quot;update sidebars&quot; button.').
'</p>';

if ($core->auth->isSuperAdmin()) {
echo
'<p>&nbsp;</p>'.
'<form action="'.$p_url.'" method="post">'.
'<h3>'.__('Restriction').'</h3>'.
'<p><label class="classic">'.__('Allow the plugin configuration to:').' '.
'<select name="adminaccess"><option value="0"'.($adminaccess == 0 ? ' selected="selected"' : '').'>'.__('All users').'</option><option value="1"'.($adminaccess == 1 ? ' selected="selected"' : '').'>'.__('Only administrators').'</option><option value="2"'.($adminaccess == 2 ? ' selected="selected"' : '').'>'.__('Only super administrators').'</option></select>'.
'</label> &nbsp; '.
'<input type="submit" name="adminaccess_update" value="'.__('save').'" />'.
$core->formNonce().
'</p>'.
'</form>';
}

echo '</div>';


echo '<div id="recipients" title="'.__('Recipients').'" class="multi-part">';

echo
'<form id="recipients_form" action="'.$p_url.'&amp;part=recipients" method="post">'.

'<h3>'.__('Recipient list').'</h3>'.
'<p>'.
$core->formNonce().
'<input type="hidden" name="recipients_autofill" value="" />'.
'<input type="hidden" name="recipients_update" value="true" />'.
'<input type="hidden" name="recipients_action" value="" />'.
'<input type="hidden" name="recipients_recid" value="" />'.
'<input type="hidden" name="recipients_recname" value="" />'.
'<input type="hidden" name="recipients_recemail" value="" />'.
__('Define here who is(are) going to be the email recipient(s).').'</p>'.
'<p>'.__('Please note that your visitors will not be able to see the email adresses.').'</p>'.
'<fieldset>'.
'<p>';

if (!empty($recipients)) {
	foreach ($recipients as $v) {
		echo
		'<span><strong>'.html::escapeHTML($v['name']).'</strong></span> &nbsp; <span style="color: #666;">&lt;'.html::escapeHTML($v['email']).'&gt;</span> &nbsp; &nbsp; '.
		'<a href="#" onclick="javascript:recipients_form_edit(\''.html::escapeJS($v['email']).'\', \''.html::escapeJS($v['name']).'\', \''.html::escapeJS($v['email']).'\'); return false;">'.__('edit').'</a> '.
		'<a href="#" onclick="javascript:recipients_form_delete(\''.html::escapeJS($v['email']).'\'); return false;">'.__('delete').'</a><br />';
	}
} else {
	echo '<span style="color: #666;"><em>'.__('There is no recipient yet.').'</em></span>';
	
	if ($core->auth->isSuperAdmin()) {
		echo
		'<br /><a href="#" onclick="javascript:recipients_autofill(); return false;">'.__('Try to import recipients from the blog users list').'</a>';
	}
}

echo
'</p>'.
'<p><a href="#" onclick="javascript:recipients_form_add(); return(false);">'.__('Add a recipient').'</a></p>'.
'</fieldset>'.
'<p>'.__('If there are two or more recipients, a list will ask the visitor who he or she wants to send the email to.').'</p>';
if (!empty($recipients)) {
	echo '<p>'.__('You can make a direct link to contact a specific recipient using an url such as:').'<br />'.
	'<a href="'.$core->blog->url.'contact/'.$recipients[0]['name'].'">'.$core->blog->url.'contact/'.$recipients[0]['name'].'</a></p>';
}
echo
'</form>';

echo '</div>';

echo '<div id="display" title="'.__('Displaying').'" class="multi-part">';

echo
'<form action="'.$p_url.'&amp;part=display" method="post" enctype="multipart/form-data">'.
'<h3>'.__('Contact page appearance').'</h3>'.

'<p>'.__('You can personalize your contact page appearance by setting your own texts and by enabling or not some fields.').'<br />'.
__('If needed, you can also edit the form code by editing the file').' <ins>plugins/contact/contact.'.$core->blog->id.'.html</ins>.</p>'.

'<p>'.__('You can use XHTML code.').'</p>'.

'<table style="width: 100%;">'.

'<tr><th colspan="2">'.__('Texts for your visitors').'</th></tr>'.

'<tr><td><label for="contact_formconfig_pagetitle">'.__('Page title &lt;title&gt;').'</label></td>'.
'<td><input type="text" id="contact_formconfig_pagetitle" name="formconfig[pagetitle]" value="'.html::escapeHTML($formconfig['pagetitle']).'" style="width: 200px;" /> &nbsp; '.
__('Use %N instead of your blog name.').'</td></tr>'.

'<tr><td><label for="contact_formconfig_h2text">'.__('Form title &lt;h2&gt;').'</label></td>'.
'<td><input type="text" id="contact_formconfig_h2text" name="formconfig[h2text]" value="'.html::escapeHTML($formconfig['h2text']).'" style="width: 200px;" /></td></tr>'.

'<tr><td><label for="contact_formconfig_infotext">'.__('Information text').'</label></td>'.
'<td><textarea id="contact_formconfig_infotext" name="formconfig[infotext]" cols="60" rows="6">'.html::escapeHTML($formconfig['infotext']).'</textarea></td></tr>'.

'<tr><td><label for="contact_formconfig_emailsenttext">'.__('Text after email is sent').'</label></td>'.
'<td><textarea id="contact_formconfig_emailsenttext" name="formconfig[emailsenttext]" cols="60" rows="6">'.html::escapeHTML($formconfig['emailsenttext']).'</textarea></td></tr>'.

'</table>'.

'<table style="width: 100%;">'.

'<tr><th colspan="2">'.__('Enable/Disable field').'</th>'.
'<th>'.__('Field caption').'</th></tr>'.

'<tr><td>&nbsp;</td>'.
'<td><label for="contact_formconfig_recipient_caption">'.__('Recipient select list').'</label></td>'.
'<td><input type="text" id="contact_formconfig_recipient_caption" name="formconfig[recipient_caption]" value="'.html::escapeHTML($formconfig['recipient_caption']).'" style="width: 200px;" /></td></tr>'.

'<tr><td><input type="checkbox" id="contact_formconfig_name_enabled" name="formconfig[name_enabled]"'.($formconfig['name_enabled'] ? ' checked="checked"' : '').' /></td>'.
'<td><label for="contact_formconfig_name_enabled">'.__('Visitor name').'</label></td>'.
'<td><input type="text" id="contact_formconfig_name_caption" name="formconfig[name_caption]" value="'.html::escapeHTML($formconfig['name_caption']).'" style="width: 200px;" />'.
' &nbsp; &nbsp; <input type="checkbox" id="contact_formconfig_name_required" name="formconfig[name_required]"'.($formconfig['name_required'] ? ' checked="checked"' : '').' /> '.
'<label for="contact_formconfig_name_required" class="classic">'.__('Required field').'</label></td></tr>'.

'<tr><td><input type="checkbox" id="contact_formconfig_email_enabled" name="formconfig[email_enabled]"'.($formconfig['email_enabled'] ? ' checked="checked"' : '').' /></td>'.
'<td><label for="contact_formconfig_email_enabled">'.__('Visitor email').'</label></td>'.
'<td><input type="text" id="contact_formconfig_email_caption" name="formconfig[email_caption]" value="'.html::escapeHTML($formconfig['email_caption']).'" style="width: 200px;" />'.
' &nbsp; &nbsp; <input type="checkbox" id="contact_formconfig_email_required" name="formconfig[email_required]"'.($formconfig['email_required'] ? ' checked="checked"' : '').' /> '.
'<label for="contact_formconfig_email_required" class="classic">'.__('Required field').'</label></td></tr>'.

'<tr><td><input type="checkbox" id="contact_formconfig_subject_enabled" name="formconfig[subject_enabled]"'.($formconfig['subject_enabled'] ? ' checked="checked"' : '').' /></td>'.
'<td><label for="contact_formconfig_subject_enabled">'.__('Email subject').'</label></td>'.
'<td><input type="text" id="contact_formconfig_subject_caption" name="formconfig[subject_caption]" value="'.html::escapeHTML($formconfig['subject_caption']).'" style="width: 200px;" />'.
' &nbsp; &nbsp; <input type="checkbox" id="contact_formconfig_subject_required" name="formconfig[subject_required]"'.($formconfig['subject_required'] ? ' checked="checked"' : '').' /> '.
'<label for="contact_formconfig_subject_required" class="classic">'.__('Required field').'</label></td></tr>'.

'<tr><td>&nbsp;</td>'.
'<td><label for="contact_formconfig_body_caption">'.__('Email body').'</label></td>'.
'<td><input type="text" id="contact_formconfig_body_caption" name="formconfig[body_caption]" value="'.html::escapeHTML($formconfig['body_caption']).'" style="width: 200px;" />'.
' &nbsp; &nbsp; <input type="checkbox" id="contact_formconfig_body_required" name="formconfig[body_required]"'.($formconfig['body_required'] ? ' checked="checked"' : '').' /> '.
'<label for="contact_formconfig_body_required" class="classic">'.__('Required field').'</label></td></tr>'.

'<tr><td><input type="checkbox" id="contact_formconfig_file_enabled" name="formconfig[file_enabled]"'.($formconfig['file_enabled'] ? ' checked="checked"' : '').' /></td>'.
'<td><label for="contact_formconfig_file_enabled">'.__('Email attached file').'</label></td>'.
'<td><input type="text" id="contact_formconfig_file_caption" name="formconfig[file_caption]" value="'.html::escapeHTML($formconfig['file_caption']).'" style="width: 200px;" /></td></tr>'.

'<tr><td><input type="checkbox" id="contact_formconfig_antispam_enabled" name="formconfig[antispam_enabled]"'.($gd2installed ? ($formconfig['antispam_enabled'] ? ' checked="checked"' : '') : ' disabled="disabled"').' /></td>'.
'<td><label for="contact_formconfig_antispam_enabled"'.(!$gd2installed ? ' style="color: #999;"' : '').'>'.__('Anti-spam code').'</label></td>'.
'<td><input type="text" id="contact_formconfig_antispam_caption" name="formconfig[antispam_caption]"'.(!$gd2installed ? ' disabled="disabled"' : '').' value="'.html::escapeHTML($formconfig['antispam_caption']).'" style="width: 200px;" />'.
' &nbsp; &nbsp; <span style="color: #999;">'.__('Requires GD 2 library or higher.').'</span></td></tr>'.

'<tr><td><input type="checkbox" id="contact_formconfig_preview_enabled" name="formconfig[preview_enabled]"'.($formconfig['preview_enabled'] ? ' checked="checked"' : '').' /></td>'.
'<td><label for="contact_formconfig_preview_enabled">'.__('Preview button').'</label></td>'.
'<td><input type="text" id="contact_formconfig_preview_caption" name="formconfig[preview_caption]" value="'.html::escapeHTML($formconfig['preview_caption']).'" style="width: 200px;" /></td></tr>'.

'<tr><td>&nbsp;</td>'.
'<td><label for="contact_formconfig_send_caption">'.__('Send button').'</label></td>'.
'<td><input type="text" id="contact_formconfig_send_caption" name="formconfig[send_caption]" value="'.html::escapeHTML($formconfig['send_caption']).'" style="width: 200px;" /></td></tr>'.

'</table>'.

'<p>&nbsp;</p>'.
'<p>'.__('Additionnal fields added to the form and sent in the email :').'</p>'.

'<table style="width: 100%;">'.
'<tr><th>&nbsp;</th>'.
'<th>'.__('Field caption').'</th>'.
'<th>'.__('Type').'</th>'.
'<th>'.__('Initial value').'</th></tr>';
if (empty($formconfig['additionnal'])) {
	echo '<tr><td colspan="4"><span style="color: #999;">'.__('No additionnal field for the moment.').'</span></td></tr>';
} else {
	foreach ($formconfig['additionnal'] as $k => $v) {
		echo
		'<tr><td><input type="checkbox" name="formconfig[additionnal]['.$k.'][dontdelete]" checked="checked" /></td>'.
		'<td><input type="text" name="formconfig[additionnal]['.$k.'][caption]" value="'.html::escapeHTML($v['caption']).'" style="width: 200px;" /></td>'.
		'<td><select name="formconfig[additionnal]['.$k.'][type]"><option value="text"'.($v['type'] == 'text' ? ' selected="selected"' : '').'>'.__('Text').'</option><!--<option value="select"'.($v['type'] == 'select' ? ' selected="selected"' : '').'>'.__('Select').'</option>--><option value="checkbox"'.($v['type'] == 'checkbox' ? ' selected="selected"' : '').'>'.__('Checkbox').'</option></select></td>'.
		'<td><input type="text" name="formconfig[additionnal]['.$k.'][ini]" value="'.html::escapeHTML($v['ini']).'" /></td></tr>';
	}
}
echo
'</table>'.
'<p><label class="classic"><input type="checkbox" name="formconfig[additionnal_add]" /> &nbsp;'.__('Add an additionnal field').'</label></p>'.

'<p>&nbsp;</p>'.

'<p><label class="classic"><input type="checkbox" name="formconfig[mimemail]"'.($formconfig['mimemail'] ? ' checked="checked"' : '').' /> '.
__('Send MIME compliant emails').'</label><br />'.
__('This options allows more flexible emails and attached files. Disable it may fix some sending problems.').'</p>'.

'<p><input type="submit" name="formconfig_update" value="'.__('save').'" />'.$core->formNonce().'</p>'.
'</form>'.

'</div>';


echo '<div id="template" title="'.__('Template').'" class="multi-part">'.

'<form action="'.$p_url.'&amp;part=template" method="post">'.
'<h3>'.__('Adaptation to the template').'</h3>'.

'<p>'.__('The contact page is adapted to the DotClear default theme. If you are using another template, the contact page may seem different than other pages.').'</p>'.
'<fieldset>'.
'<p>'.__('Warning : this will cancel the possible changes you could have made on the code of the file').' <ins>plugins/contact/contact.'.$core->blog->id.'.html</ins>.</p>'.

'<p>'.__('Your template :').' <select name="adapttpl_sourcetpl">';
$core->themes = new dcModules($core);
$core->themes->loadModules($core->blog->system->themes_path,null);
foreach ($core->themes->getModules() as $k => $v) {
	if (file_exists($core->blog->system->themes_path.'/'.$k.'/post.html')) {
		echo '<option value="'.html::escapeHTML($k).'"'.($k == $core->blog->settings->system->theme ? ' selected="selected"' : '').'>'.html::escapeHTML($v['name']).'</option>';
	}
}
echo '</select>'.
'<input type="submit" name="adapttpl_try" value="'.__('Try to adapt').'" />'.$core->formNonce().'</p>'.
'</fieldset>'.

'</form>';

echo '</div>';
?>
</body>
</html>
