<?php
# ***** BEGIN LICENSE BLOCK *****
# This is Smilies Manager, a plugin for DotClear. 
# Copyright (c) 2005 k-net. All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!$core->auth->isSuperAdmin()) { exit; }


$errors = array();
$infos = array();


if (!empty($_GET['theme']) && is_dir($core->blog->themes_path.'/'.$_GET['theme'])) {
	$theme = $_GET['theme'];
} elseif ($core->blog->settings->theme != 'default' && file_exists($core->blog->themes_path.'/'.$core->blog->settings->theme.'/smilies/smilies.txt')) {
	$theme = $core->blog->settings->theme;
} else {
	$theme = 'default';
}

$p_url .= '&amp;theme='.$theme;


$smilies = smiliesManager::getSmilies($theme);

$smilies_codes = array();
foreach ($smilies as $k => $smiley) {
	$smilies_codes[] = $smiley['code'];
}


$toolbarTpl = $core->blog->settings->smiliesmanager_toolbartpl;
if (empty($toolbarTpl)) {
	$toolbarTpl = '<p class="field"><label>'.htmlspecialchars(__('Smilies')).'&nbsp;:</label><span style="display: block; overflow: hidden;">%s</span></p>';
	$core->blog->settings->setNamespace('smiliesmanager');
	$core->blog->settings->put('smiliesmanager_toolbartpl',$toolbarTpl,'string');
	$core->blog->triggerBlog();
}

