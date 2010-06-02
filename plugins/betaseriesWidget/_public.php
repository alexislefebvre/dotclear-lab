<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of betaseriesWidget, a plugin for Dotclear.
# 
# Copyright (c) 2010 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }
 
require dirname(__FILE__).'/_widgets.php';

class publicBetaSeriesWidget
{

    public static function getContent(&$w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		$limit = abs((integer) $w->limit);

		try {
			$feed_url = "http://www.betaseries.com/rss/timeline/".$w->userName;
			$feed = feedReader::quickParse($feed_url,DC_TPL_CACHE);
			if ($feed == false || count($feed->items) == 0) {
				return;
			}
		} catch (Exception $e) {
			return;
		}

		$res =
		'<div class="betaseries">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';

		$img_base_url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__)).'/img/';
		
		$i = 0;
		foreach ($feed->items as $item) {
			$content = preg_replace ("/\/episode\//", "http://www.betaseries.com/episode/", $item->content);
			$type = "";
			$exploded_content = explode (" ", $content);
			if (in_array("ajout&eacute;", $exploded_content)) $type = "add";
			else if (in_array("regarder", $exploded_content))	$type = "watch";
			else $type = "archive";

			$li = '<img src="'.$img_base_url.$type.'.png" alt="'.$type.'" style="margin:0 5px -5px 0" />'.$content;

			$res .= ' <li>'.$li.'</li> ';
			$i++;
			if ($i >= $limit) {
				break;
			}
		}			
		$res .= '</ul></div>';
			
		return $res;
	}

}
 
?>
