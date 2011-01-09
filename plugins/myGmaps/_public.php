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

$core->addBehavior('publicHeadContent',array('myGmapsPublic','publicHeadContent'));
$core->addBehavior('publicEntryAfterContent',array('myGmapsPublic','publicMapContent'));
$core->addBehavior('publicPageAfterContent',array('myGmapsPublic','publicMapContent'));

class myGmapsPublic
{
	public static function thisPostMap ($post_id)
	{
		global $core;
		$meta =& $core->meta;
		$my_params['post_id'] = $post_id;
		$my_params['no_content'] = true;
		$my_params['post_type'] = array('post','page');
					
		$rs = $core->blog->getPosts($my_params);
		return $meta->getMetaStr($rs->post_meta,'map');
	}
	public static function publicHeadContent($core,$_ctx)
	{		
			echo
			'<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>'."\n".
			'<style type="text/css">'."\n".
			'html,body { height: 100% }'."\n".
			'.map_canvas { width:100% ;height: 400px; }'."\n".
			'.map_canvas * { color:black }'."\n".
			'.map_canvas a { color:blue;text-decoration:underline }'."\n".
			'</style>'."\n";
	}
	public static function publicMapContent($core,$_ctx)
	{
		# Settings
		
		$s =& $core->blog->settings->myGmaps;
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		
		if (self::thisPostMap($_ctx->posts->post_id) != '') {
			
			echo 
				'<script type="text/javascript">'."\n".
				"//<![CDATA[\n".
				'$(function () {'."\n";
				
			
			$meta =& $GLOBALS['core']->meta;
			$post_map_options = explode(",",$meta->getMetaStr($_ctx->posts->post_meta,'map_options'));
			if ($post_map_options[3] == 'roadmap') {
				$mapTypeId = 'google.maps.MapTypeId.ROADMAP';
			} elseif ($post_map_options[3] == 'satellite') {
				$mapTypeId = 'google.maps.MapTypeId.SATELLITE';
			} elseif ($post_map_options[3] == 'hybrid') {
				$mapTypeId = 'google.maps.MapTypeId.HYBRID';
			} elseif ($post_map_options[3] == 'terrain') {
				$mapTypeId = 'google.maps.MapTypeId.TERRAIN';
			}
			echo
				'var myOptions = {'."\n".
					'zoom: parseFloat('.$post_map_options[2].'),'."\n".
					'center: new google.maps.LatLng('.$post_map_options[0].','.$post_map_options[1].'),'."\n".
					'scrollwheel: false,'."\n".
					'mapTypeId: '.$mapTypeId."\n".
				'};'."\n".
				'var map_'.$_ctx->posts->post_id.' = new google.maps.Map(document.getElementById("map_canvas_'.$_ctx->posts->post_id.'"), myOptions);'."\n".
				'var infowindow_'.$_ctx->posts->post_id.' = new google.maps.InfoWindow({});'."\n".
				
				'google.maps.event.addListener(map_'.$_ctx->posts->post_id.', "click", function (event) {'."\n".
					'infowindow_'.$_ctx->posts->post_id.'.close();'."\n".
				'});'."\n";
			
			
			$maps_array = explode(",",self::thisPostMap($_ctx->posts->post_id));
			
			$params['post_type'] = 'map';
			$params['post_status'] = '1';
			$maps = $core->blog->getPosts($params);
			
			while ($maps->fetch()) {
				if (in_array($maps->post_id,$maps_array)) {
					$list = explode("\n",html::clean($maps->post_excerpt_xhtml));
					$content = str_replace("\\", "\\\\", $maps->post_content_xhtml);
					$content = str_replace(array("\r\n", "\n", "\r"),"\\n",$content);
					$content = str_replace(array("'"),"\'",$content);
					if (sizeof($list) == 1) {
						$marker = explode("|",$list[0]);
						if (sizeof($marker) == 1) {
							$layer = $marker[0];
							echo
								'layer = new google.maps.KmlLayer("'.$layer.'", {'."\n".
									'preserveViewport: true'."\n".
								'});'."\n".
								'layer.setMap(map_'.$_ctx->posts->post_id.');'."\n";
						} else {
							
							echo
								'var title_'.$maps->post_id.' = "'.html::escapeHTML($maps->post_title).'";'."\n".
								'var content_'.$maps->post_id.' = \''.$content.'\';'."\n".
								'if (content_'.$maps->post_id.' == "<p>Pas de description</p>") {'."\n".
									'content_'.$maps->post_id.' = "";'."\n".
								'}'."\n".
								
								'marker = new google.maps.Marker({'."\n".
									'icon : "'.$marker[2].'",'."\n".
									'position: new google.maps.LatLng('.$marker[0].','.$marker[1].'),'."\n".
									'title: title_'.$maps->post_id.','."\n".
									'map: map_'.$_ctx->posts->post_id."\n".
								'});'."\n".
								
								'google.maps.event.addListener(marker, "click", function() {'."\n".
									'openmarkerinfowindow(this,title_'.$maps->post_id.',content_'.$maps->post_id.');'."\n".
								'});'."\n";
								
						}
					} elseif (sizeof($list) > 1) {
						
						echo
							'var list = "'.implode(",",$list).'";'."\n".
							'var lines = list.split(",");'."\n".
							'var polylineCoordinates = [];'."\n".
							'for (var i = 0; i < lines.length; i++) {'."\n".
								'if (lines[i].length > 1) {'."\n".
									'var parts = lines[i].split("|");'."\n".
									'var pos = new google.maps.LatLng(parseFloat(parts[0]), parseFloat(parts[1]));'."\n".
									'polylineCoordinates.push(pos);'."\n".
									'var color = parts[3];'."\n".
								'}'."\n".
							'}'."\n".
							'var polyline = new google.maps.Polyline({'."\n".
								'path: polylineCoordinates,'."\n".
								'strokeColor: color,'."\n".
								'strokeOpacity: 0.8,'."\n".
								'strokeWeight: 3'."\n".
							'});'."\n".
							'polyline.setMap(map_'.$_ctx->posts->post_id.');'."\n".
							
							'var title_'.$maps->post_id.' = "'.html::escapeHTML($maps->post_title).'";'."\n".
							'var content_'.$maps->post_id.' = \''.$content.'\';'."\n".
							'if (content_'.$maps->post_id.' == "<p>Pas de description</p>") {'."\n".
								'content_'.$maps->post_id.' = "";'."\n".
							'}'."\n".
							
							'google.maps.event.addListener(polyline, "click", function(event) {'."\n".
								'var pos = event.latLng;'."\n".
								'openpolyinfowindow(title_'.$maps->post_id.',content_'.$maps->post_id.',pos);'."\n".
							'});'."\n";
						
					}
				}
			}
			echo
				'function openmarkerinfowindow(marker,title,content) {'."\n".
					'infowindow_'.$_ctx->posts->post_id.'.setContent('."\n".
						'"<h3>"+title+"</h3>"+'."\n".
						'"<div class=\"post-infowindow\" id=\"post-infowindow_'.$_ctx->posts->post_id.'\">"+content+"</div>"'."\n".
					');'."\n".
					'infowindow_'.$_ctx->posts->post_id.'.open(map_'.$_ctx->posts->post_id.', marker);'."\n".
					'$("#post-infowindow_'.$_ctx->posts->post_id.'").parent("div", "div#map_canvas_'.$_ctx->posts->post_id.'").css("overflow","hidden");'."\n".
				'}'."\n";
				
			echo
				'function openpolyinfowindow(title,content,pos) {'."\n".
					'infowindow_'.$_ctx->posts->post_id.'.setPosition(pos);'."\n".
					'infowindow_'.$_ctx->posts->post_id.'.setContent('."\n".
						'"<h3>"+title+"</h3>"+'."\n".
						'"<div class=\"post-infowindow\" id=\"post-infowindow_'.$_ctx->posts->post_id.'\">"+content+"</div>"'."\n".
					');'."\n".
					'infowindow_'.$_ctx->posts->post_id.'.open(map_'.$_ctx->posts->post_id.');'."\n".
					'$("#post-infowindow_'.$_ctx->posts->post_id.'").parent("div", "div#map_canvas_'.$_ctx->posts->post_id.'").css("overflow","hidden");'."\n".
				'}'."\n";
				
			echo	
				'});'."\n".
				"\n//]]>\n".
				"</script>\n".
				'<noscript>'."\n".
				'<p>'.__('Sorry, javascript must be activated in your browser to see this map.').'</p>'."\n".
				'</noscript>'."\n".
				'<div class="map_canvas" id="map_canvas_'.$_ctx->posts->post_id.'"></div>'."\n";
			
		}
	}
}

?>