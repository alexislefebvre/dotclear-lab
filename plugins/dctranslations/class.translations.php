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

if (!defined('DC_RC_PATH')) { return; }

class dcTranslation
{
  
    // Left as was, but probably not useful:
    // * If added as an object, must be tested for existence any time
    // it is used (since templates may be arbitrary)
    // * thus, all functions but the ones used in admin part (creation,
    // destruction, update) are static and can be called independently
  
    public function __construct($core)
    {
        $this->core =& $core;
        $this->con =& $this->core->con;
        $this->table = $this->core->prefix.'translation';
    }

    // Utility parts: get all translations for a given post. If all = true,
    // the original language is also included (as a pseudo-translation).

    public static function getTranslationsByPost($post=null,$all=false)
    {
        global $core;
        $post_id=$core->con->escape($post);
        if (!$post) $post_id="0";
        $getReq = 'SELECT * FROM '.$core->prefix.'translation T, '.
            $core->prefix.'post P WHERE P.post_id = \''.$post_id.
            '\' '.
            "AND P.blog_id = '".$core->con->escape($core->blog->id).'\' ';
        //      "AND P.post_type = 'post'";
        if ($all) {
            // Get translations and joker
            $getReq .= 'AND (P.post_id = T.post_id OR T.translation_id = 0) ORDER BY T.post_id';
        } else {
            // Get only translations
            $getReq .= 'AND P.post_id = T.post_id ORDER BY T.post_id';
        }
        $rs = $core->con->select($getReq);
        return $rs;
    }

    // Get a precise translation known by its id

    public static function getTranslation($id)
    {
        global $core;
        $t_id=$core->con->escape($id);
        $getReq = 'SELECT * FROM '.$core->prefix.'translation T, '.
            $core->prefix.'post P WHERE T.translation_id = \''.
            $t_id.'\' AND P.post_id = T.post_id '.
            "AND P.blog_id = '".$core->con->escape($core->blog->id).'\' ';
        //            "AND P.post_type = 'post'";
        $rs = $core->con->select($getReq);
        return $rs;
    }

    // Instance function: modify a cursor and check that the resulting
    // translation is valid
  
    public function checkTranslation($cur,$t_id,$t_lang,$t_title,$t_xpid,
                                     $t_e,$t_ex,$t_c,$t_cx,$t_url)
    {
        if (strlen($t_lang)<2) {
            throw new Exception(sprintf(__('Language "%s" is not accepted'),$t_lang));
        }
        if (!$t_xpid) {
            throw new Exception(sprintf(__('No attached post for language "%s"'),$t_lang));
        }
        $t_pid=(integer) $t_xpid;
        $rs = $this->con->select('SELECT COUNT(*) FROM '.
                                 $this->table.' WHERE translation_lang=\''.
                                 $t_lang.'\' AND post_id=\''.$t_pid.'\'');
        if ((integer) $rs->f(0)>($t_id?1:0)) {
            throw new Exception(sprintf(__('Language "%s" is already translated'),$t_lang));
        }
        if (strlen($t_cx)==0) {
            throw new Exception(sprintf(__('No content for language "%s"'),$t_lang));
        }
        $post = $this->con->select('SELECT * FROM '.
                                   $this->core->prefix.'post WHERE post_id =\''.
                                   $t_pid.'\'');
        if ($post->isEmpty()) {
            throw new Exception(__('No such entry ID'));
        }
        $post->fetch();
        if ($post->post_lang == $t_lang) {
            throw new Exception(sprintf(__('Language "%s" is the original language'),$t_lang));      
        }
        $cur->translation_lang=$t_lang;
        $cur->translation_title=$t_title;
        if ($post->post_title && !($cur->translation_title)) {
            $cur->translation_title=$post->post_title;
        }
        $cur->translation_excerpt=$t_e;
        $cur->translation_content=$t_c;
        $cur->translation_excerpt_xhtml=$t_ex;
        $cur->translation_content_xhtml=$t_cx;
        $cur->post_id=$t_pid;
        $words =
            $cur->translation_title.' '.
            $cur->translation_excerpt_xhtml.' '.
            $cur->translation_content_xhtml;
        $cur->translation_words = implode(' ',text::splitWords($words));
        if (!$t_id) {
            $rs = $this->con->select('SELECT MAX(translation_id) FROM '.$this->table);
            $cur->translation_id = (integer) $rs->f(0) + 1;
        } else {
            $cur->translation_id = $t_id;
        }
        $this->getTranslationURL($cur,$t_url,$post);
    }

