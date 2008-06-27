<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'HTTP Redirect', a plugin for Dotclear 2           *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'HTTP Redirect' (see COPYING.txt);      *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$core->url->register('post',
	$core->url->getBase('post'),
	sprintf('^%s/(.+)$',$core->url->getBase('post')),
	array('httpRedirect','post'));

class httpRedirect
{
	public static function post($post_url)
	{
		global $core;
		
		if (!self::isInstalled()) {
			dcUrlHandlers::post($post_url);
			return;
		}
		
		$strReq =
		'SELECT post_id, redirect_url '.
		'FROM '.$core->prefix.'post '.
		"WHERE blog_id = '".$core->con->escape($core->blog->id)."' ".
		"AND post_url = '".$core->con->escape($post_url)."' ".
		"AND post_status IN (0,1) ".
		'LIMIT 1';
		
		$rs = $core->con->select($strReq);
		$redirect_url = $rs->redirect_url;
		
		# Nothing to do, normal post
		if (empty($redirect_url)) {
			dcUrlHandlers::post($post_url);
			return;
		}
		
		# Redirect to the new location
		http::head(301,'Moved Permanently');
		http::redirect($redirect_url);
		exit;
	}
	
	public static function isInstalled()
	{
		global $core;
		
		$label = 'httpredirect';
		$m_version = $core->plugins->moduleInfo($label,'version');
		$i_version = $core->getVersion($label);
		
		if (version_compare($i_version,$m_version,'=')) {
			return true;
		}
		return false;
	}
}
?>