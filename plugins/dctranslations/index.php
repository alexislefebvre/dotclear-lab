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

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

# default tab
$default_tab = 'list';
if (!empty($_REQUEST['tab']))
	{
        switch ($_REQUEST['tab'])
            {
            case 'settings' :
                $default_tab = 'settings';
                break;
            case 'maintenance' :
                $default_tab = 'settings';
                break;
            }
	}

$iso_codes = l10n::getISOCodes();
$locales_content = scandir(DC_L10N_ROOT);
$tmp = array();
foreach ($locales_content as $v) {
    $c = ($v == '.' || $v == '..' || !is_dir(DC_L10N_ROOT.'/'.$v)
          || !isset($iso_codes[$v]));
    if (!$c) {
        $tmp[$v] = ucfirst($iso_codes[$v]);
    }
}
$tmp['en']= ucfirst($iso_codes['en']);
$existent_languages_array=$tmp;

function transhelper($k,$v,$active_languages_array,$existent_languages_array) {
    echo '<p><label class="classic">'.__('String:').'</label> '.
        form::field(array('ptrans_keyword[]'),20,128,html::escapeHTML($k));
    foreach ($active_languages_array as $la) {
        $lana=$existent_languages_array[$la];
        echo ' <label class="classic">'.$lana.'</label> ';
        if (isset($v[$la])) {
            $word=$v[$la];
        } else {
            $word='';
        }
        echo form::field(array('ptrans_trans_'.$la.'[]'),20,128,
                         html::escapeHTML($word));
    }
    echo '</p>';
}

$table=$core->prefix.'word';
if (isset($_GET['page'])) {
    $page=$_GET['page'];
 } else {
    $page=0;
 }
$TRANSLATION['']['en']='';
if (isset($_GET['withtags'])) {
    $withtags=$_GET['withtags'];
 } else {
    $withtags=0;
 }
if (isset($_GET['withcat'])) {
    $withcat=$_GET['withcat'];
 } else {
    $withcat=0;
 }
$pgc_url=$p_url.'&page='.$page.'&withcat='.$withcat;
$pgt_url=$p_url.'&page='.$page.'&withtags='.$withtags;
$pct_url=$p_url.'&withcat='.$withcat.'&withtags='.$withtags;
$pgct_url=$p_url.'&page='.$page.'&withcat='.$withcat.'&withtags='.$withtags;