if (isset($_POST['autoEditTpl_uninstall']) && !empty($_POST['autoEditTpl_theme'])) {
	if (smiliesManagerAdmin::autoEditTpl_uninstall($_POST['autoEditTpl_theme'])) {
		$infos[] = __('Template restored successfully');
	} else {
		$errors[] = __('Cannot restore template');
	}
} elseif (isset($_POST['autoEditTpl_install']) && !empty($_POST['autoEditTpl_theme'])) {
	if (smiliesManagerAdmin::autoEditTpl_install($_POST['autoEditTpl_theme'])) {
		$infos[] = __('Template adapted successfully');
	} else {
		$errors[] = __('Cannot adapt this template');
	}
} elseif (isset($_POST['editSmilies_onToolbar_save'])) {
	foreach ($smilies as $k => $smiley) {
		if (isset($_POST['editSmilies_onToolbar'][$k])) {
			$smilies[$k]['onToolbar'] = true;
		} else {
			$smilies[$k]['onToolbar'] = false;
		}
	}
	if (smiliesManagerAdmin::setConfig($smilies)) {
		$infos[] = __('Settings saved successfully');
	} else {
		$errors[] = __('Cannot save settings');
	}
} elseif (isset($_POST['toolbarTpl_save']) && !empty($_POST['toolbarTpl_content'])) {
	$toolbarTpl = $_POST['toolbarTpl_content'];
	$core->blog->settings->setNamespace('smiliesmanager');
	$core->blog->settings->put('smiliesmanager_toolbartpl',$toolbarTpl,'string');
	$core->blog->triggerBlog();
} elseif (!empty($_POST['editSmilies_create_smileyCode'])) {
	if (!is_dir($core->blog->themes_path.'/'.$theme.'/smilies') && !mkdir($core->blog->themes_path.'/'.$theme.'/smilies')) {
		$errors[] = __('Cannot create the smilies folder');
	}
	if (!in_array($_POST['editSmilies_create_smileyCode'], $smilies_codes)) {
		if (!empty($_POST['editSmilies_create_filename'])) {
			$smilies[] = array(
				'code' => trim(stripslashes($_POST['editSmilies_create_smileyCode'])),
				'url' => $core->blog->settings->themes_url.'/'.$theme.'/smilies/'.stripslashes($_POST['editSmilies_create_filename']));
			if (smiliesManagerAdmin::setSmilies($theme, $smilies)) {
				$infos[] = __('Smiley created successfully');
			} else {
				$errors[] = __('Cannot create smiley');
			}
		} elseif (isset($_FILES['editSmilies_create_uploadFile']['name'])) {
			if (copy($_FILES['editSmilies_create_uploadFile']['tmp_name'], $core->blog->themes_path.'/'.$theme.'/smilies/'.$_FILES['editSmilies_create_uploadFile']['name'])) {
				$smilies[] = array(
					'code' => trim(stripslashes($_POST['editSmilies_create_smileyCode'])),
					'url' => $core->blog->settings->themes_url.'/'.$theme.'/smilies/'.stripslashes($_FILES['editSmilies_create_uploadFile']['name']));
				if (smiliesManagerAdmin::setSmilies($theme, $smilies)) {
					$infos[] = __('Smiley created successfully');
				} else {
					$errors[] = __('Cannot create smiley');
				}
			} else {
				$errors[] = __('Cannot upload image');
			}
		}
	} else {
		$errors[] = __('A smiley with this code already exists');
	}
} elseif (isset($_POST['editSmilies_edit_smileyId']) && !empty($_POST['editSmilies_edit_smileyCode'])) {
	if (!empty($smilies[$_POST['editSmilies_edit_smileyId']])) {
		if ($_POST['editSmilies_edit_smileyCode'] == $smilies[$_POST['editSmilies_edit_smileyId']]['code'] || !in_array($_POST['editSmilies_edit_smileyCode'], $smilies_codes)) {
			$bak = $smilies[$_POST['editSmilies_edit_smileyId']];
			if (!empty($_POST['editSmilies_edit_filename'])) {
				$smilies[$_POST['editSmilies_edit_smileyId']] = array(
					'code' => trim(stripslashes($_POST['editSmilies_edit_smileyCode'])),
					'url' => $core->blog->settings->themes_url.'/'.$theme.'/smilies/'.stripslashes($_POST['editSmilies_edit_filename']));
				if (smiliesManagerAdmin::setSmilies($theme, $smilies)) {
					$infos[] = __('Smiley edited successfully');
				} else {
					$errors[] = __('Cannot edit smiley');
					$smilies[$_POST['editSmilies_edit_smileyId']] = $bak;
				}
			} elseif (isset($_FILES['editSmilies_edit_uploadFile']['name'])) {
				if (copy($_FILES['editSmilies_edit_uploadFile']['tmp_name'], $core->blog->themes_path.'/'.$theme.'/smilies/'.$_FILES['editSmilies_edit_uploadFile']['name'])) {
					$smilies[$_POST['editSmilies_edit_smileyId']] = array(
						'code' => trim(stripslashes($_POST['editSmilies_edit_smileyCode'])),
						'url' => $core->blog->settings->themes_url.'/'.$theme.'/smilies/'.stripslashes($_FILES['editSmilies_edit_uploadFile']['name']));
					if (smiliesManagerAdmin::setSmilies($theme, $smilies)) {
						$infos[] = __('Smiley edited successfully');
					} else {
						$errors[] = __('Cannot edit smiley');
						$smilies[$_POST['editSmilies_edit_smileyId']] = $bak;
					}
				} else {
					$errors[] = __('Cannot upload image');
				}
			}
			unset($bak);
		} else {
			$errors[] = __('A smiley with this code already exists');
		}
	}
} elseif (isset($_POST['editSmilies_delete_smileyId'])) {
	if (!empty($smilies[$_POST['editSmilies_delete_smileyId']])) {
		if (!empty($_POST['editSmilies_delete_deleteFile'])) {
			if (unlink($core->blog->themes_path.'/'.$theme.'/smilies/'.basename($smilies[$_POST['editSmilies_delete_smileyId']]['url']))) {
				$infos[] = __('File deleted successfully');
			} else {
				$errors[] = __('Cannot delete file');
			}
		}
		$bak = $smilies[$_POST['editSmilies_delete_smileyId']];
		unset($smilies[$_POST['editSmilies_delete_smileyId']]);
		if (smiliesManagerAdmin::setSmilies($theme, $smilies)) {
			$infos[] = __('Smiley deleted successfully');
		} else {
			$errors[] = __('Cannot delete smiley');
			$smilies[$_POST['editSmilies_delete_smileyId']] = $bak;
		}
		unset($bak);
		$smilies = array_values($smilies);
	}
} elseif (isset($_POST['editSmilies_smileyUp']) && $_POST['editSmilies_smileyUp'] != '' && $_POST['editSmilies_smileyUp'] > 0) {
	if (isset($smilies[$_POST['editSmilies_smileyUp']])) {
		$tmp = $smilies[$_POST['editSmilies_smileyUp']-1];
		$smilies[$_POST['editSmilies_smileyUp']-1] = $smilies[$_POST['editSmilies_smileyUp']];
		$smilies[$_POST['editSmilies_smileyUp']] = $tmp;
	}
	smiliesManagerAdmin::setSmilies($theme, $smilies);
} elseif (isset($_POST['editSmilies_smileyDown']) && $_POST['editSmilies_smileyDown'] != '' && $_POST['editSmilies_smileyDown'] < count($smilies)-1) {
	if (isset($smilies[$_POST['editSmilies_smileyDown']])) {
		$tmp = $smilies[$_POST['editSmilies_smileyDown']+1];
		$smilies[$_POST['editSmilies_smileyDown']+1] = $smilies[$_POST['editSmilies_smileyDown']];
		$smilies[$_POST['editSmilies_smileyDown']] = $tmp;
	}
	smiliesManagerAdmin::setSmilies($theme, $smilies);
} elseif (isset($_POST['toolbarAdmin_on'])) {
	$core->blog->settings->setNamespace('smiliesmanager');
	$core->blog->settings->put('smiliesmanager_admintoolbar',true,'boolean');
	$core->blog->triggerBlog();
	$core->blog->settings->smiliesmanager_admintoolbar = true;
} elseif (isset($_POST['toolbarAdmin_off'])) {
	$core->blog->settings->setNamespace('smiliesmanager');
	$core->blog->settings->put('smiliesmanager_admintoolbar',false,'boolean');
	$core->blog->triggerBlog();
	$core->blog->settings->smiliesmanager_admintoolbar = false;
}

