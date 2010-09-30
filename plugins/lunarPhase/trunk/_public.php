<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lunarPhase, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
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
	This function add CSS file in the public header
	*/
	public static function addCss()
	{
		global $core;
		
		$url = $core->blog->getQMarkURL().'pf='.basename(dirname(__FILE__)).'/style.css';
		
		echo '<link rel="stylesheet" media="screen" type="text/css" href="'.$url.'" />';
	}
}

class lunarPhasePublic
{
	/**
	Displays lunarphase widget
	
	@param	object	w
	@return	string
	*/
	public static function widget($w)
	{
		global $core;
		
		$lp = new lunarPhase();
		
		$ul_mask = '<ul class="%2$s">%1$s</ul>';
		$li_mask = '<li class="%2$s">%1$s</li>';
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$res = strlen($w->title) > 0 ? '<h2>'.$w->title.'</h2>' : '';
		
		# Get live content
		$res .= lunarPhasePublic::getLive($w,$lp);
		# Get prevision content
		$res .= lunarPhasePublic::getPrevisions($w,$lp);
			
		return
			'<div id="lunarphase">'.
			$res.
			'</div>';
	}
	
	public static function getLive($w,$lp)
	{
		$ul_mask = '<ul class="%2$s">%1$s</ul>';
		$li_mask = '<li class="%2$s">%1$s</li>';
		$live = $lp->getLive();
		$res = '';
		
		# Phase
		if ($w->phase) {
			$res .= sprintf($li_mask,$live['name'],$live['id']);
		}
		# Illumination
		if ($w->illumination) {
			$res .=
			sprintf($li_mask,sprintf(__('Illumination: %s%%'),
			lunarPhasePublic::formatValue('percent',$live['illumination'])),
			'illumination');
		}
		# Moon's age
		if ($w->age) {
			$res .=
			sprintf($li_mask,sprintf(__('Age of moon: %s days'),
			lunarPhasePublic::formatValue('int',$live['age'])),
			'age');
		}
		# Distance from earth
		if ($w->dist_to_earth) {
			$res .=
			sprintf($li_mask,sprintf(__('Distance to earth: %s km'),
			lunarPhasePublic::formatValue('int',$live['dist_to_earth'])),
			'dist_to_earth');
		}		
		# Distance from sun
		if ($w->dist_to_sun) {
			$res .=
			sprintf($li_mask,sprintf(__('Distance to sun: %s km'),
			lunarPhasePublic::formatValue('int',$live['dist_to_sun'])),
			'dist_to_sun');
		}
		# Moon's angle
		if ($w->moon_angle) {
			$res .=
			sprintf($li_mask,sprintf(__('Angle of moon: %s deg'),
			lunarPhasePublic::formatValue('deg',$live['moon_angle'])),
			'moon_angle');
		}
		# Sun's angle
		if ($w->sun_angle) {
			$res .=
			sprintf($li_mask,sprintf(__('Angle of sun: %s deg'),
			lunarPhasePublic::formatValue('deg',$live['sun_angle'])),
			'sun_angle');
		}
		# Parallax
		if ($w->parallax) {
			$res .=
			sprintf($li_mask,sprintf(__('Parallax: %s deg'),
			lunarPhasePublic::formatValue('deg',$live['parallax'])),
			'parallax');
		}
		
		if (strlen($res) > 0) {
			return
			'<h3>'.__('In live').'</h3>'.
			sprintf($ul_mask,$res,'lunarphase');
		}
	}
	
	public static function getPrevisions($w,$lp)
	{
		$ul_mask = '<ul class="%2$s">%1$s</ul>';
		$li_mask = '<li class="%2$s">%1$s</li>';
		$res = '';
		
		if ($w->previsions) {
			foreach ($lp->getPrevisions() as $k => $v) {
				$res .= sprintf($li_mask,lunarPhasePublic::formatValue('date',$v),$k);
			}
		}
		
		if (strlen($res) > 0) {
			return
			'<h3>'.__('Previsions').'</h3>'.
			sprintf($ul_mask,$res,'lunarphase');
		}
	}
	
	public static function formatValue($type = '',$value)
	{
		$res = '';
		$format = $GLOBALS['core']->blog->settings->system->date_format.' - ';
		$format .= $GLOBALS['core']->blog->settings->system->time_format;
		
		switch ($type) {
			case 'int':
				$res = number_format($value,0);
				break;
			case 'float':
				$res = number_format($value,2);
				break;
			case 'percent':
				$res = number_format($value * 100,0);
				break;
			case 'date':
				$res = dt::str($format,$value);
				break;
			case 'deg':
				$res = number_format(($value * (180.0 / M_PI)),2);
				break;
			default:
				$res = $value;
				break;
		}
		
		return $res;
	}
}

?>