    // Instance function: delete a translation knwon by id

    public function deleteTranslation($t_id)
    {
        $id=(int)$t_id;
        $this->con->select('DELETE FROM '.$this->table.' WHERE translation_id = '."'".$id."'");
    }

    // Instance function: update a translation knwon by id

    public function updateTranslation($t_id,$t_lang,$t_title,$t_pid,
                                      $t_e,$t_ex,$t_c,$t_cx,$t_url)
    {
        $core=$this->core;
        $cur = $core->con->openCursor($this->table);
        $core->con->begin();
        $cur->clean();
        try {
            $t_id = $this->checkTranslation($cur,$t_id,$t_lang,$t_title,$t_pid,
                                            $t_e,$t_ex,$t_c,$t_cx,$t_url);
        } catch (Exception $e) {
            $core->con->rollback();
            throw $e;
        }
        $cur->update('WHERE translation_id = \''.$cur->translation_id.'\'');
        $core->con->commit();
    }

    // Instance function: insert a translation knwon by id

    public function insertTranslation($t_lang,$t_title,$t_pid,
                                      $t_e,$t_ex,$t_c,$t_cx,$t_url)
    {
        $core=$this->core;
        $cur = $core->con->openCursor($this->table);
        $core->con->begin();
        $cur->clean();
        try {
            $t_id = $this->checkTranslation($cur,0,$t_lang,$t_title,$t_pid,
                                            $t_e,$t_ex,$t_c,$t_cx,$t_url);
        } catch (Exception $e) {
            $core->con->rollback();
            throw $e;
        }
        $cur->insert();
        $core->con->commit();
    }

    // Instance function: compute an URL based on defaults

    public function getTranslationURL($cur,$t_url,$post)
    {
        $cur->translation_url=$t_url;
        if ($t_url == '') {
            $post_dt=$post->post_dt;
            $url_patterns = array(
                                  '{y}' => date('Y',strtotime($post_dt)),
                                  '{m}' => date('m',strtotime($post_dt)),
                                  '{d}' => date('d',strtotime($post_dt)),
                                  '{t}' => text::str2URL($cur->translation_title),
                                  '{id}' => (integer) $post->post_id
                                  );
            $t_url = str_replace(
                                 array_keys($url_patterns),
                                 array_values($url_patterns),
                                 $this->core->blog->settings->system->post_url_format
                                 );
        } else {
            $t_url = text::tidyURL($t_url);
        }
        // Let's check if URL is taken...
        $strReq = 'SELECT translation_url FROM '.$this->core->prefix.
            'translation T, '.$this->core->prefix.'post P '.
            "WHERE T.translation_url = '".$this->con->escape($t_url)."' ".
            'AND P.post_id = T.post_id '.
            'AND T.translation_id <> '.(integer) $cur->translation_id. ' '.
            "AND P.blog_id = '".$this->con->escape($this->core->blog->id)."' ".
            'ORDER BY translation_url DESC';
        $rs = $this->con->select($strReq);
        if (!$rs->isEmpty()) {
            $strReq = 'SELECT translation_url FROM '.$this->core->prefix.
                'translation T, '.$this->core->prefix.'post P '.
                "WHERE T.translation_url LIKE '".$this->con->escape($t_url)."%' ".
                'AND P.post_id = T.post_id '.
                'AND T.translation_id <> '.(integer) $cur->translation_id. ' '.
                "AND P.blog_id = '".$this->con->escape($this->core->blog->id)."' ".
                'ORDER BY T.translation_url DESC ';
            $rs = $this->con->select($strReq);
            if (preg_match('/(.*?)([0-9]+)$/',$rs->translation_url,$m)) {
                $i = (integer) $m[2];
                $url = $m[1];
            } else {
                $i = 1;
            }
            $t_url.= ($i+1);
        }
        $cur->translation_url=$t_url;
        return $t_url;
    }

