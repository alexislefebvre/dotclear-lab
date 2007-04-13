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
@brief tokenizer abstract class

this class is the parent of all tokenizers
*/
abstract class tokenizer
{
	protected $prefix = ''; # the prefix associated to each generated elements
	protected $final = 1; # 1 if the processing of each generated elements is finalized

	/**
	Matches something in a string
	
	@param	str		<b>string</b>		the string to analyze
	@return 		<b>array</b>		array of strings, containing : (left string, match1, match2, ..., right string)
	*/
	abstract protected function match($str); 

	/**
	Creates an element of the token array
	
	@param	elem	<b>string</b>		a string containing tokens
	@param	prefix	<b>string</b>		the prefix associated to the $elem string
	@param	final	<b>integer</b>		final state of the token 
	@return 		<b>array</b>		the element of the token array
	*/
	public function create_token($elem, $prefix, $final = 0) {
		$token = null;
		
		if ($elem !== '') {
			if (($final == 1) && ($prefix !== '')){
				$elem = $prefix.'*'.$elem;
			}
			
	
	 		$token = array(	'elem' => $elem,
					'prefix' => $prefix, 
					'final' => $final
					);
		}
		return $token;
	}


	/**
	tokenizes strings not finalized in an array of token, based on a specified
	matching method

	@param	t		<b>array</b>		array of tokens
	@return 		<b>array</b>		array of tokens
	*/
	public function tokenize($t) {
		$tab = array();
		foreach ($t as $e) {
			# we are working on non-finalized strings
			if ($e['final'] == 0) {
				$s = $e['elem'];
				$pre = $e['prefix'];
				$cur = array();
				$remain = $s;
				do { 
					if ($remain != '') {
						# call the matching method
						$matches = $this->match($remain);	

						if ($matches != 0) {
							# trim and insert the first match
							$n = count($matches)-1;
							$matches[0] = trim($matches[0]);
							# part of the string left to the found tokens
							if ($matches[0] != '') {
								$cur[] = $this->create_token($matches[0], $pre, 0);
							}
	
							# matched tokens handling
							$i = 1;
							while ($i != $n) {
								# we compute here the new prefix
								$p = ''; 
								if (!empty($pre) && !empty($this->prefix)) {
									$p = $pre.'*'.$this->prefix;
								} else {
									$p = $pre.$this->prefix;
								}
								$cur[] = $this->create_token($matches[$i], $p, $this->final);
								$i++;
							}
		
							# we trim the part of the string right to the found tokens
							# and we insert it in $remain
							$remain = trim($matches[$n]);
						} else {
							# part of the string right to the found tokens
							$remain = trim($remain);
							if ($remain != '') {
								$cur[] = $this->create_token($remain, $pre, 0);
								$remain = '';
							}
						}
					}
				} while ($remain != '');
				$tab = array_merge($tab, $cur);
			} else {
				$tab[] = $e;
			}		
		}
		return($tab);
	}

	/**
	default tokenization of a string, based on a fixed list of delimiters

	@param  t		<b>array</b>		array of tokens
	@param	prefix	<b>string</b>		prefix to add to the new tokens
	@param	type	<b>string</b>		result type : 'token' or 'string', returns an array of tokens or
											an array of string (like match_url)
	@param	delim	<b>string</b>		list of delimiters to use for the tokenization
	@return 		<barray</b>			array of tokens	or array of strings	
	*/
	public function default_tokenize($t, $prefix='', $type='token', $delim = '') {
		if ($delim == '') {
			$delim = '.,;:"?[]{}()+-*/=<>|&~`@_'."\r\n";
		}

		$tab = array();
		foreach ($t as $e) {
			if ($e['final'] == 0) {
				if (!empty($e['elem'])) {
					$i = 0; # start of mb_substring
					$j = 0; # end of mb_substring
					$s = $e['elem'];
					$n = mb_strlen($s);
					$pre = $e['prefix'];
					while($j != $n) {
						if ((mb_strpos($delim, mb_substr($s, $j, 1)) !== false) || (mb_substr($s, $j, 1) == ' ')) {
							$sub = mb_substr($s, $i, $j-$i);
							if ($sub != '') {
								if ($type == 'token') {
									$p = ''; # new prefix
									if (!empty($pre) && !empty($prefix)) {
										$p = $pre.'*'.$prefix;
									} else {
										$p = $pre.$prefix;
									}
									$tab[] = $this->create_token($sub, $p, 1);
								} else {
									$tab[] = $sub;
								}
							}
							$i = $j+1;
						}
						$j++;
					}
					$j--;
					# handling of the last word
					if (!((mb_strpos($delim, mb_substr($s, $j, 1))!== false) && (mb_substr($s, $j, 1) == ' '))) {
						$sub = mb_substr($s, $i, $j-$i+1);
						if ($sub != '') {
							if ($type == 'token') {
							$p = ''; # new prefix
								if (!empty($pre) && !empty($prefix)) {
									$p = $pre.'*'.$prefix;
								} else {
									$p = $pre.$prefix;
								}
								$tab[] = $this->create_token($sub, $p, 1);
							} else {
								$tab[] = $sub;
							}
						}
					}
				}			
			} else {
				if ($type == 'token') {
					$tab[] = $e;
				} else {
					$tab[] = $e['elem'];
				}
			}
		}
		return $tab;
	}

}
?>
