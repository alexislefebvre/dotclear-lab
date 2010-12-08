<?php
# ***** BEGIN LICENSE BLOCK *****
# Copyright (c) 2009 Olivier Azeau and contributors. All rights reserved.
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

//print_r($_SERVER);exit;
//print_r($_REQUEST);exit;
//print_r($core);exit;

if (!defined('DC_CONTEXT_ADMIN')) { return; }

dcPage::check('usage,contentadmin');

if( isset($_REQUEST['form']) ) {
	//print_r($GLOBALS);exit;
	$core->tpl = new template(DC_TPL_CACHE,'$core->tpl');
	//$core->tpl->use_cache = false;
	//$core->tpl = new dcTemplate(DC_TPL_CACHE,'$core->tpl',$core);
	require_once(dirname(__FILE__).'/_public.php');
	MyForms::$formID = $_REQUEST['form'];
	MyForms::adminForm();
} else {

# Use theme editor
$core->themes = new dcThemes($core);
$core->themes->loadModules($core->blog->themes_path,null);
$themeEditorRootPath = $core->plugins->moduleRoot("themeEditor");
if($themeEditorRootPath) {
  require_once($themeEditorRootPath."/class.themeEditor.php");
  class myFormsTplFileFinder extends dcThemeEditor
  {
  	public function __construct(&$core)
  	{
      return parent::__construct($core);
  	}
  	protected function getFilesInDir($dir,$ext=null,$prefix='')
  	{
      return parent::getFilesInDir($dir,'myforms.html',$prefix);
    }
  }
}

try
{
?>
<html>
<head>
  <title>myForms</title>
</head>
<body>
<?php
  if( isset($_POST['myFormId']) ) {
    $newFormId = $_POST['myFormId'];
    $newFormFile = path::real($core->blog->themes_path.'/'.$core->blog->settings->theme).'/tpl/'.$newFormId.'.myforms.html';
    if( file_exists($newFormFile) ) {
      print '<p class="message">'.__('Form already exists.').'</p>';
    } else {
      ob_start();
?>
<tpl:myformsInfo name="title">{{tpl:BlogName encode_html="1"}} - <?php print $newFormId; ?></tpl:myformsInfo>

<tpl:myformsOnInit>
  <p>This is a new form named '<?php print $newFormId; ?>'.</p>
  <p><tpl:myformsSubmit name="clickme">Click me</tpl:myformsSubmit></p>
</tpl:myformsOnInit>

<tpl:myformsOnSubmit name="clickme">
  <p>You got it !</p>
</tpl:myformsOnSubmit>

<?php
      if( file_put_contents( $newFormFile, ob_get_clean() ) )
        print '<p class="message">'.__('Form was successfully created.').'</p>';
      else
        print '<p class="message">'.__('An error occured while creating the form.').'</p>';
    }
  }

  if($themeEditorRootPath) {
    print '<h2>'.__('Click on a form to modify it.').'</h2>';
    $fileFinder = new myFormsTplFileFinder($core);
    echo $fileFinder->filesList('tpl','<a href="plugin.php?p=themeEditor&amp;tpl=%2$s" class="tpl-link">%1$s</a>');
  } else {
    print '<p class="message">'.__('You need the \'themeEditor\' extension to modify the existing forms.').'</p>';
  }
  print '<h2>'.__('Create a new form').'</h2>';
  print '<form action="'.$p_url.'" method="post">';
  print '<p>'.__('Form ID').' : <input type="text" name="myFormId" value="" /></p>';
  print '<p><input type="submit" value="'.__('Create').'" />'.$core->formNonce().'</p>';
  print '</form>';
?>
</body>
</html>
<?php
}
catch (Exception $e)
{
  $core->error->add($e->getMessage());
}

}
?>