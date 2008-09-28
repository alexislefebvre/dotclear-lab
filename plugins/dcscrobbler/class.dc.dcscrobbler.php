<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is a contributed part of DotClear.
# Copyright (c) 2005-2006 Boris de Laage. All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

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
    $username = $core->blog->settings->get('username');
    
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
