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

$page = !empty($_GET['page']) ? $_GET['page'] : 1;

$next_link = $prev_link = $next_headlink = $prev_headlink = null;

# If user can't publish
if (!$can_publish) {
	$post_status = -2;
}

?>
<html>
<head>
  <title>Gallery items</title>
  <?php echo dcPage::jsConfirmClose('entry-form'); ?>
  <?php echo dcPage::jsPageTabs('gal-items'); ?>
  
</script>
</head>
<body>
<?php
/* DISPLAY
-------------------------------------------------------- */
$default_tab = 'gal-items';

if ($core->error->flag()) {
	echo
	'<div class="error"><strong>'.__('Errors:').'</strong>'.
	$core->error->toHTML().
	'</div>';
}

$galtool = new dcGallery($core);



$nb_per_page =  30;


$galitems=$galtool->getGalImageMedia($params,$gal_id=$post_id);

$pager = new pager($page,$galitems->count(),$nb_per_page,10);

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
?>



<div id="gal-items" class="multi-part" title="<?php echo __('Items'); ?>">
<div class="media-list">
<?php
	$j=0;
	
	echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
        for ($i=$pager->index_start, $j=0; $i<=$pager->index_end; $i++, $j++)
        {
		$galitems->fetch();
                echo mediaItemLine($core->media->fileRecord($galitems),$j);
        }
	echo '<p class="clear">'.__('Page(s)').' : '.$pager->getLinks().'</p>';
?>
</div>
</div>
<?php 
echo '<br /><p><a href="plugin.php?p=gallery&amp;m=galupdate&amp;id='.$post_id.'" class="multi-part">'.
		__('Maintenance').'</a></p>';

?>
</div>

</body>
</html>

<?php
function mediaItemLine($f,$i)
{
        global $page_url, $type, $popup, $post_id;

        $fname = $f->basename;

        if ($f->d) {
                $link = html::escapeURL($page_url).'&amp;d='.html::sanitizeURL($f->relname);
                if ($f->parent) {
                        $fname = '..';
                }
        } else {
                $link =
                'media_item.php?type='.rawurlencode($type).
                '&amp;id='.$f->media_id.'&amp;popup='.$popup.'&amp;post_id='.$post_id;
        }

        $class = 'media-item media-col-'.($i%2);

        $res =
        '<div class="'.$class.'"><a class="media-icon media-link" href="'.$link.'">'.
        '<img src="'.$f->media_icon.'" alt="" /></a>'.
        '<ul>'.
        '<li><a class="media-link" href="'.$link.'">'.$fname.'</a></li>';

        if (!$f->d) {
                $res .=
                '<li>'.$f->media_title.'</li>'.
                '<li>'.
                $f->media_dtstr.' - '.
                files::size($f->size).' - '.
                '<a href="'.$f->file_url.'">'.__('open').'</a>'.
                '</li>';
        }

        $res .= '</ul></div>';

        return $res;
}
?>
