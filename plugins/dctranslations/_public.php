<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dctranslations, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Jean-Claude Dubacq, Franck Paul and contributors
# carnet.franck.paul@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

if (1==1) { // always
    // define or redefine a few blocks
    $core->tpl->addBlock('Translations',
                         array('dcTranslation','Translations'));
    $core->tpl->addValue('TranslationLang',
                         array('dcTranslation','TranslationLang'));
    $core->tpl->addValue('TranslationEntryURL',
                         array('dcTranslation','TranslationEntryURL'));
    $core->tpl->addValue('EntryTitle',
                         array('dcTranslation','EntryTitle'));
    $core->tpl->addValue('EntryLang',
                         array('dcTranslation','EntryLang'));
    $core->tpl->addValue('EntryLangURL',
                         array('dcTranslation','EntryLangURL'));
    $core->addBehavior('templateBeforeBlock',
                       array('dcTranslation','beforeEntries'));

    $core->tpl->addBlock('Languages',
                         array('dcTranslation','Languages'));
    $core->tpl->addBlock('LanguagesHeader',
                         array('dcTranslation','LanguagesHeader'));
    $core->tpl->addBlock('LanguagesFooter',
                         array('dcTranslation','LanguagesFooter'));
    $core->tpl->addValue('LanguageCode',
                         array('dcTranslation','LanguageCode'));
    $core->tpl->addValue('LanguageCount',
                         array('dcTranslation','LanguageCount'));
    $core->tpl->addBlock('LanguageIfCurrent',
                         array('dcTranslation','LanguageIfCurrent'));
    $core->tpl->addValue('LanguageURL',
                         array('dcTranslation','LanguageURL'));

    // Include navlang in BlogFeedURL

    $core->tpl->addValue('BlogFeedURL',
                         array('dcTranslation','BlogFeedURL'));
    $core->tpl->addValue('CategoryFeedURL',
                         array('dcTranslation','CategoryFeedURL'));

    // Allow translation of presentation elements

    $core->tpl->addValue('MetaID',
                         array('dcTranslation','TranslatedMetaID'));
    $core->tpl->addValue('EntryCategory',
                         array('dcTranslation','TranslatedEntryCategory'));
    $core->tpl->addValue('EntryDate',
                         array('dcTranslation','TranslatedEntryDate'));
    $core->tpl->addValue('EntryTime',
                         array('dcTranslation','TranslatedEntryTime'));
    $core->tpl->addValue('CategoryTitle',
                         array('dcTranslation','TranslatedCategoryTitle'));
    $core->tpl->addValue('BlogDescription',
                         array('dcTranslation','BlogDescription'));
    $core->tpl->addValue('BlogName',
                         array('dcTranslation','BlogName'));
    

    // Give new properties to posts

    $core->addBehavior('coreBlogGetPosts',
                       array('publicdcTranslation','coreBlogGetPosts'));

    // Redefine those later, after all plugins have been loaded

    $core->addBehavior('publicPrepend',
                       array('publicdcTranslation','redefine'));

    // hard-coded preferences in case the preferences are not set

    $actla=$core->blog->settings->ptrans->ptrans_active_languages;
    if (!$actla) {
        $actla = 'en,fr';
    }
    $fbla=$core->blog->settings->ptrans->ptrans_fallback_language;
    if (!$fbla) {
        $fbla = 'en';
    }
    $_langs=explode(',',$actla);
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $_langx = $_SERVER['HTTP_ACCEPT_LANGUAGE'].','.$fbla;
    } else {
        $_langx = $fbla;
    }
    // $fbla is always correct. It is always desired.
    $cookielang="dc_language_".$core->blog->id;
    if (isset($_GET['navlang'])) {
        $_langx = $_GET['navlang'].','.$_langx;
        setcookie($cookielang,$_GET['navlang'],time()+31536000,'/');
    } else if (!empty($_COOKIE[$cookielang])) {
        $_langx = $_COOKIE[$cookielang].','.$_langx;
    }
    // langpieces is the list of "desired" languages, in decreasing order
    $langpieces = explode(",", $_langx);
    foreach ($langpieces as $i => $v) {
        $langpieces[$i]=preg_replace('/^[ ]*([a-zA-Z-]+).*$/','${1}',
                                     $langpieces[$i]);
        // We ignore the subdivisions of languages.
        // We don't delete them, because
        // e.g. Safari (OSX) uses only fr-fr as language string.
        $langpieces[$i]=substr($langpieces[$i],0,2);
    }
    // now we filter out the "not active" and redundant languages.
    $activelangs=array();
    foreach ($langpieces as $piece) {
        if (in_array($piece,$_langs) && !in_array($piece,$activelangs)) {
            $activelangs[]=$piece;
        }
    }
    // First remaining is preferred.
    $_lang=$activelangs[0];
    $core->lang_array=$activelangs;
    $core->blog->settings->system->lang=$_lang;
    $core->blog->settings->system->lang_nav=$_lang;
    l10n::set(DC_L10N_ROOT.'/'.$_lang.'/date');
    l10n::set(DC_L10N_ROOT.'/'.$_lang.'/public');
    l10n::set(DC_L10N_ROOT.'/'.$_lang.'/plugins');
    $modules=$core->plugins->getmodules();
    foreach ($modules as $id => $m) {
        $core->plugins->loadModuleL10N($id,$_lang,'main');
    }
    $blog_id=$core->con->escape($core->blog->id);
    $strReq='SELECT * FROM '.$core->prefix.'word WHERE blog_id = \''.
        $blog_id.'\' AND lang = \''.$core->con->escape($_lang).'\'';
    $rs=$core->con->select($strReq);
    if ($rs) {
        while($rs->fetch()) {
            $GLOBALS['__l10n'][$rs->w_word]=$rs->w_result;
        }
        unset($rs);
    }
    // The rest is definitions
 }
