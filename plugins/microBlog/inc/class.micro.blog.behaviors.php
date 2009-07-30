<?php
class microBlogBehaviors
{
	public static $status = array();
	
	public static function afterPostCreate(&$Post,$post_id) {
		if($Post->post_status == 1)
		{
			self::pushNote($Post->post_url);
		}
	}
	
	public static function beforePostUpdate(&$Post,$post_id) {
		self::$status[$post_id] = $Post->post_status;
	}
	
	public static function afterPostUpdate(&$Post,$post_id) {
		$new = $Post->post_status;
		$old = self::$status[$post_id];
		
		if($new == 1 && $new != $old)
		{
			self::pushNote($Post->post_url);
		}
		
		unset(self::$status[$post_id]);
	}
	
	public static function pushNote($post_url){
		$txt = __('New Blog Post : ')
		   	 . $core->blog->url
		   	 . $core->url->getBase('publicpage')
		   	 . $post_url;
		
		$mb = microBlog::init();
		$s  = $mb->getServicesList();
		while($s->fetch())
		{
			$p = $mb->getServiceParams($s->id);
			if($p['sendNoteOnNewBlogPost']){
				$mb->getServiceAccess($s->id)
				   ->sendNote($txt);
			}
		}
	}
}