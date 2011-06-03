<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of enhancePostContent, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->addBehavior('initWidgets',array('enhancePostContentWidget','adminContentList'));

class enhancePostContentWidget
{
	public static function adminContentList($w)
	{
		global $core;
		
		$w->create('epclist',__('Enhance post content'),
			array('enhancePostContentWidget','publicContentList'));
		# Title
		$w->epclist->setting('title',__('Title:'),__('In this article'),'text');
		# Text
		$w->epclist->setting('text',__('Description:'),'','text');
		# Type
		$filters = libEPC::blogFilters();
		$types = array();
		foreach($filters as $name => $filter)
		{
			if (!isset($filter['widgetListFilter'])
			 || !is_callable($filter['widgetListFilter'])) continue;

			$types[__($name)] = $name;
		}
		$w->epclist->setting('type',__('Type:'),'Definition','combo',$types);
		# Content
		$contents = libEPC::defaultAllowedWidgetValues();
		foreach($contents as $k => $v)
		{
			$w->epclist->setting('content'.$v['id'],sprintf(__('Enable filter on %s'),__($k)),1,'check');
		}
		# Case sensitive
		$w->epclist->setting('nocase',__('Search case insensitive'),0,'check');
		# Plural
		$w->epclist->setting('plural',__('Search also plural'),0,'check');
		# Show count
		$w->epclist->setting('show_total',__('Show the number of appearance'),1,'check');
	}
	
	public static function publicContentList($w)
	{
		global $core, $_ctx;
		$core->blog->settings->addNamespace('enhancePostContent');
		
		# Page
		if (!$core->blog->settings->enhancePostContent->enhancePostContent_active
		 || !in_array($_ctx->current_tpl,array('post.html','page.html'))
		) return;
		
		# Content
		$content = '';
		$allowedwidgetvalues = libEPC::defaultAllowedWidgetValues();
		foreach($allowedwidgetvalues as $k => $v)
		{
			$ns = 'content'.$v['id'];
			if ($w->$ns && is_callable($v['callback']))
			{
				$content .= call_user_func_array($v['callback'],array($core,$w));
			}
		}
		
		if (empty($content)) return;
		
		# Filter
		$list = array();
		$filters = libEPC::blogFilters();
		
		if (isset($filters[$w->type]) 
		 && isset($filters[$w->type]['widgetListFilter'])
		 && is_callable($filters[$w->type]['widgetListFilter']))
		{
			$filters[$w->type]['nocase'] = $w->nocase;
			$filters[$w->type]['plural'] = $w->plural;
			
			if ($filters[$w->type]['has_list'])
			{
				$records = new epcRecords($core);
				$filters[$w->type]['list'] = $records->getRecords(array('epc_filter'=>$w->type));
			}
			
			call_user_func_array($filters[$w->type]['widgetListFilter'],array($core,$filters[$w->type],$content,$w,&$list));
		}
		
		if (empty($list)) return;
		
		# Parse result
		$res = '';
		foreach($list as $line)
		{
			if (empty($line['matches'][0]['match'])) continue;
			
			$res .= 
			'<li>'.$line['matches'][0]['match'].
			($w->show_total ? ' ('.$line['total'].')' : '').
			'</li>';
		}
		
		if (empty($res)) return;
		
		return 
		'<div class="epc-widgetlist">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		($w->text ? '<p>'.html::escapeHTML($w->text).'</p>' : '').
		'<ul>'.$res.'</ul>'.
		'</div>';
	}
}
?>