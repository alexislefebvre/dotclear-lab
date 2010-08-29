<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of browsingHistory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('widgetBrowsingHistory','adminBrowsingHistoryList'));

class widgetBrowsingHistory
{
	public static function adminBrowsingHistoryList($w)
	{
		global $core;
		
		$w->create('browsinghistorylist',
			__('Browsing history'),array('widgetBrowsingHistory','publicBrowsingHistoryList')
		);
		$w->browsinghistorylist->setting('title',
			__('Title:'),__('Recently viewed items'),'text'
		);
		$w->browsinghistorylist->setting('limit',
			__('Limit:'),'5','text'
		);
		$w->browsinghistorylist->setting('str',
			__('Text:'),'%link% (%type%)','text'
		);
		
		$bh = new browsingHistory($core);
		$types = $bh->getTypes();
		
		foreach($types as $type => $v)
		{
			$title = $bh->getTitle($type);
			$w->browsinghistorylist->setting($type,$title,1,'check');
		}
	}
	
	public static function publicBrowsingHistoryList($w)
	{
		global $core;
		
		$bh = new browsingHistory($core);
		$types = $bh->getTypes();

		$params = array();
		foreach($types as $type => $v)
		{
			if ($w->{$type}) $params['type'][] = $type;
		}
		$params['limit'] = abs((integer) $w->limit);
		if (!$params['limit']) $params['limit'] = 5;
		
		$rs = $bh->getHistoryRecords($params);
		
		if ($rs->isEmpty()) return;
		
		$li = '<li class="browsinghistory-%s">%s</li>';
		$str = $w->str ? $w->str : '%titlelink%';
				
		$res = '';
		while($rs->fetch())
		{
			
			$src = $rpl = array();
			if (preg_match_all('#\%([a-zA-Z]+)\%#',$str,$matches))
			{
				foreach($matches[1] as $m => $replace)
				{
					$src[] = '%'.$replace.'%';
					$rpl[] = $rs->exists($replace) ? $rs->{$replace} : '';
				}
			}
			
			$res .= sprintf($li,$rs->type,str_replace($src,$rpl,$str));
		}
		
		if (empty($res)) return;
		
		return
		'<div class="browsinghistory">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>'.$res.'</ul>'.
		'</div>';
	}
}
?>