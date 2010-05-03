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

class MyFormsTextAreaField extends MyFormsField
{
  public function __construct() {
    parent::__construct(func_get_args());
  }
  
  public function Display() {
    return "<textarea ".$this->AttributesAsString().">".$this->ValueDisplay()."</textarea>";
  }
  
  public static function Register() {
    global $core;
    $core->tpl->addBlock('myformsTextArea',array('MyFormsTextAreaField','TplDisplay'));
    $core->tpl->addBlock('myformsTextArea_Declare',array('MyFormsTextAreaField','TplDeclare'));
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
}

MyFormsTextAreaField::Register();

?>