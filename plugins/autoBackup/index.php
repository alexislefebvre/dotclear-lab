<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of autoBackup, a plugin for Dotclear.
# 
# Copyright (c) 2008 k-net
# http://www.k-netweb.net/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

// Need to be a super admin to access this plugin
if (!defined('DC_CONTEXT_ADMIN')) { exit; }


$config = autoBackup::getConfig();

if (isset($_POST['saveconfig'])) {
	$config['importexportclasspath'] = $_POST['importexportclasspath'];
	$config['backup_onfile'] = isset($_POST['backup_onfile']);
	$config['backup_onemail'] = isset($_POST['backup_onemail']);
	$config['backup_onfile_repository'] = $_POST['backup_onfile_repository'];
	$config['backup_onfile_compress_gzip'] = isset($_POST['backup_onfile_compress_gzip']);
	$config['backup_onfile_deleteprev'] = isset($_POST['backup_onfile_deleteprev']);
	$config['backup_onemail_adress'] = $_POST['backup_onemail_adress'];
	$config['backup_onemail_compress_gzip'] = isset($_POST['backup_onemail_compress_gzip']);
	$config['backup_onemail_header_from'] = $_POST['backup_onemail_header_from'];
	$config['backuptype'] = $core->auth->isSuperAdmin() && $_POST['backuptype'] == 'full' ? 'full' : 'blog';
	$config['backupblogid'] = $core->blog->id;#$_POST['backupblogid'];
	$config['interval'] = (int) $_POST['interval'];
	autoBackup::setConfig($config);
}

?>
<html>
<head>
<title><?php echo __('Auto Backup'); ?></title>
<script type="text/javascript">
//<![CDATA[
function toogle_backuptype(id) {
	if (id != undefined && id == 0) {
		//document.getElementById('backupblogid').disabled = 'disabled';
		document.getElementById('backupblogid_label').style.color = '#999';
	} else {
		//document.getElementById('backupblogid').disabled = '';
		document.getElementById('backupblogid_label').style.color = '#000';
	}
}
//]]>
</script>
</head>

<body>
<?php
echo '<h2>'.__('Auto Backup').'</h2>';

/*
$blogs_list = array();
foreach ($core->blogs as $k=>$v) {
	if ($core->auth->check('admin',$k)) {
		$blogs_list[html::escapeHTML($v['name']).' ('.$k.')'] = $k;
	}
}
//*/

$backuptypes = $core->auth->isSuperAdmin() ? array(__('All content export') => 'full', __('Blog export') => 'blog') : array(__('Blog export') => 'blog');

$intervals = array(
	__('disable') =>     0,
	'6 '.__('hours') =>  3600*6,
	'12 '.__('hours') => 3600*12,
	'24 '.__('hours') => 3600*24,
	'2 '.__('days') =>   3600*24*2,
	'7 '.__('days') =>   3600*24*7,
	'14 '.__('days') =>  3600*24*14,
	);
if (!in_array($config['interval'], array(0, 3600*6, 3600*12, 3600*24, 3600*24*2, 3600*24*7, 3600*24*14))) {
	$intervals[$config['interval'].' '.__('seconds')] = $config['interval'];
}

echo '<div id="general">'.

'<p>&nbsp;</p>'.
'<h3>'.__('Settings').'</h3>'.
'<p>'.__('Auto Backup allows you to create backups automatically and often.').'<br />'.
__('It uses the Import/Export plugin to work.').'</p>'.


'<form action="'.$p_url.'" method="post">'.

'<fieldset>'.

'<p><label>'.__('Import/Export plugin class path :').' '.
form::field('importexportclasspath',40,255,$config['importexportclasspath']).'</label>'.
(is_file($config['importexportclasspath']) ? '' : '<span style="color:#C00"><strong>'.__('Warning: this file doesn\'t exist!').'</strong></span>').
'</p>'.

'<p><label class="classic">'.form::checkbox('backup_onfile',1,$config['backup_onfile']).' '.
'<strong>'.__('Backup on file').'</strong></label><br />'.
'<label class="classic">'.__('Repository path :').' '.
form::field('backup_onfile_repository',40,255,$config['backup_onfile_repository']).'</label><br />'.
'<label class="classic">'.form::checkbox('backup_onfile_compress_gzip',1,$config['backup_onfile_compress_gzip']).' '.
__('Compress data with gzip').'</label><br />'.
'<label class="classic">'.form::checkbox('backup_onfile_deleteprev',1,$config['backup_onfile_deleteprev']).' '.
__('After creating the backup file, delete the previous one.').'</label></p>'.

'<p><label class="classic">'.form::checkbox('backup_onemail',1,$config['backup_onemail']).' '.
'<strong>'.__('Backup by email').'</strong></label><br />'.
'<label class="classic">'.__('Email address :').' '.
form::field('backup_onemail_adress',15,255,$config['backup_onemail_adress']).'</label><br />'.
'<label class="classic">'.form::checkbox('backup_onemail_compress_gzip',1,$config['backup_onemail_compress_gzip']).' '.
__('Compress data with gzip').'</label><br />'.
'<label class="classic">'.__('Email <em>From</em> header :').' '.
form::field('backup_onemail_header_from',30,255,$config['backup_onemail_header_from']).'</label>
</p>'.

'<p><label class="classic">'.__('Backup type :').' '.
form::combo('backuptype',$backuptypes,$config['backuptype'],'','',false,' onchange="javascript:toogle_backuptype(this.options.selectedIndex)"').'</label>&nbsp; '.
'<label id="backupblogid_label" class="classic"'.($config['backuptype'] == 'full' && $core->auth->isSuperAdmin() ? ' style="color:#999"' : '').'>'.__('Blog :').' '.
#form::combo('backupblogid',$blogs_list,$config['backupblogid'],'','',$config['backuptype'] == 'full').
'<strong>'.$core->blog->id.'</strong>'.
'</label></p>'.

'<p><label class="classic">'.__('Create a new backup every :').' '.
form::combo('interval',$intervals,$config['interval']).'</label></p>'.


'<p><input type="submit" name="saveconfig" value="'.__('Save').'" /></p>'.
'<p>'.$core->formNonce().'</p>'.
'</fieldset>'.
'</form>'.


'<p>&nbsp;</p>'.
'<h3>'.__('Last backups').'</h3>'.

'<p>'.__('Last backup on file :').'&nbsp; '.($config['backup_onfile_last']['date'] > 0 ? date('r', $config['backup_onfile_last']['date']) : '<em>'.__('never').'</em>').'<br />'.
__('File name :').'&nbsp; <abbr title="'.html::escapeHTML($config['backup_onfile_last']['file']).'">'.html::escapeHTML(basename($config['backup_onfile_last']['file'])).'</abbr>'.'</p>'.

'<p>'.__('Last backup by email :').'&nbsp; '.($config['backup_onemail_last']['date'] > 0 ? date('r', $config['backup_onemail_last']['date']) : '<em>'.__('never').'</em>').'</p>';


echo '</div>';

?>
</body>
</html>