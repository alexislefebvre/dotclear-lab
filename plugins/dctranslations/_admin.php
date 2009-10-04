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

if (1==1) {
    $core->addBehavior('adminPostForm',array('translationBehaviors','postForm'));
    $core->addBehavior('adminPostHeaders',array('translationBehaviors','postHeaders'));
    $core->addBehavior('adminAfterPostCreate',array('translationBehaviors','setTranslations'));
    $core->addBehavior('adminAfterPostUpdate',array('translationBehaviors','setTranslations'));
    $core->addBehavior('adminPageForm',array('translationBehaviors','pageForm'));
    $core->addBehavior('adminPageHeaders',array('translationBehaviors','postHeaders'));
    $core->addBehavior('adminAfterPageCreate',array('translationBehaviors','setTranslations'));
    $core->addBehavior('adminAfterPageUpdate',array('translationBehaviors','setTranslations'));
    $core->addBehavior('adminBeforeBlogSettingsUpdate',array('translationBehaviors','unsetLang'));
    $core->auth->setPermissionType('editor',
                                   __('manage translations and descriptions'));

    $_menu['Blog']->addItem(__('Translations'),
                            'plugin.php?p=dctranslations',
                            'index.php?pf=dctranslations/icon.png',
                            preg_match('/plugin.php\?p=dctranslations/',
                                       $_SERVER['REQUEST_URI']),
                            $core->auth->check('editor',$core->blog->id));

 }
class translationBehaviors {
    public static function postHeaders()
    {
    
        return 
            '<script type="text/javascript" src="index.php?pf=dctranslations/post.js"></script>'.
            '<link rel="stylesheet" type="text/css" href="index.php?pf=dctranslations/style.css" />';
    }
    public static function postForm($post) {
        echo translationBehaviors::pageForm($post);
    }
    public static function pageForm($post) {
        $rep='</fieldset>';
        $rep.='<fieldset id="translationfield"><legend>'.
            __('Translations').'</legend>';
        $translation = new dcTranslation($GLOBALS['core']);
        $i=0;
        if ($post) {
            $rs=$translation->getTranslationsByPost($post->post_id);
        } else {
            $rs=$translation->getTranslationsByPost(null);
        }
        while ($i==0 || $rs->fetch()) {
            if (!$i) {
                $t_title="";
                $t_lang="";
                $t_url="";
                $t_id=0;
                $t_pid=0;
                $t_excerpt='';
                $t_excerpt_xhtml='';
                $t_content='';
                $t_content_xhtml='';
                $t_format= $translation->core->auth->getOption('post_format');

            } else {
                $t_title=$rs->translation_title;
                $t_lang=$rs->translation_lang;
                $t_id=$rs->translation_id;
                $t_pid=$rs->post_id;
                $t_excerpt=$rs->translation_excerpt;
                $t_excerpt_xhtml=$rs->translation_excerpt_xhtml;
                $t_content=$rs->translation_content;
                $t_content_xhtml=$rs->translation_content_xhtml;
                $t_format=$post->post_format;
                $t_url=$rs->translation_url;
            }
            $translation->core->blog->setPostContent($t_pid,$t_format,$t_lang,$t_excerpt,$t_excerpt_xhtml,$t_content,$t_content_xhtml);
            $base='post_translation_'.$i;
            $rep.='<div class="area" id="translation-'.$i.'-area">';
            $rep.='<label id="translation_'.$i.'_langlabel" class="classic">'.($i?__('Translation:'):__('New translation:')).
                form::field($base.'_lang',3,255,html::escapeHTML($t_lang),'',5*$i+14).
                '</label> '.
                '<label class="classic">'.__('Translation title:').
                form::field($base.'_title',40,255,html::escapeHTML($t_title),'',5*$i+15).
                '</label>&nbsp;<label class="classic transgreyout">'.form::checkbox($base.'_delete',1,false,'',3).' '.
                __('Delete this translation').
                '</label>';
            $rep.='<label>'.__('Translation excerpt:').'</label>'.
                form::textarea($base.'_excerpt',50,3,html::escapeHTML($t_excerpt),'',5*$i+16).
                '<label>'.__('Translation content:').'</label>'.
                form::textarea($base,50,5,html::escapeHTML($t_content),'',5*$i+17);
            $rep.=form::hidden($base.'_id',$t_id);
            $rep.=form::hidden($base.'_olang',$t_lang);
            $rep.='<div class="lockable">'.
                '<p><label>'.__('Basename:').
                form::field($base.'_url',60,255,html::escapeHTML($t_url),'',5*$i+18).
                '</label></p>'.
                '<p class="form-note warn">'.
                __('Warning: If you set the URL manually, it may conflict with another entry.').
                '</p></div>';
            $rep.='</div>'."\n";
            $i++;
        }
        $rep.='<input style="margin-bottom:1ex;margin-top:1ex;" type="button" id="translationadder" value="'.
            __('New translation').'" />';
        $rep.=form::hidden('translation_count',$i-1);
        $rep.='</fieldset><fieldset class="constrained">';
      return $rep;
    }

