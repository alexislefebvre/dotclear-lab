<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Mystique Config plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------


$core->addBehavior('adminTemplateWidgetBeforeLoad',array('mystiqueConfigBehaviors','initWidgets'));


class mystiqueConfigBehaviors {

	public static function initWidgets ($template) {
		$template->setPath(dirname(__FILE__).'/widgets',$template->getPath());
	}
}
?>