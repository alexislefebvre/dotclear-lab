<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

if (isset($_POST['planet_sources'])) {
	try {
		$core->blog->settings->setNameSpace('planet');
		$core->blog->settings->put('planet_sources',$_POST['planet_sources']);
		http::redirect($p_url);
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
  <title>planet</title>
</head>

<body>

<h2><?php echo __('Planet configuration'); ?></h2>

<?php
if (!$core->blog->settings->planet_sources) {
	echo '<p>'.__('Planet is not configured yet. Add feeds to enable planet on the current blog.').'</p>';
}

$nb_rows = count(explode("\n",$core->blog->settings->planet_sources));
if ($nb_rows < 5) {
	$nb_rows = 5;
} elseif ($nb_rows > 20) {
	$nb_rows = 20;
}

echo
'<form action="plugin.php" method="post">'.
'<p><label class="area">'.__('Source feeds:').' '.
form::textarea('planet_sources',60,$nb_rows,html::escapeHTML($core->blog->settings->planet_sources)).
'</label>'.
'<p>'.form::hidden('p','planet').
$core->formNonce().
'<input type="submit" value="'.__('save').'" /></p>'.
'</form>';

?>

</body>
</html>