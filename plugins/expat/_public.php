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

$core->addBehavior('templateBeforeBlock',array('expatBehaviors','templateBeforeBlock'));
$core->addBehavior('templateAfterBlock',array('expatBehaviors','templateAfterBlock'));

$prefix='';

$core->tpl->addBlock($prefix.'If',array('expatTpl','expatIf'));
$core->tpl->addValue($prefix.'Set',array('expatTpl','expatSet'));
$core->tpl->addValue($prefix.'Echo',array('expatTpl','expatEcho'));

class expatTpl {

	public static function compileExpression ($expr,$default_object=null) {
		$core =& $GLOBALS['core'];
		if (!isset($core->expat))
			$core->expat = new expatParser($core);

		eaVariable::SetDefaultObject($default_object);
		$compiled_expr = $GLOBALS['core']->expat->parse($expr);
		return $compiled_expr->toPHP();
	}

	public static function compileIfExpression ($expr,$content,$default_object=null) {
		try {
			$compiled_expr = self::compileExpression($expr,$default_object);
			return "<?php if(".$compiled_expr."):?>\n".$content."<?php endif;?>\n";
		} catch (Exception $e) {
			return "<!-- expat Compilation error : ".$e->getMessage()." -->";
		}
		
	}
	public static function EntryIf($attr,$content) {
		return expatTpl::compileIfExpression($attr['expr'],$content,'entry');
	}

	public static function CategoryIf($attr,$content) {
		return expatTpl::compileIfExpression($attr['expr'],$content,'category');
	}
	
	public static function expatIf($attr,$content) {
		if (!isset($attr['expr']))
			return '';
		return expatTpl::compileIfExpression($attr['expr'],$content,null);
	}

	public static function expatSet($attr) {
		if (!isset($attr['expr']) && !isset($attr['var']))
			return '';
		// Add some little strength inside variable names
		if (!preg_match('#[a-zA-Z][a-zA-Z0-9_]*#',$attr['var']))
			return '';
		try {
			$compiled_expr = self::compileExpression($attr['expr']);
			return "<?php \$my['".$attr['var']."'] = ".$compiled_expr.";?>\n";
		} catch (Exception $e) {
			return "<!-- expat Compilation error : ".$e->getMessage()." -->";
		}
	}

	public static function expatEcho($attr) {
		if (!isset($attr['expr']) && !isset($attr['var']))
			return '';
		try {
			$compiled_expr = self::compileExpression($attr['expr']);
			return "<?php echo ".$compiled_expr.";?>\n";
		} catch (Exception $e) {
			return "<!-- expat Compilation error : ".$e->getMessage()." -->";
		}
	}
}

class expatBehaviors {

	// List of tpl tags to override
	public static $overridenValues=array(
		'EntryIf','CategoryIf');

	public static function templateBeforeBlock($core,$tag,$attr) {
		if (isset($attr['expr'])) {
			if (in_array($tag,expatBehaviors::$overridenValues)) {
				$core->tpl->addBlock($tag,array('expatTpl',$tag));
			}
		}
	}

	public static function templateAfterBlock($core,$tag,$attr) {
		if (isset($attr['expr'])) {
			if (in_array($tag,expatBehaviors::$overridenValues)) {
				$core->tpl->addBlock($tag,array($core->tpl,$tag));
			}
		}
	}
}

?>
