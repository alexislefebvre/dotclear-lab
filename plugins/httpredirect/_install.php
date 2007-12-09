<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'HTTPRedirect', a plugin for Dotclear 2            *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'HTTPRedirect' (see COPYING.txt);       *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$label = 'httpredirect';

# Module version
$m_version = $core->plugins->moduleInfo($label,'version');

# Installed version
$i_version = $core->getVersion($label);

# OK, nothing to do
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--

$s = new dbStruct($core->con,$core->prefix);

# Upgrading
if ($i_version !== null) {
	# Already installed, nothing to do (just clear 'post_hide' field ?)
}
# Installing
else {
	$s->post->redirect_url('varchar',255,true);
}

# --SCHEMA SYNC--

$si = new dbStruct($core->con,$core->prefix);
$si->synchronize($s); 

# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);

unset($label,$i_version,$m_version);
return true;
?>
