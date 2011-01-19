<?php

class myGmapsRsExt
{
	public static function hasMap($rs)
	{
		global $core;
		
		$meta = $core->meta->getMetaArray($rs->post_meta);
		
		return array_key_exists('map',$meta);
	}
	
	public static function getMapsId($rs)
	{
		global $core;
		
		$meta = $core->meta->getMetaArray($rs->post_meta);
		
		return array_key_exists('map',$meta) ? $meta['map'] : array();
	}
	
	public static function getMapOptions($rs)
	{
		global $core;
		
		$options = array();
		
		$meta = $core->meta->getMetaArray($rs->post_meta);
		
		if (array_key_exists('center',$meta)) {
			$latlng = explode(',',$meta['center'][0]);
			$options['center'] = sprintf('{lat:%s,lng:%s}',$latlng[0],$latlng[1]);
		}
		if (array_key_exists('zoom',$meta)) {
			$options['zoom'] = $meta['zoom'][0];
		}
		if (array_key_exists('map_type',$meta)) {
			$options['map_type'] = sprintf("'%s'",$meta['map_type'][0]);
		}
		$options['target'] = sprintf("'#map_canvas_%s'",$rs->post_id);
		$options['show_details'] = 'false';
		
		array_walk($options,create_function('&$v,$k','$v=sprintf("%s: %s",$k,$v);'));
		
		return sprintf('{%s}',implode(',',$options));
	}
}

?>