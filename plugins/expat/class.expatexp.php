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

abstract class eaExpression {
	public function __toString() {
		return "[exp]".get_class($this);
	}
	public function toPHP() {
	}
}

abstract class eaBinaryExpression extends eaExpression { 
	protected $left;
	protected $right;
	protected $operator;
	
	public function __construct($left,$right,$operator) {
		$this->left = $left;
		$this->right = $right;
		$this->operator = $operator;
	}
	
	public function toPHP() {
		return $this->left->toPHP().' '.
			$this->operator.' '.
			$this->right->toPHP();
	}

}
class eaArithmeticExpression extends eaBinaryExpression {}
class eaLogicalExpression extends eaBinaryExpression {}

class eaComparisonExpression extends eaBinaryExpression {}

class eaGroupExpression extends eaExpression {
	private $exp;
	public function __construct($exp) {
		$this->exp = $exp;
	}
	public function toPHP () {
		return '('.$this->exp->toPHP().')';
	}
}


class eaValue extends eaExpression {
	private $value;
	public function __construct($value) {
		$this->value = $value;
	}
	public function toPHP () {
		if (is_numeric($this->value)) {
			return ''.$this->value;
		} else {
			return "'".$this->value."'";
		}
	}
}

class eaVariable extends eaExpression {
	private $obj;
	private $attribute;
	
	private static $default_object=null;
	
	public static function setDefaultObject($object) {
		self::$default_object = $object;
	}
	
	public function __construct($object,$attribute) {
		$this->attribute = $attribute;
		if ($object == null) {
			$this->obj = self::$default_object;
		} else {
			$this->obj = $object;
		}
	}

	public function toPHP () {
		global $core;
		$obj = $core->expatDict->getObject($this->obj);
		if ($obj === false) {
			throw new Exception ("No such object : ".$this->obj);
		}
		$attr = $obj->getAttribute($this->attribute);
		if ($attr === false) {
			throw new Exception ("No such attribute : ".$this->obj."->".$this->attribute);
			
		}
		return $attr;
	}
}

class eaNotExpression extends eaExpression {
	private $exp;
	public function __construct($exp) {
		$this->exp = $exp;
	}

	public function toPHP () {
		return '!'.$this->exp->toPHP();
	}
}

class eaFunction extends eaExpression {
	private $name;
	private $args;
	public function __construct($name,$args) {
		$this->name = $name;
		$this->args = $args;
	}

	public function toPHP () {
		global $core;
		$a = array();
		foreach ($this->args as $p) {
			$a[] = $p->toPHP();
		}
		$func=$core->expatDict->getFunctionCode($this->name,$a);
		if ($func === false) {
			throw new Exception ("No such function : ".$this->name);
		}
		return $func;
	}
}
?>
