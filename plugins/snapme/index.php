<?php
# ***** BEGIN LICENSE BLOCK *****
# Widget SnapMe for DotClear.
# Copyright (c) 2007 Ludovic Toinel, All rights
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

require dirname(__FILE__).'/_public.php';

// Suppresion d'un snap
if (isset($_GET['delete']) AND is_numeric($_GET['delete'])) {
      
	$snapId =(int) $_GET['delete'];
	$snap = new dcSnapMe($core->blog);
	$rs = $snap->getSnap($snapId);

	if (unlink(dirname(__FILE__).'/snapshots/'.$rs->file_name)){

		// The small images for commentary
		if(file_exists(dirname(__FILE__).'/snapshots/small/'.$rs->file_name)){
			unlink(file_exists(dirname(__FILE__).'/snapshots/small/'.$rs->file_name));
		}

		$snap->deleteSnap($snapId);
		http::redirect($p_url.'&snapdeleted=1');
	} else {
		http::redirect($p_url.'&snapnotdeleted=1');
	}
}

?>
<html>
<head>
  <title>SnapMe</title>
  <?php echo dcPage::jsPageTabs(); ?>
</head>
  
<body>
<h2>SnapMe</h2>

<?php

if (!empty($_GET['snapdeleted'])) {
		echo '<p class="message">'.__('Snapshot deleted.').'</p>';
}
else if (!empty($_GET['snapnotdeleted'])) {
		echo '<p class="message">'.__('Snapshot not deleted.').'</p>';
}

// Page SnapMe
echo '<div class="multi-part" title="'.__('SnapMe').'">';
$args['nb_cols']=5;
$args['nb_snap']=20;
echo snapMeTpl::tplGallery($args);
echo '</div>';


// Page à propos
echo  '<div class="multi-part" title="'.__('About').'"><p>'.
      __('Plugin SnapMe by Ludovic Toinel').
      '<br /><br /><a href="http://www.geeek.org/category/SnapMe">Site officiel</a>'.
      '</p></div>';
?>

</body>
</html>
