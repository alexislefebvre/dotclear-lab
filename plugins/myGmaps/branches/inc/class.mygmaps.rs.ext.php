<?php

class myGmapsUtilsRsExt
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
}

?>