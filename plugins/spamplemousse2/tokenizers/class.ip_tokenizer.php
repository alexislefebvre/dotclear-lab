<?php
require_once(dirname(__FILE__).'/class.tokenizer.php');

class ip_tokenizer extends tokenizer
{

	public function __construct($prefix = '', $final = '')
	{
		if ($prefix !== '') {
			$this->prefix = $prefix;
		} else {
			$this->prefix = 'ip';
		}

		if ($final !== '') {
			$this->final = $final;
		} else {
			$this->final = 1;
		}
	}

	/**
	@function match
		matches ip addresses in a string
	@param	string	$str		the string to analyze
	@return array			array of strings, containing : (left string, match1, match2, ..., right string)
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