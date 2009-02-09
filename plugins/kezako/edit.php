<?php
  // ***** BEGIN LICENSE BLOCK *****
  // This file is (c) Jean-Christophe Dubacq.
  // Licensed under CC-BY licence.
  //
  // ***** END LICENSE BLOCK *****
@$type=$_GET['type'];
@$subtype=$_GET['subtype'];
@$lang=$_GET['lang'];
@$id=$_GET['id'];
$tst=$type.'-'.$subtype;
if ($type && $subtype && $lang && $id) {
    $full_url='&edit=1&type='.rawurlencode($type).
        '&subtype='.rawurlencode($subtype).
        '&lang='.rawurlencode($lang).
        '&id='.rawurlencode($id);
    $full_url=$p_url.html::escapeURL($full_url);
 } else {
    $full_url = $p_url.html::escapeURL('&edit=1');
 }
$desc='';
$strReq='';
if ($type && $subtype && $lang && $id) {
    $whereReq=
        'WHERE thing_type = \''.$core->con->escape($type).
        '\' AND thing_subtype = \''.$core->con->escape($subtype).
        '\' AND thing_lang = \''.$core->con->escape($lang).
        '\' AND thing_id = \''.$core->con->escape($id).
        '\' AND blog_id = \''.$core->con->escape($core->blog->id).'\'';
    $strReq='FROM '.$core->prefix.'kezako '.$whereReq;
    $rs = $core->con->select('SELECT * '.$strReq);
    if ($rs && !$rs->isEmpty()) {
        $desc=$rs->thing_text;
    }
 }
if (!empty($_POST)) {
    // do something
    // First, compute the new values
    $new_id = $_POST['thing_id'];
    $new_typename = $_POST['type_name'];
    $new_lang = $_POST['thing_lang'];
    $new_id = $_POST['thing_id'];
    $new_desc = $_POST['desc_desc'];
    $new_tst = $_POST['type_id'];
    $new_catid = $_POST['cat_id'];
    if ($new_tst == 'category-cat') {
        $new_typename=$new_tst;
        $new_id=$new_catid;
    }
    if ($new_tst == 'metadata-tag') {
        $new_typename=$new_tst;
    }
    if (!preg_match('/^([a-z]+)-([a-z]+)$/',$new_typename,$m) ||
        !$new_id || !$new_lang
        ) {
        $core->error->add(__('Some field is missing or incorrect. Please redo from start.').$new_typename);
    } else {
        // type and subtype
        $new_type=$m[1];
        $new_subtype=$m[2];
        $cur = $core->con->openCursor($core->prefix.'kezako');
        $cur->clean();
        $cur->thing_id = $new_id;
        $cur->thing_type = $new_type;
        $cur->thing_subtype = $new_subtype;
        $cur->thing_lang = $new_lang;
        $cur->thing_text = $new_desc;
        $cur->blog_id = $core->blog->id;
        // Then insert/update
        if ($type && $subtype && $lang && $id) {
            if ($rs && !$rs->isEmpty()) {
                // We found the description to be modified
                $cur->update($whereReq);
                if ($core->con->error()) {
                    $core->error->add($core->con->error());
                } else {
                    $core->blog->triggerBlog();
                    http::redirect($p_url.'&up=1');
                }
            } else {
                $core->error->add(__('No such description found.'));
            }
        } else {
            // We must insert a new entry
            $cur->insert();
            if ($core->con->error()) {
                $core->error->add($core->con->error());
            } else {
                $core->blog->triggerBlog();
                http::redirect($p_url.'&up=2'); 
            }
        }
    }
 }

echo '<html><head><title>Kezako</title>'.
'<script type="text/javascript" src="index.php?pf=kezako/edit.js"></script>'.
dcPage::jsToolBar().
'</head><body><h2>'.
__('Edit a description').'</h2>';
if ($_GET['edit'] == 1) {
    try {
        $categories = $core->blog->getCategories();
        while ($categories->fetch()) {
            $categories_combo[html::escapeHTML($categories->cat_title)] = $categories->cat_id;
        }
    } catch (Exception $e) { }
    foreach ($realnames as $k => $v) {
        $type_combo[html::escapeHTML($v)] = $k;
    }
    $cat_id=1;
    if ($tst == 'category-cat' && $id) {
        $cat_id=$id;
        $type_id=$tst;
    }
    if ($tst == 'category-cat') {
        $cat_id=$id;
        $type_id=$tst;
        $type_name = '';
    } elseif ($tst == '-') {
        $type_id = 'metadata-tag';
        $type_name = '';
    } else {
        $type_id = 'somethingelse';
        $type_name = $tst;
    }
    $lang_combo = array('' => '', __('Most used') => array(), __('Available') => l10n::getISOcodes(1));
    $all_langs = l10n::getISOcodes();
    if ($core->plugins->moduleExists('dctranslations')) {
        $rs=dcTranslation::getLangs(array('order'=>'asc'));
        while ($rs->fetch()) {
            if (isset($all_langs[$rs->real_lang])) {
                $lang_combo[__('Most used')][__($all_langs[$rs->real_lang])] = $rs->real_lang;
                unset($lang_combo[__('Available')][$all_langs[$rs->real_lang]]);
            } else {
                $lang_combo[__('Most used')][$rs->real_lang] = $rs->real_lang;
            }
        }
    } else {
        $rs = $core->blog->getLangs(array('order'=>'asc'));
        while ($rs->fetch()) {
            if (isset($all_langs[$rs->post_lang])) {
                $lang_combo[__('Most used')][$all_langs[$rs->post_lang]] = $rs->post_lang;
                unset($lang_combo[__('Available')][$all_langs[$rs->post_lang]]);
            } else {
                $lang_combo[__('Most used')][$rs->post_lang] = $rs->post_lang;
            }
        }
        unset($all_langs);
        unset($rs);
    }
    echo '<form action="'.$full_url.'" method="post">'.
        '<fieldset class="constrained">';
    echo '<p><label class="classic">'.
        __('This description is for:').' '.
        form::combo('type_id',$type_combo,$type_id,'',1,false,'onchange="update_visibility()"').
        '</label></p>';
    echo '<p id="id_ste"><label class="classic">'.
        __('Use this box to enter the type-subtype (you know what you are doing):').' '.
        form::field('type_name',20,64,$type_name,'',4).
        '</label></p>';
    echo '<p id="id_tag"><label class="required" title="'.__('Required field').'">'.
        __('Description identifier:').' '.
        form::field('thing_id',20,255,$id,'',2).
        '</label></p>';
    echo '<p id="id_cat"><label class="required" title="'.__('Required field').'">'.
        __('Description for the category:').' '.
        form::combo('cat_id',$categories_combo,$cat_id,'',3,false).
        '</label></p>';
    if ($core->blog->settings->kezako_manylang) {
        echo '<p><label class="required" title="'.__('Required field').'">'.
            __('Description language:').' '.
            form::combo('thing_lang',$lang_combo,$lang?$lang:$core->blog->settings->lang,'',5).
            '</label></p>';
        $hidden='';
    } else {
        $hidden=form::hidden(array('thing_lang'),$core->blog->settings->lang);
    }
    echo '<p class="area"><label for="desc_desc">'.__('Description:').'</label> '.
        form::textarea('desc_desc',50,8,html::escapeHTML($desc),'',4).
        '</p>';
    echo '<p><input type="submit" value="'.__('save').'" />'.
        $core->formNonce().$hidden.'</p>'.'</fieldset>'.
        '</form>';
 }
echo '</body></html>';
?>