    public static function setTranslations($cur,$post_id)
    {
        $post_id = (integer) $post_id;
        $translation = new dcTranslation($GLOBALS['core']);
        $i=0;
        $max=$_POST['translation_count'];
        while ($i<=$max) {
            $base='post_translation_'.$i;
            //      print_r($_POST);
            if (isset($_POST[$base.'_id'])) {
                $t_id=$_POST[$base.'_id'];
            } else {
                $t_id=0;
            }
            if (isset($_POST[$base.'_olang'])) {
                $t_olang=$_POST[$base.'_olang'];
            } else {
                $t_olang="";
            }
            $t_delete = isset($_POST[$base.'_delete']);
            if (isset($_POST[$base.'_lang'])) {
                $t_lang=$_POST[$base.'_lang'];
            } else {
                $t_lang="";
            }
            if (isset($_POST[$base.'_title'])) {
                $t_title=$_POST[$base.'_title'];
            } else {
                $t_title="";
            }
            if (isset($_POST[$base.'_url'])) {
                $t_url=$_POST[$base.'_url'];
            } else {
                $t_url="";
            }
            if ($t_delete == false) {
                $t_excerpt=$_POST[$base.'_excerpt'];
                $t_content=$_POST[$base];
            } else {
                $t_excerpt='';$t_content='X';
            }
            $t_format=$_POST['post_format'];
            $t_pid=$post_id;
            $t_excerpt_xhtml='';
            $t_content_xhtml='';
            $translation->core->blog->setPostContent($post_id,$t_format,$t_lang,$t_excerpt,$t_excerpt_xhtml,$t_content,$t_content_xhtml);
            if ($t_id != 0 &&
                $t_lang != '' &&
                !$t_delete) {
                $translation->updateTranslation($t_id,$t_lang,$t_title,$t_pid,
                                                $t_excerpt,$t_excerpt_xhtml,
                                                $t_content,$t_content_xhtml,$t_url);
                // update
            } elseif ($t_id == 0 && !($t_delete || $t_lang=='')) {
                $translation->insertTranslation($t_lang,$t_title,$t_pid,
                                                $t_excerpt,$t_excerpt_xhtml,
                                                $t_content,$t_content_xhtml,$t_url);
                // insert
            } elseif ($t_id != 0) { // Deletion
                $translation->deleteTranslation($t_id);
                // delete
            } else {
                // Do nothing: t_id = 0 (new) but no language or deleted
            }
            $i++;
        }
        dcTranslation::indexAllPosts($post_id);
    }
    public static function unsetLang()
    {
        global $core;
        $core->blog->settings->setNameSpace('system');
        $core->blog->settings->put('lang','');
    }
}

?>