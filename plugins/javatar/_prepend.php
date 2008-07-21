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
if (!defined('DC_RC_PATH')) { return; }

$core->url->register('post',
    $core->url->getBase('post'),
    sprintf('^%s/(.+)$',$core->url->getBase('post')),
    array('JabberHandler','post'));
$GLOBALS['__autoload']['rsExtCommentJavatar'] = dirname(__FILE__).'/rs.extensions.php';
$GLOBALS['__autoload']['publicJavatar'] = dirname(__FILE__).'/class.public.javatar.php';
$GLOBALS['__autoload']['tplJavatar'] = dirname(__FILE__).'/class.tpl.javatar.php';
?>
