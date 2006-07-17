<?php
require_once(dirname(__FILE__).'/class.tokenizer.php');

class html_tokenizer extends tokenizer
{

	public function __construct($prefix = '', $final = '')
	{
		if (empty($prefix)) {
			$this->prefix = 'html';
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
		matches html tags in a string
	@param	string	$str		the string to analyze
	@return array			array of strings, containing : (left string, match1, match2, ..., right string)
	*/
	protected function match($str) {
		return $str;
	}

	public function tokenize($s) {
		# nothing to be done here for the moment
		return $s;
	}
}
?>