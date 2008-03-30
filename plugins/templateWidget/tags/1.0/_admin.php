<?php
# ***** BEGIN LICENSE BLOCK *****
# Copyright (c) 2008 Olivier Azeau and contributors. All rights
# reserved.
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

$core->addBehavior('initWidgets',array('templateWidgetAdmin','InitWidgets'));

define('DC_WIDGET_ADMIN_TPL_CACHE',DC_TPL_CACHE.'/cbtpl/templateWidget');

class templateWidgetAdmin
{
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
    global $core, $_ctx;
    
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
  
  // behaviour for widget initialization
  public static function InitCore() {
    global $core;

    if (!isset($core->themes)) {
      $core->themes = new dcModules($core);
      $core->themes->loadModules($core->blog->themes_path,null);
    }

    if (!is_dir(DC_WIDGET_ADMIN_TPL_CACHE))
			mkdir(DC_WIDGET_ADMIN_TPL_CACHE);
    $core->widgetTpl = new template(DC_WIDGET_ADMIN_TPL_CACHE,'$core->widgetTpl');
    $core->widgetTpl->setPath(
      $core->blog->themes_path.'/'.$core->blog->settings->theme,
      $core->blog->themes_path.'/default',
      dirname(__FILE__).'/default-templates',
      $core->widgetTpl->getPath()
    );

    $core->widgetTpl->addBlock('WidgetName',array('templateWidgetAdmin','Name'));
    $core->widgetTpl->addBlock('WidgetDescription',array('templateWidgetAdmin','Description'));

    $core->widgetTpl->addBlock('WidgetDefineBlock',array('templateWidgetAdmin','DefineBlock'));
    $core->widgetTpl->addValue('WidgetUseBlock',array('templateWidgetAdmin','UseBlock'));
    $core->widgetTpl->addBlock('WidgetPageTypeIf',array('templateWidgetAdmin','PageTypeIf'));

    $core->widgetTpl->addValue('WidgetText',array('templateWidgetAdmin','Text'));
    $core->widgetTpl->addBlock('WidgetCheckboxIf',array('templateWidgetAdmin','CheckboxIf'));
    $core->widgetTpl->addBlock('WidgetComboIf',array('templateWidgetAdmin','ComboIf'));
    $core->widgetTpl->addValue('WidgetCombo',array('templateWidgetAdmin','Combo'));
  }
  
  // behaviour for widget initialization
  public static function InitWidgets(&$widgets) {
    global $core;
    
    self::InitCore();
    
    // loop on all template widgets definitions and create associated widgets
    foreach (self::GetAllActiveWidgets() as $widgetId => $widgetFile) {
      self::CreateWidget($widgets,$widgetId,$widgetFile);
    }
    
    $core->widgetTpl = null;
  }
  
  
  //////////////////////////////////////////////////////////////////////////////////////////////
  // Definition of widget template blocks and values in admin mode, that is to create the corresponding widgets
  
  public static function Name($attr,$content) {
    return '<?php $_widgetDefinition["name"] = __("'.addslashes($content).'"); ?>';
  }
  
  public static function Description($attr,$content) {
    return '<?php $_widgetDefinition["description"] = __("'.addslashes($content).'"); ?>';
  }

  // Define a block to be reused
  public static function DefineBlock($attr,$content) {
    return $content;
  }

  // Use a block
  public static function UseBlock($attr) {
    return '';
  }

  // Test page type - useful to display a widget on home page only
  public static function PageTypeIf($attr,$content) {
    return $content;
  }

  public static function DefineSetting($name,$title,$value,$type,$order,$options=null) {
    $settingVar = '$_widgetDefinition["settings"]["'.$name.'"]';
    $code = '<?php if( !isset('.$settingVar.') ) { '.$settingVar.' = ';
    if( $options == null )
      $code .= 'array("'.$name.'",__("'.$title.'"),__("'.$value.'"),"'.$type.'");';
    else
      $code .= 'array("'.$name.'",__("'.$title.'"),__("'.$value.'"),"'.$type.'",'.$options.');';
    $code .= '$_widgetDefinition["order"]["'.$name.'"] = '.$order.'; } ?>';
    return $code;
  }

  public static function DefineError($attr,$missing) {
    if (isset($attr['name']))
      $base = '<?php if( !isset($_widgetDefinition["settings"]["'.$attr['name'].'"]) )';
    else
      $base = '<?php';
    return $base.' $_widgetDefinition["error"] = "Missing attr '.$missing.' field" ?>';
  }

  // Widget text field
  public static function Text($attr) {
    if( !isset($attr['name']) || !isset($attr['title']) )
      return self::DefineError($attr,'name or title on Text');
    $settingType = isset($attr['type']) ? $attr['type'] : 'text' ;
    $settingValue = isset($attr['default']) ? $attr['default'] : '' ;
    $settingOrder = isset($attr['order']) ? $attr['order'] : '100' ;
    return self::DefineSetting($attr['name'],$attr['title'],$settingValue,$settingType,$settingOrder);
  }
  
  // Widget checkbox field
  public static function CheckboxIf($attr,$content) {
    if( !isset($attr['name']) || !isset($attr['title']) )
      return self::DefineError($attr,'name or title on Checkbox');
    $settingValue = isset($attr['default']) ? $attr['default'] : '' ;
    $settingOrder = isset($attr['order']) ? $attr['order'] : '100' ;
    return self::DefineSetting($attr['name'],$attr['title'],$settingValue,'check',$settingOrder).$content;
  }
  
  // Widget combo field
  public static function Combo($attr) {
    if( !isset($attr['name']) || !isset($attr['title']) || !isset($attr['options']) )
      return self::DefineError($attr,'name, title or options on Combo');
    $settingValue = isset($attr['default']) ? $attr['default'] : '' ;
    $settingOptions = explode(':',$attr['options']);
    foreach ($settingOptions as $i => $settingOption)
      $settingOptions[$i] = '"'.$settingOption.'"=>__("'.$settingOption.'")';
    $settingOptions = 'array('.implode(',',$settingOptions).')';
    $settingOrder = isset($attr['order']) ? $attr['order'] : '100' ;
    return self::DefineSetting($attr['name'],$attr['title'],$settingValue,'combo',$settingOrder,$settingOptions);
  }
  
  // Widget combo field
  public static function ComboIf($attr,$content) {
    return self::Combo($attr).$content;
  }
}

?>