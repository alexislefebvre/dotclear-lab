<?php
  // ***** BEGIN LICENSE BLOCK *****
  // This file is (c) Jean-Christophe Dubacq.
  // Licensed under CC-BY licence.
  //
  // ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { exit; }
if (!$core->auth->check('editor',$core->blog->id)) {
    return;
 }
$lasttst='XXX';
$realnames=array(
                 'metadata-tag' => __('Tags'),
                 'category-cat' => __('Categories'),
                 'somethingelse' => __('Something else')
                 );
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

if (isset($_GET['edit'])) {
    include dirname(__FILE__)."/edit.php";
 } else {
    include dirname(__FILE__)."/list.php";
 }
?>
