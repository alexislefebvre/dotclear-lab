<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of hackMyTags,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__)."/class.dc.hackmytags.php";

$core->addBehavior('templateBeforeBlock',array('behaviorsHackMyTags','templateBeforeBlock'));
$core->addBehavior('templateBeforeValue',array('behaviorsHackMyTags','templateBeforeValue'));

$core->hackMyTags = new dcHackMyTags($core);

class behaviorsHackMyTags
{
	public static function templateBeforeBlock($core,$b,$attr)
	{
		if ($core->hackMyTags->isBlockHacked($core->url->type,$b)) {
			$core->hackMyTags->updateBlockCounters($b);
			$core->hackMyTags->hackBlock($core->url->type, $b, $attr);
		}
		return "";
	}

	public static function templateBeforeValue($core,$b,$attr)
	{
		if ($core->hackMyTags->isValueHacked($core->url->type,$b)) {
			$core->hackMyTags->updateValueCounters($b);
			$core->hackMyTags->hackValue($core->url->type, $b, $attr);
		}
		return "";
	}
}

?>