    // Manage languages
    // replacement for blog getLangs, which does not take translations
    // into account
    // Used in Languages block (replace here)
    // Used in Languages widgets (replace in new widget)
    // Used in urlhandling (replace here)
    // Used in admin (can be left as is)

    public static function getLangs($params=array())
    {
        global $core;

        $post_lang='(if(T.translation_lang<>\'\',T.translation_lang,P.post_lang))';
        $strReq = 'SELECT COUNT(P.post_id) as nb_post, '.$post_lang.
            ' as real_lang '.
            'FROM '.$core->prefix.'post P, '.$core->prefix.'translation T '.
            "WHERE blog_id = '".$core->con->escape($core->blog->id)."' ".
            "AND $post_lang <> '' ".
            "AND $post_lang IS NOT NULL ".
            "AND ((T.translation_id = 0) or (T.post_id = P.post_id)) ";
    
        if (!$core->auth->check('contentadmin',$core->blog->id)) {
            $strReq .= 'AND ((post_status = 1 ';
      
            if ($core->blog->without_password) {
                $strReq .= 'AND post_password IS NULL ';
            }
            $strReq .= ') ';
      
            if ($core->auth->userID()) {
                $strReq .= "OR user_id = '".$core->con->escape($core->core->auth->userID())."')";
            } else {
                $strReq .= ') ';
            }
        }
        
        if (isset($params['lang'])) {
            $strReq .= "AND $post_lang = '".$core->con->escape($params['lang'])."' ";
        }
        $strReq .= 'GROUP BY real_lang ';

        $order = 'desc';
        if (!empty($params['order']) && preg_match('/^(desc|asc)$/i',$params['order'])) {
            $order = $params['order'];
        }
        $strReq .= 'ORDER BY nb_post '.$order.' ';
        return $core->con->select($strReq);
    }

    // -----------------------------------------------------
    // -----------------------------------------------------
    //
    // Public parts
    //
    // -----------------------------------------------------
    // -----------------------------------------------------

    // Translations: loop on translations; language, entry URL for this exact
    // translation. It also loops on the original language.

