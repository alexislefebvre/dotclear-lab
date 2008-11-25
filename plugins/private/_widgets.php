<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Private', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Private blog' (see LICENSE);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('privateWidgets','initWidgets'));

class privateWidgets 
{
        public static function initWidgets(&$widgets)
        {
                $widgets->create('privateblog',__('Blog logout'),array('tplPrivate','privateWidgets'));
                $widgets->privateblog->setting('title',__('Title:'),'');
                $widgets->privateblog->setting('homeonly',__('Home page only'),0,'check');
        }
}
?>