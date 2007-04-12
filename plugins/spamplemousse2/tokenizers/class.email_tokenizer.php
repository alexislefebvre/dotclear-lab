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
@brief email tokenizer

this class has to tokenizes email addresses
*/
class email_tokenizer extends tokenizer
{

	/**
	Constructor
		
	@param	prefix		<b>string</b>		The prefix to add to the tokens
	@param	final		<b>integer</b>		1 if the tokens have to be final
											(no modifications after)
	*/	
	public function __construct($prefix = '', $final = '')
	{
		if ($prefix !== '') {
			$this->prefix = $prefix;
		} else {
			$this->prefix = 'email';
		}

		if ($final !== '') {
			$this->final = $final;
		} else {
			$this->final = 1;
		}
	}

	/**
	Matches mail addresses in a string
	@param	str		<b>string</b>		the string to analyze
	@return 		<b>array</b>		array of strings, containing : (left string, match1, match2, ..., right string)
	*/
	protected function match($str) {
		$result = '';
		$matches = '';

		$debut_mail = '[\d\w\/+!=#|$?%{^&}*`\'~-]';
		$elem_mail =  '[.\d\w\/+!=#|$?%{^&}*`\'~-]';
		$extrem_host = '[A-Z0-9]';
		$elem_host = '[A-Z0-9.-]{1,61}';
		$tld = '[A-Z]{2,6}';

		$regexp = '('.$debut_mail.$elem_mail.'*)@('.$extrem_host.$elem_host.$extrem_host.'\.'.$tld.')';
		$res = preg_match('/'.$regexp.'(.*)/uism', $str, $matches);
		if ($res != 0) {
			$result = array();

			$mail = $matches[1].'@'.$matches[2];
			$pos = mb_strpos($str, $mail);
			$result[] = mb_substr($str, 0, $pos);
			$result[] = $matches[1];
			$result = array_merge($result, explode('.', $matches[2]));
			$result[] = $matches[3];
		} else {
			$result = 0;
		}	
		return $result;
	}
}
?>