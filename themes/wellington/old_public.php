<?php
if (!defined('DC_RC_PATH')) { return; }

//$core->addBehavior('publicAfterContentFilter',array('neokraftBehaviors','publicAfterContentFilter'));
$core->addBehavior('coreBlogGetPosts',array('wtangoBehaviors','coreBlogGetPosts'));

class neokraftBehaviors
{
	protected static $post_imgs = array();
	protected static $post_imgs_reg = '#(?:<p>\s*)(?:<a[^>]+href="(.+?)"(.+?)>\s*</a>)\s*</p>#msu';
	
	public static function publicAfterContentFilter($core,$tag,$args)
	{
		global $_ctx;
		
		if ($tag != 'EntryContent' || ($core->url->type != 'post' && $core->url->type != 'preview')) {
			return;
		}
		
		self::$post_imgs = array();
		$str =& $args[0];
		
		if (preg_match_all(self::$post_imgs_reg,$str,$m) > 0)
		{
			foreach ($m[1] as $i => $v) {
				self::$post_imgs[$v] = array(
					'w' => null,
					'h' => null,
					'title' => null,
					'meta' => null
				);
			}
			
			$m = new neokraftMedia($core);
			$m->getMediaItems(self::$post_imgs);
			
			$str = preg_replace_callback(self::$post_imgs_reg,array('self','photoMeta'),$str);
		}
	}
	
	protected static function photoMeta($m)
	{
		if (!self::$post_imgs[$m[1]]['w']) {
			return $m[0];
		}
		
		$i = self::$post_imgs[$m[1]];
		
		# read meta
		$meta = $i['meta'];
		$meta_title = array('Title','City','Country');
		
		$title = array();
		foreach ($meta_title as $t) {
			if ((string) $meta->$t) {
				$title[] = html::escapeHTML((string) $meta->$t);
			}
		}
		$title[] = dt::dt2str(
			'<span class="exif-datetime">'.
			'<span class="exif-weekday"> %A</span>'.
			'<span class="exif-daynum"> %e</span>'.
			'<span class="exif-month"> %B</span>'.
			'<span class="exif-year"> %Y</span>'.
			'<span class="exif-time">, %H:%M</span>'.
			'</span>',
			(string) $meta->DateTimeOriginal);
		
		$description = '';
		if ((string) $meta->Description && (string) $meta->Title != (string) $meta->Description) {
			$description = '<p class="exif-desc">'.html::escapeHTML((string) $meta->Description).'</p>';
		}
		
		$info = array();
		if ((string) $meta->Exposure) {
			$info['speed'] = (string) $meta->Exposure.'s';
		}
		if ((string) $meta->FNumber) {
			$info['aperture'] = (string) $meta->FNumber;
			$aperture = sscanf($info['aperture'],'%d/%d');
			if ($aperture) {
				$info['aperture'] = 'f/'.$aperture[0]/$aperture[1];
			}
		}
		if ((string) $meta->ISOSpeedRatings) {
			$info['ISO'] = (string) $meta->ISOSpeedRatings;
		}
		if ((string) $meta->FocalLength) {
			$info['focal length'] = (string) $meta->FocalLength;
			$flength = sscanf($info['focal length'],'%d/%d');
			if ($flength) {
				$info['focal length'] = $flength[0]/$flength[1].'mm';
			}
		}
		if (!empty($info)) {
			foreach ($info as $k => &$v) {
				$v = $k.': <span class="exif-value">'.$v.'</span>';
			}
			$info = '<p class="exif-info">'.implode(', ',$info).'</p>';
		} else {
			$info = '';
		}
		
		$s = preg_replace('#(</p>)$#',
			'$1'.
			'<div class="exif">'.
			'<p class="exif-title">'.implode(', ',$title).'</p>'.
			$description.
			$info.
			'</div>',
			$m[0]
		);
		
		return $s;
	}
}

class neokraftMedia extends dcMedia
{
	public function getMediaItems(&$post_imgs)
	{
		$paths = array_keys($post_imgs);
		
		foreach ($paths as &$v) {
			$v = preg_replace('/^'.preg_quote($this->core->blog->settings->public_url,'/').'(\/?)/','',$v);
		}

		$rs = $this->con->select(
			'SELECT media_id, media_path, media_title, '.
			'media_file, media_meta, media_dt, media_creadt, '.
			'media_upddt, media_private, user_id '.
			'FROM '.$this->table.' '.
			"WHERE media_path = '".$this->path."' ".
			'AND media_file '.$this->con->in($paths)
		);
		
		while ($rs->fetch()) {
			$u = $this->core->blog->settings->public_url.'/'.$rs->media_file;
			if (!isset($post_imgs[$u])) {
				continue;
			}
			
			$o = $this->fileRecord($rs);
			$s = getimagesize($o->file);
			$post_imgs[$u]['w'] = $s[0];
			$post_imgs[$u]['h'] = $s[1];
			$post_imgs[$u]['title'] = $o->media_title;
			$post_imgs[$u]['meta'] = $o->media_meta;
		}
	}
}

class wtangoBehaviors
{
	public static function coreBlogGetPosts(&$rs)
	{
		$rs->extend('rsExtOskuPosts');
	}
}

class rsExtOskuPosts extends rsExtPost
{
	public static function getURL(&$rs)
	{	
		if (preg_match('%^http[s]?://%',$rs->post_url))
		{
			return $rs->post_url;
		}
		else
		{
			return $rs->core->blog->url.$rs->core->getPostPublicURL(
				$rs->post_type,html::sanitizeURL($rs->post_url)
				);
		}
	}
}
?>