?>
<html>
<head>
<title><?php echo __('Smilies Manager'); ?></title>
  <script type="text/javascript">
  //<![CDATA[
	var smilies = new Array(
<?php
foreach ($smilies as $k => $smiley) {
	echo ($k > 0 ? ",\n\t\t" : "\t\t").'new Array(\''.html::escapeJS($smiley['code']).'\', \''.html::escapeJS($smiley['url']).'\', \''.html::escapeJS(basename($smiley['url'])).'\', '.($smiley['onToolbar'] ? 'true' : 'false').')';
}
?>);
	function editSmilies_create_enableArea() {
		document.getElementById('editSmilies_create_form').style.display = 'block';
		document.getElementById('editSmilies_edit_form').style.display = 'none';
		document.getElementById('editSmilies_delete_form').style.display = 'none';
	}
	function editSmilies_edit_enableArea(smileyId) {
		document.getElementById('editSmilies_create_form').style.display = 'none';
		document.getElementById('editSmilies_edit_form').style.display = 'block';
		document.getElementById('editSmilies_delete_form').style.display = 'none';
		
		document.getElementById('editSmilies_edit_smileyId').value = smileyId;
		document.getElementById('editSmilies_edit_smileyCode').value = smilies[smileyId][0];
		for (i=0; i<document.getElementById('editSmilies_edit_filename').options.length; i++) {
			if (document.getElementById('editSmilies_edit_filename').options[i].value == smilies[smileyId][2]) {
				document.getElementById('editSmilies_edit_filename').selectedIndex = i;
				break;
			}
		}
		document.getElementById('editSmilies_edit_previewArea').innerHTML = smilies[smileyId][0]+' &nbsp; <img src="'+smilies[smileyId][1]+'" alt="" /> &nbsp; '+smilies[smileyId][2];
	}
	function editSmilies_delete_enableArea(smileyId) {
		document.getElementById('editSmilies_create_form').style.display = 'none';
		document.getElementById('editSmilies_edit_form').style.display = 'none';
		document.getElementById('editSmilies_delete_form').style.display = 'block';
		
		document.getElementById('editSmilies_delete_smileyId').value = smileyId;
		document.getElementById('editSmilies_delete_previewArea').innerHTML = smilies[smileyId][0]+' &nbsp; <img src="'+smilies[smileyId][1]+'" alt="" /> &nbsp; '+smilies[smileyId][2];
	}
	function editSmilies_displayList() {
		text = '';
		for (i=0; i < smilies.length; i++) {
			text += 
			'<tr><td style="font-size:2px">'+(i > 0 ? '<a href="#" onclick="javascript:editSmilies_smileyUp('+i+');return false;" style="border:none;"><img src="index.php?pf=smiliesManager/up.png" alt="↑" width="12" height="6" title="<?php echo __('up'); ?>" /></a>' : '&nbsp;')+'<br />'+
			(i < smilies.length-1 ? '<a href="#" onclick="javascript:editSmilies_smileyDown('+i+');return false;" style="border:none;"><img src="index.php?pf=smiliesManager/down.png" alt="↓" width="12" height="6" title="<?php echo __('down'); ?>" /></a>' : '&nbsp;')+'</td>'+
			'<td><input type="checkbox" name="editSmilies_onToolbar['+i+']"'+(smilies[i][3] == true ? ' checked="checked"' : '')+' /> &nbsp; '+smilies[i][0]+'</td>'+
			'<td><img src="'+smilies[i][1]+'" alt="'+smilies[i][0]+'" /></td>'+
			'<td>'+smilies[i][2]+'</td>'+
			'<td><a href="#" onclick="javascript:editSmilies_edit_enableArea('+i+'); return false;"><?php echo html::escapeJS(__('edit')); ?></a> '+
			'<a href="#" onclick="javascript:editSmilies_delete_enableArea('+i+'); return false;"><?php echo html::escapeJS(__('delete')); ?></a></td></tr>';
		}
		document.getElementById('editSmilies_listContener').innerHTML = text;
	}
	function editSmilies_smileyUp(smileyId) {
		if (smileyId > 0) {
			document.getElementById('editSmilies_smileyUp').value = smileyId;
			document.getElementById('editSmilies_action').submit();
		}
	}
	function editSmilies_smileyDown(smileyId) {
		if (smileyId < smilies.length-1) {
			document.getElementById('editSmilies_smileyDown').value = smileyId;
			document.getElementById('editSmilies_action').submit();
		}
	}
  //]]>
  </script>
