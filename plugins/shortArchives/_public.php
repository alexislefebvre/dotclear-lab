<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shortArchives, a plugin for Dotclear.
# 
# Copyright (c) 2009-10 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

require dirname(__FILE__).'/_widgets.php';

$core->addBehavior('publicHeadContent',array('publicShortArchives','publicHeadContent'));

class publicShortArchives
{
	public static function publicHeadContent($core)
	{
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo '<script type="text/javascript" src="'.$url.'/js/accordion.js"></script>'."\n";
		echo '<style type="text/css">'."\n".
				'@import url('.$url.'/css/shortArchives.css);'."\n".
			'</style>'."\n";
	}
}

class tplShortArchives
{
	public static function shortArchivesWidgets($w)
	{
		global $core;

		if ($w->offline)
			return;

		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default')) {
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
		($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').
		'<ul>';
		
		foreach($posts as $annee=>$post) {
			$res .= '<li><span>'.$annee.'</span><ul>';
			for($i=0; $i<sizeof($post); $i++) {
				$res .=
					'<li><a href="'.$post[$i]['url'].'">'.$post[$i]['date'].'</a>'.
					($w->postcount ? ' ('.$post[$i]['nbpost'].')' : '').
					'</li>';
			}
			$res .= '</ul></li>';
		}
        $res .= '</ul>';

		return $w->renderDiv($w->content_only,'shortArchives '.$w->class,'',$res);
	}
	
}