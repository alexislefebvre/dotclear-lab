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
if (!defined('DC_RC_PATH')) {return;}

$core->addBehavior('initWidgets',array('dctranslationsWidgets','initWidgets'));
$core->addBehavior('initDefaultWidgets',array('dctranslationsWidgets','initDefaultWidgets'));

class dctranslationsWidgets
{
    public static function initWidgets(&$w)
    {
        $w->create('navlangs',__('Navigation language'),array('dctranslationsWidgets','navlangs'));
        $w->navlangs->setting('title',__('Title:'),__('Navigation language'));
        $w->navlangs->setting('homeonly',__('Home page only'),1,'check');
        $w->create('dctranslations',__('Show articles only in'),array('dctranslationsWidgets','languageWidget'));
        $w->dctranslations->setting('title',__('Title:'),__('Show articles only in'));
        $w->dctranslations->setting('homeonly',__('Home page only'),0,'check');
        $w->create('showtranslations',__('Translations'),array('dctranslationsWidgets','translationWidget'));
        $w->showtranslations->setting('title',__('Title:'),__('Translations'));
    }
        
    public static function initDefaultWidgets(&$w,&$d)
    {
        $d['extra']->append($w->navlangs);
        $d['extra']->append($w->dctranslations);
        $d['extra']->append($w->showtranslations);
    }
    public static function languageWidget(&$w)
    {
        global $core, $_ctx;
		
        if ($w->homeonly && $core->url->type != 'default' && $core->url->type != 'lang') {
            return;
        }
        $res='';
        $add = $core->blog->url.$core->url->getBase('lang');
        $rs = dcTranslation::getLangs(null);
        if (!$rs->isEmpty()) {
            $res = '<div class="languagewidget">'.
                ($w->title ? '<h2>'.html::escapeHTML(__($w->title)).'</h2>' : '').
                '<ul>';
            $langs=l10n::getISOcodes();
            while ($rs->fetch()) {
                $res .=
                    ' <li>'.
                    '<a href="'.$add.'/'.$rs->real_lang.'"><span class="language" lang="'.$rs->real_lang.'">'.ucfirst($langs[$rs->real_lang]).'</span></a>'.
                    ' </li>';
            }
            $res .= '</ul></div>';
        }
        return $res;
    }
    public static function translationWidget(&$w)
    {
        global $core, $_ctx;
        if (!preg_match('/post/',$core->url->type) && !preg_match('/^pages/',$core->url->type) ) {
            return '';
        }
        $langs=l10n::getISOcodes();
        $post=$_ctx->posts;
        $langname=$post->getLang();
        $p = '';
        $rs=dcTranslation::getTranslationsByPost($post->post_id,true);
        if ($rs) {
            $translations=array();
            while($rs->fetch()) {
                if ($rs->translation_id == 0 && $rs->post_lang != $langname) {
                    $tlang=$rs->post_lang;
                    $turl=$core->blog->url.$core->url->getBase('opost').
                        '/'.html::sanitizeURL($tlang.'/'.$rs->post_url);
                    $translations[]='<a href="'.$turl.'"><span class="language" lang="'.$tlang.'">'.ucfirst($langs[$tlang]).'</span></a>';
                } elseif ($rs->translation_id != 0 && $rs->translation_lang != $langname) {
                    $tlang=$rs->translation_lang;
                    $turl=$core->blog->url.$core->url->getBase('tpost').
                        '/'.html::sanitizeURL($tlang.'/'.$rs->translation_url);
                    $translations[]='<a href="'.$turl.'"><span class="language" lang="'.$tlang.'">'.ucfirst($langs[$tlang]).'</span></a>';
                }
            }
            unset($rs);
            if ($translations) {
                $p.='<p class="post-translations">';
                $p.=join('&nbsp;',$translations);
                $p.='</p>';
            }
        }
        if (!$p) {
            return '';
        }
        $p ='<div class="translations" id="translations">'.
            ($w->title ? '<h2>'.html::escapeHTML(__($w->title)).'</h2>' : '').
            $p.'</div>';
        return $p;
    }
    public static function navlangs(&$w)
    {
        global $core, $_ctx;
		
        if ($w->homeonly && $core->url->type != 'default' && $core->url->type != 'lang') {
            return;
        }
		
        $al=$core->blog->settings->ptrans_active_languages;
        if (!$al) {
            $al="fr,en";
        }
        $active_languages=explode(',',$al);
		
        $res =
            '<div class="navlang">'.
            ($w->title ? '<h2>'.html::escapeHTML(__($w->title)).'</h2>' : '').
            '<ul>';
		
        foreach ($active_languages as $k => $v)
            {
                $langs=l10n::getISOcodes();
                $e_url = http::getSelfURI();
                $replace = (bool) preg_match('/(\\?|&)navlang\\=[^&]*/',$e_url);
                if ($replace) {
                    $e_url = preg_replace('/(\\?|&)(navlang\\=)([^&]*)(.*)/', '$4',$e_url);
                }
                if (strpos($e_url,'?')) {
                    $e_url.="&amp;";
                } else {
                    $e_url.="?";
                }
                $e_url .= 'navlang=';
                $res .=
                    ' <li>'.
                    '<a href="'.$e_url.$v.'"><span class="language" lang="'.$v.'">'.ucfirst($langs[$v]).'</span></a>'.
                    ' </li>';
            }
		
        $res .= '</ul></div>';
		
        return $res;
    }
}
?>