    public static function Translations($attr,$content)
    {
        $res = "<?php\n";
        $res .= '$_ctx->translation = new dcTranslation($core);';
        $res .= '$_ctx->translations = $_ctx->translation->'.
            'getTranslationsByPost($_ctx->posts->post_id,true);';
        $res .= 'while ($_ctx->translations->fetch()) : ?>'.
            $content.
            '<?php endwhile; $_ctx->translations = null; ?>';   
        return $res;
    }
    public static function TranslationLang($attr)
    {
        $f = $GLOBALS['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,'($_ctx->translations->translation_id?$_ctx->translations->translation_lang:$_ctx->translations->post_lang)').'; ?>';
    }
    public static function TranslationEntryURL($attr)
    {
        return '<?php $type=($_ctx->translations->translation_id?\'tpost\':'.
            '\'opost\');$turl=($_ctx->translations->translation_id?'.
            '$_ctx->translations->translation_url:$_ctx->translations->post_url);'.
            '$tlang=($_ctx->translations->translation_id?'.
            '$_ctx->translations->translation_lang:$_ctx->translations->post_lang);'.
            'echo $GLOBALS[\'core\']->blog->url.'.
            '$GLOBALS[\'core\']->url->getBase($type).\'/\'.'.
            'html::sanitizeURL($tlang.\'/\'.$turl); ?>';
    }

    // Entries: loop on entries redefined (to filter on translations, not lang)
    // Redefine EntryTitle, EntryLang, so that they operate on the translated
    // EntryLangURL links to posts in the same language as the displayed entry

    public static function EntryTitle($attr)
    {
        $f = $GLOBALS['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,'$_ctx->posts->getTitle()').'; ?>';
    }
    public static function EntryLang($attr)
    {
        $f = $GLOBALS['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,'$_ctx->posts->getLang()').'; ?>';
    }
    public static function EntryLangURL($attr)
    {
        $f = $GLOBALS['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("lang")."/".$_ctx->posts->getLang()').'; ?>';
    }

    // before Entries, do that
    public static function beforeEntries($core,$b,$attr)
    {
        if ($b == 'Entries') {
            $result='';
            $p='';
            if (isset($attr['lang'])) {
                $p = "\$params['post_lang'] = '".addslashes($attr['lang'])."';\n";
                unset($attr['lang']);
            }
            $p .=
                'if ($_ctx->exists("tlangs")) { '.
                // Use the translation table
                "@\$params['from'] .= ', '.\$core->prefix.'translation T ';".
                // Either a translation or the joker must match
                "@\$params['sql'] .= 'AND (T.post_id = P.post_id OR T.translation_id = 0) ';".
                // post_lang or trans_lang must match
                "\$params['sql'] .= \"AND ((P.post_lang = '\".\$core->blog->con->escape(\$_ctx->tlangs->real_lang).\"' AND T.translation_id = 0) OR T.translation_lang = '\".\$core->blog->con->escape(\$_ctx->tlangs->real_lang).\"') \"; ".
                "}\n";
            $result='<?php '.$p.' ?>';
            return($result);
        }
    }

    // Languages : redefine loop, to operate on any language, not only original
    // To maintain the filtering in the cases somebody does <Languages><Entries>
    // (to show may be the excerpt of the first few articles in each language)
    // redefine LanguagesHeader/Footer/Code/URL + new LanguageCount (useful?)
    // Add option to LanguageIfCurrent -> <tpl:LanguageIfCurrent not="1">

    public static function LanguagesHeader($attr,$content)
    {
        return 
            "<?php if (\$_ctx->tlangs->isStart()) : ?>".
            $content.
            "<?php endif; ?>";
    }
  
    public static function LanguagesFooter($attr,$content)
    {
        return
            "<?php if (\$_ctx->tlangs->isEnd()) : ?>".
            $content.
            "<?php endif; ?>";
    }
  
    public static function LanguageCode($attr)
    {
        $f = $globals['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,'$_ctx->tlangs->real_lang').'; ?>';
    }
  
    public static function LanguageCount($attr)
    {
        $f = $globals['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,'$_ctx->tlangs->nb_post').'; ?>';
    }

    public static function LanguageIfCurrent($attr,$content)
    {
        $op='==';
        if (isset($attr['not'])) {
            $op='!=';
        }
        return
            "<?php if (\$_ctx->cur_lang ".$op." \$_ctx->tlangs->real_lang) : ?>".
            $content.
            "<?php endif; ?>";
    }

    public static function LanguageURL($attr)
    {
        $f = $globals['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("lang")."/".$_ctx->tlangs->real_lang').'; ?>';
    }
  
    public static function Languages($attr,$content)
    {
        $p = '$params = array();';
        if (isset($attr['lang'])) {
            $p = "\$params['lang'] = '".addslashes($attr['lang'])."';\n";
        }
        $order = 'desc';
        if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
            $p .= "\$params['order'] = '".$attr['order']."';\n ";
        }
        $res = "<?php\n";
        $res .= $p;
        $res .= '$_ctx->tlangs = dcTranslation::getLangs($params); unset($params);'."\n";
        $res .= "?>\n";
    
        $res .=
            '<?php if ($_ctx->tlangs->count() > 1) : '.
            'while ($_ctx->tlangs->fetch()) : ?>'.$content.
            '<?php endwhile; $_ctx->langs = null; endif; ?>';
        return $res;
    }

    // Rewrite of many trivial functions, __ing the results
    public static function BlogFeedURL($attr,$content) {
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';
		
		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'atom';
		}
		
        $f = $GLOBALS['core']->tpl->getFilters($attr);
 		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("feed")."/navlang:".join("~",$core->lang_array)."/'.$type.'"').'; ?>';
    }
    public static function CategoryFeedURL($attr,$content) {
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';
		
		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'atom';
		}
		
        $f = $GLOBALS['core']->tpl->getFilters($attr);
 		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("feed")."/navlang:".join("~",$core->lang_array)."/category/".$_ctx->categories->cat_url."/'.$type.'"').'; ?>';
    }
    public static function TagFeedURL($attr,$content) {
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';
		
		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'atom';
		}
		
        $f = $GLOBALS['core']->tpl->getFilters($attr);
 		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("tag_feed")."/navlang:".join("~",$core->lang_array)."/".'.
                                     'rawurlencode($_ctx->meta->meta_id)."/'.$type.'"').'; ?>';
    }
    public static function BlogName($attr,$content) {
        $f = $GLOBALS['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,'__($core->blog->name)').'; ?>';
    }
    public static function BlogDescription($attr,$content) {
        $f = $GLOBALS['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,'__($core->blog->desc)').'; ?>';
    }
    public static function TranslatedMetaID($attr,$content) {
        $f = $GLOBALS['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,'__($_ctx->meta->meta_id)').'; ?>';
    }
    public static function TranslatedEntryCategory($attr,$content) {
        $f = $GLOBALS['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,'__($_ctx->posts->cat_title)').'; ?>';
    }
    public static function TranslatedCategoryTitle($attr,$content) {
        $f = $GLOBALS['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,'__($_ctx->categories->cat_title)').'; ?>';
    }
    public static function TranslatedEntryDate($attr) {
        $format = '';
        if (!empty($attr['format'])) {
            $format = addslashes($attr['format']);
        } else {
            $format=$GLOBALS['core']->blog->settings->system->date_format;
        }
        $f = $GLOBALS['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,"\$_ctx->posts->getTime(__('".$format."'))").'; ?>';
    }
    public static function TranslatedEntryTime($attr) {
        $format = '';
        if (!empty($attr['format'])) {
            $format = addslashes($attr['format']);
        } else {
            $format=$GLOBALS['core']->blog->settings->system->time_format;
        }
        $f = $GLOBALS['core']->tpl->getFilters($attr);
        return '<?php echo '.sprintf($f,"\$_ctx->posts->getTime(__('".$format."'))").'; ?>';
    }

    // Function to reindex all posts with translation //
  
    public static function indexAllPosts($postid=null,$start=null,$limit=null)
    {
        global $core;
        $strReq = 'SELECT COUNT(post_id) '.
            'FROM '.$core->prefix.'post';
        $rs = $core->con->select($strReq);
        $count = $rs->f(0);
    
        $strReq = 'SELECT post_id, post_title, post_excerpt_xhtml, post_content_xhtml, post_lang '.
            'FROM '.$core->prefix.'post';
    
        if ($postid !== null) {
            $strReq .= ' WHERE post_id = \''.$postid.'\'';
        }
        if ($start !== null && $limit !== null) {
            $strReq .= $core->con->limit($start,$limit);
        }
    
        $rs = $core->con->select($strReq,true);
    
        $cur = $core->con->openCursor($core->prefix.'post');
    
        while ($rs->fetch())
            {
                $words = $rs->post_title.' '.   $rs->post_excerpt_xhtml.' '.
                    $rs->post_content_xhtml;
                $strReq='SELECT translation_id, translation_title, '.
                    'translation_excerpt_xhtml, translation_content_xhtml, '.
                    'translation_lang FROM '.$core->prefix.'translation as T WHERE '.
                    'T.post_id = \''.$rs->post_id.'\'';
                $trs=$core->con->select($strReq,true);
                while ($trs->fetch());
                $words .= ' '.$trs->translation_title.' '.
                    $trs->translation_excerpt_xhtml.' '.
                    $trs->translation_content_xhtml;
                $cur->post_words = implode(' ',text::splitWords($words));
                $cur->update('WHERE post_id = '.(integer) $rs->post_id);
                $cur->clean();
            }
    
        if ($start+$limit > $count) {
            return null;
        }
        return $start+$limit;
    }
}

?>