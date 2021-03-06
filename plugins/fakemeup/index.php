<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Fake Me Up" plugin.
#
# Copyright (c) 2010-2015 Bruno Hondelatte, and contributors.
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$page_title = __('Fake Me Up');

//define('DC_DIGESTS',DC_ROOT.'/inc/digests');
define('DC_DIGESTS_BACKUP',DC_ROOT.'/inc/digests.bak');

function check_config($root,$digests_file)
{
	if (!is_readable($digests_file)) {
		throw new Exception(__('Unable to read digests file.'));
	}

	$opts = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;
	$contents = file($digests_file,$opts);

	$changed = array();
	$same = array();
	$removed = array();

	foreach ($contents as $digest)
	{
		if (!preg_match('#^([\da-f]{32})\s+(.+?)$#',$digest,$m)) {
			continue;
		}

		$md5 = $m[1];
		$filename = $root.'/'.$m[2];

		# Invalid checksum
		if (is_readable($filename)) {
			$md5_new = md5_file($filename);
			if ($md5 == $md5_new) {
				$same[$m[2]] = $md5;
			} else {
				$changed[$m[2]] = array("old"=>$md5,"new"=>$md5_new);
			}
		} else {
			$removed[$m[2]] = true;
		}

	}

	# No checksum found in digests file
	if (empty($md5)) {
		throw new Exception(__('Invalid digests file.'));
	}

	return array("same"=>$same,"changed"=>$changed,"removed" => $removed);
}

function backup ($changes) {
	$core =& $GLOBALS['core'];
	if (preg_match('#^http(s)?://#',$core->blog->settings->system->public_url)) {
		$public_root = $core->blog->settings->system->public_url;
	} else {
		$public_root = $core->blog->host.path::clean($core->blog->settings->system->public_url);
	}
	$zip_name = sprintf("fmu_backup_%s.zip",date("YmdHis"));
	$zip_file = sprintf("%s/%s",$GLOBALS['core']->blog->public_path,$zip_name);
	$zip_uri = sprintf("%s/%s",$public_root,$zip_name);
	$checksum_file= sprintf("%s/fmu_checksum_%s.txt",$GLOBALS['core']->blog->public_path,date("Ymd"));

	$c_data = 'Fake Me Up Checksum file - '.date("d/m/Y H:i:s")."\n\n".
		'Dotclear version : '.DC_VERSION."\n\n";
	if (count($changes["removed"])) {
		$c_data .= "== Removed files ==\n";
		foreach ($changes["removed"] as $k=>$v) {
			$c_data .= sprintf(" * %s\n",$k);
		}
		$c_data .= "\n";
	}
	if (file_exists($zip_file))
		@unlink($zip_file);
	$b_fp = @fopen($zip_file,'wb');
	if ($b_fp === false) {
		return false;
	}
	$b_zip = new fileZip($b_fp);
	if (count($changes["changed"])) {
		$c_data .= "== Invalid checksum files ==\n";
		foreach ($changes["changed"] as $k => $v) {
			$name = substr($k,2);
			$c_data .= sprintf(" * %s [expected: %s ; current: %s]\n",$k,$v['old'],$v['new']);
			try {
				$b_zip->addFile(DC_ROOT.'/'.$name,$name);
			} catch (Exception $e) {
				$c_data .= $e->getMessage();
			}

		}
	}
	file_put_contents($checksum_file,$c_data);
	$b_zip->addFile($checksum_file,basename($checksum_file));
	$b_zip->write();
	fclose($b_fp);
	$b_zip->close();
	@unlink($checksum_file);
	return $zip_uri;
}

