<?php
  // ***** BEGIN LICENSE BLOCK *****
  // This file is (c) Jean-Christophe Dubacq.
  // Licensed under CC-BY licence.
  //
  // ***** END LICENSE BLOCK *****

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
    http::redirect($p_url.'&up=4');
 }
echo "<html><head><title>Kezako</title></head><body><h2>Kezako</h2>";
if (!empty($_GET['up'])) {
    if ($_GET['up'] == 1) {
        echo '<p class="message">'.__('Description has been successfully updated.').'</p>';
    }
    if ($_GET['up'] == 2) {
        echo '<p class="message">'.__('Description has been successfully inserted.').'</p>';
    }
    if ($_GET['up'] == 3) {
        echo '<p class="message">'.__('Description has been successfully deleted.').'</p>';
    }
    if ($_GET['up'] == 4) {
        echo '<p class="message">'.__('Settings have been successfully updated.').'</p>';
    }
 }

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
echo '<h3>'.__('Settings').'</h3>';
echo '<form action="'.$p_url.'" method="post">'.'<p><label class="classic">'.form::checkbox('kezako_usecat',1,$core->blog->settings->kezako_usecat).' '.__('Describe categories with Kezako').'</label></p>';
echo '<p><label class="classic">'.form::checkbox('kezako_manylang',1,$core->blog->settings->kezako_manylang).' '.__('Use many languages').'</label></p>';
echo '<p><input type="submit" value="'.__('save').'" />'.
form::hidden(array('kezako_option'),'do').$core->formNonce().'</p>'.
'</form>';
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
echo '</body></html>';
?>
