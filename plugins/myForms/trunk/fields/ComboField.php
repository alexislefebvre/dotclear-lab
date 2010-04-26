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

class MyFormsComboChoice
{
  public $id, $value, $isSelected;
}

class MyFormsComboField extends MyFormsField
{
  private $choices;
  
  public function __construct() {
    parent::__construct(func_get_args());
  }
  
  public function addChoice($id,$value,$isSelected) {
    $choice = new MyFormsComboChoice();
    $choice->id = $id;
    $choice->value = $value;
    $choice->isSelected = $isSelected;
    $this->choices[$id] = $choice;
  }
  
  public function Display() {
    return "<select ".$this->AttributesAsString().">".$this->content."</select>";
  }
  
  // override Field Value : use string value from selected choice instead of id
  public function Value($defaultValue=false) {
    return $this->choices[$this->Input($defaultValue)]->value;
  }
  
  public static function Register() {
    global $core;
    $core->tpl->addBlock('myformsCombo',array('MyFormsComboField','Combo'));
    $core->tpl->addBlock('myformsCombo_Declare',array('MyFormsComboField','Combo_Declare'));
    $core->tpl->addBlock('myformsComboChoice',array('MyFormsComboField','ComboChoice'));
    $core->tpl->addBlock('myformsComboChoice_Declare',array('MyFormsComboField','ComboChoice_Declare'));
  }
  
  // Display Combo Field
  public static function Combo($attr,$content)
  {
    return MyFormsField::DisplayObject(__CLASS__,$attr,$content);
  }
  
  // Declare Combo Field
  public static function Combo_Declare($attr,$content)
  {
    return MyFormsField::BuildDeclaration(__CLASS__,$attr,$content);
  }
  
  // Display choice in Combo Field
  public static function ComboChoice($attr,$content)
  {
    return "<option value='".$attr['id']."'".(isset($attr['selected'])?" selected='selected'":"")."><?php ob_start(); ?>".$content."<?php print ob_get_clean(); ?></option>";
  }
  
  // Declare choice in Combo Field
  public static function ComboChoice_Declare($attr,$content)
  {
    return "<?php ob_start(); ?>"
          .$content
          ."<?php \$field->addChoice('".$attr['id']."',ob_get_clean(),".(isset($attr['selected'])?1:0)."); ?>\n";
 }
}
?>