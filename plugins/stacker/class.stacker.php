<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of stacker, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jean-Christophe Dubacq
# jcdubacq1@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) {return;}

class stackerBehaviors
{
    public static function coreBlogGetPosts($rs)
    {
        $rs->extend('rsExtPostStacker');
    }
    public static function displayPluginsPanel($core)
    {
        $stacker=$core->stacker;
        if (!$stacker->sorted) {
            uasort($stacker->stack,array("dcStacker", "sort"));
            $stacker->sorted=true;
        }
        echo '<div class="multi-part" id="stacker" title="Stacker">';
        if (!empty($_POST['change_stacker'])) {
            if (isset($_POST['stack'])) {
                $newdis=implode(',',array_keys($_POST['stack']));
            } else {
                $newdis='';
            }
            echo '<p>'.__('Disabled filters:').' '.$newdis.'</p>';
            $core->blog->settings->setNameSpace('stacker');
            $core->blog->settings->put('stacker_disabled',$newdis);
            $disabled=explode(',',$newdis);
            foreach ($stacker->stack as $key => $value) {
                $stacker->disable($key,true);
            }
            foreach ($stacker->stack as $key => $value) {
            }
            foreach ($disabled as $entry) {
                if ($entry)
                    $stacker->disable($entry);
            }
            echo
                '<p class="message">'.
                __('Settings have been successfully updated.').
                '</p>';
        }
        echo '<form action="plugins.php" method="post">';
        echo '<table class="clear"><tr>'.
            '<th colspan="2">'.__('Name').'</th>'.
            '<th class="nowrap">'.__('Origin').'</th>'.
            '<th class="nowrap">'.__('Priority').'</th>'.
            '<th class="nowrap">'.__('Context').'</th>'.
            '<th class="nowrap">'.__('Description').'</th>'.
            '<th>'.__('Function').'</th>'.
            '</tr>';
        foreach ($stacker->stack as $key => $value) {
            $function=$value[0].'::'.$value[1];
            $context=join(',',array_keys($value[2]));
            $priority=$value[3];
            $origin=$value[4];
            $desc=$value[5];
            echo '<tr'.((!$value[7])?
                        ' style="background: #C0C0C0; font-style: italic;"':
                        '').'><td>'.
                form::checkbox(array('stack['.html::escapeHTML($key).']'),1,
                               !$value[7]).
                '</td><td>'.$key.'</td><td>'.
                $origin.'</td><td>'.
                $priority.'</td><td>'.
                $context.'</td><td>'.
                $desc.'</td><td>'.
                $function.'</td></tr>';
        }
        echo '</table>';
        if (!isset($core->blog->settings->stacker_disabled)) {
            $setting='';
        } else {
            $setting=$core->blog->settings->stacker_disabled;
        }
        echo '<p>'.__('Warning: disable with care only, it could disturb the normal functions of plugins using stacker.').'</p>';
        echo '<p>'.$core->formNonce().'</p>'.
            form::hidden(array('tab'),'stacker').
            '<input type="submit" name="change_stacker" value="'.__('change').
            '" />'.
            '</form>';
        echo '</div>';
    } 
}

class rsExtPostStacker
{
    public static function getExcerpt($rs,$absolute_urls=false)
    {
        $rep = rsExtPost::getExcerpt($rs,$absolute_urls);
        $newrep = dcStacker::treatChunks($rs,$rep,$absolute_urls,'excerpt');
        return $newrep;
    }

    public static function getContent($rs,$absolute_urls=false)
    {
        $rep = rsExtPost::getContent($rs,$absolute_urls);
        $newrep = dcStacker::treatChunks($rs,$rep,$absolute_urls,'content');
        return $newrep;
    }
}

class dcStacker
{
    public $stack;
    public $sorted;
  
    public function __construct($core)
    {
        $this->core =& $core;
        $this->con =& $this->core->con;
        $this->stack = array();
        $this->sorted = false;
    }

    public function addFilter($n,$class,$fun,$context,$prio,$orig,$desc,
                              $trigg=null)
    {
        $carray=array();
        $carray[$context]=true;
        $this->stack[$n]=array($class,$fun,$carray,$prio,
                               $orig,$desc,$trigg,true);
    }

    public function disable($disabled,$value=false)
    {
        if (is_array($this->stack[$disabled])) {
            $this->stack[$disabled][7] = $value;
        }
    }

    public static function sort($a,$b) {
        if ($a[3] == $b[3]) {
            return 0;
        }
        return ($a[3] < $b[3]) ? -1 : 1;
    }

    public static function treatChunks($rs,$rep,$absolute_urls,$contextname)
    {
        $stack=array();
        $elements=array();
        $stacker=$rs->core->stacker;
        if (!$stacker->sorted) {
            uasort($stacker->stack,array("dcStacker", "sort"));
            $stacker->sorted=true;
        }
        foreach ($stacker->stack as $value) {
            $enabled=$value[7];
            if (!$enabled) {
                continue;
            }
            $context=$value[2];
            $class=$value[0];
            $function=$value[1];
            $func=array($class,$function);
            if (isset($context['any'])||isset($context[$contextname])) {
                $rep=call_user_func_array($func,
                                          array($rs,$rep,$absolute_urls));
            }
            if (isset($context['textonly'])) {
                if (!preg_match($value[6],$rep,$matches)) {
                    continue;
                }
                $newrep="";
                while($rep) {
                    if (preg_match('/^(<.*?>)(.*)$/s',$rep,$matches)) {
                        $markup=$matches[1];
                        $newrep.=$markup;
                        $rep=$matches[2];
                        // Stack management
                        // We either close a tag, open a tag, put a
                        // self-closed tag.
                        // Case 1: close, Case 2: open (case 3: do nothing)
                        if (substr($markup,1,1)=='/') {
                            // implicitely close preceding tag, we are not a
                            // browser and need not to be strict
                            $exmarkup=array_pop($stack);
                            $elements[$exmarkup]--;
                        } elseif (substr($matches[1],-2)!='/>') {
                            preg_match('/^<([^ ]*)(.*)>$/s',$markup,$matches);
                            $stack[]=$matches[1];
                            if (isset($elements[$matches[1]])) {
                                $elements[$matches[1]]++;
                            } else {
                                $elements[$matches[1]]=1;
                            }
                        }
                    } elseif (preg_match('/^(.*?)(<.*)$/s',$rep,$matches)) {
                        $newrep.=call_user_func_array($func,array($rs,
                                                                  $matches[1],
                                                                  $stack,
                                                                  $elements));
                        $rep=$matches[2];
                    } else {
                        $newrep.=call_user_func_array($func,array($rs,
                                                                  $rep,
                                                                  $stack,
                                                                  $elements));
                        $rep='';
                    }
                }
                $rep=$newrep;
            }
        }
        return $rep;
    }
}
?>
