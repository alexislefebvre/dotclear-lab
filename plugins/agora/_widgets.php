<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku , Tomtom and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------


if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraMember'));
$core->addBehavior('initWidgets',array('agoraWidgets','initWidgetsAgoraModerate'));

class agoraWidgets 
{
	public static function initWidgetsAgoraMember($w)
	{
		$w->create('memberAgoraWidget',__('Agora member connection'),array('tplAgora','memberWidget'));
		$w->memberAgoraWidget->setting('title',__('Title:'),__('Agora connection'));
		#$widgets->privateblog->setting('text',__('Text:'),'','textarea');
		#$widgets->privateblog->setting('label',__('Button:'),__('Disconnect'));
		#$widgets->privateblog->setting('homeonly',__('Home page only'),0,'check');
	}
	
	public static function initWidgetsAgoraModerate($w)
	{
		$w->create('moderateAgoraWidget',__('Agora moderation'),array('tplAgora','moderateWidget'));
		$w->moderateAgoraWidget->setting('title',__('Title:'),__('Agora moderation'));
		#$widgets->privateblog->setting('text',__('Text:'),'','textarea');
		#$widgets->privateblog->setting('label',__('Button:'),__('Disconnect'));
		#$widgets->privateblog->setting('homeonly',__('Home page only'),0,'check');
	}
}
?>