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
  public static function InitWidgets(&$widgets) {
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
      $widgetFilesForFolder = @glob($templateFolder.'/*.widget.html');
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
  private static function CreateWidget(&$widgets,$widgetDefinition) {
    $widgetId = $widgetDefinition['id'];
    $widgets->create($widgetId,__($widgetDefinition['name']),array('templateWidgetBlocksAndValues','WidgetCore'));
    
    asort($widgetDefinition['order']);
    foreach ($widgetDefinition['order'] as $settingName => $settingOrder) {
      call_user_func_array(array($widgets->$widgetId, 'setting'), $widgetDefinition['settings'][$settingName]);
    }
  }
}

?>