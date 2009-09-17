<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dayMode, a plugin for Dotclear 2.
#
# Copyright (c) 2006-2009 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$GLOBALS['__autoload']['dcCalendar'] = dirname(__FILE__).'/inc/class.dc.calendar.php';
$GLOBALS['__autoload']['dcDayTools'] = dirname(__FILE__).'/inc/class.dc.calendar.php';

class dayModeBehaviors
{
	// Public behaviors
	public static function block()
	{
		$args = func_get_args();
		array_shift($args);

		if ($args[0] == 'Entries') {
			$attrs = $args[1];

			if (!empty($attrs['today'])) {
				$p =
				'<?php $today = dcDayTools::getEarlierDate(array("ts_type" => "day")); '.
					"\$params['post_year'] = \$today->year(); ".
					"\$params['post_month'] = \$today->month(); ".
					"\$params['post_day'] = \$today->day(); ".
					"unset(\$params['limit']); ".
					"unset(\$today); ".
				" ?>\n";
			}
			else {
				$p =
				'<?php if ($_ctx->exists("day")) { '.
					"\$params['post_year'] = \$_ctx->day->year(); ".
					"\$params['post_month'] = \$_ctx->day->month(); ".
					"\$params['post_day'] = \$_ctx->day->day(); ".
					"unset(\$params['limit']); ".
				"} ?>\n";
			}
			return $p;
		}
	}

	public static function addTplPath(&$core)
	{
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
	}

	// Admin behaviors
	public static function adminBlogPreferencesForm(&$core,&$settings)
	{
		echo
		'<fieldset><legend>'.__('Daily Archives').'</legend>'.
		'<p><label class="classic">'.
		form::checkbox('daymode_active','1',$settings->daymode_active).
		__('Enable daily archives and calendar').'</label></p>'.
		'</fieldset>';
	}
	
	public static function adminBeforeBlogSettingsUpdate(&$settings)
	{
		$settings->setNameSpace('daymode');
		try {
			$settings->put('daymode_active',!empty($_POST['daymode_active']),'boolean');
		}
		catch (Exception $e) {
			$settings->drop('daymode_active');
			$settings->put('daymode_active',!empty($_POST['daymode_active']),'boolean');
		}
		$settings->setNameSpace('system');
	}
}
?>