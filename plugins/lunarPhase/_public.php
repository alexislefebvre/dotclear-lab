<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lunarPhase, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicHeadContent',array('lunarPhaseBehaviors','addCss'));

class lunarPhaseBehaviors
{
	/**
	 * This function add CSS file in the public header
	 */
	public static function addCss()
	{
		global $core;

		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__)).'/style.css';
		
		echo '<link rel="stylesheet" media="screen" type="text/css" href="'.$url.'" />';
	}
}

class lunarPhasePublic
{
	/**
	 * Displays lunarphase widget
	 *
	 * @param	object	w
	 *
	 * @return	string
	 */
	public function widget(&$w)
	{
		$lp = new self(&$GLOBALS['core'],$w);

		if ($w->homeonly && $lp->core->url->type != 'default') {
			return;
		}

		$res = strlen($w->title) > 0 ? '<h2>'.$w->title.'</h2>' : '';

		$res .=
			'<h3>'.__('In live').'</h3>'.
			'<ul class="lunarphase">'.
			$lp->setLine($lp->phase,$w,'live').
			'</ul>'.
			'<h3>'.__('Previsions').'</h3>'.
			'<ul class="lunarphase">';
		foreach ($lp->previsions as $prevision) {
			$mode = (!strpos($prevision->id,'moon')) ? 'illumination' : 'previsions';
			$res .= $lp->setLine($prevision,$w,$mode);
		}
		$res .=
			'</ul>'.
			'</div>';

		return 
			'<div id="lunarphase">'.
			$res.
			'</div>';
	}

	/**
	 * Returns each line of the item list
	 *
	 * @param	object	obj
	 * @param	object	w
	 * @param	string	mode
	 *
	 * @return	string
	 */
	private function setLine($obj,$w,$mode)
	{
		$item = '<li class="%1$s">%2$s</li>'."\n";
		$str = preg_replace(array('/%days%/','/%date%/'),array('%1$s','%2$s'),$w->{$obj->id});
		
		if ($mode == 'previsions') {
			$text = sprintf($str,$obj->days,$obj->date);
		}
		elseif ($mode == 'illumination') {
			$text = sprintf($w->{$obj->id},$obj->value);
		}
		elseif ($mode == 'live') {
			$text = $obj->name;
		}
		else {
			$text = '';
		}

		return !empty($text) ? sprintf($item,$obj->id,$text) : '';
	}
}

?>