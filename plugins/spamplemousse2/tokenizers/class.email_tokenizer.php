<?php
require_once(dirname(__FILE__).'/class.tokenizer.php');

class email_tokenizer extends tokenizer
{

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
	@function match
		matches mail addresses in a string
	@param	string	$str		the string to analyze
	@return array			array of strings, containing : (left string, match1, match2, ..., right string)
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