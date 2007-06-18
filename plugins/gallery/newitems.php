<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
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

$core->meta = new dcMeta($core);
$core->gallery= new dcGallery($core);
$core->media = new dcMedia($core);
$params=array();

$dirs_combo = array();
foreach ($core->media->getRootDirs() as $v) {
	if ($v->w) {
		$dirs_combo['/'.$v->relname] = $v->relname;
	}
}

$media_dir =    !empty($_REQUEST['media_dir'])    ? $_REQUEST['media_dir']    : '';
$scan_media =   !empty($_REQUEST['scan_media'])   ? $_REQUEST['scan_media']   : 0;
$create_posts = !empty($_REQUEST['create_posts']) ? $_REQUEST['create_posts'] : 0;;


?>
<html>
<head>
  <title><?php echo __('Gallery Items'); ?></title>
  <?php echo dcPage::jsLoad('index.php?pf=gallery/js/_items_lists.js').
             dcPage::jsLoad('index.php?pf=gallery/js/_newitems.js').
	     dcPage::jsPageTabs("new_items");
  ?>
</head>
<body>

<?php
echo '<h2>'.$core->blog->name.' &gt; '.__('Entries').'</h2>';
echo '<p><a href="plugin.php?p=gallery" class="multi-part">'.__('Galleries').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=items" class="multi-part">'.__('Images').'</a></p>';
echo '<div class="multi-part" id="new_items" title="'.__('Manage new items').'">';

echo '<form action="#" method="post" id="actions-form" onSubmit="return false;">'.
	'<fieldset><legend>'.__('New Items').'</legend>'.
	'<p><label class="classic">'.__('Media dir:').
	form::combo('media_dir',$dirs_combo,'').'</label></p> '.
	'<p><label class="classic">'.form::checkbox('scan_media',1,1).
	__('Scan dir for new media').'</label></p>'.
	'<p><label class="classic">'.form::checkbox('create_posts',1,1).
	__('Create image-posts for media in dir').'</label></p> '.
	'<input type="button" class="proceed" value="'.__('proceed').'" /></p>'.
	'</fieldset></form>';

	echo '<fieldset><legend>'.__('Processing result').'</legend>';
	echo '<table id="process" class="clear"><tr><th>ID</th><th>Action</th><th>Status</th></tr></table>';
	echo '</fieldset>';
?>

</div>
</body>
</html>
