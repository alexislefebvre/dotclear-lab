<?php
/**
 * This file implements the EntriesSelection class.
 * @author Nicolas Frandeboeuf <nicofrand@gmail.com>
 * @version 1.0.1
 * @package feed2img
 */

class EntriesSelection
{
    /**
     * The blog title.
     * @var string
     */
    private $blogTitle;

    /**
     * Whether to display the blog title.
     * @var bool
     */
    private $showBlogTitle;

    /**
     * The number of posts to display.
     * @var int
     */
    private $numberOfPosts;

    /**
     * The font name.
     * @var string
     */
    private $font;

    /**
     * The title font size.
     * @var int
     */
    private $titleFontSize;

    /**
     * The title position on the horizontal axis.
     * @var int
     */
    private $titleX;

    /**
     * The title position on the vertical axis.
     * @var int
     */
    private $titleY;

    /**
     * The title color, in RGB.
     * @var array
     */
    private $titleColor;

    /**
     * The text font size.
     * @var int
     */
    private $textFontSize;

    /**
     * The text position on the horizontal axis.
     * @var int
     */
    private $textX;

    /**
     * The text position on the vertical axis.
     * @var int
     */
    private $textY;

    /**
     * The text color, in RGB.
     * @var array
     */
    private $textColor;

    /**
     * The image type as a mime type.
     * @var string
     */
    private $imageType = "image/png";

    /**
     * The image source
     * @var string
     */
    private $imageSource;

    /**
     * The image name.
     * @var string
     */
    private $imageName = "feed2img_output.png";

    /**
     * The line height.
     * @var float
     */
    private $lineHeight;

    /**
     * The maximum number of chars by line.
     * @var int
     */
    private $maxCharsByLine;

    /**
     * The posts list.
     * @var array
     */
    private $posts = array();

    /**
     * Creates an error message.
     * @global dcCore $core
     * @param string $msg the error message.
     */
    private function createError($msg)
    {
	global $core;
	// Load translations of alert messages
	$_lang = $core->auth->getInfo("user_lang");
	$_lang = preg_match("/^[a-z]{2}(-[a-z]{2})?$/", $_lang) ? $_lang : "en";
	l10n::set(dirname(__FILE__) . "/../locales/" . $_lang . "/error");
	throw new Exception(__("Feed2img : " . $msg . "."));
    }

    /**
     * Constructor
     * @global dcCore $core
     * @throws Exception
     */
    public function __construct()
    {
	global $core;
	$publicDir = path::real(path::fullFromRoot($core->blog->settings->public_path, DC_ROOT), false);

	$this->blogTitle = $core->blog->name;
	$this->showBlogTitle = $core->auth->user_prefs->feed2img->show_blog_title;

	if ($this->showBlogTitle)
	{
	    $this->titleFontSize = $core->auth->user_prefs->feed2img->title_font_size;
	    $this->titleX = $core->auth->user_prefs->feed2img->title_x;
	    $this->titleY = $core->auth->user_prefs->feed2img->title_y;
	    $this->titleColor = $this->hexaColorToRGB($core->auth->user_prefs->feed2img->title_color);
	}

	$this->numberOfPosts = $core->auth->user_prefs->feed2img->number_of_posts;
	$this->maxCharsByLine = $core->auth->user_prefs->feed2img->max_chars_by_line;

	if ($core->auth->user_prefs->feed2img->font == "")
	    $this->font = dirname(__FILE__) . "/../font/Arimo-Regular-Latin.ttf";
	else
	{
	    $this->font = $publicDir . "/" . $core->auth->user_prefs->feed2img->font;
	    if (!is_file($this->font))
		$this->createError("font source does not exist");
	}

	$this->textFontSize = $core->auth->user_prefs->feed2img->text_font_size;
	$this->textX = $core->auth->user_prefs->feed2img->text_x;
	$this->textY = $core->auth->user_prefs->feed2img->text_y;
	$this->lineHeight = $core->auth->user_prefs->feed2img->line_height;
	$this->textColor = $this->hexaColorToRGB($core->auth->user_prefs->feed2img->text_color);
	if ($core->auth->user_prefs->feed2img->image_source != "")
	{
	    $this->imageSource = $publicDir . '/' . $core->auth->user_prefs->feed2img->image_source;
	    if (!is_file($this->imageSource))
		$this->createError("image source does not exist");

	    $this->imageType = strtolower(image_type_to_mime_type(exif_imagetype($this->imageSource)));
	}
	else
	    $this->imageSource = dirname(__FILE__) . '/../img/feed2img.png';

	$this->imageName = $core->auth->user_prefs->feed2img->image_output;
    }

    /**
     * Builds the image.
     * @param string $postId the post identifier.
     */
    public static function buildImage($postId)
    {
	$feed2img = new EntriesSelection();
	$id = (is_numeric($postId)) ? intval($postId) : null;

	if ($feed2img->getPosts($id))
	    $feed2img->drawImage();
    }

