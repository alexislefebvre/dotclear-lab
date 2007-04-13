<?php
# ***** BEGIN LICENSE BLOCK *****
# This is spamplemousse2, a plugin for DotClear. 
# Copyright (c) 2007 Alain Vagner and contributors. All rights
# reserved.
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

/**
@ingroup SPAMPLE2
@brief reassembly tokenizer

this class detects and reassembles tokens like v.i.a.g.r.a
*/
class reassembly_tokenizer extends tokenizer
{
	/**
	Matches tokens of length equal to 1 separated only by 1 delimiter
	
	@param	str		<b>string</b>		the string to analyze
	@return 		<b>array</b>		array of strings, containing : (left string, match1, match2, ..., right string)
	*/	
	protected function match($str) {
		$result = '';
		$matches = '';

		$regexp = '([$.:*|`@_]?([\w][$.:*|`@_])+[\w]?)'; # FIXME this regexp does not detect "v i a g r a"
		$res = preg_match('/'.$regexp.'(.*)/uism', $str, $matches);
		if ($res != 0) {
			$result = array();
			$word_tmp = $matches[1];
			$word = '';
			$i = 0;
			$n = mb_strlen($word_tmp);
			if ($n >= 4) {
				if (!preg_match('/[\w]/uis', $word_tmp[0])) {
					$i = 1;
				}
				for (;$i<$n; $i = $i+2) {
					$word .= $word_tmp[$i];
				}
				$pos = mb_strpos($str, $word_tmp);
				$result[] = mb_substr($str, 0, $pos);
				$result[] = $word;
				$result[] = $matches[3];
			} else {
				$result = 0;
			}
		} else {
			$result = 0;
		}	
		return $result;
	}
}
?>