<?php
$part = empty($_GET['part']) ? 'smilies' : ($_GET['part'] == 'template' ? 'template' : 'smilies');
echo version_compare(DC_VERSION, '2.0-beta4') < 0 ? dcPage::jsMultiPartPage($part) : dcPage::jsPageTabs($part);
?>
</head>

<body>
<?php
echo '<h2>'.__('Smilies Manager').'</h2>';

if (!empty($errors)) {
	echo '<div class="error">';
	foreach ($errors as $e) {
		echo $e.'<br />';
	}
	echo '</div>';
}
if (!empty($infos)) {
	echo '<div class="message">';
	foreach ($infos as $i) {
		echo $i.'<br />';
	}
	echo '</div>';
}


echo
'<p>&nbsp;</p>'.
'<form id="changeTheme_form" action="'.$p_url.'" method="get">'.
'<fieldset>'.
'<p>'.__('Manage smilies from theme :').' <input type="hidden" name="p" value="smiliesManager" />'.
'<select name="theme" onchange="javascript:document.getElementById(\'changeTheme_form\').submit();">';
$core->themes = new dcModules($core);
$core->themes->loadModules($core->blog->themes_path,null);
foreach ($core->themes->getModules() as $k => $v) {
	if (is_dir($core->blog->themes_path.'/'.$k)) {
		echo '<option value="'.html::escapeHTML($k).'"'.($k == $theme ? ' selected="selected"' : '').'>'.html::escapeHTML($v['name']).'</option>';
	}
}

echo
'</select>'.$core->formNonce().
'</p>'.
'</fieldset>'.
'</form>';



echo '<div id="smilies" title="'.__('Smilies').'" class="multi-part">'.

'<h3>'.__('Installed smilies').'</h3>'.
'<p>'.__('Here are the smilies installed on the theme :').'</p>';

if (empty($smilies)) {
	echo '<p style="color: #999;"><em>'.__('No smiley on this theme : those from Blue Silence theme are used instead.').'</em></p>';
} else {
	echo
	'<form id="editSmilies_action" action="'.$p_url.'" method="post">'.
	'<p><input type="hidden" id="editSmilies_smileyUp" name="editSmilies_smileyUp" value="" /><input type="hidden" id="editSmilies_smileyDown" name="editSmilies_smileyDown" value="" />'.$core->formNonce().'</p>'.
	'</form>';
	
	echo
	'<form action="'.$p_url.'" method="post"><fieldset><table id="editSmilies_listContener">';
?>
<?php /*<script type="text/javascript">
//<![CDATA[
document.write('<table>');
for (i=0; i < smilies.length; i++) {
	document.write(
	'<tr><td><input type="checkbox" name="editSmilies_onToolbar['+i+']"'+(smilies[i][3] == true ? ' checked="checked"' : '')+' /> &nbsp; '+smilies[i][0]+'</td>'+
	'<td><img src="'+smilies[i][1]+'" alt="'+smilies[i][0]+'" /></td>'+
	'<td>'+smilies[i][2]+'</td>'+
	'<td><a href="#" onclick="javascript:editSmilies_edit_enableArea('+i+'); return false;"><?php echo html::escapeJS(__('edit')); ?></a> '+
	'<a href="#" onclick="javascript:editSmilies_delete_enableArea('+i+'); return false;"><?php echo html::escapeJS(__('delete')); ?></a></td></tr>'
	);
}
document.write('</table>');
//]]>
</script>*/ ?>
<script type="text/javascript">
//<![CDATA[
editSmilies_displayList();
//]]>
</script>
<?php
	echo
	'</table>'.
	
	'<p>'.__('Check the smilies you want to appear on the template smilies tool bar.').'</p>'.
	'<p><input type="submit" name="editSmilies_onToolbar_save" value="'.__('Save').'" />'.$core->formNonce().'</p>'.
	'</fieldset></form>';
}

