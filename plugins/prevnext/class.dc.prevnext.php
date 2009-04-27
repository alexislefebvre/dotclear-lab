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
if (!defined('DC_RC_PATH')) { return; }

class dcPrevNext {  
    public static function getNextPostBy($post,$currentcat,$currentlang,$dir)
    {
        global $core;
    
        $post_id = (integer) $post->post_id;
        $dt = $post->post_dt;
    
        if($dir > 0) {
            $sign = '>';
            $order = 'ASC';
        }
        else {
            $sign = '<';
            $order = 'DESC';
        }
        $params=array();
        $params['limit'] = 1;
        $params['order'] = 'post_dt '.$order.', P.post_id '.$order;
        $params['sql'] =
            'AND ('.
            "(post_dt = '".$core->con->escape($dt)."' AND P.post_id ".$sign." ".$post_id.")".
            "OR(post_dt ".$sign." '".$core->con->escape($dt)."')".')';
        if ($currentcat) {
            $params['cat_id'] = $post->cat_id;
        }
        if ($currentlang) {
            if ($core->plugins->moduleExists('dctranslations')) {
                // compatibility with dctranslations plugin
                $lang=$core->con->escape($post->getLang());
                $params['from'] = ', '.$core->prefix.'translation T ';
                $params['sql'] .= 'AND (T.post_id = P.post_id OR T.translation_id = 0) ';
                $params['sql'] .= "AND ((P.post_lang = '".$lang."' AND T.translation_id = 0) OR T.translation_lang = '".$lang."') ";
            } else {
                $params['post_lang'] = $post->post_lang;
            }
        }
        $rs = $core->blog->getPosts($params);
        if ($rs->isEmpty()) {
            return null;
        }
        return $rs;
    }


