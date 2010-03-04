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

define('DC_WIDGET_ADMIN_TPL_CACHE',DC_TPL_CACHE.'/pluginTemplateWidget');

// This class is a template processor dedicated to the admin area. It generates the widget initialization code. 
class templateWidget_WidgetBuilder extends template
{
	function __construct($self_name)
	{
    global $core;
    
    if (!isset($core->themes)) { // In admin area, themes are not usually initialized, but now we might need them...
      $core->themes = new dcModules($core);
      $core->themes->loadModules($core->blog->themes_path,null);
    }
    
		files::makeDir(DC_WIDGET_ADMIN_TPL_CACHE,true);
		parent::__construct(DC_WIDGET_ADMIN_TPL_CACHE,$self_name);

		$this->tag_block = '<tpl:(%1$s)(?:(\s+.*?)>|>)(.*?)</tpl:%1$s>';
		$this->tag_value = '{{tpl:(%s)(\s(.*?))?}}';

    $this->setPath(
      $core->blog->themes_path.'/'.$core->blog->settings->system->theme.'/tpl',
      $core->blog->themes_path.'/default/tpl',
      path::real(dirname(__FILE__).'/../default-templates'),
      $this->getPath()
    );
		
		$this->remove_php = !$core->blog->settings->system->tpl_allow_php;
		$this->use_cache = $core->blog->settings->system->tpl_use_cache;

    $this->addBlock('WidgetName',array($this,'Name'));
    $this->addBlock('WidgetDescription',array($this,'Description'));

    $this->addBlock('WidgetDefineBlock',array($this,'DefineBlock'));
    $this->addValue('WidgetUseBlock',array($this,'UseBlock'));
    $this->addBlock('WidgetPageTypeIf',array($this,'PageTypeIf'));
    $this->addBlock('WidgetSubstring',array($this,'Substring'));

    $this->addValue('WidgetText',array($this,'Text'));
    $this->addBlock('WidgetTextIf',array($this,'TextIf'));
    $this->addBlock('WidgetTextLike',array($this,'TextLike'));
    $this->addValue('WidgetTextMatch',array($this,'TextMatch'));
    $this->addBlock('WidgetTextNotLike',array($this,'TextNotLike'));
    $this->addBlock('WidgetCheckboxIf',array($this,'CheckboxIf'));
    $this->addBlock('WidgetComboIf',array($this,'ComboIf'));
    $this->addValue('WidgetCombo',array($this,'Combo'));

		$core->callBehavior('adminTemplateWidgetBeforeLoad',$this);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////
  // Definition of widget template blocks and values in admin mode, that is to create the corresponding widgets
  
  public function Name($attr,$content) {
    return '<?php $_widgetDefinition["name"] = __("'.addslashes($content).'"); ?>';
  }
  
  public function Description($attr,$content) {
    return '<?php $_widgetDefinition["description"] = __("'.addslashes($content).'"); ?>';
  }

  // Define a block to be reused
  public function DefineBlock($attr,$content) {
    return $content;
  }

  // Use a block
  public function UseBlock($attr) {
    return '';
  }

  // Test page type - useful to display a widget on home page only
  public function PageTypeIf($attr,$content) {
    return $content;
  }

  public function Substring($attr,$content) {
    return $content;
  }

  public function DefineSetting($name,$title,$value,$type,$order,$options=null) {
    $settingVar = '$_widgetDefinition["settings"]["'.$name.'"]';
    $code = '<?php if( !isset('.$settingVar.') ) { '.$settingVar.' = ';
    if( $options == null )
      $code .= 'array("'.$name.'",__("'.$title.'"),__("'.$value.'"),"'.$type.'");';
    else
      $code .= 'array("'.$name.'",__("'.$title.'"),__("'.$value.'"),"'.$type.'",'.$options.');';
    $code .= '$_widgetDefinition["order"]["'.$name.'"] = '.$order.'; } ?>';
    return $code;
  }

  public function DefineError($attr,$missing) {
    if (isset($attr['name']))
      $base = '<?php if( !isset($_widgetDefinition["settings"]["'.$attr['name'].'"]) )';
    else
      $base = '<?php';
    return $base.' $_widgetDefinition["error"] = "Missing attr '.$missing.' field" ?>';
  }

  // Widget text field
  public function Text($attr) {
    if( !isset($attr['name']) || !isset($attr['title']) )
      return $this->DefineError($attr,'name or title on Text');
    $settingType = isset($attr['type']) ? $attr['type'] : 'text' ;
    $settingValue = isset($attr['default']) ? $attr['default'] : '' ;
    $settingOrder = isset($attr['order']) ? $attr['order'] : '100' ;
    return $this->DefineSetting($attr['name'],$attr['title'],$settingValue,$settingType,$settingOrder);
  }
  
  // Widget text field
  public function TextIf($attr,$content) {
    return $this->Text($attr).$content;
  }
  
  // Widget text field
  public function TextLike($attr,$content) {
    return $this->Text($attr).$content;
  }
 
  // Widget text field
  public function TextMatch($attr) {
    return '';
  }
  
  // Widget text field
  public function TextNotLike($attr,$content) {
    return $this->Text($attr).$content;
  }
 
  // Widget checkbox field
  public function CheckboxIf($attr,$content) {
    if( !isset($attr['name']) || !isset($attr['title']) )
      return $this->DefineError($attr,'name or title on Checkbox');
    $settingValue = isset($attr['default']) ? $attr['default'] : '' ;
    $settingOrder = isset($attr['order']) ? $attr['order'] : '100' ;
    return $this->DefineSetting($attr['name'],$attr['title'],$settingValue,'check',$settingOrder).$content;
  }
  
  // Widget combo field
  public function Combo($attr) {
    if( !isset($attr['name']) || !isset($attr['title']) || !isset($attr['options']) )
      return $this->DefineError($attr,'name, title or options on Combo');
    $settingValue = isset($attr['default']) ? $attr['default'] : '' ;
    $settingOptions = explode(':',$attr['options']);
    foreach ($settingOptions as $i => $settingOption)
      $settingOptions[$i] = '"'.$settingOption.'"=>__("'.$settingOption.'")';
    $settingOptions = 'array('.implode(',',$settingOptions).')';
    $settingOrder = isset($attr['order']) ? $attr['order'] : '100' ;
    return $this->DefineSetting($attr['name'],$attr['title'],$settingValue,'combo',$settingOrder,$settingOptions);
  }
  
  // Widget combo field
  public function ComboIf($attr,$content) {
    return $this->Combo($attr).$content;
  }
}

?>