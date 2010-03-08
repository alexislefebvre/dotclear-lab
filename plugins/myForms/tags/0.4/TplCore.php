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

$core->tpl->addValue('myformsContext',array('MyFormsTplCore','Context'));
$core->tpl->addBlock('myformsPassword',array('MyFormsTplCore','Password'));
$core->tpl->addBlock('myformsInfo',array('MyFormsTplCore','Info'));
$core->tpl->addValue('myformsInfo',array('MyFormsTplCore','DisplayInfo'));
$core->tpl->addBlock('myformsOnInit',array('MyFormsTplCore','OnInit'));
$core->tpl->addValue('myformsDisplay',array('MyFormsTplCore','Display'));
$core->tpl->addBlock('myformsOnSubmit',array('MyFormsTplCore','OnSubmit'));
$core->tpl->addBlock('myformsOnSuccess',array('MyFormsTplCore','OnSuccess'));
$core->tpl->addValue('myformsOnSuccess',array('MyFormsTplCore','OnSuccessGoto'));
$core->tpl->addBlock('myformsOnError',array('MyFormsTplCore','OnError'));
$core->tpl->addValue('myformsOnErrorGoto',array('MyFormsTplCore','OnErrorGoto'));
$core->tpl->addBlock('myformsErrors',array('MyFormsTplCore','Errors'));
$core->tpl->addValue('myformsError',array('MyFormsTplCore','Error'));

class MyFormsTplCore
{
  // functions management
  private static function DefineFunction($name,$content)
  {
    return '<?php
function '.self::GetFunction($name).'() { global $core, $_ctx; ?>'.$content.'<?php
} ?>';
  }
  
  public static function GetFunction($name)
  {
    return 'myforms_'.ereg_replace("[^A-Za-z0-9]", "", MyForms::$formID ).'_'.$name;
  }

  // Check the form context
  public static function Context($attr)
  {
    $checks = '<?php ';
    if( isset($attr['query']) )
      $checks .= 'MyForms::checkQueryMatches("'.$attr['query'].'");';
    if( isset($attr['blog']) )
      $checks .= 'MyForms::checkBlogMatches("'.$attr['blog'].'");';
    $checks .= ' ?>';
    return $checks;
  }

  // Define the form password
  public static function Password($attr,$content)
  {
    return '<?php MyForms::password("'.$content.'"); ?>';
  }

  // Define the form information
  public static function Info($attr,$content)
  {
    return self::DefineFunction('Info_'.$attr['name'],$content);
  }

  // Display the current form information
  public static function DisplayInfo($attr)
  {
    return '<?php MyForms::info("'.$attr['name'].'"); ?>';
  }

  // Process / Display the current form
  public static function Display($attr)
  {
    return '<?php MyForms::display(); ?>';
  }
  
  // Define the form fields
  public static function OnInit($attr,$content)
  {
    return self::DefineFunction('Display',"<form action='<?php print \$core->blog->url; ?>form' method='post' enctype='multipart/form-data'><input type='hidden' name='myforms[formID]' value='".MyForms::$formID."' />".$content."</form>");
  }
  
  // Define the form actions
  public static function OnSubmit($attr,$content)
  {
    return self::DefineFunction('OnSubmit_'.$attr['name'],$content).'<?php MyForms::registerEvent("'.$attr['name'].'"); ?>';
  }
  
  // Display the action result
  public static function OnSuccess($attr,$content)
  {
    return '<?php if(!MyForms::hasErrors()) { ?>'.$content.'<?php } ?>';
  }
  public static function OnError($attr,$content)
  {
    if( isset($attr['class']) || isset($attr['message']) )
      return '<?php if(MyForms::hasError("'.$attr['class'].'","'.$attr['message'].'")) { ?>'.$content.'<?php } ?>';
    else
      return '<?php if(MyForms::hasErrors()) { ?>'.$content.'<?php } ?>';
  }
  
  // Move to another form
  public static function OnSuccessGoto($attr,$content)
  {
    return '<?php if(!MyForms::hasErrors()) MyForms::goto("'.$attr['goto'].'"); ?>';
  }
  public static function OnErrorGoto($attr,$content)
  {
    if( isset($attr['class']) || isset($attr['message']) )
      return '<?php if(MyForms::hasError("'.$attr['class'].'","'.$attr['message'].'")) MyForms::goto("'.$attr['goto'].'"); ?>';
    else
      return '<?php if(MyForms::hasErrors()) MyForms::goto("'.$attr['goto'].'"); ?>';
  }

  public static $currentError;
  public static function Errors($attr,$content)
  {
    return 
    '<?php foreach(MyForms::allErrors() as MyFormsTplCore::$currentError) :
    ?>'.
    $content.
    '<?php endforeach; ?>';
  }
  public static function Error($attr,$content)
  {
    if($attr['text'] == 'class')
      return '<?php print MyFormsTplCore::$currentError[0]; ?>';
    else if($attr['text'] == 'message')
      return '<?php print MyFormsTplCore::$currentError[1]; ?>';
    else
      return '';
  }

}
?>