    public static function getNextPostByTag($post,$dir,$currenttag='photo',$tagtype='tag')
    {
        global $core;
        global $_ctx;
        $post_id = (integer) $post->post_id;
        $dt = $post->post_dt;
    
        $params=array();
        if($dir > 0) {
            $sign = '>';
            $order = 'ASC';
        } else {
            $sign = '<';
            $order = 'DESC';
        }
        $params['from'] = ', '.$core->prefix.'meta META ';
        $params['limit'] = 1;
        $params['order'] = 'post_dt '.$order.', P.post_id '.$order;
    
        if ($currenttag === null) {
            $currenttag = $_ctx->meta->meta_id;
        }
        $params['sql'] =
            'AND ('.
            "(post_dt = '".$core->con->escape($dt)."' AND P.post_id ".$sign." ".$post_id.")".
            "OR(post_dt ".$sign." '".$core->con->escape($dt)."')".')'.
            " AND META.post_id = P.post_id AND META.meta_type = '".$tagtype."'".
            " AND META.meta_id = '".$core->con->escape($currenttag)."' ";
        $rs = $core->blog->getPosts($params);
        if ($rs->isEmpty()) {
            return null;
        }
        return $rs;
    }
    public static function helper($trans,$dir,$prevsign,$nextsign,&$rs) {
        global $core;
        $ans='';
        //compatibility with dctranslations
        if ($trans) {
            $lname=$rs->getTitle();
        } else {
            $lname=$rs->post_title;
        }
        if ($dir == 1) {
            $char = $nextsign;
        } else {
            $char = $prevsign;
        }
        return '<a title="'.$lname.'" href="'.$core->blog->url.
            $core->url->getBase("post").'/'.$rs->post_url.'">'.$char.'</a>';
    }
    public static function contextualNavigation(&$w)
    {
        global $core;
        global $_ctx;
        if (!preg_match('/post/',$core->url->type) && !preg_match('/^pages/',$core->url->type) ) {
            return '';
        }
        $ps=$w->prevsign;
        $ns=$w->nextsign;
        $trans=$core->plugins->moduleExists('dctranslations');
        $langs=l10n::getISOcodes();
    
        $p='<div id="prevnext">'.
            ($w->title ? '<h2 title="'.__('Browse through similar posts').'">'.html::escapeHTML(__($w->title)).'</h2>' : '');
        $p.='<ul>';
        $post=$_ctx->posts;
        $page=($post->post_type == 'page');
        if ($post->cat_id) {
            $p.='<li>';
            $rs=dcPrevNext::getNextPostBy($post,1,0,-1);
            if ($rs) {
                $p.=dcPrevNext::helper($trans,-1,$ps,$ns,$rs).'&nbsp;';
                unset ($rs);
            }
            $catname=html::escapeHTML(__($post->cat_title));
            $p.='<a href="'.$core->blog->url.$core->url->getBase("category").
                '/'.$post->cat_url.'" title="'.$catname.'">'.$catname.'</a>';
            $rs=dcPrevNext::getNextPostBy($post,1,0,1);
            if ($rs) {
                $p.='&nbsp;'.dcPrevNext::helper($trans,1,$ps,$ns,$rs);
                unset ($rs);
            }
            $p.='</li>';
        } else {
            // no category
        }
        if ($trans) {
            $langname=$post->getLang();
        } else {
            $langname=$post->post_lang;
        }
        if (!$page) {
            $p.='<li class="post-translations tags-sep">';
            $rs=dcPrevNext::getNextPostBy($post,0,1,-1);
            if ($rs) {
                $p.=dcPrevNext::helper($trans,-1,$ps,$ns,$rs).'&nbsp;';
                unset ($rs);
            }
            $p.='<a href="'.$core->blog->url.$core->url->getBase("lang").
                '/'.$langname.'" title="'.$langname.'"><span class="language" lang="'.$langname.'">'.ucfirst($langs[$langname]).'</span></a>';
            $rs=dcPrevNext::getNextPostBy($post,0,1,1);
            if ($rs) {
                $p.='&nbsp;'.dcPrevNext::helper($trans,1,$ps,$ns,$rs);
                unset ($rs);
            }
            $p.='</li>';
        }
        // Now, tags (begin)
        $meta=new dcMeta($core);
        $tags=$meta->getMeta('tag',null,null,$post->post_id);
        // Now, translations
        if ($trans) {
            $rs=dcTranslation::getTranslationsByPost($post->post_id,true);
            if ($rs) {
                $translations=array();
                while($rs->fetch()) {
                    if ($rs->translation_id == 0 && $rs->post_lang != $langname) {
                        $tlang=$rs->post_lang;
                        $turl=$core->blog->url.$core->url->getBase('opost').
                            '/'.html::sanitizeURL($tlang.'/'.$rs->post_url);
                        $translations[]='<a href="'.$turl.'" hreflang="'.$tlang.'"><span class="language" lang="'.$tlang.'">'.ucfirst($langs[$tlang]).'</span></a>';
                    } elseif ($rs->translation_id != 0 && $rs->translation_lang != $langname) {
                        $tlang=$rs->translation_lang;
                        $turl=$core->blog->url.$core->url->getBase('tpost').
                            '/'.html::sanitizeURL($tlang.'/'.$rs->translation_url);
                        $translations[]='<a href="'.$turl.'" hreflang="'.$tlang.'"><span class="language" lang="'.$tlang.'">'.ucfirst($langs[$tlang]).'</span></a>';
                    }
                }
                unset($rs);
                if ($translations) {
                    $p.='<li class="post-translations'.($tags?' tags-sep':'').'">'.__('Translations:').'&nbsp;';
                    $p.=join('&nbsp;',$translations);
                    $p.='</li>';
                }
            }
        }
        // End language
        // Now tags (begun earlier)
        if ($tags) {
            $alltags=$meta->getMeta('tag',null,null,null);
            $tagcarray=array();
            while ($alltags->fetch()) {
                $tagcarray[$alltags->meta_id]=$alltags->count;
            }
            $tagsarray=array();
            while($tags->fetch()) {
                $pp='<li>';
                $rs=dcPrevNext::getNextPostByTag($post,-1,$tags->meta_id);
                if ($rs) {
                    $pp.=dcPrevNext::helper($trans,-1,$ps,$ns,$rs).'&nbsp;';
                    unset ($rs);
                }
                $pp.='<a href="'.$core->blog->url.$core->url->getBase('tag').
                    '/'.rawurlencode($tags->meta_id).'">'.html::escapeHTML(__($tags->meta_id)).'</a>';
                $rs=dcPrevNext::getNextPostByTag($post,1,$tags->meta_id);
                if ($rs) {
                    $pp.='&nbsp;'.dcPrevNext::helper($trans,1,$ps,$ns,$rs);
                    unset ($rs);
                }
                $pp.='</li>';
                $tagsarray[$tagcarray[$tags->meta_id].$tags->meta_id]=$pp;
            }
            ksort($tagsarray);
            foreach($tagsarray as $k => $v) {
                $p.=$v;
            }
        }
        unset($tags);
        $p.='</ul></div>';
        return $p;
    }
}

?>