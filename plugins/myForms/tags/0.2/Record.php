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

class MyFormsRecord
{
  private $cursor, $keys, $table;
  
  public function __construct($table,$usePrefix=true) {
    global $core;
    $this->table = ($usePrefix?$core->prefix:'').$table;
    $this->cursor = $core->con->openCursor($this->table);
    $this->keys = array();
  }
  
  public function keySelection() {
    return implode(" AND ", $this->keys);
  }
  
  public function set($fieldname,$value,$iskey) {
    $this->cursor->$fieldname = $value;
    if($iskey)
      $this->keys[] = $fieldname."='".$value."'";
  }
  
  public function insert() {
    try
    {
      $this->cursor->insert();
      return 0;
    }
    catch (Exception $e)
    {
      return array('database', $e->getMessage(), $e);
    }
  }
  
  public function insertOrUpdate() {
    try
    {
      global $core;
      $findRecord = $core->con->select('SELECT COUNT(*) FROM '.$this->table.' WHERE '.$this->keySelection());
      if( $findRecord->f(0) > 0 )
        $this->cursor->update('WHERE '.$this->keySelection());
      else
        $this->cursor->insert();
      return 0;
    }
    catch (Exception $e)
    {
      return array('database', $e->getMessage(), $e);
    }
  }
  
}
?>