<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcScrobbler, a plugin for Dotclear.
# 
# Copyright (c) 2008 Boris de Laage
# bdelaage@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# $Id: class.dc.dcscrobbler.php 27 2006-08-23 20:42:45Z bdelaage $
if (!defined('DC_RC_PATH')) { return; }

class dcScrobbler
{

  private static $webservice_url = 'http://ws.audioscrobbler.com/1.0/user/%s/%s.xml';
  private static $cache_file = '%s/dcscrobbler-%s-%s.xml';
  private static $debug = "%s/dcs_debug.txt";
 

  private static function writeData($data, $username)
  {
    $xml = HttpClient::quickGet(sprintf(self::$webservice_url, $username, $data));
    if ($xml) {
      $fp = @fopen(sprintf(self::$cache_file, DC_TPL_CACHE, $username, $data),'wb');
      if ($fp !== false) {
        fwrite($fp, $xml);
        fclose($fp);
      }
    }
  }


  public static function getData($data)
  {
    global $core;

    $core->blog->settings->setNameSpace('dcscrobbler');
    $username = $core->blog->settings->get('dcs_username');
    
    $file = sprintf(self::$cache_file,DC_TPL_CACHE, $username, $data);

    if (file_exists($file) && (filemtime($file) + 120) > time()) {
      $xml = @simplexml_load_file($file);
    }
    else {
      dcScrobbler::writeData($data, $username);
      if (file_exists($file)) {
        $xml = simplexml_load_file($file);
        $core->blog->triggerBlog();
      }
    }
    if (isset($xml))
      return $xml;
    else
      return false;
  }
}
?>