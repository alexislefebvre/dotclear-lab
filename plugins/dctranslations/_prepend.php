<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dctranslations, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Franck Paul and contributors
# carnet.franck.paul@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

  // register new urls. Only in prepend?

if (1==1) { // always
    $GLOBALS['core']->url->register('opost','opost','^opost/(.+)$',
                                    array('urlTranslation','opost'));
    $GLOBALS['core']->url->register('tpost','tpost','^tpost/(.+)$',
                                    array('urlTranslation','tpost'));
    $GLOBALS['core']->url->register('lang','lang','^lang/(.+)$',
                                    array('urlTranslation','lang'));
    $GLOBALS['core']->url->register('superfeed','^superfeed/','^superfeed/(.+)$',
                                    array('urlTranslation','superfeed'));
    $GLOBALS['core']->url->register('feed','feed','^feed/(.+)$',
                                    array('urlTranslation','feed'));

    $core->addBehavior('initStacker',
                       array('dctranslationsStacker','initStacker'));

    // autoload main class
    $GLOBALS['__autoload']['dcTranslation'] = dirname(__FILE__).'/class.translations.php';

    require dirname(__FILE__).'/_widgets.php';
 }

class dctranslationsStacker
{
    public static function initStacker($core)
    {
        $core->stacker->addFilter('ExcerptTranslation',
                                  'rsExtPostTranslation', // Class
                                  'TranslateExcerpt',     // Function
                                  'excerpt',              // Context
                                  10,                     // Priority
                                  'dctranslations',       // Origin
                                  __('Translates excerpts')
                                  );
        $core->stacker->addFilter('ContentTranslation',
                                  'rsExtPostTranslation', // Class
                                  'TranslateContent',     // Function
                                  'content',              // Context
                                  10,                     // Priority
                                  'dctranslations',       // Origin
                                  __('Translates contents')
                                  );
    }
}
?>