echo
'<p><a href="#" onclick="javascript:editSmilies_create_enableArea(); return false;">'.__('Create a smiley').'</a></p>';


if (is_dir($core->blog->themes_path.'/'.$theme.'/smilies')) {
	$smilies_files = '';
	
	$handle = opendir($core->blog->themes_path.'/'.$theme.'/smilies');
	while ($file = readdir($handle)) {
		if (is_file($core->blog->themes_path.'/'.$theme.'/smilies/'.$file)
		&& in_array(files::getExtension($file), array('png', 'jpg', 'jpeg', 'gif', 'bmp'))) {
			$smilies_files .= '<option value="'.html::escapeHTML($file).'">'.html::escapeHTML($file).'</option>';
		}
	}
}

echo
'<p>&nbsp;</p>'.
'<form id="editSmilies_create_form" action="'.$p_url.'" enctype="multipart/form-data" method="post">'.
'<h3>'.__('Create a smiley').'</h3>'.
'<fieldset>'.
'<p><label class="classic">'.__('Smiley code').'&nbsp;: <input type="text" name="editSmilies_create_smileyCode" value="" /></label></p>'.
'<p><label class="classic">'.__('Smiley image').'&nbsp;: ';
if (!empty($smilies_files)) {
	echo
	'<select name="editSmilies_create_filename"><option value="">&nbsp;</option>'.$smilies_files.'</select> '.__('or').' ';
}
echo
'<input type="file" name="editSmilies_create_uploadFile" />'.
'</label></p>'.
'<p><input type="submit" value="'.__('Send').'" />'.$core->formNonce().'</p>'.
'</fieldset>'.
'</form>';

echo
'<form id="editSmilies_edit_form" action="'.$p_url.'" enctype="multipart/form-data" method="post">'.
'<h3>'.__('Edit a smiley').'</h3>'.
'<fieldset>'.
'<p id="editSmilies_edit_previewArea"></p>'.
'<p><label class="classic">'.__('Smiley code').'&nbsp;: <input type="text" id="editSmilies_edit_smileyCode" name="editSmilies_edit_smileyCode" value="" /></label></p>'.
'<p><label class="classic">'.__('Smiley image').'&nbsp;: ';
if (!empty($smilies_files)) {
	echo
	'<select id="editSmilies_edit_filename" name="editSmilies_edit_filename"><option value="">&nbsp;</option>'.$smilies_files.'</select> '.__('or').' ';
}
echo
'<input type="file" name="editSmilies_edit_uploadFile" /></label>'.
'<input type="hidden" id="editSmilies_edit_smileyId" name="editSmilies_edit_smileyId" value="" />'.
'</p>'.
'<p><input type="submit" value="'.__('Send').'" />'.$core->formNonce().'</p>'.
'</fieldset>'.
'</form>';

echo
'<form id="editSmilies_delete_form" action="'.$p_url.'" method="post">'.
'<h3>'.__('Delete a smiley').'</h3>'.
'<fieldset>'.
'<p id="editSmilies_delete_previewArea"></p>'.
'<p>'.__('Are you sure you want to delete this smiley ?').'</p>'.
'<p><label class="classic"><input type="checkbox" name="editSmilies_delete_deleteFile" /> '.__('Also delete the file').'</label>'.
'<input type="hidden" id="editSmilies_delete_smileyId" name="editSmilies_delete_smileyId" value="" />'.
'</p>'.
'<p><input type="submit" value="'.__('Delete').'" />'.$core->formNonce().'</p>'.
'</fieldset>'.
'</form>';

