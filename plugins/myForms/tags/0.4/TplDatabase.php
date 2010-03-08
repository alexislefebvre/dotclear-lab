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

require_once("Record.php");

$core->tpl->addBlock('myformsInsertRecord',array('MyFormsTplDatabase','InsertRecord'));
$core->tpl->addBlock('myformsInsertOrUpdateRecord',array('MyFormsTplDatabase','InsertOrUpdateRecord'));
$core->tpl->addBlock('myformsRecordField',array('MyFormsTplDatabase','RecordField'));

$core->tpl->addBlock('myformsDbSelect',array('MyFormsTplDatabase','DbSelect'));
$core->tpl->addBlock('myformsDbRecord',array('MyFormsTplDatabase','DbRecord'));
$core->tpl->addValue('myformsDbField',array('MyFormsTplDatabase','DbField'));

class MyFormsTplDatabase
{
  public static function InsertRecord($attr,$content)
  {
    return '<?php $record=new MyFormsRecord("'.$attr['table'].'"); ?>'.$content.'<?php MyForms::execute( $record->insert() ); ?>';
  }
  
  public static function InsertOrUpdateRecord($attr,$content)
  {
    return '<?php $record=new MyFormsRecord("'.$attr['table'].'"); ?>'.$content.'<?php MyForms::execute( $record->insertOrUpdate() ); ?>';
  }
  
  public static function RecordField($attr,$content)
  {
    return '<?php ob_start(); ?>'.$content.'<?php $record->set("'.$attr['name'].'",ob_get_clean(),'.(isset($attr['key'])?'true':'false').'); ?>';
  }
  
  public static function DbSelect($attr,$content)
  {
    $where = "";
    if(isset($attr['where']))
      $where = ' WHERE '.$attr['where'];
    return '<?php $record = $core->con->select("SELECT * FROM ".$core->prefix."'.$attr['table'].$where.'"); ?>'.$content;
	}
  
  public static function DbRecord($attr,$content)
  {
    return '<?php while ($record->fetch()) { ?>'.
           $content.
           '<?php } ?>';
	}

  public static function DbField($attr)
  {
    return '<?php print $record->field("'.$attr['name'].'"); ?>';
  }
  
}
?>