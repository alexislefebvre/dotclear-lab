<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Fake Me Up" plugin.
#
# Copyright (c) 2003-2010 DC Team
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }
define(DC_DIGESTS,DC_ROOT.'/inc/digests');
define(DC_DIGESTS_BACKUP,DC_ROOT.'/inc/digests.bak');

function md5sum($root,$digests_file)
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



function fake_digest() {
	if (!rename(DC_DIGESTS,DC_DIGESTS_BACKUPUP))
		return false;
	$fmu_file = DC_ROOT.'/inc/prepend.php';
	$fmu_md5 = md5_file($fmu_file);
	
	$mydigest = sprintf("%s  %s\n",$fmu_md5,$fmu_file);
	if (file_put_contents(DC_DIGEST,$mydigest)===false)
		return false;
	else
		return true;
}
?>
<html>
<head><title><?php echo __('Fake Me Up'); ?></title></head>
<body>
<?php
	global $_lang;
	$disclaimer = l10n::getFilePath(dirname(__FILE__).'/locales','disclaimer.html',$GLOBALS['_lang']);
	
	echo '<h2>'.__('Fake Me Up').'</h2>';
	if (isset($_POST['erase_backup'])) {
		@unlink(DC_DIGESTS_BACKUP);
	}
	if (isset($_POST['override'])) {
		$changes = md5sum(DC_ROOT,DC_DIGESTS);

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
		echo '<p class="message">'.__("The updates have been performed.").'</p>';
	} elseif (isset($_POST['confirm'])) {
		$changes = md5sum(DC_ROOT,DC_DIGESTS);
		if (count($changes["changed"])==0 && count($changes["removed"])==0) {
			echo '<p class="message">'.__('No changed filed have been found, nothing to do!').'</p>';
		} else {
			if (count($changes["changed"]) != 0) {
				echo '<div class="message">'.
					'<p>'.__('The following files will have their checksum faked :').'</p>'.
					'<ul>';
				foreach ($changes["changed"] as $k => $v) {
					printf('<li> %s [old:%s, new:%s]</li>',$k,$v['old'],$v['new']);
				}
				echo '</ul>';
			}
			if (count($changes["removed"]) != 0) {
				echo '<div class="message">'.
					'<p>'.__('The following files digests will have their checksum cleaned :').'</p>'.
					'<ul>';
				foreach ($changes["removed"] as $k => $v) {
					printf('<li> %s</li>',$k);
				}
				echo '</ul>';
			}
			echo '<form action="'.$p_url.'" method="post"><p>'.
			$core->formNonce().
			form::hidden("override",1).
			'<input type="submit" name="confirm" value="'.__('Still ok to continue').'"/></p></div>';
		}
	} else {
		if (file_exists(DC_DIGESTS_BACKUP)) {
			echo '<div class="error"><p>'.__('Fake Me Up has already been run once.').
				'<form action="'.$p_url.'" method="post">'.
				'<p><input type="checkbox" name="erase_backup" id="erase_backup" />&nbsp;'.
				'<label for="confirm" class="inline">'.__("Remove the backup digest file, I want to play again").'</label>'.
				$core->formNonce().
				'</p>'.
				'<p><input type="submit" name="confirm" value="'.__('Continue').'"/></p>'.
				'</form></div>';
		} else {
			echo '<p class="error">'.__('Please read carefully the following disclaimer before proceeding !').'</p>';
			echo '<div class="message">'.file_get_contents($disclaimer);
			echo '<form action="'.$p_url.'" method="post">'.
				'<p><input type="checkbox" name="confirm" id="confirm" />&nbsp;'.
				'<label for="confirm" class="inline">'.__("I have read and understood the disclaimer and wish to continue anyway").'</label>'.
				$core->formNonce().
				'</p>'.
				'<p><input type="submit" name="confirm" value="'.__('Continue').'"/></p>'.
				'</form></div>';
		}
	}
	echo '<p><a class="back" href="index.php">'.__('back').'</a></p>';
?>

</body>