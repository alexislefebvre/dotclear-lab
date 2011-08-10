<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

abstract class QRcodeType
{
	abstract public static function getTitle($qrc);
	abstract public static function getForm($qrc);
	abstract public static function saveForm($qrc);
	abstract public static function getTemplate($qrc,$attr);
	abstract public static function encodeData($qrc,$args);
	
	public static function returnImg($qrc,$id)
	{
		$img = $qrc->getURL($id);
		$size = $qrc->getParam('size');
		
		echo 
		'<h3>'.__('QRcode successfully created').'</h3>'.
		'<p><a href="'.$img.'" title="QR code">'.$img.'</a></p>'.
		'<p><img alt="QR code" src="'.$img.'" width="'.$size.'px" height="'.$size.'px" /></p>';
	}
}
?>