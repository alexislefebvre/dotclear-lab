<?php


$core->url->register('annuaire','annuaire','^annuaire',array('annuaireURL','annuaire'));
$core->tpl->addValue('displayAnnuaire',array('tplAnnuaire','annuaire'));

class tplAnnuaire
{
	public static function annuaire($args)
	{
		global $core;
		$params;
				
		$res .= "<ul class=\"annuaire\">";
		
		$params['order'] = 'blog_creadt desc';
		$rs = $core->getBlogs($params);
		//pour chaque post,
		while ($rs->fetch()) {
			$blog = $core->getBlog($rs->blog_id);
			if($blog->blog_id !="default" && $blog->blog_status==1) {
				$res .= "<li>";
				$res .= "<a href=\"".$blog->blog_url."\"><img src=\"http://images.websnapr.com/?url=".$blog->blog_url."&amp;size=t\" alt=\"\" /></a>";
				$res .= "<h3><a href='".$blog->blog_url."'>".strtoupper($blog->blog_name)."</a></h3>";
				$res .= $blog->blog_desc."<div class='clear'>&nbsp;</div></li>";
				
			}
		}
		$res.="</ul>";
		
		return $res;
	}
}


class annuaireURL extends dcUrlHandlers
{
	public static function annuaire($args)
	{		
		self::serveDocument('annuaire.html');
	}
}

?>