<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2010 Bruno Hondelatte, and contributors.
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Mymeta plugin for DC2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
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

$core->tpl->addValue('MetaType',array('tplMyMeta','MetaType'));
$core->tpl->addValue('MyMetaTypePrompt',array('tplMyMeta','MyMetaTypePrompt'));
$core->tpl->addValue('EntryMyMetaValue',array('tplMyMeta','EntryMyMetaValue'));
$core->tpl->addValue('MyMetaValue',array('tplMyMeta','MyMetaValue'));
$core->tpl->addValue('MyMetaURL',array('tplMyMeta','MyMetaURL'));
$core->tpl->addBlock('MyMetaIf',array('tplMyMeta','MyMetaIf'));
$core->tpl->addBlock('MyMetaData',array('tplMyMeta','MyMetaData'));

$core->addBehavior('templateBeforeBlock',array('behaviorsMymeta','templateBeforeBlock'));
$core->addBehavior('publicBeforeDocument',array('behaviorsMymeta','addTplPath'));

$core->mymeta = new myMeta($core);


class behaviorsMymeta
{
	public static function addTplPath($core)
	{
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
	}
	
	public static function templateBeforeBlock($core,$b,$attr)
	{
			/* tpl:Entries extra attributes :
				<tpl:Entries mymetaid="<id>" mymetavalue="value1,value2,value3"> 
					selects mymeta entries having mymetaid <id> with values value1, value2 or value3
				<tpl:Entries mymetaid="<id>" mymetavalue="!value1,value2,value3"> 
					selects mymeta entries having mymetaid <id> with any value but value1, value2 or value3
				<tpl:Entries mymetaid="<id>">
					selects mymeta entries having mymetaid <id> set to any value
				<tpl:Entries mymetaid="!<id>">
					selects mymeta entries having mymetaid <id> not set
			*/
			if ($b != 'Entries' && $b != 'Comments')
				return;
			if (!isset($attr['mymetaid'])) {
				if (empty($attr['no_context'])) {
					return
					'<?php if ($_ctx->exists("mymeta")) { '.
					"\$params['sql'] = str_replace(\"META.meta_type = 'tag'\",\"META.meta_type = '\".\$core->con->escape(\$_ctx->mymeta->id).\"'\", \$params['sql']);\n".
					"} ?>\n";
				  } else {
					return;
				}
			}
			$metaid = $core->con->escape($attr['mymetaid']);
			if (isset($attr['mymetavalue'])) {
				$values=$attr['mymetavalue'];
				$in_expr=" in ";
				if (substr($values,0,1)=="!") {
					$in_expr=" not in ";
					$values=substr($values,1);
				}
				$cond=array();
				foreach (explode(',',$values) as $expr) {
					$cond[]= "'".$core->con->escape($expr)."'";
				}
				return
				"<?php\n".
				"@\$params['from'] .= ', '.\$core->prefix.'meta META ';\n".
				"@\$params['sql'] .= 'AND META.post_id = P.post_id ';\n".
				"\$params['sql'] .= \"AND META.meta_type = '".$metaid."' \";\n".
				"\$params['sql'] .= \"AND META.meta_id ".$in_expr." (".join(',',$cond).") \";\n".
				"?>\n";
				
			} else {
				$in_expr=" in ";
				if (substr($metaid,0,1)=="!") {
					$in_expr=" not in ";
					$metaid=substr($metaid,1);
				}
				return
				"<?php\n".
				"@\$params['sql'] .= \"AND P.post_id ".$in_expr.
					"(SELECT META.post_id from \".\$core->prefix.\"meta META where META.meta_type = '".$metaid."') \";\n".
				"?>\n";
			}

	}
}

class tplMyMeta
{

	public static function getCommonMyMeta ($attr) {
		if (isset ($attr['type']))
			$attr['id'] = $attr['type'];
		if (isset ($attr['id']) && preg_match('/[a-zA-Z0-9-_]+/',$attr['id'])) {
			return '<?php'."\n".
				'$_ctx->mymeta = $core->mymeta->getByID(\''.$attr['id'].'\');'."\n".
				'%s'."\n".
				'$_ctx->mymeta = null;'."\n".'?>';
		}
		return '<?php %s ?>';
	}
	protected static function attr2str($attr) {
		$filter = array("id","type");
		$a = array();
		foreach ($attr as $k=>$v) {
			if (!in_array($k,$filter)) {
				$a[]="'".addslashes($k)."' =>'".addslashes($v)."'";
			}
		}
		return "array(".join(',',$a).")";
		
	}

