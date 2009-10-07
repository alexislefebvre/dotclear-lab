<?php
$core->addBehavior('adminPageHTMLHead',array('customAdmin','adminCssLink'));
$core->tpl->use_cache = false;

class customAdmin
{
	public static function adminCssLink()
	{
		global $core;
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo
		'<style type="text/css">'."\n".
		'@import url('.$url.'/css/admin.css);'."\n".
		"</style>\n";
	}
}
?>