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

class url_tokenizer extends tokenizer
{

	public function __construct($prefix = '', $final = '')
	{
		if ($prefix !== '') {
			$this->prefix = $prefix;
		} else {
			$this->prefix = 'url';
		}

		if ($final !== '') {
			$this->final = $final;
		} else {
			$this->final = 1;
		}
	}

	/**
	@function match
		matches urls in a string
	@param	string	$str		the string to analyze
	@return array			array of strings, containing : (left string, match1, match2, ..., right string)
	*/
	protected function match($str) {
		$result = '';
		$matches = '';

		$scheme = 'http:\/\/';
		$extrem_host = '[A-Z0-9]';
		$elem_host = '[A-Z0-9.-]{1,61}';
		$tld = '[A-Z]{2,6}';
		$num = '(25[0-5]|2[0-4]\d|[01]?\d\d|\d)';
		$ip = $num.'\.'.$num.'\.'.$num.'\.'.$num;
		$path = '[^\'">\s\r\n]*';
		$delim = '[\'">\s\r\n]?';

		$regexp = $scheme.'(('.$extrem_host.$elem_host.$extrem_host.'\.'.$tld.')|('.$ip.'))('.$path.')'.$delim;
		$res = preg_match('/'.$regexp.'(.*)/uism', $str, $matches);
		if ($res != 0) {
			$result = array();
			$url = 'http://'.$matches[1].$matches[8];
			$pos = mb_strpos($str, $url);
			$result[] = mb_substr($str, 0, $pos);
			$matched_ip = $matches[1];
			if ($matched_ip) {
				$result[] = $matched_ip;
			}
			$result = array_merge($result, $this->default_tokenize(array(array('elem'=>$matches[8])), '','string', '/?=.:&'));
			$result[] = $matches[9];
		} else {
			$result = 0;
		}	
		return $result;
	}

}
?>