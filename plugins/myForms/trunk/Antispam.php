<?php
# ***** BEGIN LICENSE BLOCK *****
# Copyright (c) 2009 Olivier Azeau and contributors. All rights  reserved.
# Captcha generator from dotclear 'contact' plugin copyright (c) 2005 k-net. All rights reserved.
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

define('MYFORMS_ANTISPAM_MASTER_KEY',sha1_file(dirname(__FILE__).'/../../inc/config.php'));

class MyFormsAntispam
{
  private $key, $img;
  
  public function __construct() {
    if (!function_exists('imagecreatetruecolor')) {
      die('myforms antispam needs GD for creating images');
    }
    
    $chars = 'ABCDEFGHJKLMNPRSTUVWXYZ23456789';
    $this->key = '';
    for ($i = 0; $i < 6; $i++) {
      $this->key .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    
    $this->img = imagecreatetruecolor(100, 25);
    $backgroundcolor = imagecolorallocate($this->img, 240, 240, 255);
    imagefilledrectangle($this->img, 0, 0, 100, 25, $backgroundcolor);
    
    $fontcolors = array(imagecolorallocate($this->img, 102, 102, 153), imagecolorallocate($this->img, 153, 153, 255), imagecolorallocate($this->img, 102, 102, 204), imagecolorallocate($this->img, 102, 102, 153), imagecolorallocate($this->img, 51, 51, 153), imagecolorallocate($this->img, 153, 153, 255));
    if (function_exists('imagettftext')) {
      for ($i = 0; $i < 6; $i++) {
          imagettftext($this->img, mt_rand(13, 20), mt_rand(-20, 20), $i*15 + 5, 20, $fontcolors[$i], dirname(__FILE__).'/arial_lite.ttf', $this->key[$i]);
      }
    } else {
      for ($i = 0; $i < 6; $i++) {
          imagestring($this->img, 5, $i*15 + 8, mt_rand(2, 9), $this->key[$i], $fontcolors[$i]);
      }
    }
    
    $bordercolor = imagecolorallocate($this->img, 200, 200, 255);
    imageline($this->img, 0, 0, 100, 0, $bordercolor);
    imageline($this->img, 0, 0, 0, 25, $bordercolor);
    imageline($this->img, 99, 0, 99, 25, $bordercolor);
    imageline($this->img, 0, 24, 100, 24, $bordercolor);
  }
  
  public function getImageAsHtml() {
    ob_start();
    imagepng($this->img);
    $data = base64_encode(ob_get_clean());
    return "<img src='data:image/png;base64,".$data."' alt='Anti-spam' width='100' height='25' style='vertical-align: middle;' />";
  }
  
  public function getReference() {
    return crypt::hmac(MYFORMS_ANTISPAM_MASTER_KEY,$this->key);
  }
  
  public static function isValid($input,$reference) {
    return crypt::hmac(MYFORMS_ANTISPAM_MASTER_KEY,$input) == $reference;
  }
  
  public static function display($refFieldAttr,$inputFieldAttr) {
    $antispam = new MyFormsAntispam();
    echo $antispam->getImageAsHtml();
    echo '<input type="hidden" '.$refFieldAttr.' value="'.$antispam->getReference().'" />';
    echo '<input type="text" '.$inputFieldAttr.' value="" />';
  }
  
}
?>