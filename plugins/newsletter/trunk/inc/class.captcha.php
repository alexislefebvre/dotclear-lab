<?php

/**
* Utilisation:
*
* <img src="script.php?name=newsletter&strlen=5" alt="anti spam" />
*/

class Captcha
{
    var $font = 'comic.ttf';
    var $width = 170;
    var $height = 60;
    var $length = 0;
    var $img = null;
    var $code = '';
    var $colors = array();
    var $rgb_font = array(
        array('r' => 70, 'v' => 130, 'b' => 255),
        array('r' => 255, 'v' => 237, 'b' => 175),
        array('r' => 166, 'v' => 250, 'b' => 186),
        array('r' => 253, 'v' => 188, 'b' => 251),
        array('r' => 255, 'v' => 255, 'b' => 255)
        );
    var $offsetX = 15;
    var $offsetY = 10;
    var $size = 25;
    var $dstWidth = 0;
    var $dstHeight = 0;
    var $bkrgb = array('r' => 200, 'v' => 200, 'b' => 200);
    var $bkgradient = true;
    var $noise = false;
    var $type = 'png';

	/**
	* test de disponibilité de la librairie GD
	*/
    public static function isGD()
    {
        if (!function_exists('imagecreatetruecolor')) return false;
        else return true;
    }

	/**
	* constructeur de la classe
	*/
    public function __construct($_width, $_height, $_length)
    {
        if (!self::isGD()) return;
        
        if (isset($_width) && !empty($_width)) $this->dstWidth = (integer) $_width;
        if (isset($_height) && !empty($_height)) $this->dstHeight = (integer) $_height;
        if (isset($_length) && !empty($_length)) $this->length = (integer) $_length;

		// création de l'image
        $this->img = imagecreateTRUEcolor($this->width, $this->height);
        imageantialias($this->img, 1);
    }

	/**
	* génération du code à saisir
	*/
    private function generateCode()
    {
        $string = 'ABCDEFGHiJKLMNOPqRSTUVWXYZ0123456789';
        for ($i = 0; $i < $this->length; $i++) $this->code .= $string[ mt_rand(0, 35) ];
    }

	/**
	* prépare l'image en remplissant par une couleur
	*/
    private function prepareImg()
    {
        if (!self::isGD()) return;
        
        $bk_color = imagecolorallocate($this->img, $this->bkrgb['r'], $this->bkrgb['v'], $this->bkrgb['b']);
        imagefilledrectangle($this->img, 0, 0, $this->width, $this->height, $bk_color);
    }

	/**
	* génère l'image
	*/
    private function generateImg()
    {
        if (!self::isGD()) return;
        
		// on crée les couleurs (départ, finale et liste)
        $c1 = array(mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
        $c2 = array(mt_rand(150, 200), mt_rand(150, 200), mt_rand(150, 200));

        $this->colors = array();
        foreach ($this->rgb_font as $rgb) { $this->colors[] = imagecolorallocate($this->img, $rgb['r'], $rgb['v'], $rgb['b']); }

        if ($this->bkgradient)
        {
			// on crée l'image
            for ($i = 0; $i < $this->width; $i++)
            {
                $r = $c1[0] + $i * ($c2[0] - $c1[0]) / $this->width;
                $v = $c1[1] + $i * ($c2[1] - $c1[1]) / $this->width;
                $b = $c1[2] + $i * ($c2[2] - $c1[2]) / $this->width;
                $color = imagecolorallocate($this->img, $r, $v, $b);
                imageline($this->img, $i, 0, $i, $this->height, $color);
            }
        }
    }

	/**
	* écriture du code
	*/
    private function writeCode()
    {
        if (!self::isGD()) return;
        
        $font = dirname(__FILE__).'/'.$this->font;
        for ($i = 0; $i < $this->length; $i++)
        {
            $col = imagecolorallocate($this->img, mt_rand(0, 120), mt_rand(0, 120), mt_rand(0, 120));
            imagettftext($this->img, mt_rand($this->size -2, $this->size + 2), mt_rand(-30, 30), $this->offsetX + $i * $this->width / 6, $this->offsetY + $this->height / 2, $col, $font, $this->code[$i]);
        }
    }

	/**
	* on rajoute du bruit sur l'image
	*/
    private function addNoise()
    {
        if (!self::isGD()) return;        
        else if (!$this->noise) return;
		// on rajoute des petites lignes pour rendre un peu moins lisible
        else for ($i = 0; $i < 8; $i++) { imageline($this->img, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $this->colors[mt_rand(0, 4)]); }
    }

	/**
	* on finalise le dessin de l'image
	*/
    private function finalizeImg()
    {
        if (!self::isGD()) return;
        
		// on dessine la bordure
        $noir = imagecolorallocate($this->img, 0, 0, 0);
        imageline($this->img, 0, 0, $this->width, 0, $noir);
        imageline($this->img, 0, 0, 0, $this->height, $noir);
        imageline($this->img, $this->width -1, 0, $this->width -1, $this->height, $noir);
        imageline($this->img, 0, $this->height -1, $this->width -1, $this->height -1, $noir);
    }

	/**
	* redimensionne l'image
	*/
    private function resizeImg()
    {
        if (!self::isGD()) return;
        
        $nimg = imagecreateTRUEcolor($this->dstWidth, $this->dstHeight);
        imagecopyresampled($nimg, $this->img, 0, 0, 0, 0, $this->dstWidth, $this->dstHeight, $this->width, $this->height);
        imagedestroy($this->img);
        $this->img = $nimg;
    }

	/**
	* génère l'image
	*/
    public function generate()
    {
        if (!self::isGD()) return;
            
        $this->generateCode();
        $this->prepareImg();
        $this->generateImg();
        $this->writeCode();
        $this->addNoise();
        $this->finalizeImg();
        $this->resizeImg();
    }

	/**
	* on affiche l'image
	*/
    public function header()
    {
        if (!self::isGD()) return;
            
        switch ($this->type)
        {
            case 'jpg':
                header("Content-type: image/jpg");
                imagejpg($this->img, null, 80);
                break;

            case 'gif':
                header("Content-type: image/gif");
                imagegif($this->img);
                break;

            case 'png':
                header("Content-type: image/png");
                imagepng($this->img);
                break;
        }

        imagedestroy($this->img);
    }

	/**
	* url de génération des fichiers
	*/
    public static function path()
    {
        global $core;
        $blog = &$core->blog;
        return $blog->public_path;
    }

	/**
	* url de génération des fichiers
	*/
    public static function www()
    {
        global $core;
        $blog = &$core->blog;
        $settings = &$blog->settings;
        return $settings->public_url;
    }

	/**
	* on affiche l'image
	*/
    public function file()
    {
        if (!self::isGD()) return;
                    
        $file = self::path().'/';
        switch ($this->type)
        {
            case 'jpg':
                $file .= 'captcha.img.jpg';
                imagejpg($this->img, $file, 80);
                break;

            case 'gif':
                $file .= 'captcha.img.gif';
                imagegif($this->img, $file);
                break;

            case 'png':
                $file .= 'captcha.img.png';
                imagepng($this->img, $file);
                break;
        }
        imagedestroy($this->img);
        return $file;
    }

	/**
	* inscrit dans une variable de session le code
	*/
    public function write()
    {
        file_put_contents(self::path().'/captcha.key.txt', $this->code);
    }

	/**
	* inscrit dans une variable de session le code
	*/
    public static function read()
    {
        $content = @file_get_contents(self::path().'/captcha.key.txt');
        if ($content === FALSE) return null;
        else return $content;
    }
}

?>
