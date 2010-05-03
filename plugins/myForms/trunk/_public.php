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

require_once("Field.php");
require_once("FieldSet.php");
require_once("fields/TextField.php");
require_once("fields/TextAreaField.php");
require_once("fields/HiddenField.php");
require_once("fields/SubmitField.php");
require_once("fields/ComboField.php");
require_once("fields/CheckBoxField.php");
require_once("fields/RadioButtonField.php");
require_once("fields/FileField.php");

require_once("TplCore.php");
require_once("Captcha.php");
require_once("TplFields.php");

require_once("TplEmail.php");
require_once("TplDatabase.php");

$core->url->register('formGet','form','^form/(.+)$',array('MyForms','formGet'));
$core->url->register('formPost','form','^form$',array('MyForms','formPost'));

class PostSimu
{
  public function getURL()
  {
    return $_SERVER['REQUEST_URI'];
  }
}

class MyForms extends dcUrlHandlers
{
  public static $formID;
  private static $nextFormID;
  public static $events;
  private static $errors;
  private static $fields;
  private static $htmlOut;
  private static $formHTML;
  private static $formIsValid;
  private static $captchaIsValidated;
  private static $passwordProtected;
  
  public static function formGet($args)
  {
    self::$formID = $args;
    self::form();
  }
  
  public static function formPost($args)
  {
    global $_REQUEST;
    self::$formID = @$_REQUEST["myforms"]["formID"];
    self::form();
  }
  
  public static function form()
  {
    global $core, $_REQUEST, $_ctx;
    
    // add all 'default-templates' folders to form search path
    $tplPath = $core->tpl->getPath();
		$plugins = $core->plugins->getModules();
		foreach($plugins as $plugin)
			array_push($tplPath, $plugin['root'].'/default-templates');
    $core->tpl->setPath($tplPath);

    self::$passwordProtected = false;
    
    self::loadForm();
    
    if(self::$passwordProtected) {
      $_ctx->posts = new PostSimu();
			self::serveDocument('password-form.html','text/html',false);
			return;
		}
    
    // process  form post
    if( isset($_REQUEST["myforms"]) && self::$captchaIsValidated && self::$formIsValid ) {
      self::$nextFormID = false;
      self::$errors = array();
      $currentEventCallback = MyFormsTplCore::GetFunction('OnSubmit_'.self::getCurrentEvent());
      ob_start();
      $currentEventCallback();
      self::$htmlOut = ob_get_clean();
      if( self::$nextFormID ) {
        self::$formID = self::$nextFormID;
        self::loadForm(true);
      }
    }
    
    // display current form page
    self::serveDocument('myforms.html');
  }
  
  private static function loadForm($afterGoto=false)
  {
    global $core;
    $formTpl = self::$formID.'.myforms.html';
    
    if (!$core->tpl->getFilePath($formTpl)) {
      header('Content-Type: text/plain; charset=UTF-8');
      echo 'Unable to find template for form <'.self::$formID.'>.';
      exit;
    }
    
    // include form template template which, in turn, defines 'myforms' functions
    self::$events = array();
    //print $core->tpl->getFile($formTpl);exit;
    //if($afterGoto) {print $core->tpl->getFile($formTpl);exit;}
    //include $core->tpl->getFile($formTpl);exit;
    //if($afterGoto) {include $core->tpl->getFile($formTpl);exit;}
    //print $core->tpl->getData($formTpl); exit;
    //if($afterGoto) {print $core->tpl->getData($formTpl); exit;}
    $core->tpl->getData($formTpl);
    
    // form is password protected
    if(self::$passwordProtected) {
		  // Get passwords cookie
			if (isset($_COOKIE['dc_form_passwd'])) {
				$pwd_cookie = unserialize($_COOKIE['dc_form_passwd']);
			} else {
				$pwd_cookie = array();
			}
					
			// Check for match
			if ((!empty($_POST['password']) && $_POST['password'] == self::$passwordProtected)
			|| (isset($pwd_cookie[self::$formID]) && $pwd_cookie[self::$formID] == self::$passwordProtected))	{
			  // store password in cookie and clear password protection
				$pwd_cookie[self::$formID] = self::$passwordProtected;
				setcookie('dc_form_passwd',serialize($pwd_cookie),0,'/');
        self::$passwordProtected = false;
			} else {
			  // incorrect password : no need to go further
			  return;
			}
    }
    
    // field declaration
    ob_start();
    $declareFunction = MyFormsTplCore::GetFunction('Declare');
    self::$fields = $declareFunction();
    //print ob_get_clean();print_r(self::$fields);exit;
    //if($afterGoto) {print ob_get_clean();print_r(self::$fields);exit;}
    ob_get_clean();
    
    // field display and validation
    self::$formIsValid = true;
    self::$captchaIsValidated = true;
    ob_start();
    $displayFunction = MyFormsTplCore::GetFunction('Display');
    $displayFunction();
    self::$htmlOut = ob_get_clean();
  }
   
