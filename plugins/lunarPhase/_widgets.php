<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin lunarPhase for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('lunarPhaseWidgets','initWidgets'));

/**
 * Class lunarPhaseWidget
 */
class lunarPhaseWidgets
{
	/**
	 * This function create a new lunarPhase widget
	 *
	 * @param	object	w
	 */
	public static function initWidgets(&$w)
	{
		$w->create('lunarphase',__('Moon phases'),array('lunarPhaseUi','widget'));
		$w->lunarphase->setting('title',__('Title:'),__('Moon phases'));
		$w->lunarphase->setting('new_moon',__('Text for new moon:'),__('New moon in %days% day(s) - %date%'));
		$w->lunarphase->setting('first_quarter_moon',__('Text for first quarter:'),__('First Quarter in %days% day(s) - %date%'));
		$w->lunarphase->setting('full_moon',__('Text for full moon:'),__('Full moon in %days% day(s) - %date%'));
		$w->lunarphase->setting('last_quarter_moon',__('Text for last quarter:'),__('Last Quarter in %days% day(s) - %date%'));
		$w->lunarphase->setting('illumination',__('Text for illumination:'),__('Illumination : %s %%'));
		$w->lunarphase->setting('format_date',__('format date (leave blank to use dotclear date format):'),'');
		$w->lunarphase->setting('homeonly',__('Home page only'),1,'check');
	}

}

?>
