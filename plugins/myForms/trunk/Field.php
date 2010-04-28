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

abstract class MyFormsField
{
  public $attributes, $content;
  
  protected function __construct($args) {
    $attr = $args[0];
    if( is_array($attr) || get_class($attr)=='ArrayObject' ) {
      $this->attributes = $attr;
      $this->content = $args[1];
    } else {
      $this->attributes = self::GetAttributesFromArguments($args);
      $this->content = "";
    }
    $this->attributes['id'] = $this->attributes['name'];
  }
    public function Name() {
    return $this->attributes['name'];
  }
    public function Input($defaultValue=false) {
    global $_REQUEST;
    if(isset($_REQUEST["myforms"][$this->Name()])) {
      return $_REQUEST["myforms"][$this->Name()];
    } else {
      return $defaultValue;
    }
  }
    public function Value($defaultValue=false) {
    return $this->Input($defaultValue);
  }
  
  public function IsValid($condition) {
    global $_REQUEST;
    $fieldIsValid = !isset($_REQUEST["myforms"]) || preg_match('#'.$condition.'#', @$_REQUEST["myforms"][$this->Name()]);
    if(!$fieldIsValid)
      MyForms::InvalidateForm();
    return $fieldIsValid;
  }

  public function InputMatches($pattern) {
    return preg_match('#'.$pattern.'#', $this->Input());
  }
  
  public function Matches($pattern) {
    return preg_match('#'.$pattern.'#', $this->Value());
  }
  
  
  //=========================
  // template code generation
    abstract public function Display();
  
  public function AttributesAsString() {
    $str = "id='myforms_".$this->attributes['id']."' name='myforms[".$this->attributes['name']."]'";
    foreach( $this->attributes as $k => $v )
      if( $k != 'name' && $k != 'id' )
        $str .= " ".$k."='".$v."'";
    return $str;
  }
  
  public function ValueCode($defaultValue='') {
    return self::BuildValueCode($this->attributes,$defaultValue);
  }
  
  public function ValueDisplay() {
    return self::BuildValueDisplay($this->attributes,$this->content,isset($this->attributes['html']));
  }
  
  
  //=========================
  // static utility functions
  
  public static function BuildDeclaration($class,$attr,$content) {
    $attrStrings = array();
    foreach( $attr as $k => $v )
      $attrStrings[] = "'".$k."','".$v."'";
    return "<?php \$field = new ".$class."(".implode(",",$attrStrings)."); ?>\n"
          .$content
          ."<?php \$fields->add(\$field); ?>\n";
  }
  
  private static function BuildObject($class,$attr,$content) {
    return new $class($attr,$content);
  }

  public static function DisplayObject($class,$attr,$content)
  {
    $field = self::BuildObject($class,$attr,$content);
    return $field->Display();
  }
  
  public static function GetAttributesFromArguments($args) {
    $attr = array();
    for($i = 0; $i < count($args); $i=$i+2)
      $attr[$args[$i]] = $args[$i+1];
    return $attr;
  }
  
  protected static function BuildValueCode($attr,$defaultValue)
  {
    return "MyForms::getField('".$attr['name']."')->Value(".$defaultValue.")";
  }
  
  protected static function BuildValueDisplay($attr,$content,$asHtml=false)
  {
    $valueCode = self::BuildValueCode($attr,"ob_get_clean()");
    if( $asHtml )
      return "<?php ob_start(); ?>".$content."<?php print nl2br(htmlentities(".$valueCode.",ENT_QUOTES,'UTF-8')); ?>";
    else
      return "<?php ob_start(); ?>".$content."<?php print ".$valueCode."; ?>";
  }

  
  //==========================
  // template tags definitions
  
  public static function Register() {
    global $core;
    $core->tpl->addValue('myformsFieldValue',array('MyFormsField','FieldValue'));
    $core->tpl->addBlock('myformsFieldMatches',array('MyFormsField','FieldMatches'));
    $core->tpl->addBlock('myformsFieldInputMatches',array('MyFormsField','FieldInputMatches'));
    $core->tpl->addBlock('myformsFieldWarning',array('MyFormsField','FieldWarning'));
  }
  
  // Field Value
  public static function FieldValue($attr)
  {
    return self::BuildValueDisplay($attr,'',isset($attr['html']));
  }
  
  // Field Matching
  public static function FieldMatches($attr,$content)
  {
    return '<?php if( MyForms::getField("'.$attr['name'].'")->Matches("'.$attr['pattern'].'") ) { ?>'.$content.'<?php } ?>';
  }
  
  // Field Matching
  public static function FieldInputMatches($attr,$content)
  {
    return '<?php if( MyForms::getField("'.$attr['name'].'")->InputMatches("'.$attr['pattern'].'") ) { ?>'.$content.'<?php } ?>';
  }
  
  // Field Matching
  public static function FieldWarning($attr,$content)
  {
    return '<?php if( MyForms::getField("'.$attr['name'].'")->IsValid("'.$attr['validate'].'") ) { ?>'.$content.'<?php } ?>';
  }
}

MyFormsField::Register();

?>