// Class for public initialization

class publicdcTranslation {
    public static function redefine() {
        global $core;
        $core->tpl->addValue('MetaID',
                             array('dcTranslation','TranslatedMetaID'));
        $core->tpl->addValue('TagFeedURL',
                             array('dcTranslation','TagFeedURL'));
        $core->url->register('tag_feed','feed/tag','^feed/tag/(.+)$',array('urlTranslation','metafeed'));
    }
    public static function coreBlogGetPosts($rs)
    {
        $rs->extend('rsExtPostTranslation');
    }
}

class rsExtPostTranslation
{
    public static function pickTranslation($rs) {
        global $_ctx;
        global $core;

        if (!isset($core->thistranslation)) {
            $core->thistranslation=array();
        }
        if (!isset($core->thistranslation['postid']) ||
            ($rs->post_id != $core->thistranslation['postid'])) {
            if (!isset($core->translation_force_id)) {
                if (isset($core->translation_force_lang)) {
                    $target_languages=array($core->translation_force_lang);
                } else {
                    $target_languages=$rs->core->lang_array;
                }
                $t_lev=0;
                $t_id=0;
                if (in_array($rs->post_lang, $target_languages)) {
                    $t_lev=array_search($rs->post_lang, $target_languages);
                } else {
                    $t_lev = 200000; // absurdly large value;
                }
                $translations=dcTranslation::getTranslationsByPost($rs->post_id);
                if (!$translations->isEmpty()) {
                    while ($translations->fetch()) {
                        if (in_array($translations->translation_lang, $target_languages)) {
                            $n_lev=array_search($translations->translation_lang, $target_languages);
                            if ($n_lev < $t_lev) {
                                $t_lev=$n_lev;
                                $t_id=$translations->translation_id;
                            }
                        }
                    }
                }
                unset($translations);
            } else {
                $t_id = $core->translation_force_id;
            }
            $core->thistranslation['postid']=$rs->post_id;
            if (!$t_id) {
                $core->thistranslation['lang']=$rs->post_lang;
                if (isset($rs->post_content_xhtml)) {
                    $core->thistranslation['excerpt']=$rs->post_excerpt;
                    $core->thistranslation['excerpt_xhtml']=$rs->post_excerpt_xhtml;
                    $core->thistranslation['content']=$rs->post_content;
                    $core->thistranslation['content_xhtml']=$rs->post_content_xhtml;
                    $core->thistranslation['ok']=true;
                } else {
                    $core->thistranslation['ok']=false;
                }
                $core->thistranslation['title']=$rs->post_title;
            } else {
                $translation=dcTranslation::getTranslation($t_id);
                $translation->fetch();
                $core->thistranslation['ok']=true;
                $core->thistranslation['lang']=$translation->translation_lang;
                $core->thistranslation['excerpt']=$translation->translation_excerpt;
                $core->thistranslation['excerpt_xhtml']=$translation->translation_excerpt_xhtml;
                $core->thistranslation['content']=$translation->translation_content;
                $core->thistranslation['content_xhtml']=$translation->translation_content_xhtml;
                $core->thistranslation['title']=$translation->translation_title;
                unset($translation);
            }
        }
    }
    public static function beginTranslation($rs)
    {
        if ($rs->core->thistranslation['ok'])  {
            $rs->post_content=$rs->core->thistranslation['content'];
            $rs->post_content_xhtml=$rs->core->thistranslation['content_xhtml'];
            $rs->post_excerpt_xhtml=$rs->core->thistranslation['excerpt_xhtml'];
            $rs->post_excerpt=$rs->core->thistranslation['excerpt'];    
        }
    }
    public static function endTranslation($rs)
    {
        if ($rs->core->thistranslation['ok']) {
            unset($rs->post_content);
            unset($rs->post_content_xhtml);
            unset($rs->post_excerpt_xhtml);
            unset($rs->post_excerpt);
        }
    }
    public static function getTitle($rs,$absolute_urls=false)
    {
        rsExtPostTranslation::pickTranslation($rs);
        return $rs->core->thistranslation['title'];
    }
    public static function getLang($rs,$absolute_urls=false)
    {
        rsExtPostTranslation::pickTranslation($rs);
        return $rs->core->thistranslation['lang'];
    }
    public static function translateContent($rs,$content,$absolute_urls=false)
    {
        rsExtPostTranslation::pickTranslation($rs);
        rsExtPostTranslation::beginTranslation($rs);
        $rep = rsExtPost::getContent($rs,$absolute_urls);
        rsExtPostTranslation::endTranslation($rs);
        return $rep;

    }
    public static function translateExcerpt($rs,$content,$absolute_urls=false) {
        // Completely ignore basic excerpt, redo everything
        rsExtPostTranslation::pickTranslation($rs);
        rsExtPostTranslation::beginTranslation($rs);
        $rep = rsExtPost::getExcerpt($rs,$absolute_urls);
        rsExtPostTranslation::endTranslation($rs);
        return $rep;    
    }
}