    /**
     * Installs the plugin.
     * @global dcCore $core
     */
    public static function install()
    {
	global $core;

	$sql = "SELECT post_title FROM " . $core->prefix . "post WHERE post_status = 1 ORDER BY post_id DESC LIMIT 3";
	$request = $core->con->select($sql);

	if (!get_extension_funcs("gd"))
	    $this->createError("GD lib is needed");

	if (!($image = imagecreatefrompng(dirname(__FILE__) . "/../img/feed2img.png")))
	    $this->createError("image file is not readable");

	$font = dirname(__FILE__) . "/../font/Arimo-Regular-Latin.ttf";

	$titleColor = imagecolorallocate($image, 26, 45, 82);
	imagettftext($image, 13, 0, 9, 21, $titleColor, $font, $core->blog->name);

	$textColor = imagecolorallocate($image, 67, 64, 62);

	$i = 44;
	while ($request->fetch())
	{
	    $title = $request->f("post_title");
	    #$title = $this->removeSpecialChars(trim($title));
	    $title = trim($title);

	    if (strlen($title) > 80)
	    {
		$title = substr($title, 0, 80);
		$title .= "...";
	    }
	    imagettftext($image, 10, 0, 44, $i, $textColor, $font, $title);
	    $i += 23;
	}
	imagealphablending($image, false);
	imagesavealpha($image, true);
	$publicDir = path::real(path::fullFromRoot($core->blog->settings->public_path, DC_ROOT), false);
	imagepng($image, $publicDir . "/" . "feed2img_output.png");
	imagedestroy($image);
    }

    /**
     * Gets the posts to display on the image.
     * @global dcCore $core
     * @param string $postDeleted the identifier of the deleted post
     * @return boolean
     */
    private function getPosts($postDeleted = null)
    {
	global $core;

	if ($postDeleted)
	    $sql = "SELECT post_title FROM " . $core->prefix . "post WHERE post_status = 1 AND post_id <> " . $postDeleted . " ORDER BY post_id DESC LIMIT " . $this->numberOfPosts;
	else
	    $sql = "SELECT post_title FROM " . $core->prefix . "post WHERE post_status = 1 ORDER BY post_id DESC LIMIT " . $this->numberOfPosts;


	$request = $core->con->select($sql);
	while ($request->fetch())
	{
	    $title = $request->f("post_title");
	    #$title = $this->removeSpecialChars(trim($title));
	    $title = trim($title);

	    if (strlen($title) > $this->maxCharsByLine)
	    {
		$title = substr($title, 0, $this->maxCharsByLine);
		$title .= "...";
	    }
	    $this->posts[] = $title;
	}
	return true;
    }

    /*
    private function removeSpecialChars($string)
    {
    return preg_replace('/[^A-Za-z_. ]/', '', $string);
    }
    */

    /**
     * Tranforms a hexadecimal string color in a RGB array.
     * @param string $hexa
     * @return array
     */
    private function hexaColorToRGB($hexa)
    {
	$rgb = str_split(substr($hexa, 1), 2);
	return array_map("hexdec", $rgb);
    }

    /**
     * Draws the image.
     * @global dcCore $core
     * @throws Exception
     */
    private function drawImage()
    {
	global $core;

	switch ($this->imageType)
	{
	    case "image/png" :
		$image = imagecreatefrompng($this->imageSource);
		break;

	    case "image/jpeg" :
		$image = imagecreatefromjpeg($this->imageSource);
		break;

	    case "image/gif" :
		$image = imagecreatefromgif($this->imageSource);
		break;

	    default :
		$this->createError("wrong image type");
		break;
	}

	if ($this->showBlogTitle)
	{
	    $titleColor = imagecolorallocate($image, $this->titleColor[0], $this->titleColor[1], $this->titleColor[2]);
	    imagettftext($image, $this->titleFontSize, 0, $this->titleX, $this->titleY, $titleColor, $this->font, $this->blogTitle);
	}

	$textColor = imagecolorallocate($image, $this->textColor[0], $this->textColor[1], $this->textColor[2]);
	$i = $this->textY;

	foreach ($this->posts as $lastTitle)
	{
	    imagettftext($image, $this->textFontSize, 0, $this->textX, $i, $textColor, $this->font, $lastTitle);
	    $i += $this->lineHeight;
	}

	imagealphablending($image, false);
	imagesavealpha($image, true);
	$publicDir = path::real(path::fullFromRoot($core->blog->settings->public_path, DC_ROOT), false);

	switch ($this->imageType)
	{
	    case "image/png" :
		imagepng($image, $publicDir . "/" . $this->imageName);
		break;

	    case "image/jpeg" :
		imagejpeg($image, $publicDir . "/" . $this->imageName, 100);
		break;

	    case "image/gif" :
		imagegif($image, $publicDir . "/" . $this->imageName);
		break;

	    default :
		$this->createError("wrong image type");
		break;
	}

	//TODO : utiliser
	/*
	  imagepng($im, $cachefile); # store the image to cachefile
	  imagedestroy($im);
	  $fp = fopen($cachefile, 'rb'); # stream the image directly from the cachefile
	  fpassthru($fp);
	 */
	imagedestroy($image);
    }
}
?>
