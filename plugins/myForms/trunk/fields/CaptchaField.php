<?php
# ***** BEGIN LICENSE BLOCK *****
# Copyright (c) 2009 Olivier Azeau and contributors. All rights  reserved.
#
# This is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# This is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

define('MYFORMS_CAPTCHA_MASTER_KEY','gjlikgnqlnlqhaazaqots');

class MyFormsCaptchaField extends MyFormsField
{
  public function __construct() {
    parent::__construct(func_get_args());
  }
  
  public function Display() {
		$text = self::GenerateRandomText();
    ob_start();
    imagepng(self::GenerateImage($text));
    $imageData = base64_encode(ob_get_clean());
    return "\n<img src='data:image/png;base64,".$imageData."' alt='Captcha' width='100' height='25' style='vertical-align: middle;' />"
						."\n<input type='hidden' ".self::GetNameAndId($this->Name().'_sig', $this->Id().'_sig')." value='".$this->ComputeTextSignature($text)."' />"
						."\n<input type='text' ".$this->AttributesAsString()." value='' />\n";
  }
  
  public function Key() {
		if( isset($this->attributes['key']) )
			return $this->attributes['key'];
		else
			return MYFORMS_CAPTCHA_MASTER_KEY;
  }
  
  public  function ComputeTextSignature($text)  {
    return crypt::hmac($this->Key(),$text);
	}
	
  public function IsValid($condition) {
    global $_REQUEST;
    $fieldIsValid = !isset($_REQUEST["myforms"]) || ( $this->ComputeTextSignature($this->Value()) == self::GetInput($this->Name().'_sig') );
    if(!$fieldIsValid)
      MyForms::InvalidateForm();
    return $fieldIsValid;
  }

	//============================
  
  protected static function GenerateRandomText($length=6)  {
    $chars = 'ABCDEFGHJKLMNPRSTUVWXYZ23456789';
    $text = '';
    for ($i = 0; $i < $length; $i++) {
      $text .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
		return $text;
	}
  
  protected static function GenerateImage($text)  {
    if (!function_exists('imagecreatetruecolor')) {
      die('myforms captcha needs GD for creating images');
    }
    
    $img = imagecreatetruecolor(100, 25);
    $backgroundcolor = imagecolorallocate($img, 240, 240, 255);
    imagefilledrectangle($img, 0, 0, 100, 25, $backgroundcolor);
    
    $fontcolors = array(imagecolorallocate($img, 102, 102, 153), imagecolorallocate($img, 153, 153, 255), imagecolorallocate($img, 102, 102, 204), imagecolorallocate($img, 102, 102, 153), imagecolorallocate($img, 51, 51, 153), imagecolorallocate($img, 153, 153, 255));
    if (function_exists('imagettftext')) {
      for ($i = 0; $i < strlen($text); $i++) {
          imagettftext($img, mt_rand(13, 20), mt_rand(-20, 20), $i*15 + 5, 20, $fontcolors[$i], dirname(__FILE__).'/arial_lite.ttf', $text[$i]);
      }
    } else {
      for ($i = 0; $i < strlen($text); $i++) {
          imagestring($img, 5, $i*15 + 8, mt_rand(2, 9), $text[$i], $fontcolors[$i]);
      }
    }
    
    $bordercolor = imagecolorallocate($img, 200, 200, 255);
    imageline($img, 0, 0, 100, 0, $bordercolor);
    imageline($img, 0, 0, 0, 25, $bordercolor);
    imageline($img, 99, 0, 99, 25, $bordercolor);
    imageline($img, 0, 24, 100, 24, $bordercolor);
		
		return $img;
	}
  
	//============================
	
  public static function Register() {
    global $core;
    $core->tpl->addValue('myformsCaptchaField',array('MyFormsCaptchaField','TplDisplay'));
    $core->tpl->addValue('myformsCaptchaField_Declare',array('MyFormsCaptchaField','TplDeclare'));
  }
  
  // Display Field
  public static function TplDisplay($attr,$content)
  {
    return parent::DisplayObject(__CLASS__,$attr,$content);
  }
  
  // Declare Field
  public static function TplDeclare($attr,$content)
  {
    return parent::BuildDeclaration(__CLASS__,$attr,$content);
  }
}

MyFormsCaptchaField::Register();

?>