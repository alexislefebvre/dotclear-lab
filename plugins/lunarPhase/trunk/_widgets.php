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

$core->addBehavior('initWidgets',array('lunarPhaseWidgets','initWidgets'));

class lunarPhaseWidgets
{
	/**
	 * This function create a new lunarPhase widget
	 *
	 * @param	object	w
	 */
	public static function initWidgets($w)
	{
		$w->create('lunarphase',__('Moon phases'),array('lunarPhasePublic','widget'));
		$w->lunarphase->setting('title',__('Title:'),__('Moon phases'));
		$w->lunarphase->setting('new_moon',__('Text for new moon:'),__('New moon in %days% day(s) - %date%'));
		$w->lunarphase->setting('first_quarter_moon',__('Text for first quarter:'),__('First Quarter in %days% day(s) - %date%'));
		$w->lunarphase->setting('full_moon',__('Text for full moon:'),__('Full moon in %days% day(s) - %date%'));
		$w->lunarphase->setting('last_quarter_moon',__('Text for last quarter:'),__('Last Quarter in %days% day(s) - %date%'));
		$w->lunarphase->setting('illumination',__('Text for illumination:'),__('Illumination : %s %%'));
		$w->lunarphase->setting('format_date',__('format date (leave blank to use Dotclear date format):'),'');
		$w->lunarphase->setting('homeonly',__('Home page only'),1,'check');
	}

}

?>