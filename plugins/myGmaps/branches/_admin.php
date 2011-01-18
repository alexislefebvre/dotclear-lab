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

$core->addBehavior('adminPostHeaders',array('myGmapsBehaviors','adminHeaders'));
$core->addBehavior('adminPageHeaders',array('myGmapsBehaviors','adminHeaders'));
$core->addBehavior('adminPostForm',array('myGmapsBehaviors','adminForm'));
$core->addBehavior('adminPageForm',array('myGmapsBehaviors','adminForm'));

$_menu['Blog']->addItem(
	__('Google Maps'),
	'plugin.php?p=myGmaps&amp;go=maps','index.php?pf=myGmaps/icon.png',
	preg_match('/plugin.php\?p=myGmaps(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('usage,contentadmin',$core->blog->id)
);

class myGmapsBehaviors
{
	public static function adminHeaders()
	{
		return dcPage::jsLoad('index.php?pf=myGmaps/js/_post.js');
	}
	
	public static function adminForm($cur)
	{
		global $core;
		
		$table = '';
		$post_id = 'NULL';
		$p_a = '<a href="%s">%s</a>';
		$p_img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		$items = $links = array();
		
		$redir = new ArrayObject;
		$redir['post'] = 'post.php?id=%s';
		$redir['page'] = 'plugin.php?p=pages&act=page&id=%s';
		
		# --BEHAVIOR-- coreBlogConstruct
		$core->callBehavior('myGmapsRedirURL',$redir);
		
		$meta = $core->meta->getMetaArray($cur->post_meta);
		
		if (array_key_exists('map',$meta)) {
			$post_id = $meta['map'];
		}
		
		$rs = $core->blog->getPosts(array('post_type' => 'map', 'post_id' => $post_id));
		
		while ($rs->fetch()) {
			
			switch ($rs->post_status) {
				case 1:
					$img_status = sprintf($p_img,__('published'),'check-on.png');
					break;
				case -2:
					$img_status = sprintf($p_img,__('pending'),'check-wrn.png');
					break;
				case 0:
					$img_status = sprintf($p_img,__('unpublished'),'check-off.png');
					break;
			}
			if ($core->auth->check('categories',$core->blog->id)) {
				$cat_link = '<a href="category.php?id=%s">%s</a>';
			} else {
				$cat_link = '%2$s';
			}
			if ($rs->cat_title) {
				$cat_title = sprintf($cat_link,$rs->cat_id,
				html::escapeHTML($rs->cat_title));
			} else {
				$cat_title = __('None');
			}
			
			$type_list = array(
				'none' => __('None'),
				'marker' => __('Point of interest'),
				'polyline' => __('Polyline'),
				'polygon' => __('Polygon'),
				'kml' => __('Included kml file')
			);
			$rs_meta = $core->meta->getMetaArray($rs->post_meta);
			$type = array_key_exists($rs_meta['elt_type'][0],$type_list) ? $type_list[$rs_meta['elt_type'][0]] : '';
			
			array_push(
				$items,
				'<tr>'.
				'<td class="maximal"><a href="'.sprintf('plugin.php?p=myGmaps&amp;go=map&amp;id=%s',$rs->post_id).
				'" title="'.__('Edit map element').' : '.html::escapeHTML($rs->post_title).'">'.
				html::escapeHTML($rs->post_title).'</a></td>'.
				'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$rs->post_dt).'</td>'.
				'<td class="nowrap">'.$cat_title.'</td>'.
				'<td class="nowrap">'.$rs->user_id.'</td>'.
				'<td class="nowrap">'.$type.'</td>'.
				'<td class="nowrap status">'.$img_status.'</td>'.
				'</tr>'
			);
		}
		
		if (count($items) > 0) {
			$table = sprintf(
				'<table class="clear"><tr>'.
				'<th>'.__('Title').'</th>'.
				'<th>'.__('Date').'</th>'.
				'<th>'.__('Category').'</th>'.
				'<th>'.__('Author').'</th>'.
				'<th class="nowrap">'.__('Type').'</th>'.
				'<th>'.__('Status').'</th>'.
				'</tr>'.
				'%s'.
				'</table>',
				implode("\n",$items)
			);
			array_push($links,sprintf($p_a,sprintf(
				'plugin.php?p=myGmaps&go=maps_post&amp;post_id=%s&amp;redir=%s',
				$cur->post_id,rawurlencode((array_key_exists($cur->post_type,$redir) ? $redir[$cur->post_type] : ''))
			),__('Edit map')));
			array_push($links,sprintf($p_a,'',__('Remove map')));
		}
		else {
			array_push($links,sprintf($p_a,sprintf(
				'plugin.php?p=myGmaps&go=maps_post&amp;post_id=%s&amp;redir=%s',
				$cur->post_id,rawurlencode((array_key_exists($cur->post_type,$redir) ? $redir[$cur->post_type] : ''))
			),__('Add map')));
		}
		
		echo
		'<p class="area" id="map-area" >'.
			'<label for="map">'.__('Google Map').'</label>'.
			'<div id="map">'.
				$table.
				'<p>'.implode(' - ',$links).'</p>'.
			'</div>'.
		'</p>';
	}
	
	public static function adminPageForm($post)
	{
		global $core;
	}
}

?>