try {
    if ($core->blog->settings->lang) {
        $core->blog->settings->addNameSpace('system');
        $core->blog->settings->system->put('lang','');
    }
    // Create settings if they don't exist
    $core->blog->settings->addNameSpace('ptrans');
    if ($core->blog->settings->ptrans->ptrans_fallback_language === null) {
        $core->blog->settings->ptrans->put('ptrans_active_languages','en,fr','string','Navigation language',true,true);
        $core->blog->settings->ptrans->put('ptrans_fallback_language','en','string','Fallback language',true,true);
        http::redirect($p_url);
    }
    $active_languages = $core->blog->settings->ptrans->ptrans_active_languages;
    $fallback_language = $core->blog->settings->ptrans->ptrans_fallback_language;
    if (!$active_languages) {
        // FIXME It should probably be join(',',$existent_languages_array)
        $active_languages = 'en,fr';
        $active_languages_array = array();
    }
    if (!$fallback_language) {
        $fallback_language = 'en';
    }
    $active_languages_array = explode(',',$active_languages);
    if (isset($_POST['ptrans_active_languages'])) {
        $ptrans_active_languages = $_POST['ptrans_active_languages'];
        $ptrans_fallback_language = $_POST['ptrans_fallback_language'];
        if (!preg_match('/^[a-z][-A-Za-z,]*$/',$ptrans_active_languages)) {
            $ptrans_active_languages=$active_languages;
        }
        if ($ptrans_fallback_language != 'en' && $ptrans_fallback_language != 'fr') {
            $ptrans_fallback_language=$fallback_language;
        }
        $test=explode(',',$ptrans_active_languages);
        $valid_languages=array();
        $valid_languages[$ptrans_fallback_language]=$ptrans_fallback_language;
        foreach ($test as $i) {
            $j=substr($i,0,2);
            if (in_array($j,array_keys($existent_languages_array))) {
                $valid_languages[$j]=$j;
            }
        }
        $ptrans_active_languages=implode(',',array_keys($valid_languages));
        $core->blog->settings->addNameSpace('ptrans');
        $core->blog->settings->ptrans->put('ptrans_active_languages',$ptrans_active_languages);
        $core->blog->settings->ptrans->put('ptrans_fallback_language',$ptrans_fallback_language);
        http::redirect($pgct_url.'&up=1&tab=settings');
    }
    if (isset($_POST['ptrans_keyword'])) {
        $cur = $core->con->openCursor($table);
        $core->con->begin();
        $keywords = is_array($_POST['ptrans_keyword']) ?
            $_POST['ptrans_keyword'] :
            array();
        $wordlist=array();
        foreach($keywords as $v) {
            $core->con->select("DELETE FROM $table WHERE blog_id = '".$core->con->escape($core->blog->id).'\' AND w_word = \''.$core->con->escape($v).'\'');
        }
        $blog_id=$core->con->escape($core->blog->id);
        foreach ($existent_languages_array as $la => $lana) {
            $translation = is_array($_POST['ptrans_trans_'.$la]) ?
                $_POST['ptrans_trans_'.$la] :
                array();
            $lla=$core->con->escape($la);
            foreach ($translation as $i => $v) {
                if ($v!='' && $v != $keywords[$i]) {
                    $cur->clean();
                    $cur->blog_id = $blog_id;
                    $cur->lang = $lla;
                    $cur->w_word = $keywords[$i];
                    $cur->w_result = $v;
                    $cur->insert();
                    $wordlist[]=$keywords[$i];
                }
            }
        }

        $core->con->commit();
        http::redirect($pgct_url.'&up=2&wordlist='.html::escapeURL(implode(',',array_unique($wordlist))));
    }
}
catch (Exception $e)
{
    $core->error->add($e->getMessage());
}
if (!empty($_GET['action'])) {
    dcTranslation::indexAllPosts();
    http::redirect($pgct_url.'&up=3&tab=settings');
 }
