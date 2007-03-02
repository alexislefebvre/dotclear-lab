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

$post_id = $_GET['id'];

$post_media = array();

$page_title = __('Edit gallery');
$params['post_type']='gal';
$can_view_page = true;
$can_edit_post = $core->auth->check('usage,gallery',$core->blog->id);
$can_publish = $core->auth->check('publish,galleryadmin',$core->blog->id);
$preview = false;

$core->media = new dcMedia($core);
$core->meta = new dcMeta($core);

$next_link = $prev_link = $next_headlink = $prev_headlink = null;

# If user can't publish
if (!$can_publish) {
	$post_status = -2;
}

?>
<html>
<head>
  <title>Gallery</title>
<?php echo dcPage::jsDatePicker(); ?>  
  <?php echo dcPage::jsToolBar(); ?>
  
  <?php echo dcPage::jsConfirmClose('entry-form'); ?>
  <?php echo dcPage::jsPageTabs('gal-maint'); ?>
  
  <?php echo metaBehaviors::postHeaders();?>

</script>
</head>
<body>
<?php
/* DISPLAY
-------------------------------------------------------- */
$default_tab = 'gal-maint';

if ($core->error->flag()) {
	echo
	'<div class="error"><strong>'.__('Errors:').'</strong>'.
	$core->error->toHTML().
	'</div>';
}

$galtool = new dcGallery($core);


$params['post_id'] = $_REQUEST['id'];
$params['post_type']='gal';	
$gallery = $core->blog->getPosts($params);


$gal_directory=$core->meta->getMetaStr($gallery->post_meta,"galmediadir");


echo '<h2>'.$core->blog->name.' &gt; '.$page_title.'</h2>';

# Exit if we cannot view page
if (!$can_view_page) {
	exit;
}

/* Post form if we can edit post
-------------------------------------------------------- */
?>

<?php
	echo '<br /><p><a href="plugin.php?p=gallery&amp;m=gal&amp;id='.$post_id.'" class="multi-part">'.
		__('Description').'</a></p>';
	echo '<br /><p><a href="plugin.php?p=gallery&amp;m=galitemlist&amp;id='.$post_id.'" class="multi-part">'.
		__('Items').'</a></p>';
?>




<div id="gal-maint" class="multi-part" title="<?php echo __('Maintenance'); ?>">
<h2><?php echo __('Operations performed :') ?></h2>
<table class="clear">
<tr>
	<th><?php echo __('Media'); ?></th>
	<th><?php echo __('Title'); ?></th>
	<th><?php echo __('Operation performed'); ?></th>
	<th><?php echo __('Status'); ?></th>
</tr>
<?php
	$rs = $galtool->getMediaWithoutGalItems($gal_directory);
	while ($rs->fetch()) {
		$media = $core->media->getFile($rs->media_id);
		if ($media->media_image) {
			echo '<tr><td><img src="'.$media->media_icon.'" alt="'.$media->media_title.'"/></td><td>'.$media->media_title.'</td><td>'.__('Add image linked to media, add image to gallery').'</td><td></td></tr>';
			$img_id=$galtool->createPostForMedia($media);
			$core->meta->setPostMeta($post_id,'galitem',$img_id);
		}
		
	}
	$rs = $galtool->getItemsWithoutGal($gal_directory,$post_id);
	?>
	<?php
	while ($rs->fetch()) {
		$media = $core->media->getFile($rs->media_id);
		if ($media->media_image) {
			echo '<tr><td><img src="'.$media->media_icon.'" alt="'.$media->media_title.'"/></td><td>'.$media->media_title.'</td><td>'.__('Add image to gallery').'</td><td><img alt="published" title="published" src="images/check-on.png" /></td></tr>';
			$core->meta->setPostMeta($post_id,'galitem',$rs->post_id);
		}
	}
	$rs = $galtool->getGalImageMedia(array(),$gallery->post_id);

	while ($rs->fetch()) {
		$media = $core->media->getFile($rs->media_id);
		if ($media->media_image) {
			$p = path::info($media->file);
			$thumb = sprintf($core->media->thumb_tp,$p['dirname'],$p['base'],'%s');

			try {
				$img = new imageTools();
				$img->loadImage($media->file);
			
				$w = $img->getW();
				$h = $img->getH();
			
				foreach ($core->media->thumb_sizes as $suffix => $s) {
					$thumbname=sprintf($thumb,$suffix);
					if (!file_exists($thumbname)) {
						if ($suffix == 'sq' || ($w > $s[0] && $h > $s[0])) {
							$img->resize($s[0],$s[0],$s[1]);
							$img->output('jpeg',sprintf($thumb,$suffix),80);
			echo '<tr><td><img src="'.$media->media_icon.'" alt="'.$media->media_title.'"/></td><td>'.$media->media_title.'</td><td>'.__('Create thumb'). ' "'.$suffix.'"</td><td><img alt="published" title="published" src="images/check-on.png" /></td></tr>';
						}
					}
				}
			} catch (Exception $e) {}
		}
	}
	?>

</table>
</div>
</body>
</html>

