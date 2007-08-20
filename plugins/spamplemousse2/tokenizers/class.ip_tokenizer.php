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
@brief ip tokenizer

this class has to tokenize ip addresses
*/
class ip_tokenizer extends tokenizer
{

	public function __construct()
	{
		$this->prefix = 'ip';
	}

	/**
	Matches ip addresses in a string
	
	@param	str		<b>string</b>		the string to analyze
	@return 		<b>array</b>		array of strings, containing : (left string, match1, match2, ..., right string)
	*/	
	protected function match($str) {
		$result = '';
		$matches = '';
		$num = '(25[0-5]|2[0-4]\d|[01]?\d\d|\d)';
		$regexp = $num.'\.'.$num.'\.'.$num.'\.'.$num;	
		$res = preg_match('/'.$regexp.'(.*)/uism', $str, $matches);
		if ($res != 0) {
			$result = array();
			$ip = $matches[1].'.'.$matches[2].'.'.$matches[3].'.'.$matches[4];
			$pos = mb_strpos($str, $ip.$matches[5]);
			$result[] = mb_substr($str, 0, $pos);
			$result[] = $ip;
			$result[] = $matches[5];	

		} else {
			$result = 0;
		}	
		return $result;
	}
}

?>