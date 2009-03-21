<?php
# ***** BEGIN LICENSE BLOCK *****
# Copyright (c) 2009 Olivier Azeau and contributors. All rights reserved.
#
# This class is free software; you can redistribute it and/or modify
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
# ***** END LICENSE BLOCK *****

class SettingsList
{
  private $items;
  private $namespace;
  private $listKey;
  private $class;
  private $itemKey;
  private $httpTypes;
  
  public function __construct($namespace,$listKey,$class,$itemKey,$httpTypes)
  {
    global $core;
    $this->namespace = $namespace;
    $this->listKey = $listKey;
    $this->itemKey = $itemKey;
    $this->httpTypes = $httpTypes;
    $this->class = new ReflectionClass($class);
    $core->blog->settings->setNameSpace($namespace);
    if( $core->blog->settings->$listKey === null ) {
      $this->items = array();
    } else {
      $this->items = @unserialize($core->blog->settings->$listKey);
    }
  }
 
  public function IsStorable($property)
  {
      return $property->isPublic() && ($property->getName() != $this->itemKey);
  }
 
  public function UpdateWith($item)
  {
    $itemKey = $this->itemKey;
    $itemKeyValue = $item->$itemKey;
    if( !$itemKeyValue )
      return;
    if( isset($this->items[$itemKeyValue]) ) {
      foreach( $this->class->getProperties() as $property ) {
        if(!$this->IsStorable($property))
          continue;
        $propertyValue = $property->getValue($this->items[$itemKeyValue]);
        $property->setValue($item, $propertyValue);
      }
    }
    $this->items[$itemKeyValue] = $item;
  }
 
  public function Display()
  {
    foreach( $this->items as $item )
      $item->Display($this);
  }
 
  public function GetItem($id)
  {
    return @$this->items[$id];
  }
    
  public function GetHttpDefinition($propertyName) {
    return array($this->GetHttpProperty($propertyName).'[]');
  }
    
  private function GetHttpProperty($propertyName) {
    return $this->namespace.'_'.$this->listKey.'_'.$propertyName;
  }
    
  public function Store() {
    global $core;
    $core->blog->settings->setNameSpace($this->namespace);
    $core->blog->settings->put($this->listKey,@serialize($this->items)); // put in blog local settings
  }
    
  public function LoadFromHttp() {   
    $itemKey = $this->itemKey;
    $itemKeyValues = @$_POST[$this->GetHttpProperty($itemKey)];
    if( !$itemKeyValues )
      return false;

    // Create missing items from IDs found in http post
    foreach( $itemKeyValues as $idx => $itemKeyValue ) {
      if( isset($this->items[$itemKeyValue]) )
        continue;
      $this->items[$itemKeyValue] = $this->class->newInstance();
      $this->items[$itemKeyValue]->$itemKey = $itemKeyValue;
    }
    
    // update property values from http post
    foreach( $this->class->getProperties() as $property ) {
      if(!$this->IsStorable($property))
        continue;
      $httpProperty = $this->GetHttpProperty($property->getName());
      if( !isset($_POST[$httpProperty]) )
        continue;
      $loadFunction = 'LoadFromHttp_'.$this->httpTypes[$property->getName()];
      $this->$loadFunction($property,$httpProperty);
    }

    return true;
  }
    
  private function LoadFromHttp_checkbox($property,$httpProperty) {
      foreach( $this->items as $item )
        $property->setValue($item, false);
      foreach( $_POST[$httpProperty] as $idx => $propertyValue )
        $property->setValue($this->items[$propertyValue], true);
  }
  
}




?>