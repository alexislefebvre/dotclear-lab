<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class soCialMeTemplate
{
	#
	# Commons
	#
	
	protected static function value($v,$a)
	{
		return '<?php echo '.sprintf($GLOBALS['core']->tpl->getFilters($a),$v).'; ?>';
	}
	
	protected static function getOperator($op)
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
	
	public static function SoCialMePreloadBox($attr,$content)
	{
		$forced = !empty($attr['forced']);
		
		$res = '';
		if (!$forced) {
			$res .= "<?php if (\$_ctx->soCialMeRecords->preload) { ?>\n";
		}
		$res .=  
		"<?php if (!isset(\$GLOBALS['soCialMePreloadBoxNumber'])) { \$GLOBALS['soCialMePreloadBoxNumber'] = 0; } \$GLOBALS['soCialMePreloadBoxNumber'] += 1; ?>\n".
		'<div id="social-preloadbox<?php echo $GLOBALS[\'soCialMePreloadBoxNumber\']; ?>"></div>'.
		'<script type="text/javascript">'.
		"\n//<![CDATA[ \n".
		'$(\'#social-preloadbox<?php echo $GLOBALS[\'soCialMePreloadBoxNumber\']; ?>\').hide(); '.
		'$(document).ready(function(){ '.
		'$(\'#social-preloadbox<?php echo $GLOBALS[\'soCialMePreloadBoxNumber\']; ?>\').show().replaceWith($(\''.$content.'\')); '.
		"}); ".
		"\n//]]> \n".
		'</script> ';
		if (!$forced) {
			$res .= "\n<?php } else { ?>".$content."<?php } ?>\n";
		}
		return $res;
	}
	
	#
	# soCialMe Reader page
	#
	
	public static function SoCialMeReaderPageTitle($a)
	{
		return self::value('$_ctx->soCialMeReaderPageTitle',$a);
	}
	
	public static function SoCialMeReaderPageContent($a)
	{
		return self::value('$_ctx->soCialMeReaderPageContent',$a);
	}
	
	
	
	#
	# soCialMe Record
	#
	
	## records ##
	
	public static function SoCialMeRecords($attr,$content)
	{
		return 
		'<?php $socialrecordcountlimit = 1; '.
		'while ($_ctx->soCialMeRecords->fetch()) : '.
		'if ($socialrecordcountlimit <= $_ctx->soCialMeRecordsLimit) : '.
		'?>'.$content.'<?php endif; $socialrecordcountlimit++; endwhile; '.
		'$_ctx->soCialMeRecords = null; $socialrecordcountlimit = 1; ?>';
	}
	
	public static function SoCialMeRecordsHeader($attr,$content)
	{
		return '<?php if ($_ctx->soCialMeRecords->isStart()) : ?>'.$content.'<?php endif; ?>';
	}
	
	public static function SoCialMeRecordsFooter($attr,$content)
	{
		return '<?php if ($_ctx->soCialMeRecords->isEnd() || $socialrecordcountlimit == $_ctx->soCialMeRecordsLimit) : ?>'.$content.'<?php endif; ?>';
	}
	
	public static function SoCialMeRecordsIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		// 0 or 1
		if (isset($attr['has_title'])) {
			$sign = (boolean) $attr['has_title'] ? '' : '!';
			$if[] = $sign.'($_ctx->soCialMeRecordsTitle != "")';
		}
		// 0 or 1
		if (isset($attr['has_record'])) {
			$sign = (boolean) $attr['has_record'] ? '' : '!';
			$if[] = $sign.'($_ctx->soCialMeRecords->count() > 0)';
		}
		// small or normal or ''
		if (isset($attr['use_icon'])) {
			$icon = addslashes(trim($attr['use_icon']));
			if (substr($icon,0,1) == '!') {
				$icon = substr($icon,1);
				$if[] = '($_ctx->soCialMeRecordsIcon != "'.$icon.'")';
			} else {
				$if[] = '($_ctx->soCialMeRecordsIcon == "'.$icon.'")';
			}
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	# Global record options
	// ex: {{tpl:SoCialMeRecordsOptionsIf part="!profil,sharer" thing="Big"}}
	public static function SoCialMeRecordsOptionsIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		foreach(array('part','thing','place') as $option)
		{
			if (!isset($attr[$option])) continue;

			$glue = ' || ';
			$sign = '=';
			if (substr($attr[$option],0,1) == '!') {
				$glue = ' && ';
				$sign = '!';
				$attr[$option] = substr($attr[$option],1);
			}
			$values = explode(',',$attr[$option]);
			
			if (empty($values)) continue;
			
			$rs = array();
			foreach($values as $value)
			{
				$value = addslashes(trim($value));
				if (empty($value)) continue;
				
				$rs[] = '$_ctx->soCialMeRecordsOptions["'.$option.'"] '.$sign.'= "'.$value.'"';
			}
			if (!empty($rs)) {
				$if[] = ' ('.implode($glue,$rs).') ';
			}
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	public static function SoCialMeRecordsTitle($a)
	{
		return self::value('$_ctx->soCialMeRecordsTitle',$a);
	}
	
	public static function SoCialMeRecordsPart($a)
	{
		return self::value('$_ctx->soCialMeRecordsOptions["part"]',$a);
	}
	
	public static function SoCialMeRecordsThing($a)
	{
		return self::value('$_ctx->soCialMeRecordsOptions["thing"]',$a);
	}
	
	public static function SoCialMeRecordsPlace($a)
	{
		return self::value('$_ctx->soCialMeRecordsOption["place"]',$a);
	}
	
	## record ##
	
	# Check if some fields are empty or not
	public static function SoCialMeRecordFieldIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		$record = soCialMeUtils::$record;
		
		foreach($record as $field => $plop)
		{
			if (isset($attr[$field])) {
				$sign = (boolean) $attr[$field] ? ' !' : ' =';
				$if[] = '$_ctx->soCialMeRecords->'.$field.$sign.'= ""';
			}
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	// could be removed!
	public static function SoCialMeRecordIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		// service id
		if (isset($attr['service'])) {
			$service = addslashes(trim($attr['service']));
			if (substr($service,0,1) == '!') {
				$service = substr($service,1);
				$if[] = '($_ctx->soCialMeRecords->service != "'.$service.'")';
			} else {
				$if[] = '($_ctx->soCialMeRecords->service == "'.$service.'")';
			}
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	public static function SoCialMeRecordIfMe($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'me';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->soCialMeRecords->me) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	public static function SoCialMeRecordIfFirst($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'first';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->soCialMeRecords->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	public static function SoCialMeRecordIfOdd($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->soCialMeRecords->index()+1)%2 == 1) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	public static function SoCialMeRecordService($a)
	{
		return self::value('$_ctx->soCialMeRecords->service',$a);
	}
	
	public static function SoCialMeRecordSourceName($a)
	{
		return self::value('$_ctx->soCialMeRecords->source_name',$a);
	}
	
	public static function SoCialMeRecordSourceURL($a)
	{
		return self::value('$_ctx->soCialMeRecords->source_url',$a);
	}
	
	public static function SoCialMeRecordSourceIcon($a)
	{
		return self::value('$_ctx->soCialMeRecords->source_icon',$a);
	}
	
	public static function SoCialMeRecordId($a)
	{
		return self::value('$_ctx->soCialMeRecords->index()',$a);
	}
	
	public static function SoCialMeRecordTitle($a)
	{
		return self::value('$_ctx->soCialMeRecords->title',$a);
	}
	
	public static function SoCialMeRecordIcon($a)
	{
		return self::value('$_ctx->soCialMeRecords->icon',$a);
	}
	
	public static function SoCialMeRecordAvatar($a)
	{
		return self::value('$_ctx->soCialMeRecords->avatar',$a);
	}
	
	public static function SoCialMeRecordExcerpt($a)
	{
		return self::value('$_ctx->soCialMeRecords->excerpt',$a);
	}
	
	public static function SoCialMeRecordContent($a)
	{
		return self::value('$_ctx->soCialMeRecords->content',$a);
	}
	
	public static function SoCialMeRecordURL($a)
	{
		return self::value('$_ctx->soCialMeRecords->url',$a);
	}
	
	public static function SoCialMeRecordDate($attr)
	{
		global $core;
		
		$format = $core->blog->settings->system->date_format;
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $core->tpl->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"dt::rfc822(\$_ctx->soCialMeRecords->date)").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"dt::iso8601(\$_ctx->soCialMeRecords->date)").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"dt::str('".$format."',\$_ctx->soCialMeRecords->date)").'; ?>';
		}
	}
	
	public static function SoCialMeRecordTime($attr)
	{
		global $core;
		
		$format = $core->blog->settings->system->time_format;
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
        
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,"dt::str('".$format."',\$_ctx->soCialMeRecords->date)").'; ?>';
	}
}
?>