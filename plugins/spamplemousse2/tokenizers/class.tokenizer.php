abstract class tokenizer
{
	private $prefix; # the prefix associated to each generated elements
	private $final; # true if the processing of each generated elements is finalized

	/**
	@function create_token
		creates an element of the token array
	@param	string	$elem		a string containing tokens
	@param	string	$prefix		the prefix associated to the $elem string 
	@return array			the element of the token array
	*/
	private function create_token($elem, $prefix) {
		if (($final == 1) && (!empty($prefix))){
			$elem = $prefix.'*'.$elem;
		}

 		$token = array(	'elem' => $elem,
				'prefix' => $prefix, 
				'final' => $this->final
				);

		return $token;
	}


	/**
	@function tokenize
		tokenizes strings not finalized in an array of token, based on a specified matching method
	@param	array	$t		array of tokens
	@return array			array of tokens
	*/
	private function tokenize($t) {
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
								$cur[] = $this->create_token($matches[$i], $p);
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
	@function default_tokenize
		default tokenization of a string, based on a fixed list of delimiters
	@param  array	$t		array of tokens
	@param	string	$prefix		prefix to add to the new tokens
	@param	string	$type		result type : 'token' or 'string', returns an array of tokens or
					an array of string (like match_url)
	@param	string	$delim		list of delimiters to use for the tokenization
	@return array			array of tokens	or array of strings	
	*/
	private function default_tokenize($t, $prefix='', $type='token', $delim = '') {
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
