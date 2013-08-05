<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of acronyms, a plugin for DotClear2.
#
# Copyright (c) 2008 Vincent Garnier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');

$core->addBehavior('initWidgets',array('widgetsAcronyms','initWidgets'));

class widgetsAcronyms
{
	# Widget function
	public static function acronymsWidgets($w)
	{
		global $core;

		if (!$core->blog->settings->acronyms->acronyms_public_enabled) return;

		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default')) {
			return;
		}

		$res = ($w->content_only ? '' : '<div class="acronyms'.($w->class ? ' '.html::escapeHTML($w->class) : '').'">').
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';

		$res .=
		'<li><strong><a href="'.$core->blog->url.$core->url->getBase("acronyms").'">'.
		__('List of Acronyms').'</a></strong></li>';

		$res .= '</ul>'.
		($w->content_only ? '' : '</div>');

		return $res;
	}

	public static function initWidgets($w)
	{
		$w->create('acronyms',__('List of Acronyms'),array('widgetsAcronyms','acronymsWidgets'));

		$w->acronyms->setting('title',__('Title:'),__('List of Acronyms'),'text');
		$w->acronyms->setting('homeonly',__('Display on:'),0,'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
				)
		);
    $w->acronyms->setting('content_only',__('Content only'),0,'check');
    $w->acronyms->setting('class',__('CSS class:'),'');
	}
}
?>
