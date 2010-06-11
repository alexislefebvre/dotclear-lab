<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 "Integrity Check" plugin.
#
# Copyright (c) 2003-2010 DC Team
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$has_bad_files=false;
$updater = new dcUpdate(DC_UPDATE_URL,'dotclear',DC_UPDATE_VERSION,DC_TPL_CACHE.'/versions');
try {
	$updater->checkIntegrity(DC_ROOT.'/inc/digests',DC_ROOT);
} catch (Exception $e) {
	$msg = $e->getMessage();
	if (isset($e->bad_files)) {
		$has_bad_files=true;
		$msg =
		__('The following files differ from your initial dotclear installation :').
		'<ul><li><strong>'.
		implode('</strong></li><li><strong>',$e->bad_files).
		'</strong></li></ul>';
	} else {
		$msg = __("An unexpected error occured : ").$e->getMessage();
	}
	
	$core->error->add($msg);
	
}

?>
<html>
<head><title><?php echo __('Integrity Check'); ?></title></head>
<body>
<?php
if (!$has_bad_files) {
	echo '<h2>'.__('Diagnostics').'</h2>';
	echo '<p class="message">'.__('All your installation files are correct.').'</p>';
}
echo '<p><a class="back" href="index.php">'.__('back').'</a></p>';
?>

</body>