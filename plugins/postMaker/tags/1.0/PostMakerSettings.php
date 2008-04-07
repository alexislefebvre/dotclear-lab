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

class PostMakerEntry
{
  static private $templatesList = null;
  public $name;
  public $template;
  public $feed;
  
  public function __construct($name,$template,$feed)
  {
    $this->name = $name;
    $this->template = $template;
    $this->feed = $feed;
  }
  
  public function TemplateFile()
  {
    return $this->template.'.'.self::GetTemplateType();
  }
  
  public function Display()
  {
    self::DisplayRow($this->name,$this->template,$this->feed);
  }
  
  static public function GetTemplatesList()
  {
    $templateList = array();
    $extension = '.'.self::GetTemplateType();
    $templateFileList = @glob(DC_POST_MAKER_TPL_FOLDER.'/*'.$extension);
    if (!is_array($templateFileList))
      return $templateList;
    foreach ($templateFileList as $templateFile) {
      $templateName = basename($templateFile,$extension);
      if (!isset($templateList[$templateName]))
        $templateList[$templateName] = $templateName;
    }
    return $templateList;
  }
  
  static public function DisplayEmptyRow()
  {
    self::DisplayRow('','','');
  }
  
  static public function DisplayRow($name,$template,$feed)
  {
    if(self::$templatesList == null)
      self::$templatesList = self::GetTemplatesList();
    print
      '<p><label class="classic">'.
      ' '.form::field(array('pmEntries_names[]'), 25, 25, html::escapeHTML($name)).
      ' '.form::combo(array('pmEntries_templates[]'), self::$templatesList, $template).
      ' '.form::field(array('pmEntries_feeds[]'), 50, 250, html::escapeHTML($feed)).
      '</label></p>';
  }
  
  static public function GetTemplateType()
  {
    global $core;
    $templateTypes = array('xhtml'=>'hentry','wiki'=>'wentry');
    return $templateTypes[$core->auth->getOption('post_format')];
  }

}

class PostMakerSettings
{
  public $values;
  
  public function __construct()
  {
    global $core;
    $core->blog->settings->setNameSpace('postmaker');
    if( $core->blog->settings->entries === null ) {
      $this->values = array();
    } else {
      $this->values = @unserialize($core->blog->settings->entries);
    }
  }
 
  public function Display()
  {
    foreach( $this->values as $entry )
      $entry->Display();
    PostMakerEntry::DisplayEmptyRow();
  }
    
  public function LoadFromHTTP() {
    if( !isset($_POST['pmEntries_names']) )
      return false;
    $names = is_array($_POST['pmEntries_names']) ? $_POST['pmEntries_names'] : array();
    $templates = is_array($_POST['pmEntries_templates']) ? $_POST['pmEntries_templates'] : array();
    $feeds = is_array($_POST['pmEntries_feeds']) ? $_POST['pmEntries_feeds'] : array();
    
    $this->values = array();
    foreach( $names as $pos => $name ) {
      $name = trim($name);
      $template = trim($templates[$pos]);
      $feed = trim($feeds[$pos]);
      if ($name && $template)
        $this->values[$name] = new PostMakerEntry($name,$template,$feed);
    }
    global $core;
    $core->blog->settings->setNameSpace('postmaker');
    $core->blog->settings->put('entries',@serialize($this->values)); // put in blog local settings
    return true;
  }
  
}
?>