	public static function getOperator($op)
	{
		switch (strtolower($op))
		{
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}

	public static function MyMetaURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("mymeta").'.
		'"/".$_ctx->mymeta->id."/".rawurlencode($_ctx->meta->meta_id)').'; ?>';
	}

	public static function MetaType($attr) {
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->meta->meta_type').'; ?>';
	}

	public static function MyMetaTypePrompt($attr) {
		$f = tplMyMeta::getCommonMyMeta($attr);
		$res = 'if ($_ctx->mymeta != null && $_ctx->mymeta->enabled) echo $_ctx->mymeta->prompt;'."\n";
		return sprintf($f,$res);
	}

	public static function EntryMyMetaValue($attr) {
		$f = tplMyMeta::getCommonMyMeta($attr);
		
		$res =
		'if ($_ctx->mymeta != null && $_ctx->mymeta->enabled)'."\n".
		'echo $_ctx->mymeta->getValue($core->mymeta->dcmeta->getMetaStr($_ctx->posts->post_meta,$_ctx->mymeta->id),'.tplMyMeta::attr2str($attr).'); '."\n";
		return sprintf($f,$res);
	}

	public static function MyMetaValue($attr) {
		$f = tplMyMeta::getCommonMyMeta($attr);
		
		$res =
		'if ($_ctx->mymeta != null && $_ctx->mymeta->enabled) {'."\n".
		'echo $_ctx->mymeta->getValue($_ctx->meta->meta_id,'.tplMyMeta::attr2str($attr).'); '."\n".
		"}\n";
		return sprintf($f,$res);
	}

	public static function MyMetaIf($attr,$content)
	{
		if (isset($attr['type']))
			$type = addslashes($attr['type']);
		else
			return "";
		$if = array();
		$operator = isset($attr['operator']) ? tplMyMeta::getOperator($attr['operator']) : '&&';
		if (isset($attr['defined'])) {
			$sign = ($attr['defined']=="true") ? '!' : '';
			$if[] = $sign.'empty($value)';
		}
		if (isset($attr['value'])) {
			$value = $attr['value'];
			if (substr($value,1,1)=='!')
				$if[] = "\$value !='".substr($value,1)."'";
			else
				$if[] = "\$value =='".$value."'";
		}
		$res =
		"<?php\n".
		"if (\$core->mymeta->isMetaEnabled('".$type."')) :\n".
		"  \$value=\$core->mymeta->dcmeta->getMetaStr(\$_ctx->posts->post_meta,'".$type."'); ".
		"  if(".implode(" ".$operator." ",$if).") : ?>".
		$content.
		"  <?php endif; ".
		"endif; ?>";
		return $res;
	}
	
	public static function MyMetaData($attr,$content)
	{
		$limit = isset($attr['limit']) ? (integer) $attr['limit'] : 'null';
		
		$sortby = 'meta_id_lower';
		if (isset($attr['sortby']) && $attr['sortby'] == 'count') {
			$sortby = 'count';
		}
		
		$order = 'asc';
		if (isset($attr['order']) && $attr['order'] == 'desc') {
			$order = 'desc';
		}
		
		$res =
		"<?php\n".
		"\$_ctx->meta = \$core->mymeta->dcmeta->getMeta(\$_ctx->mymeta->id,".$limit."); ".
		"\$_ctx->meta->sort('".$sortby."','".$order."'); ".
		'?>';
		
		$res .=
		'<?php while ($_ctx->meta->fetch()) : '."\n".
		'$_ctx->mymeta = $core->mymeta->getByID($_ctx->meta->meta_type); ?>'."\n".
		$content.'<?php endwhile; '.
		'$_ctx->mymeta = null; $_ctx->meta = null; ?>';
		
		return $res;
	}
}

class urlMymeta extends dcUrlHandlers
{
	public static function tag($args)
	{
		$n = self::getPageNumber($args);
		if ($args == '' && !$n)
		{
			self::p404();
		}
		else
		{
			$core =& $GLOBALS['core'];
			$_ctx =& $GLOBALS['_ctx'];
			if ($n) {
				$GLOBALS['_page_number'] = $n;
			}
			$values = explode('/',$args);
			$mymeta = $core->mymeta->getByID($values[0]);
			if ($mymeta==null || !$mymeta->enabled) {
				self::p404();
				return;
			}
			$_ctx->mymeta=$mymeta;

			if (sizeof($values)==1) {
				$tpl = ($mymeta->tpl_list=='')?'mymetas.html':$mymeta->tpl_list;
				if ($mymeta->url_list_enabled && $core->tpl->getFilePath($tpl)) {
					self::serveDocument($tpl);
				} else {
					self::p404();
				}
			} else {
				$mymeta_value=$values[1];
				$_ctx->meta = $core->mymeta->dcmeta->getMeta($mymeta->id,null,$mymeta_value);
				$tpl = ($mymeta->tpl_single=='')?'mymeta.html':$mymeta->tpl_single;
				if (!$_ctx->meta->isEmpty() && $mymeta->url_single_enabled && $core->tpl->getFilePath($tpl)) {
					self::serveDocument($tpl);
				} else {
					self::p404();
					return;
				}
			}
		}
	}
}

?>