  public static function InvalidateForm() {
    self::$formIsValid = false;
  }
 
  public static function validateCaptcha() {
    global $_REQUEST;
    $captchaIsValid = !isset($_REQUEST["myforms"]) || MyFormsCaptcha::isValid($_REQUEST["myforms"]["captcha"],$_REQUEST["myforms"]["captcharef"]);
    self::$captchaIsValidated = self::$captchaIsValidated && $captchaIsValid;
    return $captchaIsValid;
  }
  
  public static function validateField($fieldName,$condition) {
    global $_REQUEST;
    $fieldIsValid = !isset($_REQUEST["myforms"]) || preg_match('#'.$condition.'#', @$_REQUEST["myforms"][$fieldName]);
    self::$allFieldsAreValidated = self::$allFieldsAreValidated && $fieldIsValid;
    return $fieldIsValid;
  }
  
  public static function checkQueryMatches($queryFilter)
  {
    if( !preg_match('#'.$queryFilter.'#', $_SERVER['REQUEST_METHOD']) )
      self::p404();
  }
  
  public static function checkBlogMatches($blogFilter)
  {
    global $core;
    if( !preg_match('#'.$blogFilter.'#', $core->blog->id) )
      self::p404();
  }
  
  public static function display()
  {
    print self::$htmlOut;
  }
  
  public static function password($pw)
  {
    self::$passwordProtected = $pw;
  }
  
  public static function info($name)
  {
    $infoFunction = MyFormsTplCore::GetFunction('Info_'.$name);
    $infoFunction();
  }
  
  public static function registerEvent($name)
  {
    self::$events[] = $name;
  }
  
  private static function getCurrentEvent()
  {
    foreach( self::$events as $event )
      if( isset($_REQUEST["myforms"][$event]) )
        return $event;
  }
  
  public static function execute($returnedError)
  {
    if($returnedError)
      self::$errors[] = $returnedError;
  }
  
  public static function hasErrors()
  {
    return count(self::$errors) > 0;
  }
  
  public static function hasError($class,$message)
  {
    foreach( self::$errors as $error ) {
      $hasClass = false;
      $hasMessage = false;
      if( preg_match('#'.$class.'#', $error[0]) )
        $hasClass = true;
      if( preg_match('#'.$message.'#', $error[1]) )
        $hasMessage = true;
      if($hasClass && $hasMessage)
        return true;
    }
    return false;
  }
  
  public static function allErrors()
  {
    return self::$errors;
  }
  
  public static function goto($formID)
  {
    self::$nextFormID = $formID;
  }
  
  public static function getField($name)
  {
    $field = self::$fields->get($name);
		if( $field )
			return $field;
		print "\n===== Unknown field '".$name."'";
		exit;
  }
  
  /*
  public static function getFieldValue($name,$defaultValue)
  {
    global $_REQUEST;
    if(isset($_REQUEST["myforms"][$name])) {
      return $_REQUEST["myforms"][$name];
    } else {
      return $defaultValue;
    }
  }
  */
  /*
  public static function getFileFieldValue($name,$data)
  {
    global $_FILES;
    if(isset($_FILES["myforms"][$data][$name]))
      return $_FILES["myforms"][$data][$name];
    else
      return '';
  }
*/
}

?>