?>
<html>
<head><title><?php echo($page_title); ?></title>
</head>
<body>
	<?php
	if (is_callable(array('dcPage', 'breadcrumb')))
	{
		echo dcPage::breadcrumb(
			array(
			__('System') => '',
				'<span class="page-title">'.$page_title.'</span>' => ''
			));
	}
	else
	{
		echo('<h2>'.__('System').' &rsaquo; '.
			$page_title.'</h2>');
	}

	if (isset($_POST['erase_backup'])) {
		@unlink(DC_DIGESTS_BACKUP);
	}
	if (isset($_POST['override'])) {
		$helpus = l10n::getFilePath(dirname(__FILE__).'/locales','helpus.html',$GLOBALS['_lang']);
		$changes = check_config(DC_ROOT,DC_DIGESTS);

		$arr=$changes["same"];
		foreach ($changes["changed"] as $k=>$v) {
			$arr[$k]=$v['new'];
		}
		ksort($arr);
		$digest='';
		foreach ($arr as $k=>$v) {
			$digest .= sprintf("%s  %s\n",$v,$k);
		}
		rename(DC_DIGESTS,DC_DIGESTS_BACKUP);
		file_put_contents(DC_DIGESTS,$digest);
		$uri = backup($changes);
		echo '<div class="success">';
		if ($uri !== false) {
			printf(file_get_contents($helpus),$uri,"fakemeup@dotclear.org");
		} else {
			echo '<p>'.__("The updates have been performed.").'</p>';
		}
		echo '<p><a href="update.php">'.__('Update Dotclear').'</a></p>'.
			'</div>';
	} elseif (isset($_POST['disclaimer_ok'])) {
		$changes = check_config(DC_ROOT,DC_DIGESTS);
		if (count($changes["changed"])==0 && count($changes["removed"])==0) {
			echo '<p class="message">'.__('No changed filed have been found, nothing to do!').'</p>';
		} else {
			echo '<div class="message">';
			if (count($changes["changed"]) != 0) {
				echo '<p>'.__('The following files will have their checksum faked:').'</p>'.
					'<ul>';
				foreach ($changes["changed"] as $k => $v) {
					printf('<li> %s [old:%s, new:%s]</li>',$k,$v['old'],$v['new']);
				}
				echo '</ul>';
			}
			if (count($changes["removed"]) != 0) {
				echo '<p>'.__('The following files digests will have their checksum cleaned:').'</p>'.
					'<ul>';
				foreach ($changes["removed"] as $k => $v) {
					printf('<li> %s</li>',$k);
				}
				echo '</ul>';
			}
			echo '<form action="'.$p_url.'" method="post"><p>'.
			$core->formNonce().
			form::hidden("override",1).
			'<input type="submit" name="confirm" value="'.__('Still ok to continue').'"/></p></form></div>';
		}
	} else {
		if (file_exists(DC_DIGESTS_BACKUP)) {
			echo '<div class="static-msg"><p>'.__('Fake Me Up has already been run once.').'</p>'.
				'<form action="'.$p_url.'" method="post">'.
				'<p><input type="checkbox" name="erase_backup" id="erase_backup" class="classic" />&nbsp;'.
				'<label for="erase_backup" class="classic">'.__("Remove the backup digest file, I want to play again").'</label>'.
				$core->formNonce().
				'</p>'.
				'<p><input type="submit" name="confirm" id="confirm" value="'.__('Continue').'"/></p>'.
				'</form></div>';
		} else {
			$disclaimer = l10n::getFilePath(dirname(__FILE__).'/locales','disclaimer.html',$GLOBALS['_lang']);
			echo '<p class="error">'.__('Please read carefully the following disclaimer before proceeding!').'</p>';
			echo '<div class="message">'.file_get_contents($disclaimer);
			echo '<form action="'.$p_url.'" method="post">'.
				'<p><input type="checkbox" name="disclaimer_ok" id="disclaimer_ok" />&nbsp;'.
				'<label for="disclaimer_ok" class="classic">'.__("I have read and understood the disclaimer and wish to continue anyway.").'</label>'.
				$core->formNonce().
				'</p>'.
				'<p><input type="submit" name="confirm" id="confirm" value="'.__('Continue').'"/></p>'.
				'</form></div>';
		}
	}
	echo '<p><a class="back" href="index.php">'.__('Back').'</a></p>';
?>

</body>