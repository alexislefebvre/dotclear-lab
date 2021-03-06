<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of latexrender, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jean-Christophe Dubacq
# jcdubacq1@free.fr
# 
# Licensed under the LGPL version 2.1 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/lgpl-2.1.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

dcPage::checkSuper();
$commands=array('latex','dvips','convert','identify');
include_once(dirname(__FILE__).'/class.latexrender.php');
$core->latex=new LatexRender($core);
$method_type = array(
                     __('Safe method') => 'old',
                     __('Colorization method') => 'new'
                     );

try
{
    // Create settings if they don't exist
    $options=LatexRender::getConfig();
    if ($_POST['reset']) {
        $options=LatexRender::defaultConfig();
        LatexRender::storeConfig($options);
        http::redirect($p_url);
    }
} catch (Exception $e) {
    $core->error->add($e->getMessage());
  }
if (isset($_POST['latexrender_latexpath']) && !($_POST['reset'])
    && !($_POST['clean'])) {
    $boptions=LatexRender::defaultConfig();
    foreach ($boptions as $key => $value) {
        if (isset($_POST['latexrender_'.$key])) {
            $boptions[$key]=$_POST['latexrender_'.$key];
        } else {
            $boptions[$key]='';
        }
    }
    LatexRender::storeConfig($boptions,$options);
    http::redirect($p_url.'&up=1');
 }
if (isset($_POST['clean'])) {
    http::redirect($p_url.'&clean='.$core->latex->searchCache(1));
 }
# default tab
$default_tab = 'settings';
if (!empty($_REQUEST['tab']))
    {
        switch ($_REQUEST['tab'])
            {
            case 'autotest' :
                $default_tab = 'autotest';
                break;
            }
    }
echo '<html><head><title>LaTeXrender</title>';
echo dcPage::jsPageTabs($default_tab);
echo '</head><body>'; 
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; LaTeXrender</h2>';
$msg='';
if (!empty($_GET['up'])) {
    $msg=__('Settings have been successfully updated.');
 }
if (!empty($_GET['clean'])) {
    $msg=__('Cache has been successfully cleaned.');
 }
if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}
echo '<div class="multi-part" id="settings" title="'.
__('Settings').'">';
echo '<h3>'.__('Settings').'</h3>';
echo
'<form action="'.$p_url.'" method="post">'.
'<fieldset><legend>'.__('Utilities paths').'</legend>'.
'<p><label class="classic">'.__('<code>latex</code> path:').' '.form::field(array('latexrender_latexpath'),20,255,html::escapeHTML($core->latex->_latex_path)).'</label></p>'.
'<p><label class="classic">'.__('<code>dvips</code> path:').' '.form::field(array('latexrender_dvipspath'),20,255,html::escapeHTML($core->latex->_dvips_path)).'</label></p>'.
'<p><label class="classic">'.__('<code>convert</code> path:').' '.form::field(array('latexrender_convertpath'),20,255,html::escapeHTML($core->latex->_convert_path)).'</label></p>'.
'<p><label class="classic">'.__('<code>identify</code> path:').' '.form::field(array('latexrender_identifypath'),20,255,html::escapeHTML($core->latex->_identify_path)).'</label></p>'.
'</fieldset><fieldset><legend>'.__('Security limits').'</legend>'.
'<p><label class="classic">'.__('Max width (pixels):').' '.form::field(array('latexrender_xsizelimit'),20,255,html::escapeHTML($core->latex->_xsize_limit)).'</label></p>'.
'<p><label class="classic">'.__('Max height (pixels):').' '.form::field(array('latexrender_ysizelimit'),20,255,html::escapeHTML($core->latex->_ysize_limit)).'</label></p>'.
'<p><label class="classic">'.__('Max length (chars):').' '.form::field(array('latexrender_stringlengthlimit'),20,255,html::escapeHTML($core->latex->_string_length_limit)).'</label></p>'.
'</fieldset><fieldset><legend>'.__('Settings').'</legend>'.
'<p><label class="classic">'.__('Generation method').' '.form::combo('latexrender_method',$method_type,$core->latex->_method).'</label></p>'.
'<p><label class="classic">'.__('Formula density (dpi)').' '.form::field(array('latexrender_formuladensity'),20,255,html::escapeHTML($core->latex->_formula_density)).'</label></p>'.
'<p><label class="classic">'.__('LaTeX class').' '.form::field(array('latexrender_latexclass'),20,255,html::escapeHTML($core->latex->_latexclass)).'</label></p>'.
'<p><label class="classic">'.__('Font size').' '.form::field(array('latexrender_fontsize'),20,255,html::escapeHTML($core->latex->_font_size)).'</label></p>'.
'</fieldset><p><input type="submit" value="'.__('save').'" />'.
'&nbsp;<input type="submit" name="reset" value="'.__('return to default values').'" /> '.
'&nbsp;<input type="submit" name="clean" value="'.__('clean cache').'" /> '.
$core->formNonce().'</p>'.
'</form>';
echo '</div>';
echo '<div class="multi-part" id="autotest" title="'.
__('Plugin auto-test').'">';
echo '<h3>'.__('Plugin auto-test').'</h3>';
$core->latex=new LatexRender($core);
echo '<p>'.__('The LaTeX logo should be displayed here colored in black then red:').'<ul><li>';
echo $core->latex->getFormulaHTML('\LaTeX','000000',true);
echo '</li><li>';
echo $core->latex->getFormulaHTML('\LaTeX','FF0000',true);
echo '</li></ul>';
echo '<p>'.__('Files in cache:').$core->latex->searchCache(0).'</p>';
if ($core->stacker) {
    echo __('<p>The stacker extension is installed. Public display will take place.</p>');
 } else {
    echo __('<p>Please install the stacker extension for public display to take place.</p>');
 }
echo '<p>'.__('Images go in the following directory:').' <tt>'.
$core->latex->_picture_path.'</tt></p>';
echo '<p>'.__('Temp files go in the following directory:').' <tt>'.
$core->latex->_tmp_dir.'</tt></p>';
echo '<p>'.__('Images have the following URL:').' <tt>'.
$core->latex->_picture_path_httpd.'</tt></p>';
echo '</div>';
echo '</body>';
?>