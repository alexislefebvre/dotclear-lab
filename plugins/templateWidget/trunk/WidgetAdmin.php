<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of templateWidget
# Copyright (c) 2009 Olivier Azeau and contributors. All rights reserved.
#
# This is free software; you can redistribute it and/or modify
# it under the terms of the Affero GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
# 
# This is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the Affero GNU General Public License
# along with templateWidget; If not, see <http://www.gnu.org/licenses/>.
#
# templateWidget is a plugin for Dotclear software.
# templateWidget is not part of Dotclear.
# templateWidget can be used inside Dotclear but this license only applies to templateWidget
# ***** END LICENSE BLOCK *****

require_once(dirname(__FILE__).'/WidgetBuilder.php');
require_once(dirname(__FILE__).'/Settings.php');

class templateWidgetAdmin
{
  // behaviour for widget initialization
  public static function InitWidgets($widgets) {
    $activeWidgets = new templateWidgetSettings();
    // loop on all template widgets definitions and create associated widgets
    foreach (self::GetAllWidgetDefinitions() as $widgetId => $widgetDefinition) {
      if( $activeWidgets->GetItem($widgetId)->isActive )
        self::CreateWidget($widgets,$widgetDefinition);
    }
  }

  // get list of all active template widgets
  public static function GetAllWidgetDefinitions() {
    global $core;
    $widgetDefinitions = array();
    $core->widgetTpl = new templateWidget_WidgetBuilder('$core->widgetTpl');
    
    foreach ($core->widgetTpl->getPath() as $templateFolder) {
      $widgetFilesForFolder = self::Glob($templateFolder.'/*.widget.html');
      if (!is_array($widgetFilesForFolder))
        continue;
      foreach ($widgetFilesForFolder as $widgetFile) {
        $widgetDefinition = self::GetWidgetDefinition($widgetFile);
        $widgetId = $widgetDefinition['id'];
        if (!isset($widgetDefinitions[$widgetId]))
          $widgetDefinitions[$widgetId] = $widgetDefinition;
      }
    }
    $core->widgetTpl = null;
    return $widgetDefinitions;
  }

  private static function GetWidgetDefinition($widgetFile) {
    global $core;
    
    $_widgetDefinition = array('settings'=>array(),'order'=>array());
    $_widgetDefinition['file'] = $widgetFile;
    $_widgetDefinition['id'] = basename($widgetFile,'.widget.html');
    ob_start();
		include $core->widgetTpl->getFile(basename($widgetFile));
		ob_end_clean();
    
    if (isset($_widgetDefinition['error']))
      die('Template Widget Error: '.$_widgetDefinition['error'].' in file '.$widgetFile);
    
    return $_widgetDefinition;
  }
  
  // behaviour for widget initialization
  private static function CreateWidget($widgets,$widgetDefinition) {
    $widgetId = $widgetDefinition['id'];
    $widgets->create($widgetId,__($widgetDefinition['name']),array('templateWidgetBlocksAndValues','WidgetCore'));
    
    asort($widgetDefinition['order']);
    foreach ($widgetDefinition['order'] as $settingName => $settingOrder) {
      call_user_func_array(array($widgets->$widgetId, 'setting'), $widgetDefinition['settings'][$settingName]);
    }
  }
  
  // from http://fr3.php.net/glob#71083
  //safe_glob() by BigueNique at yahoo dot ca
  //Function glob() is prohibited on some servers for security reasons as stated on:
  //http://seclists.org/fulldisclosure/2005/Sep/0001.html
  //(Message "Warning: glob() has been disabled for security reasons in (script) on line (line)")
  //safe_glob() intends to replace glob() for simple applications
  //using readdir() & fnmatch() instead.
  //Since fnmatch() is not available on Windows or other non-POSFIX, I rely
  //on soywiz at php dot net fnmatch clone.
  //On the final hand, safe_glob() supports basic wildcards on one directory.
  //Supported flags: GLOB_MARK. GLOB_NOSORT, GLOB_ONLYDIR
  //Return false if path doesn't exist, and an empty array is no file matches the pattern
  private static function Glob($pattern, $flags=0) {
    if (!function_exists('fnmatch')) {
        function fnmatch($pattern, $string) {
            return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
        }
    }
    $split=explode('/',$pattern);
    $match=array_pop($split);
    $path=implode('/',$split);
    if (($dir=opendir($path))!==false) {
        $glob=array();
        while(($file=readdir($dir))!==false) {
            if (fnmatch($match,$file)) {
                if ((is_dir("$path/$file"))||(!($flags&GLOB_ONLYDIR))) {
                    if ($flags&GLOB_MARK) $file.='/';
                    $glob[]=$file;
                }
            }
        }
        closedir($dir);
        if (!($flags&GLOB_NOSORT)) sort($glob);
        return $glob;
    } else {
        return false;
    }
  }
}

?>
