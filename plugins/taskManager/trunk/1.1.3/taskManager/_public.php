<?php
# -- BEGIN LICENSE BLOCK -------------------------
# This file is part of taskManager, a plugin for Dotclear.
#
# Copyright (c) 2009 Louis Cherel
# cherel.louis@gmail.com
#
# Licensed under the GPL version 3.0 license.
# See COPIRIGHT file or
# http://www.gnu.org/licenses/gpl-3.0.txt
# -- END LICENCE BlOC -----------------------------

if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';

$core->tpl->addValue('TaskManager',array('tplTaskManager','taskManagerWidget'));
$core->addBehavior('initWidgets',array('tplTaskManager','initWidgets'));
$core->addBehavior('publicHeadContent',array('tplTaskManager','publicHeadContent'));

class tplTaskManager
{
	public static function taskManagerWidget(&$w)
	{
		$tM = new DcTaskManager();
		echo '<p style="text-align:center;"><strong>'.$w->title.'</strong></p>
			<div id="taskManager">
			<div id="curseur" class="infobulle"></div>'.
			$tM->showTaskBar().'</div><!-- End #taskManager -->';
	}

	public static function publicHeadContent(&$core)
	{
		$tM = new DcTaskManager();
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo '<script type="text/javascript" src="'.$url.'/js/public.js"></script>'.$tM->showPublicCSS().$tM->showPublicJS();
	}

}
?>