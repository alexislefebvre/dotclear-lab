<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Lite Box', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Lite Box' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$label = 'litebox';
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

# OK, nothing to do
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--

$truncate_cache = false;
# Upgrading
if ($i_version == '0.2') {
	$truncate_cache = true;
}
elseif ($i_version !== null) {
	# Already installed, nothing to do
}
# Installing
else {
	$truncate_cache = true;
}

# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);

if ($truncate_cache && !files::deltree(DC_TPL_CACHE.DIRECTORY_SEPARATOR.'cbtpl')) {
	throw new Exception(__('To finish installation, please delete the whole cache/cbtpl directory.'));
}

unset($label,$i_version,$m_version,$truncate_cache);
return true;
?>
