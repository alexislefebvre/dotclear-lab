<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Javatar', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Javatar' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(
        __('Javatars'),'plugin.php?p=javatar',
        'index.php?pf=javatar/icon.png',
        preg_match('/plugin.php\?p=javatar(&.*)?$/',$_SERVER['REQUEST_URI']),
        $core->auth->check('usage,contentadmin',$core->blog->id)
);
?>