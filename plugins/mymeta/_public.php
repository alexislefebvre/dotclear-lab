<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2008 Bruno Hondelatte,  and contributors.
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Mymeta plugin for DC2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****


$core->tpl->addValue('MyMetaValue',array('tplMyMeta','MyMetaValue'));
$core->tpl->addBlock('MyMetaIf',array('tplMyMeta','MyMetaIf'));

class tplMyMeta
{
	public static function getOperator($op)
	{
		switch (strtolower($op))
		{
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}

	public static function MyMetaValue($attr) {
		if (isset($attr['type']))
			$type = addslashes($attr['type']);
		else
			return "";
		$res =
		"<?php\n".
		'$objMeta = new dcMeta($core); '.
		'$objMyMeta = new myMeta($core); '.
		"if (\$objMyMeta->isMetaEnabled('".$type."'))".
		"echo \$objMeta->getMetaStr(\$_ctx->posts->post_meta,'".$type."'); ".
		'?>';
		return $res;
	}

	public static function MyMetaIf($attr,$content)
	{
		if (isset($attr['type']))
			$type = addslashes($attr['type']);
		else
			return "";
		$if = array();
		$operator = isset($attr['operator']) ? tplMyMeta::getOperator($attr['operator']) : '&&';
		if (isset($attr['defined'])) {
			$sign = ($attr['defined']=="true") ? '!' : '';
			$if[] = $sign.'empty($value)';
		}
		if (isset($attr['value'])) {
			$value = $attr['value'];
			if (substr($value,1,1)=='!')
				$if[] = "\$value !='".substr($value,1)."'";
			else
				$if[] = "\$value =='".$value."'";
		}
		$res =
		"<?php\n".
		'$objMeta = new dcMeta($core); '.
		'$objMyMeta = new myMeta($core); '.
		"if (\$objMyMeta->isMetaEnabled('".$type."')) :\n".
		"  \$value=\$objMeta->getMetaStr(\$_ctx->posts->post_meta,'".$type."'); ".
		"  if(".implode(" ".$operator." ",$if).") : ?>".
		$content.
		"  <?php endif; ".
		"endif; ?>";
		return $res;
	}

}

?>
