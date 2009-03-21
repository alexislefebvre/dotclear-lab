<?php
# ***** BEGIN LICENSE BLOCK *****
# Copyright (c) 2008-2009 Olivier Azeau and contributors. All rights reserved.
#
# This is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# This is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addBlock('WidgetName',array('templateWidgetBlocksAndValues','Name'));
$core->tpl->addBlock('WidgetDescription',array('templateWidgetBlocksAndValues','Description'));

$core->tpl->addBlock('WidgetDefineBlock',array('templateWidgetBlocksAndValues','DefineBlock'));
$core->tpl->addValue('WidgetUseBlock',array('templateWidgetBlocksAndValues','UseBlock'));
$core->tpl->addBlock('WidgetPageTypeIf',array('templateWidgetBlocksAndValues','PageTypeIf'));
$core->tpl->addBlock('WidgetSubstring',array('templateWidgetBlocksAndValues','Substring'));

$core->tpl->addValue('WidgetText',array('templateWidgetBlocksAndValues','Text'));
$core->tpl->addBlock('WidgetCheckboxIf',array('templateWidgetBlocksAndValues','CheckboxIf'));
$core->tpl->addBlock('WidgetComboIf',array('templateWidgetBlocksAndValues','ComboIf'));
$core->tpl->addValue('WidgetCombo',array('templateWidgetBlocksAndValues','Combo'));

define('CRLF',"\r\n");


class templateWidgetBlocksAndValues
{
  // widget display only consists in displaying the corresponding template file
  public static function WidgetCore(&$widget)
  {
    global $core, $_ctx;
    $_ctx->widget = $widget;
    $core->tpl->setPath(array_merge($core->tpl->getPath(),array(dirname(__FILE__).'/default-templates')));
    $code = $core->tpl->getData($widget->id().'.widget.html');
    $_ctx->widget = null;
    return $code;
  }
  
  public static function Name($attr,$content) {
    return '';
  }
  
  public static function Description($attr,$content) {
    return '';
  }

  // Defines a block unique name
  public static function BlockUniqueName($attr) {
    global $_ctx;
    $widget = preg_replace('/\W/', '_', $_ctx->widget->id());
    return 'WidgetBlock_'.$widget.'_'.$attr['name'];
  }

  // Define a block to be reused
  public static function DefineBlock($attr,$content) {
    return
    '<?php function '.self::BlockUniqueName($attr).'() {'.CRLF.
    'global $core, $_ctx;'.CRLF.
    '?>'.CRLF.
    $content.CRLF.
    '<?php } ?>'.CRLF;
  }

  // Use a block
  public static function UseBlock($attr) {
    return '<?php '.self::BlockUniqueName($attr).'(); ?>'.CRLF;
  }

  // Test page type - useful to display a widget on home page only
  public static function PageTypeIf($attr,$content) {
		return
		'<?php if ($core->url->type == "'.addslashes($attr['type']).'") : ?>'.CRLF.
			$content.
		'<?php endif; ?>'.CRLF;
  }

  // Returns a subpart of the content
  public static function Substring($attr,$content) {
		return
		'<?php ob_start(); ?>'.$content.'<?php
    echo text::cutString(strip_tags(ob_get_clean()),'.$attr['length'].'); ?>'.CRLF;
  }
  
  // Widget text field
  public static function Text($attr) {
    return '<?php print html::escapeHTML($_ctx->widget->'.$attr['name'].'); ?>'.CRLF;
  }
  
  // Widget checkbox field
  public static function CheckboxIf($attr,$content) {
    $verif = (isset($attr['value']) && !$attr['value']) ? '!' : '';
		return
		'<?php if ('.$verif.'$_ctx->widget->'.$attr['name'].') : ?>'.CRLF.
			$content.
		'<?php endif; ?>'.CRLF;
  }
  
  // Widget combo field
  public static function Combo($attr) {
    return '<?php print html::escapeHTML($_ctx->widget->'.$attr['name'].'); ?>'.CRLF;
  }
  
  // Widget combo field
  public static function ComboIf($attr,$content) {
		return
		'<?php if ($_ctx->widget->'.$attr['name'].' == "'.addslashes($attr['value']).'") : ?>'.CRLF.
			$content.
		'<?php endif; ?>'.CRLF;
  }
}
?>