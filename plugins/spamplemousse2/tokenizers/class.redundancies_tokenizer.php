<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Spamplemousse2, a plugin for DotClear.  
# Copyright (c) 2007 Alain Vagner and contributors. All rights
# reserved.
#
# Spamplemousse2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Spamplemousse2 is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Spamplemousse2; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

/**
@ingroup SPAMPLE2
@brief redundancies tokenizer

this class detects and normalizes redundancies in a string
*/
class redundancies_tokenizer extends tokenizer
{
	/**
	Matches redundancies in a string (example: viagra!!!!!!! becomes viagra!)
	
	@param	str		<b>string</b>		the string to analyze
	@return 		<b>array</b>		array of strings, containing : (left string, match1, match2, ..., right string)
	*/	
	protected function match($str) {
		$result = '';
		$matches = '';

		$regexp = '([\w.-]+[!?]{1})([!?]+)';	
		$res = preg_match('/'.$regexp.'(.*)/uism', $str, $matches);
		if ($res != 0) {
			$result = array();

			$word = $matches[1];
			$pos = mb_strpos($str, $word);
			$result[] = mb_substr($str, 0, $pos);
			$result[] = $word;
			$result[] = $matches[3];
		} else {
			$result = 0;
		}	
		return $result;
	}
}
?>