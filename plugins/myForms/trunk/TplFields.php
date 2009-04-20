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

$core->tpl->addValue('myformsFieldValue',array('MyFormsTplFields','FieldValue'));
$core->tpl->addValue('myformsFileFieldValue',array('MyFormsTplFields','FileFieldValue'));
$core->tpl->addBlock('myformsFieldWarning',array('MyFormsTplFields','FieldWarning'));
$core->tpl->addBlock('myformsFieldMatches',array('MyFormsTplFields','FieldMatches'));
$core->tpl->addBlock('myformsTextField',array('MyFormsTplFields','TextField'));
$core->tpl->addBlock('myformsTextArea',array('MyFormsTplFields','TextArea'));
$core->tpl->addValue('myformsCheckbox',array('MyFormsTplFields','Checkbox'));
$core->tpl->addValue('myformsRadioButton',array('MyFormsTplFields','RadioButton'));
$core->tpl->addValue('myformsFileField',array('MyFormsTplFields','FileField'));
$core->tpl->addValue('myformsHiddenField',array('MyFormsTplFields','HiddenField'));
$core->tpl->addValue('myformsCaptchaField',array('MyFormsTplFields','CaptchaField'));
$core->tpl->addBlock('myformsCaptchaWarning',array('MyFormsTplFields','CaptchaWarning'));
$core->tpl->addBlock('myformsSubmit',array('MyFormsTplFields','Submit'));

class MyFormsTplFields
{
  // Field Attributes
  private static function GetAttributes($attr,$name=false,$id=false)
  {
    if( $name )
      $attr['name'] = $name;
    if( !$id )
      $id = $attr['name'];
    $attributes = "id='myforms_".$id."' name='myforms[".$attr['name']."]'";
    foreach( $attr as $k => $v )
      if( $k != 'name' )
        $attributes .= " ".$k."='".$v."'";
    return $attributes;
  }
  
  // Field Value
  private static function getFieldValue($attr,$content,$asHtml)
  {
    if( $asHtml )
      return '<?php ob_start(); ?>'.$content.'<?php echo nl2br(htmlentities(MyForms::getFieldValue("'.$attr['name'].'",ob_get_clean()),ENT_QUOTES,"UTF-8")); ?>';
    else
      return '<?php ob_start(); ?>'.$content.'<?php echo MyForms::getFieldValue("'.$attr['name'].'",ob_get_clean()); ?>';
  }
  
  // Field Value
  public static function FieldValue($attr)
  {
    return self::getFieldValue($attr,'',isset($attr['html']));
  }
  
  // File Field Value
  public static function FileFieldValue($attr)
  {
    return '<?php echo MyForms::getFileFieldValue("'.$attr['name'].'","'.$attr['data'].'"); ?>';
  }
  
  // Validate Field
  public static function FieldWarning($attr,$content)
  {
    return '<?php if( !MyForms::validateField("'.$attr['name'].'","'.$attr['validate'].'") ) { ?>'.$content.'<?php } ?>';
  }
  
  // Field Matching
  public static function FieldMatches($attr,$content)
  {
    return '<?php if( MyForms::matchField("'.$attr['name'].'","'.$attr['pattern'].'") ) { ?>'.$content.'<?php } ?>';
  }
  
  // Display Text Field
  public static function TextField($attr,$content)
  {
    return "<input type='text' ".self::GetAttributes($attr)." value='".self::getFieldValue($attr,$content)."' />";
  }
  
  // Display TextArea Field
  public static function TextArea($attr,$content)
  {
    return "<textarea ".self::GetAttributes($attr).">".self::getFieldValue($attr,$content)."</textarea>";
  }

  // Display Checkbox Field
  public static function Checkbox($attr)
  {
    $checked = '<?php echo MyForms::getFieldValue("'.$attr['name'].'","")?" checked=\'1\'":""; ?>';
    return "<input type='checkbox' ".self::GetAttributes($attr)." value='checked'".$checked." />";
  }

  // Display Radio Button Field
  public static function RadioButton($attr)
  {
    $checked = '<?php echo (MyForms::getFieldValue("'.$attr['name'].'","")=="'.$attr['value'].'")?" checked=\'1\'":""; ?>';
    return "<input type='radio' ".self::GetAttributes($attr,$attr['name'],$attr['value']).$checked." />";
  }

  // Display File Upload Field
  public static function FileField($attr)
  {
    return "<input type='file' ".self::GetAttributes($attr)." />";
  }

  // Display Hidden Field
  public static function HiddenField($attr)
  {
    return "<input type='hidden' ".self::GetAttributes($attr)." value='".self::getFieldValue($attr,'')."' />";
  }

  // Display Captcha Field
  public static function CaptchaField($attr,$content)
  {
    return '<?php MyFormsCaptcha::display("'.self::GetAttributes($attr,'captcharef').'","'.self::GetAttributes($attr,'captcha').'"); ?>';
  }

  // Display Warning when Captcha code do not match
  public static function CaptchaWarning($attr,$content)
  {
    return '<?php if( !MyForms::validateCaptcha() ) { ?>'.$content.'<?php } ?>';
  }

  // Display Submit Field
  public static function Submit($attr,$content)
  {
    return "<input type='submit' ".self::GetAttributes($attr)." value='".self::getFieldValue($attr,$content)."' />";
  }

}
?>