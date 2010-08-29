<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of browsingHistory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->blog->settings->addNamespace('browsingHistory');
if (!$core->blog->settings->browsingHistory->mem_time) {
	$core->blog->settings->browsingHistory->put('mem_time',604800,'integer');
}
if (!$core->blog->settings->browsingHistory->lastn) {
	$core->blog->settings->browsingHistory->put('lastn',10,'integer');
}
if (null === $core->blog->settings->browsingHistory->more_css) {
	$core->blog->settings->browsingHistory->put('more_css','','string');
}
if (null === $core->blog->settings->browsingHistory->on_footer) {
	$core->blog->settings->browsingHistory->put('on_footer',false,'boolean');
}

$core->tpl->setPath($core->tpl->getPath(),dirname(__FILE__).'/default-templates/');
	
require_once dirname(__FILE__).'/_widgets.php';

$core->addBehavior('publicHeadContent',array('publicBrowsingHistory','publicHeadContent'));
$core->addBehavior('publicFooterContent',array('publicBrowsingHistory','publicFooterContent'));
$core->addBehavior('publicAfterDocument',array('publicBrowsingHistory','publicAfterDocument'));

$core->tpl->AddBlock('BrowsingHistories',array('tplBrowsingHistory','BrowsingHistories'));
$core->tpl->AddBlock('BrowsingHistoriesHeader',array('tplBrowsingHistory','BrowsingHistoriesHeader'));
$core->tpl->AddBlock('BrowsingHistoriesFooter',array('tplBrowsingHistory','BrowsingHistoriesFooter'));
$core->tpl->AddBlock('BrowsingHistoryIf',array('tplBrowsingHistory','BrowsingHistoryIf'));
$core->tpl->AddBlock('BrowsingHistoryIfFirst',array('tplBrowsingHistory','BrowsingHistoryIfFirst'));
$core->tpl->AddBlock('BrowsingHistoryIfOdd',array('tplBrowsingHistory','BrowsingHistoryIfOdd'));
$core->tpl->AddValue('BrowsingHistoryURL',array('tplBrowsingHistory','BrowsingHistoryURL'));
$core->tpl->AddValue('BrowsingHistoryTitle',array('tplBrowsingHistory','BrowsingHistoryTitle'));
$core->tpl->AddValue('BrowsingHistoryText',array('tplBrowsingHistory','BrowsingHistoryText'));
$core->tpl->AddValue('BrowsingHistoryFirstImage',array('tplBrowsingHistory','BrowsingHistoryFirstImage'));
$core->tpl->AddValue('BrowsingHistoryDate',array('tplBrowsingHistory','BrowsingHistoryDate'));
$core->tpl->AddValue('BrowsingHistoryTime',array('tplBrowsingHistory','BrowsingHistoryTime'));

class publicBrowsingHistory
{
	public static function publicAfterDocument($core)
	{
		global $_ctx;
		$bh = new browsingHistory($core);
		
		if ($_ctx->exists('posts'))
		{
			$bh->addHistory('post',$_ctx->posts->post_id);
		}
		
		if ($core->url->type == 'tag' && $_ctx->exists('meta'))
		{
			$bh->addHistory('tag',$_ctx->meta->meta_id);
		}
	}
	
	public static function publicHeadContent($core)
	{
		$more_css = $core->blog->settings->browsingHistory->more_css;
		if (!empty($more_css)) {
			echo 
			"\n<!-- style for plugin browsingHistory --> \n".
			'<style type="text/css">'."\n".
			html::escapeHTML($more_css)."\n".
			"\n</style>\n";
		}
	}
	
	public static function publicFooterContent($core)
	{
		if (!$core->blog->settings->browsingHistory->on_footer) return;
		
		echo $core->tpl->getData('browsinghistory.html');
	}
}

class tplBrowsingHistory
{
	public static function BrowsingHistories($a,$c)
	{
		$p = '';
		
		$lastn = -1;
		if (isset($a['lastn'])) {
			$lastn = abs((integer) $a['lastn'])+0;
		}		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "\$params['limit'] = \$core->blog->settings->browsingHistory->lastn; \n";
		}
		
		if (!empty($a['type'])) {
			$p .= "\$params['type'] = preg_split('/\s*,\s*/','".addslashes($a['type'])."',-1,PREG_SPLIT_NO_EMPTY);\n";
		}
		
