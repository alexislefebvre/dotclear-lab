<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$core->meta = new dcMeta($core);
$core->gallery= new dcGallery($core);
$core->media = new dcMedia($core);
$params=array();

$dirs_combo = array();
foreach ($core->media->getRootDirs() as $v) {
	if ($v->relname == "")
		$dirs_combo['/'] = ".";
	else
		$dirs_combo['/'.$v->relname] = $v->relname;
}

$defaults=($core->blog->settings->gallery->gallery_new_items_default != null)?$core->blog->settings->gallery->gallery_new_items_default:"YYYYY";
if (strlen($defaults)<6) 
	$defaults="YYYYYY";
$c_delete_orphan_media=($defaults{0} == "Y");
$c_delete_orphan_items=($defaults{1} == "Y");
$c_create_media=($defaults{2} == "Y");
$c_create_items=($defaults{3} == "Y");
$c_create_items_for_new_media=($defaults{4} == "Y");
$c_update_ts=($defaults{5} == "Y");

$max_ajax_requests = (int) $core->blog->settings->gallery->gallery_max_ajax_requests;
if ($max_ajax_requests == 0)
	$max_ajax_requests=5;

?>
<html>
<head>
  <title><?php echo __('Gallery Items'); ?></title>
  <?php echo dcPage::jsLoad('index.php?pf=gallery/js/jquery.ajaxmanager.js').
             dcPage::jsLoad('index.php?pf=gallery/js/_ajax_tools.js').
             dcPage::jsLoad('index.php?pf=gallery/js/_newitems.js').
	     dcPage::jsPageTabs("new_items");
	echo 
	'<script type="text/javascript">'."\n".
	"//<![CDATA[\n".
		"dotclear.maxajaxrequests = ".$max_ajax_requests.";\n".
		"dotclear.msg.deleting_orphan_media = '".html::escapeJS(__('Deleting orphan media'))."';\n".
		"dotclear.msg.deleting_orphan_items = '".html::escapeJS(__('Deleting orphan image-posts'))."';\n".
		"dotclear.msg.creating_media = '".html::escapeJS(__('Creating media : %s'))."';\n".
		"dotclear.msg.creating_item = '".html::escapeJS(__('Creating image-post for : %s'))."';\n".
	"\n//]]>\n".
	"</script>\n";	
  ?>
  <link rel="stylesheet" type="text/css" href="index.php?pf=gallery/admin_css/style.css" />
</head>
<body>

<?php
echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; '.__('Galleries').' &gt; '.__('New entries').'</h2>';
echo '<p><a href="plugin.php?p=gallery" class="multi-part">'.__('Galleries').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=items" class="multi-part">'.__('Images').'</a></p>';
echo '<div class="multi-part" id="new_items" title="'.__('Manage new items').'">';

echo '<form action="#" method="post" id="dir-form" onsubmit="return false;">'.
	'<fieldset><legend>'.__('New Items').'</legend>'.
	'<p><label class="classic">'.__('Select directory to analyse : ').
	form::combo('media_dir',$dirs_combo,'').'</label></p> '.

	'<input type="button" class="proceed" value="'.__('proceed').'" />'.
	'</fieldset></form>';
echo '<form action="#" method="post" id="actions-form" onsubmit="return false;">'.
	'<fieldset id="dirresults"><legend>'.__('Directory results').' : <span id="directory"></span></legend>'.
	'<table>'.
	'<tr><th>'.__('Request').'</th><th>'.__('Result').'</th><th colspan="2">'.__('Action').'</th></tr>'.
	'<tr><td>'.__('Number of orphan media (ie. media entries in database whose matching file no more exists):').'</td><td id="nborphanmedia" class="tdresult">&nbsp;</td>'.
		'<td>'.form::checkbox('delete_orphan_media',1,$c_delete_orphan_media).'</td><td>'.__('Delete orphan media').'</td></tr>'.
	'<tr><td>'.__('Number of orphan items (ie. image-post associated to a non-existent media in DB) :').'</td><td id="nborphanitems" class="tdresult">&nbsp;</td>'.
		'<td>'.form::checkbox('delete_orphan_items',1,$c_delete_orphan_items).'</td><td>'.__('Delete orphan items').'</td></tr>'.
	'<tr><td>'.__('Number of new media detected :').'</td><td id="nbnewmedia" class="tdresult">&nbsp;</td>'.
		'<td>'.form::checkbox('create_new_media',1,$c_create_media).'</td><td>'.__('Create media in database').'</td></tr>'.
	'<tr><td>'.__('Number of media without post associated :').'</td><td id="nbmediawithoutpost" class="tdresult">&nbsp;</td>'.
		'<td>'.form::checkbox('create_img_post',1,$c_create_items).'</td><td>'.__('Create image-post associated to media').'</td></tr>'.
	'</table>'.
	'<h2>Options</h2>'.
	'<p>'.form::checkbox('create_new_media_posts',1,$c_create_items_for_new_media).__('Create post-image for each new media').'</p>'.
	'<p>'.form::checkbox('update_ts',1,$c_update_ts).__('Set post date to image exif date').'</p>'.
	'<input type="button" class="proceed" value="'.__('proceed').'" />'.
	'</fieldset></form>';
	
echo '<fieldset id="itemsresults"><legend>'.__('Operations').'</legend>'.
	'<form action="#" onsubmit="return false;"><p><input type="button" id="abort" value="'.__('Abort processing').'"/></p></form>'.
	'<table id="resulttable">'.
	'<tr class="keepme"><th>'.__('Request').'</th><th>'.__('Result').'</th></tr>'.
	'</table>'.
	'</fieldset>';

	echo '<p><a href="plugin.php?p=gallery&amp;m=options" class="multi-part">'.__('Options').'</a></p>';
	if ($core->auth->isSuperAdmin())
		echo '<p><a href="plugin.php?p=gallery&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
?>

</div>
</body>
</html>