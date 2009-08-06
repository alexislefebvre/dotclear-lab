<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of referer, a plugin for Dotclear.
# 
# Copyright (c) 2008 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicBeforeDocument',array('refererBehaviors','addReferer'));

class refererBehaviors
{
	/*
	 * Adds referers in settings
	 */
	public static function addReferer($core)
	{
		$last = unserialize($core->blog->settings->last_referer);
		$top = unserialize($core->blog->settings->top_referer);

		$ref = isset($_SERVER['HTTP_REFERER']) ? trim(html::escapeHTML($_SERVER['HTTP_REFERER'])) : '';

		if (!empty($ref)) {
			$url = parse_url($ref);
			$domain = !empty($url['host']) ? $url['scheme'].'://'.$url['host'] : __('Direct entrance');
			$ownurl = parse_url($core->blog->url);
			$owndomain = $ownurl['scheme'].'://'.$ownurl['host'];
			$time = time() + dt::getTimeOffset($core->blog->settings->blog_timezone);

			if ($owndomain == $domain) {
				return;
			}
			# Added last referer
			$new_last = array(
				'domain'	=> $domain,
				'url'	=> $ref,
				'dt'		=> $time
			);
			if (count($last) > 19) {
				unset($last[19]);
			}
			array_unshift($last,$new_last);

			# Added top referer
			$new_top = array(
				'domain' => $domain,
				'count' => 0,
			);
			$find = false;
			foreach ($top as $k => $v) {
				if ($v['domain'] == $domain) { 
					$top[$k]['count'] = $v['count'] + 1;
					$find = true;
					break;
				}
			}
			if (count($top) > 19 && !$find) {
				usort($top,'refererBehaviors::cmp');
				unset($top[19]);
			}
			if (!$find) {
				array_unshift($top,$new_top);
			}

			$core->blog->settings->setNamespace('referer');
			$core->blog->settings->put('last_referer',serialize($last),'string');
			$core->blog->settings->put('top_referer',serialize($top),'string');
			//echo '<pre>'; print_r($top); echo '</pre>'; exit;
		}
	}

	public static function cmp($a,$b)
	{
		if ($a['count'] == $b['count']) {
			return 0;
		}
		return $a['count'] < $b['count'] ? -1 : 1;
	}
}

class refererPublic
{
	/**
	 * This function displays the referer widget
	 *
	 * @param	w	Widget object
	 *
	 * @return	string
	 */
	public static function last($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		$limask = '<li>%s</li>';
		$amask = '<a href="%1$s">%2$s</a>';

		$title = strlen($w->title) > 0 ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '';

		$last = unserialize($core->blog->settings->last_referer);

		$res = '';

		foreach ($last as $k => $v) {
			if ($k < $w->numbertodisplay) {
				$link = sprintf($amask,$v['url'],$v['domain']);
				$res .= sprintf($limask,$link);
			}
		}

		return 
			'<div id="last_referers">'.
			$title.
			(!empty($res) ? '<ul>'.$res.'</ul>' : '').
			'</div>';
	}

	/**
	 * This function displays the top referer widget
	 *
	 * @param	w	Widget object
	 *
	 * @return	string
	 */
	public static function top($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		$limask = '<li>%s</li>';
		$amask = '<a href="%1$s">%2$s</a>';

		$title = strlen($w->title) > 0 ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '';

		$last = unserialize($core->blog->settings->top_referer);

		$res = '';

		foreach ($last as $k => $v) {
			if ($k < $w->numbertodisplay) {
				$link = sprintf($amask,$v['domain'],$v['domain']);
				$res .= sprintf($limask,$link.' - <em>'.$v['count'].' '.__('visit(s)').'</em>');
			}
		}

		return
			'<div id="top_referers">'.
			$title.
			(!empty($res) ? '<ul>'.$res.'</ul>' : '').
			'</div>';
	}
}

?>