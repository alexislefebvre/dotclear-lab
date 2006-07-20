<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2003-2006 Olivier Meunier and contributors. All rights
# reserved.
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
'<input type="submit" value="'.__('save').'" /></p>'.
'</form>';

?>

</body>
</html>