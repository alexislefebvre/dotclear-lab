<?php
class EntriesSelection
{
    public static function ImgOutput($post_id)
        {
		global $core;
		define('font_size', 3);
        define("accents", "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ‗אבגדהוזחטיךכלםמןנסעףפץצרשתû‎‎‏ÿ");
        define("without_accent", "aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyyby");
		$blog_title = $core->blog->name;
		$blog_font_size = 5;
		if(strlen($blog_title)) { $blog_font_size = 4; }
		$last_entries = array();
		$sql = 'SELECT post_title FROM blog_post WHERE post_status = 1 ORDER BY post_id DESC LIMIT 5';
		$request = $core->con->select($sql);
		while ($request->fetch()) {
		  $title = $request->f('post_title');
          $title = iconv('UTF8', 'ISO-8859-15', $title);
          $title = strtr(trim($title), accents, without_accent);
		  if(strlen($title) > 55) {
			$title = substr($title, 0, 55);
			$title .= '...';
		  }
		  $last_entries[] = $title;
		}        
		header ("Content-type: image/png");
		$image = imagecreatefrompng(dirname(__FILE__).'/../img/feed2img.png');
		$color = imagecolorallocate($image, 126, 125, 119);
		imagestring($image, $blog_font_size, 65, 10, $blog_title, $color);
		$i = 40;
		foreach($last_entries as $last_title){
		  imagestring($image, font_size, 80, $i, $last_title, $color);
		  $i += 18;
		}
		imagesavealpha($image, true);
        $public_dir = path::real(path::fullFromRoot($core->blog->settings->public_path,DC_ROOT),false);
        imagepng($image, $public_dir.'/feed2img_output.png');
	}
}
?>