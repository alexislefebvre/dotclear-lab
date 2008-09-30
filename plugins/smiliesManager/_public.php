<?php
# ***** BEGIN LICENSE BLOCK *****
# This is Smilies Manager, a plugin for DotClear. 
# Copyright (c) 2005 k-net. All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
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
if (!defined('DC_RC_PATH')) { return; }

class tplSmiliesManager
{
	public static function toolbar($attr) {
		global $core;
		
		$smilies = smiliesManager::getSmilies();
		
		$toolbarTpl = $core->blog->settings->smiliesmanager_toolbartpl;
		
		$res = '';
		foreach ($smilies as $smiley) {
			if ($smiley['onToolbar']) {
				$res .= ' <img src="'.$smiley['url'].'" alt="'.htmlspecialchars($smiley['code']).'" title="'.htmlspecialchars($smiley['code']).'" onclick="javascript:smiliesManagerInsertSmiley(\''.$attr['textarea'].'\', \''.htmlspecialchars(str_replace('\'', '\\\'', str_replace('\\', '\\\\', $smiley['code']))).'\');" style="cursor:pointer; padding-left: 4px;" />';
			}
		}
		
		if (!empty($res)) {
			return "\n".
			'        <script type="text/javascript"> function smiliesManagerInsertSmiley(textarea, smiley) { smiley = \' \'+smiley+\' \'; textarea = document.getElementById(textarea); textarea.focus(); var start, end, scrollPos; if (typeof(document["selection"]) != \'undefined\') { document.selection.createRange().text = smiley; textarea.caretPos += smiley.length; } else if (typeof(textarea[\'setSelectionRange\']) != \'undefined\') { start = textarea.selectionStart; end = textarea.selectionEnd; scrollPos = textarea.scrollTop; textarea.value = textarea.value.substring(0, start)+smiley+textarea.value.substring(end); textarea.setSelectionRange(start + smiley.length, start + smiley.length); textarea.scrollTop = scrollPos; } } </script>'."\n".
			'        '.sprintf($toolbarTpl, $res)."\n".
			'        ';
		}
		return '';
	}
}

$core->tpl->addValue('SmiliesManagerToolbar',array('tplSmiliesManager','toolbar'));

?>