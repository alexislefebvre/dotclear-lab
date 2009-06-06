<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shortArchives, a plugin for Dotclear.
# 
# Copyright (c) 2009 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

require dirname(__FILE__).'/_widgets.php';

$core->addBehavior('publicHeadContent',array('publicShortArchives','publicHeadContent'));

class publicShortArchives
{

	public static function publicHeadContent(&$core)
	{
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo '<script type="text/javascript" src="'.$url.'/js/accordion.js"></script>'."\n";
	}
}

class tplShortArchives
{
	public static function shortArchivesWidgets(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
        $params = array();
        $params['type'] = 'month';
        $rs = $core->blog->getDates($params); 
        unset($params);
        if ($rs->isEmpty()) {
            return;
        }

		$posts = array();
        while ($rs->fetch()) {
			$posts[dt::dt2str(__('%Y'),$rs->dt)][] = array('url' => $rs->url($core), 
					  'date' => html::escapeHTML(dt::dt2str(__('%B'),$rs->dt)), 
					  'nbpost' => $rs->nb_post);
        }
		        
        $res =
            '<div class="shortArchives">'.
            ($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
            '<ul>';
		foreach($posts as $annee=>$post) {
			$res .= '<li><a class="archives-year" href="#">'.$annee.'</a><ul>';
			for($i=0; $i<sizeof($post); $i++) {
				$res .=
					'<li><a href="'.$post[$i]['url'].'">'.$post[$i]['date'].'</a>'.
					($w->postcount ? ' ('.$post[$i]['nbpost'].')' : '').
					'</li>';
			}
			$res .= '</ul></li>';
		}
        $res .= '</ul></div>';
		
        return $res;
	}
}
?>
