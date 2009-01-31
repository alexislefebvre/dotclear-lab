<?php 
  // ***** BEGIN LICENSE BLOCK *****
  // This file is (c) Jean-Christophe Dubacq.
  // Licensed under CC-BY licence.
  //
  // ***** END LICENSE BLOCK *****

$core->addBehavior('initStacker',array('tplStacker','initStacker'));

// load main class
require_once(dirname(__FILE__).'/class.stacker.php');
$core->stacker=new dcStacker($GLOBALS['core']);
$core->callBehavior('initStacker',$core);
if ($core->blog->settings->stacker_disabled) {
    $disabled=explode(',',$core->blog->settings->stacker_disabled);
    foreach ($disabled as $entry) {
        $core->stacker->disable($entry);
    }
 }
uasort($core->stacker->stack,array("dcStacker", "sort"));
$core->stacker->sorted=true;

class tplStacker
{
    static $frenchtypo=array(
                             '/ :/',
                             '/ ;/',
                             '/ !/',
                             '/ \?/',
                             '/« /',
                             '/ »/',
                             '/\'/',
                             );
    static $frenchtypox=array(
                              '&nbsp;:',
                              '&thinsp;;',
                              '&thinsp;!',
                              '&thinsp;?',
                              '«&nbsp;',
                              '&nbsp;»',
                              '&rsquo;',
                              );
    public static function initStacker(&$core) {
        $core->stacker->addFilter('TestStackerFilter',
                                  'tplStacker',  // Class
                                  'SwedishA',    // Function
                                  'textonly',    // Context
                                  100,           // Priority
                                  'stacker',     // Origin
                                  __('Test replacing Dotclear with Dotcleår'),
                                  '/Dotclear/'   // Trigger
                                  );
        $core->stacker->addFilter('FrenchTypography',
                                  'tplStacker',         // Class
                                  'FrenchTypography',   // Function
                                  'textonly',           // Type
                                  100,                  // Priority
                                  'stacker',            // Origin
                                  __('Changes spacing for French punctuation'),
                                  '/[:«»!?;\']/');
    }
    public static function SwedishA(&$rs,$text,$stack,$elements) {
        return (preg_replace('/Dotclear/', 'Dotcleår',$text));
    }
    public static function FrenchTypography(&$rs,$text,$stack,$elements) {
        if ((isset($elements['pre']) && $elements['pre']>0) ||
            (isset($elements['code']) && $elements['code']>0)) {
            return $text;
        }
        $_ctx =& $GLOBALS['_ctx'];
        $core=$rs->core;
        if ($core->plugins->moduleExists('dctranslations') && $_ctx->posts) {
            $lang=$_ctx->posts->getLang();
        } elseif ($_ctx->posts && $_ctx->posts->post_lang) {
            $lang=$_ctx->posts->post_lang;
        } else {
            // unknown context
            $lang=$core->blog->settings->lang;
        }
        if ($lang != 'fr') {
            return $text;
        }
        $newcontent=preg_replace(tplStacker::$frenchtypo,tplStacker::$frenchtypox,$text);
        return $newcontent;
    }
}


?>