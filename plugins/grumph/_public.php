<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Grumph,
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

//require dirname(__FILE__).'/_widgets.php';

$core->tpl->addBlock('EntryResources',array('tplGrumph','resources'));
$core->tpl->addBlock('ResourcesHeader',array('tplGrumph','resHeader'));
$core->tpl->addBlock('ResourcesFooter',array('tplGrumph','resFooter'));
$core->tpl->addValue('ResTitle',array('tplGrumph','resTitle'));
$core->tpl->addValue('ResURL',array('tplGrumph','resURL'));
$core->tpl->addValue('ResType',array('tplGrumph','resType'));


$core->addBehavior('templateBeforeBlock',array('behaviorsGrumph','templateBeforeBlock'));


class behaviorsGrumph
{
	public static function templateBeforeBlock($core,$b,$attr)
	{
		if ($b == 'Entries') {
			return
			"<?php\n".
			"@\$params['columns'][]='post_res';\n".
			"?>";
		}
	}
}

class tplGrumph
{
	public static function resources($attr,$content)
	{
		$p = '<?php'."\n".
			'$rparams=array();'."\n";
		if (isset($attr['type'])) {
			$temp=explode(',',$attr['type']);
			$types=array();
			foreach ($temp as $type) {
				if (array_search ($type,dcGrumph::$resource_types)) {
					$types[]="'".$type."'";
				}
			}
			$p .= '$rparams["types"]=array('.join(',',$types).');'."\n";
		}
		if (isset($attr['internal'])) {
			$p .= '$rparams["internal"]='.(($attr['internal']==1)?'1':'0').';'."\n";
		}
		if (isset($attr['scope']) && in_array($attr['scope'],array('content','excerpt','both'))) {
			$p .= '$rparams["scope"]="'.$attr['scope'].'";'."\n";
		}
		
		$p .= '$_ctx->res = $core->grumph->getResources($_ctx->posts,$rparams);'."\n".
			'$res_i=0; foreach ($_ctx->res as $res_e): ?>'."\n".
			$content."\n".
			'<?php $res_i++; endforeach; $_ctx->res = null; ?>';
		return $p;
	}

	public static function resHeader($attr,$content) {
		return
		"<?php if (\$res_i == 0) : ?>".
		$content.
		"<?php endif; ?>";
		
	}
	
	public static function resFooter($attr,$content) {
		return
		"<?php if (\$res_i+1 == count(\$_ctx->res)) : ?>".
		$content.
		"<?php endif; ?>";
		
	}
	
	public static function resURL($attr) {
		return '<?php echo $res_e["u"]; ?>';
	}

	public static function resType($attr) {
		return '<?php echo $res_e["t"]; ?>';
	}
	
	public static function resTitle($attr) {
		return '<?php echo $res_e["d"]; ?>';
	}
}

?>
