<?php
if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('MetaImage',array('tplMetaImageTpl','MetaImage'));

class tplMetaImageTpl
{
	public static function MetaImage($attr)
	{
		return '
<?php 
$objMeta = new dcMeta($core);
$imageName = $objMeta->getMetaStr($_ctx->posts->post_meta,"image");
$imageAttached = !empty($imageName);
if ($imageAttached) {

  echo "   <div id=\"cadre-photo\">";

  $public_url   = $core->blog->settings->public_url;
  $public_path  = $core->blog->public_path;
  $image_url    = $public_url."/illustration-articles/".$imageName;
  $image_path   = $public_path."/illustration-articles/".$imageName;
  $image_alt    = "";
  $image_width  = 0;
  $image_height = 0;
  
  if (file_exists($image_path))
    list($image_width, $image_height) = getimagesize($image_path);
    
  if ($image_width > 165) {
    $image_height = 165 * $image_height / $image_width;
    $image_width = 165;
  }

  echo "<img src=\"".$image_url."\" alt=\"".$image_alt."\" width=\"".$image_width."\" height=\"".$image_height."\" />";

  echo "   </div>";
}

?> ';
	}
}
?>