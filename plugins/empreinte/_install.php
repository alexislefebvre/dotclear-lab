<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Empreinte', a plugin for Dotclear 2               *
 *                                                             *
 *  Copyright (c) 2007,2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Empreinte' (see COPYING.txt);          *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$label = 'empreinte';
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--

$s = new dbStruct($core->con,$core->prefix);
$sets = &$core->blog->settings;
$sets->setNamespace(strtolower($label));

# Update
if (version_compare($i_version,'0.1a','=')) {
	$sets->put('empreinte_authorlink_mask','%1$s',
		'string','AuthorLink mask');
}
# New install
elseif ($i_version === null) {
	$sets->put('empreinte_authorlink_mask','%1$s',
		'string','AuthorLink mask');
	$s->comment
		->comment_browser('varchar',	65,true,null)
		->comment_system('varchar',	65,true,null)
		;
}

# --SCHEMA SYNC--

$si = new dbStruct($core->con,$core->prefix);
$si->synchronize($s);

# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);

if ($truncate_cache && !files::deltree(DC_TPL_CACHE.DIRECTORY_SEPARATOR.'cbtpl')) {
	throw new Exception(__('To finish installation, please delete the whole cache/cbtpl directory.'));
}

unset($label,$i_version,$m_version,$s,$si);
return true;
?>
