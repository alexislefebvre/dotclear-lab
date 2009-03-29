<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Private mode, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2009 Osku and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('privateWidgets','initWidgets'));

class privateWidgets 
{
        public static function initWidgets(&$widgets)
        {
                $widgets->create('privateblog',__('Blog logout'),array('tplPrivate','privateWidgets'));
                $widgets->privateblog->setting('title',__('Title:'),__('Blog logout'));
                $widgets->privateblog->setting('text',__('Text:'),'','textarea');
                $widgets->privateblog->setting('label',__('Button:'),__('Disconnect'));
                $widgets->privateblog->setting('homeonly',__('Home page only'),0,'check');
        }
}
?>
