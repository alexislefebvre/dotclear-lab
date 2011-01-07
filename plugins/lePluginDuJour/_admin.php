<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lePluginDuJour, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
 if (!defined('DC_CONTEXT_ADMIN')) { return; }

$__autoload['lePluginDuJour'] = dirname(__FILE__).'/inc/class.leplugindujour.php';

function lePluginDuJourDashboard($core,$icons)
{
	if ($core->auth->isSuperAdmin()) {

		$leplugindujour_day = $core->blog->settings->leplugindujour->leplugindujour_day;
		$leplugindujour_plugin = $core->blog->settings->leplugindujour->leplugindujour_plugin;
		$lePluginDuJour = new lePluginDuJour($core);
		$lePluginDuJour->check();

		$avail_plugins = $lePluginDuJour->getModules('plugins');
		if( $leplugindujour_day != date("j, n, Y") ) {
			$leplugindujour_day = date("j, n, Y");
			$leplugindujour_plugin = array_rand($avail_plugins);
		}
		$avail_plugin = $avail_plugins[$leplugindujour_plugin];
		
		$txt_plugin = 
			'<div class="message" style="background:url(http://media.dotaddict.org/pda/dc2/'.html::escapeHTML($avail_plugin['id']).'/icon.png) 8px 6px no-repeat;">'.
			'<h3 style="color:#cccccc;">'.html::escapeHTML($avail_plugin['label']).'</h3>'.
			'<p><em>'.html::escapeHTML($avail_plugin['desc']).'</em></p>'.
			'<p>'.__('by').' '.html::escapeHTML($avail_plugin['author']).'<br />'.
			'( <a href="'.$avail_plugin['details'].'" class="learnmore modal">'.__('More details').'</a> )</p></div>';
		

		$doc_links = $icons->offsetGet(0);
		$news = $icons->offsetGet(1);
		$icons->offsetSet(0, array($txt_plugin));
		$icons->offsetSet(1, $doc_links);
		$icons->offsetSet(2, $news);
		
		$core->blog->settings->leplugindujour->put('leplugindujour_day', $leplugindujour_day);
		$core->blog->settings->leplugindujour->put('leplugindujour_plugin', $leplugindujour_plugin);
	}
}

$core->addBehavior('adminDashboardItems','lePluginDuJourDashboard');