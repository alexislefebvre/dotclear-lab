<?php
require_once(dirname(__FILE__).'/class.tokenizer.php');

class reassembly_tokenizer extends tokenizer
{

	public function __construct($prefix = '', $final = '')
	{
		if ($prefix !== '') {
			$this->prefix = $prefix;
		} else {
			$this->prefix = '';
		}

		if ($final !== '') {
			$this->final = $final;
		} else {
			$this->final = 1;
		}
	}

	/**
	@function match
		matches tokens of length equal to 1 separated only by 1 delimiter
	@param	string	$str		the string to analyze
	@return array			array of strings, containing : (left string, match1, match2, ..., right string)
	*/
	protected function match($str) {
		$result = '';
		$matches = '';

		$regexp = '([$.:*|`@_]?([\w][$.:*|`@_])+[\w]?)'; # FIXME this regexp does not detect "v i a g r a"
		$res = preg_match('/'.$regexp.'(.*)/uism', $str, $matches);
		if ($res != 0) {
			$result = array();
			$word_tmp = $matches[1];
			$word = '';
			$i = 0;
			$n = mb_strlen($word_tmp);
			if ($n >= 4) {
				if (!preg_match('/[\w]/uis', $word_tmp[0])) {
					$i = 1;
				}
				for (;$i<$n; $i = $i+2) {
					$word .= $word_tmp[$i];
				}
				$pos = mb_strpos($str, $word_tmp);
				$result[] = mb_substr($str, 0, $pos);
				$result[] = $word;
				$result[] = $matches[3];
			} else {
				$result = 0;
			}
		} else {
			$result = 0;
		}	
		return $result;
	}
}
?>