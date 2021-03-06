<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Upload Updater" plugin.
#
# Copyright (c) 2003-2010 DC Team
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

require_once dirname(__FILE__)."/class.dc.update.local.php";

$updater = new dcUpdateLocal('uu_dotclear.zip','uu_info',DC_TPL_CACHE.'/versions');
$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : 'check';

$step = in_array($step,array('check','upload','backup','unzip')) ? $step : '';

$archives = array();
foreach (files::scanDir(DC_ROOT) as $v) {
	if (preg_match('/backup-([0-9A-Za-z\.-]+).zip/',$v)) {
		$archives[] = $v;
	}
}

# Revert or delete backup file
if (!empty($_POST['backup_file']) && in_array($_POST['backup_file'],$archives))
{
	$b_file = $_POST['backup_file'];
	
	try
	{
		if (!empty($_POST['b_del']))
		{
			if (!@unlink(DC_ROOT.'/'.$b_file)) {
				throw new Exception(sprintf(__('Unable to delete file %s'),html::escapeHTML($b_file)));
			}
			http::redirect($p_url);
		}
		
		if (!empty($_POST['b_revert']))
		{
			$zip = new fileUnzip(DC_ROOT.'/'.$b_file);
			$zip->unzipAll(DC_ROOT.'/');
			@unlink(DC_ROOT.'/'.$b_file);
			http::redirect($p_url);
		}
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Upgrade process
if ($step != '')
{
	try
	{
		$updater->setForcedFiles('inc/digests');
		
		switch ($step)
		{
			case 'check':
				$updater->checkIntegrity(DC_ROOT.'/inc/digests',DC_ROOT);
				break;
			case 'upload':
				$updater->do_upload($_FILES['upfile']);
				http::redirect($p_url.'&step=backup');
				break;
			case 'backup':
				$updater->backup(
					'dotclear/inc/digests',
					DC_ROOT, DC_ROOT.'/inc/digests',
					DC_ROOT.'/backup-'.DC_VERSION.'.zip'
				);
				http::redirect($p_url.'&step=unzip');
				break;
			case 'unzip':
				$updater->performUpgrade(
					'dotclear/inc/digests', 'dotclear',
					DC_ROOT, DC_ROOT.'/inc/digests'
				);
				break;
		}
	}
	catch (Exception $e)
	{
		$msg = $e->getMessage();
		if ($e->getCode() == dcUpdate::ERR_FILES_CHANGED)
		{
			$msg =
			__('The following files of your Dotclear installation '.
			'have been modified so we won\'t try to update your installation. '.
			'Please try to <a href="http://dotclear.org/download">update manually</a>.');
		}
		elseif ($e->getCode() == dcUpdate::ERR_FILES_UNREADABLE)
		{
			$msg =
			sprintf(__('The following files of your Dotclear installation are not readable. '.
			'Please fix this or try to make a backup file named %s manually.'),
			'<strong>backup-'.DC_VERSION.'.zip</strong>');
		}
		elseif ($e->getCode() == dcUpdate::ERR_FILES_UNWRITALBE)
		{
			$msg =
			__('The following files of your Dotclear installation cannot be written. '.
			'Please fix this or try to <a href="http://dotclear.org/download">update manually</a>.');
		}
		
		if (isset($e->bad_files)) {
			$msg .=
			'<ul><li><strong>'.
			implode('</strong></li><li><strong>',$e->bad_files).
			'</strong></li></ul>';
		}
		
		$core->error->add($msg);
		$step='';
	}
}

/* DISPLAY Main page
-------------------------------------------------------- */

?>
<html>
<head>
  <title><?php echo __('Dotclear update by upload'); ?></title>
</head>
<body>
<?php


echo '<h2>'.__('Dotclear update').'</h2>';

if ($step == 'check' && !$core->error->flag())
{
	echo '<form id="dc-upload" class="clear" action="'.$p_url.'" method="post" enctype="multipart/form-data">'.
		'<fieldset id="add-file-f">'.
		'<legend>'.__('Upload new version').'</legend>'.
		'<p><label>'.__('Choose a file:').
		' ('.sprintf(__('Maximum size %s'),files::size(DC_MAX_UPLOAD_SIZE)).')'.
		'<input type="file" name="upfile" size="20" />'.
		'</label></p>'.
		'<p><input type="submit" value="'.__('send').'" />'.
		form::hidden(array('MAX_FILE_SIZE'),DC_MAX_UPLOAD_SIZE).
		form::hidden(array('step'),'upload').
		$core->formNonce().'</p>'.
		'</fieldset>'.
		'</form>';

	
	if (!empty($archives))
	{
		echo
		'<h3>'.__('Update backup files').'</h3>'.
		'<p>'.__('The following files are backups of previously updates. '.
		'You can revert your previous installation or delete theses files.').'</p>';
		
		echo	'<form action="'.$p_url.'" method="post">';
		
		foreach ($archives as $v) {
			echo
			'<p><label class="classic">'.form::radio(array('backup_file'),html::escapeHTML($v)).' '.
			html::escapeHTML($v).'</label></p>';
		}
		
		echo
		'<p><strong>'.__('Please note that reverting your Dotclear version may have some '.
		'unwanted side-effects. Consider reverting only if you experience strong issues with this new version.').'</strong> '.
		sprintf(__('You should not revert to version prior to last one (%s).'),end($archives)).
		'</p>'.
		'<p><input type="submit" name="b_del" value="'.__('Delete selected file').'" /> '.
		'<input type="submit" name="b_revert" value="'.__('Revert to selected file').'" />'.
		$core->formNonce().'</p>'.
		'</form>';
	}
}
elseif ($step == 'unzip' && !$core->error->flag())
{
	echo
	'<p class="message">'.
	__("Congratulations, you're one click away from the end of the update.").
	' <strong><a href="index.php?logout=1">'.__('Finish the update.').'</a></strong>'.
	'</p>';
}

?>

</body>
</html>