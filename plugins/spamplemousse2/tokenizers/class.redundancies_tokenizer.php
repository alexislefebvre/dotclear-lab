<?php
require_once(dirname(__FILE__).'/class.tokenizer.php');

class redundancies_tokenizer extends tokenizer
{

	public function __construct($prefix = '', $final = '')
	{
		if (empty($prefix)) {
			$this->prefix = '';
		} else {
			$this->prefix = $prefix;
		}

		if (empty($final)) {
			$this->final = 0;
		} else {
			$this->final = $final;
		}
	}

	/**
	@function match
		matches redundancies in a string (example: viagra!!!!!!! becomes viagra!)
	@param	string	$str		the string to analyze
	@return array			array of strings, containing : (left string, match1, match2, ..., right string)
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