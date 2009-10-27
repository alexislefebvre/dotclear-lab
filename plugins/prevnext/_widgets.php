<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of prevnext, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jean-Christophe Dubacq
# jcdubacq1@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('prevnextWidgets','initWidgets'));
$core->addBehavior('initDefaultWidgets',array('prevnextWidgets','initDefaultWidgets'));

class prevnextWidgets
{
    public static function initWidgets($w)
    {
        $w->create('prevnext',__('Contextual navigation'),
                   array('dcPrevNext','contextualNavigation'));
        $w->prevnext->setting('title',__('Title:'),__('Contextual navigation'));
        $w->prevnext->setting('prevsign',__('"Previous" symbol:'),'«');
        $w->prevnext->setting('nextsign',__('"Next" symbol:'),'»');
    }
  
    public static function initDefaultWidgets($w,&$d)
    {
        $d['nav']->append($w->prevnext);
    }
}
?>