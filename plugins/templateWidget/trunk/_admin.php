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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

require_once(dirname(__FILE__).'/WidgetBuilder.php');
$core->addBehavior('initWidgets',array('templateWidgetAdmin','InitWidgets'));

class templateWidgetAdmin
{
  // behaviour for widget initialization
  public static function InitWidgets(&$widgets) {
    global $core;
    
    $core->widgetTpl = new templateWidget_WidgetBuilder('$core->widgetTpl');
    
    // loop on all template widgets definitions and create associated widgets
    foreach (self::GetAllActiveWidgets() as $widgetId => $widgetFile) {
      self::CreateWidget($widgets,$widgetId,$widgetFile);
    }
    
    $core->widgetTpl = null;
  }

  // get list of all active template widgets
  public static function GetAllActiveWidgets() {
    global $core;
    $widgets = array();
    foreach ($core->widgetTpl->getPath() as $templateFolder) {
      $widgetFilesForFolder = @glob($templateFolder.'/*.widget.html');
      if (!is_array($widgetFilesForFolder))
        continue;
      foreach ($widgetFilesForFolder as $widgetFile) {
        $widgetId = basename($widgetFile,'.widget.html');
        if (!isset($widgets[$widgetId]))
          $widgets[$widgetId] = $widgetFile;
      }
    }
    return $widgets;
  }
  
  // behaviour for widget initialization
  public static function CreateWidget(&$widgets,$widgetId,$widgetFile) {
    global $core;
    
    $_widgetDefinition = array('settings'=>array(),'order'=>array());
    ob_start();
		include $core->widgetTpl->getFile(basename($widgetFile));
		ob_end_clean();
    
    if (isset($_widgetDefinition['error']))
      die('Template Widget Error: '.$_widgetDefinition['error'].' in file '.$widgetFile);
    
    $widgets->create($widgetId,__($_widgetDefinition['name']),array('templateWidgetBlocksAndValues','WidgetCore'));
    
    asort($_widgetDefinition['order']);
    foreach ($_widgetDefinition['order'] as $settingName => $settingOrder) {
      call_user_func_array(array($widgets->$widgetId, 'setting'), $_widgetDefinition['settings'][$settingName]);
    }
  }
}

?>