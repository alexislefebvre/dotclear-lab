<?php
require_once(dirname(__FILE__).'/class.tokenizer.php');

class url_tokenizer extends tokenizer
{

	public function __construct($prefix = '', $final = '')
	{
		if (empty($prefix)) {
			$this->prefix = 'URL';
		} else {
			$this->prefix = $prefix;
		}

		if (empty($final)) {
			$this->final = 1;
		} else {
			$this->final = $final;
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