echo
'</div>';
?>
<script type="text/javascript">
//<![CDATA[
editSmilies_create_enableArea();
//]]>
</script>
<?php



$tplfile_theme = $theme;
if ($tplfile_theme != 'default' && !file_exists($core->blog->themes_path.'/'.$tplfile_theme.'/post.html')) {
	$tplfile_theme = 'default';
}
$tplfile = 'themes/'.$tplfile_theme.'/post.html';

echo '<div id="template" title="'.__('Template').'" class="multi-part">'.

'<form action="'.$p_url.'&amp;part=template" method="post">'.
'<h3>'.__('Adapt the template').'</h3>'.
'<p>'.__('Smilies Manager also allows you to adapt the template in order to display the smilies list above the comment text field, like this :').'</p>'.
'<p><img src="index.php?pf=smiliesManager/toolbar-demo.png" alt="demo" width="447" height="131" style="border: 1px solid #CCA;" /></p>';

if (smiliesManagerAdmin::autoEditTpl_isInstalled($tplfile_theme) === false) {
	echo
	'<p>'.sprintf(__('To insert this clickable smilies bar into the template, the file <ins>%s</ins> needs to be edited.'), $tplfile).'</p>'.
	'<p><input type="hidden" name="autoEditTpl_theme" value="'.$tplfile_theme.'" />'.
	__('To modify this file automatically, click on this button').'&nbsp;: <input type="submit" name="autoEditTpl_install" value="'.__('Adapt').'" /></p>'.
	'<p>'.__('If you prefer to make the modification by yourself, follow the instructions bellow').'&nbsp;:</p>'.
	'<fieldset>'.
	'<p>'.sprintf(__('Open the file <ins>%s</ins>.'), $tplfile).' '.__('Search the following code').'&nbsp;:</p>'.
	'<pre>&lt;p class=&quot;field&quot;&gt;&lt;label for=&quot;c_content&quot;&gt;</pre>'.
	'<p>'.__('Just before, add').'&nbsp;:</p>'.
	'<pre>{{tpl:SmiliesManagerToolbar textarea=&quot;c_content&quot;}}</pre>'.
	'<p>'.__('That\'s all !').'</p>'.
	'</fieldset>';
} else {
	echo
	'<p>'.sprintf(__('The template file <ins>%s</ins> seems to be already adapted.'), $tplfile).'</p>'.
	'<p><input type="hidden" name="autoEditTpl_theme" value="'.$tplfile_theme.'" />'.
	__('To restore this file automatically, click on this button').'&nbsp;: <input type="submit" name="autoEditTpl_uninstall" value="'.__('Restore').'" /></p>'.
	'<p>'.__('If you prefer to make the modification by yourself, follow the instructions bellow').'&nbsp;:</p>'.
	'<fieldset>'.
	'<p>'.sprintf(__('Open the file <ins>%s</ins>.'), $tplfile).' '.__('Search the following code').'&nbsp;:</p>'.
	'<pre>{{tpl:SmiliesManagerToolbar textarea=&quot;c_content&quot;}}</pre>'.
	'<p>'.__('Delete it.').' '.__('That\'s all !').'</p>'.
	'</fieldset>';
}

echo
'<p>&nbsp;</p>'.
'<h3>'.__('Personalize the smilies tool bar').'</h3>'.
'<p>'.__('You can edit the smilies tool bar XHTML code to personalize it.').'</p>'.
'<fieldset>'.
'<p><textarea name="toolbarTpl_content" cols="40" rows="10" style="width: 100%; height: 70px;">'.html::escapeHTML($toolbarTpl).'</textarea></p>'.
'<p>'.__('<strong>%s</strong> representes the clickable smilies location.').'</p>'.
'<p><input type="submit" name="toolbarTpl_save" value="'.__('Save').'" />'.$core->formNonce().'</p>'.
'</fieldset>';



echo
'<p>&nbsp;</p>'.

'<h3>'.__('Toolbar in admin').'</h3>'.
'<p>'.__('You can also display the clickable smilies toolbar in administration.').'</p>'.
'<p><input type="submit" name="toolbarAdmin_'.($core->blog->settings->smiliesmanager_admintoolbar ? 'off' : 'on').'" value="'.__($core->blog->settings->smiliesmanager_admintoolbar ? 'Disable' : 'Enable').'" /></p>';



echo
'</form>';

echo '</div>';

?>
</body>
</html>