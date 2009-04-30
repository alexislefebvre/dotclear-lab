<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dctranslations, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jean-Christophe Dubacq
# jcdubacq1@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

if (!$core->auth->check('editor',$core->blog->id)) {
    return;
 }

$lasttst='XXX';
function kezakohelper($id,$type,$subtype,$lang,$text) {
    global $lasttst;
    global $realnames;
    global $core;
    global $p_url;
    $tst=$type.'-'.$subtype;
    if ($tst != $lasttst) {
        if (isset($realnames[$tst])) {
            $realname=$realnames[$tst];
        } else {
            $realname=$tst;
        }
        echo '<tr><th style="text-align:center;background: #CCC; border: 1px solid;" colspan="5">'.$realname.
            '</th></tr>';
        $lasttst=$tst;
    }
    if ($tst == 'category-cat') {
        $rs=$core->con->select('SELECT cat_title FROM '.$core->prefix.
                               'category WHERE cat_id = \''.$core->con->escape($id).'\'');
        if ($rs) {
            $xid=$rs->cat_title;
        } else {
            $xid=__('Undefined category').'['.$id.']';
        }
    } else {
        $xid=$id;
    }
    $delete_url='&type='.rawurlencode($type).
        '&subtype='.rawurlencode($subtype).
        '&lang='.rawurlencode($lang).
        '&id='.rawurlencode($id);
    $edit_url   = '&edit=1'.$delete_url;
    $delete_url = '&delete=1'.$delete_url;
    $delete_url = $p_url.html::escapeURL($delete_url);
    $edit_url = $p_url.html::escapeURL($edit_url);
    echo '<tr><td style="border: 1px solid;">'.
        html::escapeHTML($xid).'</td><td style="border: 1px solid;">'.
        html::escapeHTML($lang).
        '</td><td style="border: 1px solid;">'.
        ((strlen($text)>50)?
         (html::escapeHTML(substr($text,0,47).'...')):
         (html::escapeHTML($text))).
        '</td><td style="border: 1px solid;">'.
        '<a href="'.$edit_url.'"><img alt="~" src="images/edit-mini.png"></a>'.
        '</td><td style="border: 1px solid;">'.
        '<a href="'.$delete_url.'"><img alt="X" src="images/trash.png"></a>'.
        '</td></tr>';
}

# default tab
$default_tab = 'list';
if (!empty($_REQUEST['tab']))
	{
        switch ($_REQUEST['tab'])
            {
            case 'settings' :
                $default_tab = 'settings';
                break;
            }
	}
try
{
    // Create settings if they don't exist
    if ($core->blog->settings->kezako_usecat === null) {
        $core->blog->settings->setNameSpace('kezako');
        $core->blog->settings->put('kezako_usecat',0,
                                   'boolean','Use kezako for categories',true,true);
        $core->blog->settings->put('kezako_manylang',0,
                                   'boolean','Allow selection of many languages',true,true);
        http::redirect($p_url);
    }
} catch (Exception $e) {
    $core->error->add($e->getMessage());
  }
if (isset($_POST['kezako_option'])) {
    $core->blog->settings->setNameSpace('kezako');
    $setcat=$core->blog->settings->kezako_usecat;
    $core->blog->settings->put('kezako_usecat',
                               !empty($_POST['kezako_usecat']),
                               null,null,true,true);
    $core->blog->settings->put('kezako_manylang',
                               !empty($_POST['kezako_manylang']),
                               null,null,true,true);
    $core->blog->triggerBlog();
    if ((!empty($_POST['kezako_usecat'])) != $setcat) {
        files::deltree(DC_TPL_CACHE.DIRECTORY_SEPARATOR.'cbtpl');
    }
    // do something more ?
    http::redirect($p_url.'&up=4&tab=settings');
 }
echo '<html><head><title>Kezako</title>';
echo dcPage::jsPageTabs($default_tab);
echo '</head><body><h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.
'Kezako</h2>';
$msg='';
if (!empty($_GET['up'])) {
    if ($_GET['up'] == 1) {
        $msg=__('Description has been successfully updated.');
    }
    if ($_GET['up'] == 2) {
        $msg=__('Description has been successfully inserted.');
    }
    if ($_GET['up'] == 3) {
        $msg=__('Description has been successfully deleted.');
    }
    if ($_GET['up'] == 4) {
        $msg=__('Settings have been successfully updated.');
    }
 }
if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}
// treat the deletion

if (isset($_GET['delete'])) {
    @$type=$_GET['type'];
    @$subtype=$_GET['subtype'];
    @$lang=$_GET['lang'];
    @$id=$_GET['id'];
    if ($type && $subtype && $lang && $id) {
        $strReq='FROM '.$core->prefix.
            'kezako WHERE thing_type = \''.$core->con->escape($type).
            '\' AND thing_subtype = \''.$core->con->escape($subtype).
            '\' AND thing_lang = \''.$core->con->escape($lang).
            '\' AND thing_id = \''.$core->con->escape($id).'\'';
        $rs = $core->con->select('SELECT * '.$strReq);
        if ($rs && !$rs->isEmpty()) {
            unset($rs);
            $core->con->select('DELETE '.$strReq);
            http::redirect($p_url.'&up=3');
        } else {
            $core->error->add(__('No such description found.'));
        }
    }
 }
if (isset($_GET['delete'])) {
    $core->error->add(__('Something went wrong with your request. No deletion.'));
 }
echo '<div class="multi-part" id="list" title="'.
__('List of descriptions').'">';
echo '<h3>'.__('List of descriptions').'</h3>';
echo '<p>'.__('Even if you disabled categories or languages, all descriptions are still listed and not deleted, even if they describe categories or use other languages.').'</p>';
$blog_id=$core->con->escape($core->blog->id);
$strReq='SELECT * FROM '.$core->prefix.'kezako WHERE blog_id = \''.
    $blog_id.'\' ORDER BY thing_type, thing_subtype, thing_id,thing_lang';
$rs=$core->con->select($strReq);
if ($rs) {
    echo '<table style="border: solid 1px;">';
    while($rs->fetch()) {
        echo kezakohelper($rs->thing_id,$rs->thing_type,
                          $rs->thing_subtype,$rs->thing_lang, $rs->thing_text);
    }
    echo '</table>';
 }
$insert_url='&edit=1';
$insert_url=$p_url.html::escapeURL($insert_url);
echo '<p><a href="'.$insert_url.'"><img alt="+" src="images/edit-mini.png">&nbsp;'.__('Insert a new description').'</a></p>';
echo '</div>';
echo '<div class="multi-part" id="settings" title="'.__('Settings').'">';
echo '<h3>'.__('Settings').'</h3>';
echo '<form action="'.$p_url.'" method="post">'.'<p><label class="classic">'.form::checkbox('kezako_usecat',1,$core->blog->settings->kezako_usecat).' '.__('Describe categories with Kezako').'</label></p>';
echo '<p><label class="classic">'.form::checkbox('kezako_manylang',1,$core->blog->settings->kezako_manylang).' '.__('Use many languages').'</label></p>';
echo '<p><input type="submit" value="'.__('save').'" />'.
form::hidden(array('kezako_option'),'do').$core->formNonce().'</p>'.
'</form>';
echo '</div>';
echo '</body></html>';
?>
