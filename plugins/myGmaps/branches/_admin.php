<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of myGmaps, a plugin for Dotclear 2.
#
# Copyright (c) 2010 Philippe aka amalgame
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminPostHeaders',array('myGmapsBehaviors','postHeaders'));
$core->addBehavior('adminPageHeaders',array('myGmapsBehaviors','postHeaders'));
$core->addBehavior('adminPostForm',array('myGmapsBehaviors','adminPostForm'));
$core->addBehavior('adminPageForm',array('myGmapsBehaviors','adminPageForm'));

$_menu['Blog']->addItem(
	__('Google Maps'),
	'plugin.php?p=myGmaps&amp;go=maps','index.php?pf=myGmaps/icon.png',
	preg_match('/plugin.php\?p=myGmaps(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('usage,contentadmin',$core->blog->id)
);

if (isset($_GET['remove']) && $_GET['remove'] == 'map') {
	try {
	$post_id = $_GET['id'];
	$meta =& $GLOBALS['core']->meta;
	$meta->delPostMeta($post_id,'map');
	$meta->delPostMeta($post_id,'map_options');
	if ($_GET['post_type'] == 'page') {
		http::redirect('plugin.php?p=pages&act=page&id='.$post_id);
	} else {
		http::redirect(DC_ADMIN_URL.'post.php?id='.$post_id);
	}
	
  } catch (Exception $e) {
    $core->error->add($e->getMessage());
  }
} elseif (!empty($_GET['remove']) && is_numeric($_GET['remove'])) {
	try {
	$post_id = $_GET['id'];
	$meta =& $GLOBALS['core']->meta;
	$meta->delPostMeta($post_id,'map',(integer) $_GET['remove']);
	
	if ($_GET['post_type'] == 'page') {
		http::redirect('plugin.php?p=pages&act=page&id='.$post_id);
	} else {
		http::redirect(DC_ADMIN_URL.'post.php?id='.$post_id);
	}
	
  } catch (Exception $e) {
    $core->error->add($e->getMessage());
  }
}

class myGmapsBehaviors
{
	public static function postHeaders()
	{
		return;
	}
	
	public static function adminPostForm($cur)
	{
		global $core;
		
		echo 
		'<fieldset><legend>'.__('Google Map').'</legend>'.
		'<p><a href="plugin.php?p=myGmaps&go=maps_popup&post_id='.$cur->post_id.'">'.__('Add a map to entry').'</a></p>'.
		'</fieldset>';
		/*$id = $post->post_id;
		$type = $post->post_type;
		$meta =& $GLOBALS['core']->meta;
		$meta_rs = $meta->getMetaStr($post->post_meta,'map_options');
		if ($id) {
			if (!$meta_rs) {
				echo 
					'<fieldset><legend>'.__('Google Map').'</legend>'.
					'<p><a class="add-maps" href="plugin.php?p=myGmaps&go=maps_popup&popup=1&post_id='.$id.'">'.__('Add a map to entry').'</a></p>'.
					'</fieldset>';
			} else {
				
				$meta =& $GLOBALS['core']->meta;
				$maps_array = explode(",",$meta->getMetaStr($post->post_meta,'map'));
				$maps_options = explode(",",$meta->getMetaStr($post->post_meta,'map_options'));
				
				echo '<fieldset><legend>'.__('Google Map').'</legend>'.
				'<h3>'.__('Map elements').'</h3>';
				if ($meta->getMetaStr($post->post_meta,'map') != '') {
					echo
						'<table class="clear"><tr>'.
						'<th>'.__('Title').'</th>'.
						'<th>'.__('Date').'</th>'.
						'<th>'.__('Category').'</th>'.
						'<th>'.__('Author').'</th>'.
						'<th class="nowrap">'.__('Type').'</th>'.
						'<th>&nbsp;</th>'.
						'</tr>';
					
					$params['post_type'] = 'map';
					$params['no_content'] = true;
					
					$rsp = $core->blog->getPosts($params);
					while ($rsp->fetch()) {
						if (in_array($rsp->post_id,$maps_array)) {
							$meta_rs = $meta->getMetaStr($rsp->post_meta,'map');
							if ($core->auth->check('categories',$core->blog->id)) {
								$cat_link = '<a href="category.php?id=%s">%s</a>';
							} else {
								$cat_link = '%2$s';
							}
							if ($rsp->cat_title) {
								$cat_title = sprintf($cat_link,$rsp->cat_id,
								html::escapeHTML($rsp->cat_title));
							} else {
								$cat_title = __('None');
							}
							echo
							'<tr>'.
							'<td class="maximal"><a href="plugin.php?p=myGmaps&amp;do=edit&amp;id='.$rsp->post_id.'" title="'.__('Edit map element').' : '.html::escapeHTML($rsp->post_title).'">'.html::escapeHTML($rsp->post_title).'</a></td>'.
							'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$rsp->post_dt).'</td>'.
							'<td class="nowrap">'.$cat_title.'</td>'.
							'<td class="nowrap">'.$rsp->user_id.'</td>'.
							'<td class="nowrap">'.__($meta_rs).'</td>'.
							'<td class="nowrap"><a class="element-remove" href="'.DC_ADMIN_URL.'post.php?id='.$id.'&amp;remove='.$rsp->post_id.'" title="'.__('Remove map element').' : '.html::escapeHTML($rsp->post_title).'">[x]</a></td>'.
							'</tr>';
							
						}
					}
	
					echo '</table>';
				} else {
					echo '<p>'.__('No element (empty map)').'</p>';
				}
				echo
				'<div class="two-cols">'.
				'<p class="col"><a href="plugin.php?p=myGmaps&amp;post_id='.$id.'"><strong>'.__('Edit map').'</strong></a></p>'.
				'<p class="col right"><a class="map-remove" href="'.DC_ADMIN_URL.'post.php?id='.$id.'&amp;remove=map"><strong>'.__('Remove map').'</strong></a></p>'.
				'</div>'.
				'</fieldset>';
				
			}
		}*/
	}
	
	public static function adminPageForm($post)
	{
		global $core;
	}
}   
?>