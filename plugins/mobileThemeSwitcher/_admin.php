<?php
/*
Copyright (c) 2008 Noel GUILBERT

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminBlogPreferencesForm',array('mobileThemeSwitcherAdminBehaviours','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('mobileThemeSwitcherAdminBehaviours','adminBeforeBlogSettingsUpdate'));


class mobileThemeSwitcherAdminBehaviours
{
  public static function adminBlogPreferencesForm($core, $settings)
  {
    $themes = array('' => '');
    foreach (new DirectoryIterator(path::fullFromRoot($core->blog->settings->themes_path,DC_ROOT)) as $dir)
    {
      if ($dir->isDir() && ! $dir->isDot())
      {
        $themes[$dir->getFilename()] = $dir->getFilename();
      }
    }
    echo '<div class="fieldset"><h4>'.__('Mobile Theme Switcher').'</h4>'.
    '<p><label>'.__('Mobile theme').'</label>'.
    form::combo('mobileThemeSwitcher_theme', $themes, $settings->mobileThemeSwitcher->mobileThemeSwitcher_theme).
    '</p></div>';
  }

  public static function adminBeforeBlogSettingsUpdate($settings)
  {
    $settings->addNameSpace('mobileThemeSwitcher');
    $settings->mobileThemeSwitcher->put('mobileThemeSwitcher_theme', empty($_POST['mobileThemeSwitcher_theme'])?"":$_POST['mobileThemeSwitcher_theme'], 'string');
  }
}
?>
