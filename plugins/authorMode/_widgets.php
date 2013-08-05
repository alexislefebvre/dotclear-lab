<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of authorMode, a plugin for DotClear2.
#
# Copyright (c) 2003 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('widgetsAuthorMode','init'));

class widgetsAuthorMode
{
	public static function authors($w)
	{
		global $core;
		
		if (!$core->blog->settings->authormode->authormode_active) return;

		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default')) {
			return;
		}
		
		$rs = authormodeUtils::getPostsUsers();
		if ($rs->isEmpty()) {
			return;
		}
		
		switch ($core->url->type)
		{
			case 'post' :
				$currentuser = $GLOBALS['_ctx']->posts->user_id;
				break;
			case 'author' :
				$currentuser = $GLOBALS['_ctx']->users->user_id;
				break;
			default :
				$currentuser = '';
		}
		
		$res = ($w->content_only ? '' : '<div class="authors'.($w->class ? ' '.html::escapeHTML($w->class) : '').'">').
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';

		$res .=
		'<li class="listauthors"><strong><a href="'.$core->blog->url.$core->url->getBase("authors").'">'.
		__('List of authors').'</a></strong></li>';
		
		while ($rs->fetch()) {
			$res .= '<li'.
			($rs->user_id == $currentuser ? ' class="current-author"' : '').
			'><a href="'.$core->blog->url.$core->url->getBase('author').'/'.
			$rs->user_id.'">'.
			html::escapeHTML(
				dcUtils::getUserCN($rs->user_id, $rs->user_name,
					$rs->user_firstname, $rs->user_displayname)).'</a>'.
			($w->postcount ? ' ('.$rs->nb_post.')' : '').
			'</li>';
		}
		$res .= '</ul>'.
		($w->content_only ? '' : '</div>');
		
		return $res;
	}
	
	public static function init($w)
	{
	  $w->create('authors',__('Authors'),array('widgetsAuthorMode','authors'));
	  $w->authors->setting('title',__('Title:'),__('Authors'));
	  //$w->authors->setting('archiveonly',__('Archives only'),1,'check');
	  $w->authors->setting('postcount',__('With entries counts'),0,'check');
		$w->authors->setting('homeonly',__('Display on:'),0,'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
				)
		);
    $w->authors->setting('content_only',__('Content only'),0,'check');
    $w->authors->setting('class',__('CSS class:'),'');
	}
}
?>
