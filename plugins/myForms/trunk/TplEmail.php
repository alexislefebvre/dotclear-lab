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

require_once("Email.php");

$core->tpl->addBlock('myformsSendEmail',array('MyFormsTplEmail','SendEmail'));
$core->tpl->addBlock('myformsEmailFrom',array('MyFormsTplEmail','EmailFrom'));
$core->tpl->addBlock('myformsEmailTo',array('MyFormsTplEmail','EmailTo'));
$core->tpl->addBlock('myformsEmailSubject',array('MyFormsTplEmail','EmailSubject'));
$core->tpl->addBlock('myformsEmailBody',array('MyFormsTplEmail','EmailBody'));
$core->tpl->addValue('myformsEmailAttachment',array('MyFormsTplEmail','EmailAttachment'));
$core->tpl->addValue('UsersEmail',array('MyFormsTplEmail','UsersEmail'));

class MyFormsTplEmail
{
  public static function SendEmail($fieldName,$content)
  {
    return '<?php $email=new MyFormsEmail(); ?>'.$content.'<?php MyForms::execute( $email->send() ); ?>';
  }
  
  private static function AddFieldToEmail($fieldName,$content)
  {
    return '<?php ob_start(); ?>'.$content.'<?php $email->'.$fieldName.'(ob_get_clean()); ?>';
  }
  
  public static function EmailFrom($attr,$content)
  {
    return self::AddFieldToEmail('from',$content);
  }
  
  public static function EmailTo($attr,$content)
  {
    return self::AddFieldToEmail('to',$content);
  }
  
  public static function EmailSubject($attr,$content)
  {
    return self::AddFieldToEmail('subject',$content);
  }
  
  public static function EmailBody($attr,$content)
  {
    return self::AddFieldToEmail('body',$content);
  }
  
  public static function EmailAttachment($attr)
  {
    return '<?php global $_FILES; $email->attachHttpUpload($_FILES["myforms"],"'.$attr['name'].'"); ?>';
  }
  
  public static function UsersEmail($attr)
  {
    if(isset($attr['name']))
      return '<?php print MyFormsTplEmail::GetUsersEmail("'.$attr['name'].'"); ?>';
    else
      return '<?php print MyFormsTplEmail::GetUsersEmail(); ?>';
  }

  public static function GetUsersEmail($search=false) {
    global $core;
    if($search)
      $user = $core->getUsers( array('q'=>$search) );
    else
      $user = $core->getUsers();
    $usersEmail = array();
		while($user->fetch()) {
      $uname = $user->user_displayname ? $user->user_displayname : $user->user_id;
      $usersEmail[] = $uname.' <'.$user->user_email.'>';
    }
    return implode(', ',$usersEmail);
  }
}
?>