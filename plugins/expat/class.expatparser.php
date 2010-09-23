<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of ExpAt,
# a plugin for DotClear2.
#
# Copyright (c) 2010 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }


class expatParser {

	private static $basic_tokens = array(
		'==' => 'EQ',
		'!=' => 'NE',
		'||' => 'AND',
		'&&' => 'OR',
		'^^' => 'XOR',
		'&gt;' => 'GT',
		'&lt;' => 'LT',
		'&ge;' => 'GE',
		'&le;' => 'LE');
	
	private $parser;
	private $core;
		
	public function __construct($core) {
		require_once dirname(__FILE__)."/class.expatexp.php";
		$this->parser = new parse_engine(new expatEngine());
		$core->expatDict = new expatDict($core);
	}
		
	protected function tokenize($line) {
		$out = array();
		while (strlen($line)) {
			$line = trim($line);
			if (preg_match('/^[0-9]+(\.[0-9]*)?/', $line, $regs)) {
				$out[] = array('number',$regs[0]);
				$line = substr($line, strlen($regs[0]));
			} elseif (preg_match('/^[A-Za-z_0-9]+/', $line, $regs)) {
				# It's a variable name
				$out[] = array('varname',$regs[0]);
				$line = substr($line, strlen($regs[0]));
			}  elseif (preg_match('/^\'(.*?)\'/', $line, $regs)) {
				# It's a string name
				$out[] = array('string',$regs[1]);
				$line = substr($line, strlen($regs[0]));
			}else {
				$b=false;
				foreach (self::$basic_tokens as $k => $v) {
					if (substr($line,0,strlen($k))==$k) {
						$out[]=array($v,$k);
						$line = substr($line,strlen($k));
						$b=true;
						break;
					}
				}
				if (!$b) {
					# It's some other character
					$out[] = array("'".$line[0]."'",null);
					$line = substr($line, 1);
				}
			}
		}
		return $out;
	}

	public function parse($line) {
		if (!strlen($line)) return;
		try {
			$this->parser->reset();
			foreach($this->tokenize($line) as $t) {
				$this->parser->eat($t[0],$t[1]);
			}
			return $this->parser->eat_eof();
		} catch (parse_error $e) {
			throw $e;
		}
	}
}

?>