class urlTranslation extends dcUrlHandlers
{
    public static function opost($args)
    {
        global $core;
        $core->translation_force_id=0; // force post to be in original language
        if (!preg_match('/([a-z][a-z]*)\/(.*)$/',$args,$m)) {
            self::p404();
        } else {
            // remove the language
            // (not used, the original language is used anyway)
            $tlang=$m[1];
            $turl=$m[2];
            $strReq='SELECT P.post_type from '.
                $core->prefix.'post P WHERE P.post_url = \''.
                $core->con->escape($turl).
                '\' LIMIT 1';
            $rs=$core->con->select($strReq);
            if ($rs->isEmpty()) {
                self::p404();
            } else {
                $rs->fetch();
                $ttype=$rs->post_type;
                unset($rs);
                if ($ttype != 'page') {
                    self::post($turl);
                } else {
                    urlPages::pages($turl);
                }
            }
        }
        return;
    }
    public static function lang($args)
    {
        $_ctx =& $GLOBALS['_ctx'];
        $core =& $GLOBALS['core'];
    
        $n = self::getPageNumber($args);
        if (!preg_match('#^([a-z]+)$#',$args,$m)) {
            self::p404();
        } else {
            $langx=$m[1];
            $params['lang'] = $langx;
            // use tlangs instead of langs, else posts will be filtered
            // according to original language
            $_ctx->tlangs = dcTranslation::getLangs($params);
            if ($_ctx->tlangs->isEmpty()) {
                self::p404();
            } else {
                if ($n) {
                    $GLOBALS['_page_number'] = $n;
                }
                $_ctx->cur_lang = $args;
                $core->translation_force_lang = $args;
                self::home(null);
            }
        }
        return;
    }
    public static function tpost($args)
    {
        global $core;
        if (!preg_match('/([a-z][a-z]*)\/(.*)$/',$args,$m)) {
            self::p404();
        } else {
            $tlang=$m[1];
            $turl=$m[2];
            $strReq='SELECT T.translation_id, P.post_url, P.post_type from '.
                $core->prefix.'translation T, '.
                $core->prefix.'post P WHERE T.translation_lang = \''.
                $tlang.'\' AND T.translation_url = \''.
                $core->con->escape($turl).
                '\' AND P.post_id = T.post_id LIMIT 1';
            $rs=$core->con->select($strReq);
            if ($rs->isEmpty()) {
                self::p404();
            } else {
                $rs->fetch();
                $core->translation_force_id=$rs->translation_id;
                $turl=$rs->post_url;
                $ttype=$rs->post_type;
                unset($rs);
                if ($ttype != 'page') {
                    self::post($turl);
                } else {
                    urlPages::pages($turl);
                }
            }
        }
        return;
    }
  
