<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Arlequin', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2011                                         *
 *  Alex Pirine and contributors.                              *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Arlequin' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('arlequin','version');
if (version_compare($core->getVersion('arlequin'),$version,'>=')) {
	return;
}

$core->blog->settings->addNamespace('arlequin');
$s = &$core->blog->settings->arlequin;
if ($s->config === null) {
	$s->put('config','','string','Arlequin configuration');
	$s->put('exclude','','string','Excluded themes');
}

$core->setVersion('arlequin',$version);
return true;
?>