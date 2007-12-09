<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Live Counter', a plugin for Dotclear 2            *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Live Counter' (see COPYING.txt);       *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$label = 'livecounter';

# Module version
$m_version = $core->plugins->moduleInfo($label,'version');

# Installed version
$i_version = $core->getVersion($label);

# OK, nothing to do
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--
$sets = &$core->blog->settings;
$sets->setnamespace($label);

# Upgrading
if ($i_version == '0.1') {
	$sets->put('lc_cache_dir',DC_TPL_CACHE.'/livecounter',
		'string','Live Counter cache dir');
	$sets->put('lc_no_browser_cache',false,
		'boolean','Newer use client browser cache');
}
elseif ($i_version == '0.2' || $i_version == '0.3') {
	$sets->put('lc_timeout',5,
		'integer','Single connection timeout');
	if (!files::deltree(DC_TPL_CACHE.DIRECTORY_SEPARATOR.'cbtpl')) {
		throw new Exception(__('To finish installation, please delete the whole cache/cbtpl directory.'));
	}
}
elseif ($i_version !== null) {
	# Already installed, nothing to do
}
# Installing
else {
	$sets->put('lc_cache_dir',DC_TPL_CACHE.'/livecounter',
		'string','Live Counter cache dir');
	$sets->put('lc_no_browser_cache',false,
		'boolean','Newer use client browser cache');
	$sets->put('lc_timeout',5,
		'integer','Single connection timeout');

	# Create data directory
	try {files::makeDir(DC_TPL_CACHE.'/livecounter');}
	catch (Exception $e)
	{
		throw new Exception(__('Unable to create cache directory. '.
		'Please create a directory named \'livecounter\' in your cache directory.'));
	}
	
	# Deleting cached template files
	if (!files::deltree(DC_TPL_CACHE.DIRECTORY_SEPARATOR.'cbtpl')) {
		throw new Exception(__('To finish installation, please delete the whole cache/cbtpl directory.'));
	}
}

# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);	

unset($label,$i_version,$m_version);
return true;
?>