    public static function superfeed($args)
    {
        global $core;
        global $_ctx;
        $mime = 'application/xml';
        $type="rss2";
        $mime = 'application/xml';
        $subtitle = array();
        $par=explode('/',$args);
        foreach ($par as $k => $v) {
            if (preg_match('/([^:]+):(.*)/',$v,$m)) {
                $key=$m[1];
                $val=$m[2];
                if ($key == 'tag') {
                    // We have a tag: thingy
                    $objMeta = new dcMeta($GLOBALS['core']);
                    $tag = $objMeta->getMeta('tag',null,$val);
                    if (!($tag->isEmpty())) {
                        $GLOBALS['_ctx']->meta = $tag;
                        $subtitle[] = $tag->meta_id;
                    }
                }
                if ($key == 'type') {
                    // We have a type: thingy
                    if ($val == 'atom' || $val == 'rss2') {
                        $type=$val;
                    }
                }
                if ($key == 'limit') {
                    // We have a type: thingy
                    $_ctx->nb_entry_per_page=(integer)$val;
                }
                if ($key == 'lang') {
                    // We have a lang: thingy
                    $array=array('lang'=>$val);
                    $lang = dcTranslation::getLangs($array);
                    if (!($lang->isEmpty())) {
                        $_ctx->tlangs = $lang;
                        $subtitle[] = $lang->real_lang;
                        $_ctx->cur_lang = $val;
                        $core->translation_force_lang = $val;
                    }
                }
            }
        }
        if ($subtitle) {
            $GLOBALS['_ctx']->feed_subtitle = ' - '.join(' - ',$subtitle);
        }
    
        if ($type == 'atom') {
            $mime = 'application/atom+xml';
        }
    
        $tpl = $type;
        $tpl .= '.xml';
    
        self::serveDocument($tpl,$mime);
        return;
    }
    public static function metafeed($args)
    // Compatibility function
    {
        global $core;
        global $_ctx;
        if (preg_match('!^navlang:([^/]*)/(.*)$!',$args,$m)) {
            $activelangs=explode("~",$m[1]);
            $_lang=$activelangs[0];
            $core->lang_array=$activelangs;
            $core->blog->settings->system->lang=$_lang;
            $core->blog->settings->system->lang_nav=$_lang;
            $newargs=$m[2];
        } else {
            $newargs=$args;
        }
        urlMetadata::tagFeed($newargs);
        return;
    }
    public static function feed($args)
    // Compatibility function
    {
        global $core;
        global $_ctx;
        if (preg_match('!^navlang:([^/]*)/(.*)$!',$args,$m)) {
            $activelangs=explode("~",$m[1]);
            $_lang=$activelangs[0];
            $core->lang_array=$activelangs;
            $core->blog->settings->system->lang=$_lang;
            $core->blog->settings->system->lang_nav=$_lang;
            $newargs=$m[2];
        } else {
            $newargs=$args;
        }
		if (preg_match('!^([a-z]{2}(-[a-z]{2})?)/(.*)$!',$newargs,$m)) {
			$val = $m[1];
			$newargs = $m[3];
            $array=array('lang'=>$val);
            $lang = dcTranslation::getLangs($array);
            if (!($lang->isEmpty())) {
                $_ctx->tlangs = $lang;
                $_ctx->cur_lang = $val;
                $core->translation_force_lang = $val;
                $core->blog->settings->system->lang=$val;
            }
		
		}
        parent::feed($newargs);
        return;
    }
}
?>