if (1 == 1) {
    echo '<html><head><title>'.__('Translations').'</title>';
    echo dcPage::jsPageTabs($default_tab);
    echo '</head><body>';
    echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.
        __('Translations').'</h2>';
    $msg='';
    if (!empty($_GET['up'])) {
        if ($_GET['up']==1) 
            $msg= __('Settings have been successfully updated.');
        elseif ($_GET['up']==2) {
            if ($_GET['wordlist'])
                $msg= sprintf(__('Dictionary has been updated for %s.'),$_GET['wordlist']);
            else
                $msg=__('No words in the dictionary (on this page).');
        } elseif ($_GET['up']==3) 
            $msg=__('All posts have been successfully reindexed.');
    }
    if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}
    $fallback_combo=array();
    $_langs=explode(',',$active_languages);
    foreach ($_langs as $v) {
        $fallback_combo[$existent_languages_array[$v]]=$v;
    }
    echo '<div class="multi-part" id="settings" title="'.
        __('Parameters').'">';
    echo '<h3>'.__('Parameters').'</h3>';
    echo '<form action="'.$pgct_url.'" method="post">'.
        '<p><label class="classic">'.__('Navigation language').' '.
        form::field(array('ptrans_active_languages'),20,128,
                    html::escapeHTML($active_languages)).
        '</label></p>'.
        '<p><label class="classic">'.__('Fallback language').' '.
        form::combo('ptrans_fallback_language',$fallback_combo,
                    $fallback_language,'',3).
        '</label></p>';
    echo '<p><input type="submit" value="'.__('save').'" />'.
        $core->formNonce().'</p>'.
        '</form>';
    echo '<h3>'.__('Maintenance').'</h3>';
    echo '<form action="plugin.php" method="get">'.
        '<p><input type="submit" name="indexposts" value="'.
        __('Index all posts').'" /> '.
        form::hidden(array('action'),'index').
        form::hidden(array('p'),'dctranslations').'</p>'.
        '</form>';
    echo '</div>';
    $TRANSLATION=array();
    $TRANSLATION['']['en']='';
    $blog_id=$core->con->escape($core->blog->id);
    $strReq='SELECT * FROM '.$table.' WHERE blog_id = \''.
        $blog_id.'\'';
    $rs=$core->con->select($strReq);
    if ($rs) {
        while($rs->fetch()) {
            $TRANSLATION[$rs->w_word][$rs->lang]=$rs->w_result;
        }
        unset($rs);
    }
    if ($withtags) {
        $metars=$core->con->select('SELECT DISTINCT meta_id FROM '.
                                   $core->prefix.'meta M, '.
                                   $core->prefix.'post P WHERE'.
                                   ' M.meta_type = \'tag\' AND'.
                                   ' P.post_id = M.post_id AND P.blog_id =\''.
                                   $blog_id.'\' ORDER BY M.meta_id'
                                   );
        if ($metars) {
            while ($metars->fetch()) {
                $mid=$metars->meta_id;
                if (!isset($TRANSLATION[$mid]['en'])) {
                    $TRANSLATION[$mid]['en']='';
                }
            }
            unset($metars);
        }
    }
    if ($withcat) {
        $metars=$core->con->select('SELECT DISTINCT cat_title FROM '.
                                   $core->prefix.'category C, '.
                                   $core->prefix.'post P WHERE'.
                                   ' P.cat_id = C.cat_id AND P.blog_id =\''.
                                   $blog_id.'\' ORDER BY C.cat_title'
                                   );
        if ($metars) {
            while ($metars->fetch()) {
                $mid=$metars->cat_title;
                if (!isset($TRANSLATION[$mid]['en'])) {
                    $TRANSLATION[$mid]['en']='';
                }
            }
            unset($metars);
        }
    }
    $i=0;
    ksort($TRANSLATION);
    $asteps=array();
    $bsteps=array();
    $wlist=array();
    $ataglance=array();
    $i=0;$p=0;
    foreach ($TRANSLATION as $k => $v) {
        if (!$i) {
            $asteps[]=$k;
        }
        if ($p == $page) {
            $wlist[]=$k;
        }
        if ($i==15) {
            $bsteps[]=$k;
            $i=0;
            $p++;
        } else {
            $i++;
        }
        if (count($v)==1 && isset($v['en']) && $v['en'] == '' && $k != '') $ataglance[]=$k;
    }
    if ($i) {
        $bsteps[]=$k;
    }
    echo '<div class="multi-part" id="list" title="'.
        __('Quick dictionary').'">';
    echo '<form action="'.$pgct_url.'" method="post">';
    echo '<p>'.__('Quick dictionary:');
    if ($withtags) {
        echo '&nbsp;<a href="'.$pgc_url.'">'.__('Without tags').'</a>&nbsp;';
    } else {
        echo '&nbsp;<a href="'.$pgc_url.'&withtags=1">'.
            __('With tags').'</a>&nbsp;';
    }
    if ($withcat) {
        echo '&nbsp;<a href="'.$pgt_url.'">'.
            __('Without categories').'</a>&nbsp;';
    } else {
        echo '&nbsp;<a href="'.$pgt_url.'&withcat=1">'.
            __('With categories').'</a>&nbsp;';
    }
    foreach ($asteps as $k => $v) {
        echo ($k?'|':'').'<a href="'.$pct_url.'&page='.$k.'" title="'.$v.
            '---'.$bsteps[$k].'">'.($k+1).'</a>';
    }
    echo '</p>';
    foreach ($wlist as $x => $k) {
        $v=$TRANSLATION[$k];
        transhelper($k,$v,$active_languages_array,$existent_languages_array);
    }
    echo '<p><input type="submit" value="'.__('save').'" />'.
        $core->formNonce().'</p>'.
        '</form>';
    echo '<p>'.__('Not translated yet:').' '.join(', ',$ataglance).'</p>';
    echo '</div>';
    echo '</body></html>';
 }
?>