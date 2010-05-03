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

class MyFormsFileField extends MyFormsField
{
  public function __construct() {
    parent::__construct(func_get_args());
  }
  
  public function Display() {
    return "<input type='file' ".$this->AttributesAsString()." />";
  }
  
  protected function FileData($id) {
		global $_FILES;
    return $_FILES['myforms'][$id][$this->Name()];
  }
  
  public function FileName() {
    return $this->FileData('name');
  }
  
  public function FileType() {
    return $this->FileData('type');
  }
  
  public function FileTmp() {
    return $this->FileData('tmp_name');
  }
  
  public function FileError() {
    return $this->FileData('error');
  }
  
  public function FileSize() {
    return $this->FileData('size');
  }
  
	//============================
	
  public static function Register() {
    global $core;
    $core->tpl->addValue('myformsFileField',array('MyFormsFileField','TplDisplay'));
    $core->tpl->addValue('myformsFileField_Declare',array('MyFormsFileField','TplDeclare'));
    $core->tpl->addValue('myformsFileFieldValue',array('MyFormsFileField','TplDisplayValue'));
    $core->tpl->addValue('myformsFileFieldValue_Declare',array('MyFormsFileField','TplDeclare'));
  }
  
  // Display Field
  public static function TplDisplay($attr,$content)
  {
    return parent::DisplayObject(__CLASS__,$attr,$content);
  }
  
  // Declare Field
  public static function TplDeclare($attr,$content)
  {
    return parent::BuildDeclaration(__CLASS__,$attr,$content);
  }
  
  // Display Field Value
  public static function TplDisplayValue($attr,$content)
  {
		if( $attr['output'] == 'content' )
			return '<?php readfile(MyForms::getField("'.$attr['name'].'")->FileTmp()); ?>';
		else if( $attr['output'] == 'type' )
			return '<?php readfile(MyForms::getField("'.$attr['name'].'")->FileType()); ?>';
  }
}

MyFormsFileField::Register();

?>