		return 
		"<?php \n".
		"\$bh = new browsingHistory(\$core); \n".
		"\$params = array(); \n".
		$p.
		"\$_ctx->browsingHistories_params = \$params; \n".
		"\$_ctx->browsingHistories = \$bh->getHistoryRecords(\$params); unset(\$params,\$bh); \n".
		"?> \n".
		"<?php while (\$_ctx->browsingHistories->fetch()) : ?>".$c."<?php endwhile; ".
		"\$_ctx->browsingHistories = null; \$_ctx->browsingHistories_params = null; ?>";
	}
	
	public static function BrowsingHistoriesHeader($a,$c)
	{
		return
		"<?php if (\$_ctx->browsingHistories->isStart()) : ?>".
		$c.
		"<?php endif; ?>";
	}
	
	public static function BrowsingHistoriesFooter($a,$c)
	{
		return
		"<?php if (\$_ctx->browsingHistories->isEnd()) : ?>".
		$c.
		"<?php endif; ?>";
	}
	
	public static function BrowsingHistoryIf($a,$c)
	{
		$if = array();
		$operator = isset($a['operator']) ? $GLOBALS['core']->tpl->getOperator($a['operator']) : '&&';
		
		if (isset($a['type'])) {
			$type = trim($a['type']);
			$type = !empty($type) ? $type : 'post';
			$if[] = '$_ctx->browsingHistories->type == "'.addslashes($type).'"';
		}
		
		if (isset($a['first'])) {
			$sign = (boolean) $a['first'] ? '=' : '!';
			$if[] = '$_ctx->browsingHistories->index() '.$sign.'= 0';
		}
		
		if (isset($a['odd'])) {
			$sign = (boolean) $a['odd'] ? '=' : '!';
			$if[] = '($_ctx->browsingHistories->index()+1)%2 '.$sign.'= 1';
		}
		
		if (isset($a['has_text'])) {
			$sign = (boolean) $a['has_text'] ? '' : '!';
			$if[] = $sign.'$_ctx->browsingHistories->text';
		}
		
		if (isset($a['has_image'])) {
			$sign = (boolean) $a['has_image'] ? '' : '!';
			$if[] = $sign.'$_ctx->browsingHistories->firstimage';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$c.'<?php endif; ?>';
		} else {
			return $c;
		}
	}
	public function BrowsingHistoryIfFirst($a)
	{
		$ret = isset($a['return']) ? $a['return'] : 'first';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->browsingHistories->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	public function BrowsingHistoryIfOdd($a)
	{
		$ret = isset($a['return']) ? $a['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->browsingHistories->index()+1)%2 == 1) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	public static function BrowsingHistoryURL($a)
	{
		$f = $GLOBALS['core']->tpl->getFilters($a);
		return '<?php echo '.sprintf($f,'$_ctx->browsingHistories->url').'; ?>';
	}
	
	public static function BrowsingHistoryTitle($a)
	{
		$f = $GLOBALS['core']->tpl->getFilters($a);
		return '<?php echo '.sprintf($f,'$_ctx->browsingHistories->title').'; ?>';
	}
	
	public static function BrowsingHistoryText($a)
	{
		$f = $GLOBALS['core']->tpl->getFilters($a);
		return '<?php echo '.sprintf($f,'$_ctx->browsingHistories->text').'; ?>';
	}
	
	public static function BrowsingHistoryFirstImage($a) // not the best way...
	{
		$f = $GLOBALS['core']->tpl->getFilters($a);
		return '<?php echo '.sprintf($f,'$_ctx->browsingHistories->firstimage').'; ?>';
	}
	
	public function BrowsingHistoryDate($a)
	{
		$format = '';
		if (!empty($a['format'])) {
			$format = addslashes($a['format']);
		}
		$iso8601 = !empty($a['iso8601']);
		$rfc822 = !empty($a['rfc822']);
		
		$f = $GLOBALS['core']->tpl->getFilters($a);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"\$_ctx->browsingHistories->getRFC822Date()").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"\$_ctx->browsingHistories->getISO8601Date()").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"\$_ctx->browsingHistories->getDate('".$format."')").'; ?>';
		}
	}
	
	public function BrowsingHistoryTime($a)
	{
		$format = '';
		if (!empty($a['format'])) {
			$format = addslashes($a['format']);
		}
        
		$f = $GLOBALS['core']->tpl->getFilters($a);
		return '<?php echo '.sprintf($f,"\$_ctx->browsingHistories->getTime('".$format."')").'; ?>';
	}
}
?>