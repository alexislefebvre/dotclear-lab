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

$core->addBehavior('initWidgets',array('lunarPhaseWidgets','initWidgets'));

class lunarPhaseWidgets
{
	public static function initWidgets($w)
	{
		$w->create('lunarphase',__('Moon phases'),array('lunarPhasePublic','widget'));
		$w->lunarphase->setting('title',__('Title:'),__('Moon phases'));
		$w->lunarphase->setting('phase',__('Display actual phase of moon'),1,'check');
		$w->lunarphase->setting('illumination',__('Display actual illumination of moon'),1,'check');
		$w->lunarphase->setting('age',__('Display actual age of moon'),1,'check');
		$w->lunarphase->setting('dist_to_earth',__('Display actual distance between moon and earth'),1,'check');
		$w->lunarphase->setting('dist_to_sun',__('Display actual distance between moon and sun'),1,'check');
		$w->lunarphase->setting('moon_angle',__('Display actual angle of moon'),1,'check');
		$w->lunarphase->setting('sun_angle',__('Display actual angle of sun'),1,'check');
		$w->lunarphase->setting('parallax',__('Display actual parallax of moon'),1,'check');
		$w->lunarphase->setting('previsions',__('Display all previsions for the next moon phases'),1,'check');
		$w->lunarphase->setting('homeonly',__('Display on:'),0,'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
				)
		);
    $w->lunarphase->setting('content_only',__('Content only'),0,'check');
    $w->lunarphase->setting('class',__('CSS class:'),'');
	}

}

?>