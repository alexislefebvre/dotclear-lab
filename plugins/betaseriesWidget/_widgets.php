<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of betaseriesWidget, a plugin for Dotclear.
# 
# Copyright (c) 2010 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
$core->addBehavior('initWidgets', array('BetaSeriesWidget','initWidgets'));
 
class BetaSeriesWidget
{
	public static function initWidgets(&$w)
	{
		$w->create('BetaSeries','BetaSeries', array('publicBetaSeriesWidget','getContent'));
		$w->BetaSeries->setting('title',__('Title:'), '','text');
		$w->BetaSeries->setting('userName',__('Username:'), '','text');
		$w->BetaSeries->setting('limit',__('Limit:'),10);
		$w->BetaSeries->setting('homeonly',__('Home page only'),0,'check');
	}
}
?>
