<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Gallery plugin for DC2 is free sofwtare; you can redistribute it and/or modify
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
	$dirs_combo['/'.$v->relname] = $v->relname;
}

unset($dirs_combo['/']);

$media_dir =    !empty($_REQUEST['media_dir'])    ? $_REQUEST['media_dir']    : '';
$scan_media =   !empty($_REQUEST['scan_media'])   ? $_REQUEST['scan_media']   : 0;
$create_posts = !empty($_REQUEST['create_posts']) ? $_REQUEST['create_posts'] : 0;;

$defaults=($core->blog->settings->gallery_new_items_default != null)?$core->blog->settings->gallery_new_items_default:"YYYYN";

$c_delete_orphan_media=($defaults{0} == "Y");
$c_delete_orphan_items=($defaults{1} == "Y");
$c_scan_media=($defaults{2} == "Y");
$c_create_posts=($defaults{3} == "Y");
$c_create_thumbs=($defaults{4} == "Y");

?>
<html>
<head>
  <title><?php echo __('Gallery Items'); ?></title>
  <?php echo dcPage::jsLoad('index.php?pf=gallery/js/_items_lists.js').
             dcPage::jsLoad('index.php?pf=gallery/js/_newitems.js').
	     dcPage::jsPageTabs("new_items");
	echo 
	'<script type="text/javascript">'."\n".
	"//<![CDATA[\n".
	"dotclear.msg.please_wait = '".html::escapeJS(__('Waiting...'))."';\n".
	"dotclear.msg.entries_found = '".html::escapeJS(__('%s entries found'))."';\n".
	"dotclear.msg.create_media = '".html::escapeJS(__('Create media'))."';\n".
	"dotclear.msg.create_post_for_media = '".html::escapeJS(__('Create post for media'))."';\n".
	"dotclear.msg.create_thumb_for_media = '".html::escapeJS(__('Create thumbs for media'))."';\n".
	"dotclear.msg.refresh_gallery = '".html::escapeJS(__('Refresh gallery'))."';\n".
	"dotclear.msg.delete_orphan_media = '".html::escapeJS(__('Delete orphan media'))."';\n".
	"dotclear.msg.delete_orphan_items = '".html::escapeJS(__('Delete orphan items'))."';\n".
	"dotclear.msg.fetch_new_media = '".html::escapeJS(__('Fetch new media'))."';\n".
	"dotclear.msg.fetch_media_without_post = '".html::escapeJS(__('Fetch media without post'))."';\n".
	"dotclear.msg.fetch_media_without_thumbnails = '".html::escapeJS(__('Fetch media without thumbnails'))."';\n".
	"dotclear.msg.retrieve_galleries = '".html::escapeJS(__('Retrieve galleries'))."';\n".
	"dotclear.msg.whole_blog = '".html::escapeJS(__('Whole blog'))."';\n".
	"\n//]]>\n".
	"</script>\n";
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
	'<p><label class="classic">'.form::checkbox('delete_orphan_media',1,$c_delete_orphan_media).
	__('Delete orphan media').'</label></p>'.
	'<p><label class="classic">'.form::checkbox('delete_orphan_items',1,$c_delete_orphan_items).
	__('Delete orphan image-posts').'</label></p>'.
	'<p><label class="classic">'.form::checkbox('scan_media',1,$c_scan_media).
	__('Scan dir for new media').'</label></p>'.
	'<p><label class="classic">'.form::checkbox('create_posts',1,$c_create_posts).
	__('Create image-posts for media in dir').'</label></p> '.
	'<p><label class="classic">'.form::checkbox('create_thumbs',1,$c_create_thumbs).
	__('Create missing thumbnails').'</label></p> '.
	'<input type="button" id="proceed" value="'.__('proceed').'" />'.
	'</fieldset></form>';
echo '<form action="#" method="post" id="update-form" onSubmit="return false;">'.
	'<fieldset><legend>'.__('Gallery mass update').'</legend>'.
	'<p><input type="button" id="proceedgal" value="'.__('Update all galeries').'" /></p>'.
	'</fieldset></form>';

	echo '<fieldset><legend>'.__('Processing result').'</legend>';
	echo '<p><input type="button" id="cancel" value="'.__('cancel').'" /></p>';
	echo '<h3>'.__('Requests').'</h3>';
	echo '<table id="request"><tr class="keepme"><th>ID</th><th>Action</th><th>Status</th></tr></table>';
	echo '<h3>'.__('Actions').'</h3>';
	echo '<table id="process"><tr class="keepme"><th>ID</th><th>Action</th><th>Status</th></tr></table>';
	echo '</fieldset>';

	echo '<p><a href="plugin.php?p=gallery&amp;m=options" class="multi-part">'.__('Options').'</a></p>';
?>

</div>
</body>
</html>
