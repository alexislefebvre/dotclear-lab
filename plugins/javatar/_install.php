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

$m_version = $core->plugins->moduleInfo('javatar','version');
$i_version = $core->getVersion('javatar');

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--
$s = new dbStruct($core->con,$core->prefix);

# We do NOT erase database contents if a previous version is installed
if ($i_version === null) {
	$s->comment
		->comment_jabber('varchar',	255,true,null)
		;
}

# --SCHEMA SYNC--
$si = new dbStruct($core->con,$core->prefix);
$si->synchronize($s);

# --SETTING NEW VERSION--
$core->setVersion('javatar',$m_version);
return true;
?>