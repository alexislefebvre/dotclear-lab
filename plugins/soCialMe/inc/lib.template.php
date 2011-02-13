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
	
	#
	# soCialMe Reader
	#
	
	## records ##
	
	public static function SoCialMeReaders($attr,$content)
	{
		return 
		'<?php $socialreadercountlimit = 1; '.
		'while ($_ctx->soCialMeReaders->fetch()) : '.
		'if ($socialreadercountlimit <= $_ctx->soCialMeReadersLimit) : '.
		'?>'.$content.'<?php endif; $socialreadercountlimit++; endwhile; '.
		'$_ctx->soCialMeReaders = null; $socialreadercountlimit = 1; ?>';
	}
	
	public static function SoCialMeReadersHeader($attr,$content)
	{
		return '<?php if ($_ctx->soCialMeReaders->isStart()) : ?>'.$content.'<?php endif; ?>';
	}
	
	public static function SoCialMeReadersFooter($attr,$content)
	{
		return '<?php if ($_ctx->soCialMeReaders->isEnd() || $socialreadercountlimit == $_ctx->soCialMeReadersLimit) : ?>'.$content.'<?php endif; ?>';
	}
	
	public static function SoCialMeReadersIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		// 0 or 1
		if (isset($attr['has_title'])) {
			$sign = (boolean) $attr['has_title'] ? '' : '!';
			$if[] = $sign.'($_ctx->soCialMeReadersTitle != "")';
		}
		// 0 or 1
		if (isset($attr['has_record'])) {
			$sign = (boolean) $attr['has_record'] ? '' : '!';
			$if[] = $sign.'($_ctx->soCialMeReaders->count() > 0)';
		}
		// small or normal or ''
		if (isset($attr['use_icon'])) {
			$icon = addslashes(trim($attr['use_icon']));
			if (substr($icon,0,1) == '!') {
				$icon = substr($icon,1);
				$if[] = '($_ctx->soCialMeReadersIcon != "'.$icon.'")';
			} else {
				$if[] = '($_ctx->soCialMeReadersIcon == "'.$icon.'")';
			}
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	public static function SoCialMeReadersTitle($a)
	{
		return self::value('$_ctx->soCialMeReadersTitle',$a);
	}
	
	## record ##
	
	public static function SoCialMeReaderIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';
		
		// service id
		if (isset($attr['service'])) {
			$service = addslashes(trim($attr['service']));
			if (substr($service,0,1) == '!') {
				$service = substr($service,1);
				$if[] = '($_ctx->soCialMeReaders->service != "'.$service.'")';
			} else {
				$if[] = '($_ctx->soCialMeReaders->service == "'.$service.'")';
			}
		}
		// 0 or 1
		if (isset($attr['has_small_icon'])) {
			$sign = (boolean) $attr['has_small_icon'] ? '' : '!';
			$if[] = $sign.'($_ctx->soCialMeReaders->icon != "")';
		}
		// 0 or 1
		if (isset($attr['has_big_icon'])) {
			$sign = (boolean) $attr['has_big_icon'] ? '' : '!';
			$if[] = $sign.'($_ctx->soCialMeReaders->avatar != "")';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	public static function SoCialMeReaderIfMe($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'me';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->soCialMeReaders->me) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	public static function SoCialMeReaderIfFirst($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'first';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->soCialMeReaders->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	public static function SoCialMeReaderIfOdd($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->soCialMeReaders->index()+1)%2 == 1) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	public static function SoCialMeReaderService($a)
	{
		return self::value('$_ctx->soCialMeReaders->service',$a);
	}
	
	public static function SoCialMeReaderSourceName($a)
	{
		return self::value('$_ctx->soCialMeReaders->source_name',$a);
	}
	
	public static function SoCialMeReaderSourceURL($a)
	{
		return self::value('$_ctx->soCialMeReaders->source_url',$a);
	}
	
	public static function SoCialMeReaderSourceIcon($a)
	{
		return self::value('$_ctx->soCialMeReaders->source_icon',$a);
	}
	
	public static function SoCialMeReaderId($a)
	{
		return self::value('$_ctx->soCialMeReaders->index()',$a);
	}
	
	public static function SoCialMeReaderTitle($a)
	{
		return self::value('$_ctx->soCialMeReaders->title',$a);
	}
	
	public static function SoCialMeReaderIcon($a)
	{
		return self::value('$_ctx->soCialMeReaders->icon',$a);
	}
	
	public static function SoCialMeReaderAvatar($a)
	{
		return self::value('$_ctx->soCialMeReaders->avatar',$a);
	}
	
	public static function SoCialMeReaderContent($a)
	{
		return self::value('$_ctx->soCialMeReaders->content',$a);
	}
	
	public static function SoCialMeReaderURL($a)
	{
		return self::value('$_ctx->soCialMeReaders->url',$a);
	}
	
	public static function SoCialMeReaderDate($attr)
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
			return '<?php echo '.sprintf($f,"dt::rfc822(\$_ctx->soCialMeReaders->date)").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"dt::iso8601(\$_ctx->soCialMeReaders->date)").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"dt::str('".$format."',\$_ctx->soCialMeReaders->date)").'; ?>';
		}
	}
	
	public static function SoCialMeReaderTime($attr)
	{
		global $core;
		
		$format = $core->blog->settings->system->time_format;
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
        
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,"dt::str('".$format."',\$_ctx->soCialMeReaders->date)").'; ?>';
	}
}
?>