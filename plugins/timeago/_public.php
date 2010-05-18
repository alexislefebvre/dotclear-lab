<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of TimeAgo,
# a plugin for DotClear2.
#
# Copyright (c) 2010 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------


$core->addBehavior('templateBeforeValue',array('TimeAgoBehaviors','templateBeforeValue'));
$core->addBehavior('templateAfterValue',array('TimeAgoBehaviors','templateAfterValue'));
TimeAgoTpl::init();

class TimeAgoTpl {

	public static $l10n;

	public static function init() {
		self::$l10n= array(
			'second' => __('less than one second ago'),
			'seconds' => __('%s seconds ago'),
			'minute' => __('less than one minute ago'),
			'minutes' => __('%s minutes ago'),
			'hour' => __('less than one hour ago'),
			'hours' => __('%d hours ago'),
			'day' => __('less than one day ago'),
			'days' => __('%d days ago'),
			'month' => __('less than one month ago'),
			'months' => __('%d months ago'),
			'year' => __('less than one year ago'),
			'years' => __('more than %d years ago'));
	}
			
	public static function getTimeAgo ($theTime,$tz,$stopat='',$l10n=null) {
		if (!is_array($l10n)) {
			$l10n= TimeAgoTpl::$l10n;
		}
		$delta = abs(time()+dt::getTimeOffset($tz)-strtotime($theTime));
		if ($delta < 60 || $stopat=='second') {
			$unit='second';
		} elseif ($delta < 3600 || $stopat=='minute') {
			$delta = round($delta / 60);
			$unit='minute';
		} elseif ($delta < 86400 || $stopat=='hour') { // 3600*24
			$delta = round($delta / 3600);
			$unit='hour';
		} elseif ($delta < 2678400 || $stopat=='day') { // 3600*24*31, less than 1 month
			$delta = round($delta / 86400);
			$unit='day';
		} elseif ($delta < 31536000 || $stopat=='month') { // 3600*24*365, less than 1 year
			$delta = round($delta / 2678400);
			$unit='month';
		} else {
			$delta = round($delta / 31536000);
			$unit='year';
		}
		
		$plur = ($delta==1)?'':'s';
		return sprintf($l10n[$unit.$plur],$delta);
	}

	public static function getElapsedCodeCall($attr,$dateField) {

		// Parse l10n attributes, if specified
		$inter = array_intersect_key($attr->getArrayCopy(),TimeAgoTpl::$l10n);
		if (sizeof($inter)==0) {
			$l10nArgs = '';
		} else {
			$l10n = array_replace(TimeAgoTpl::$l10n,$inter);
			array_walk($l10n,create_function('&$v, $k', '$v = addslashes($v);'));
			$args = array();
			foreach ($l10n as $k => $v) {
				$args[] = "'".$k."' => '".$v."'";
			}
			$l10nArgs = ',array('.join(',',$args).')';
		}
		$stopat="''";
		if (isset($attr['stopat']) && 
			in_array(
				$attr['stopat'],
				array('second','minute','hour','day','month'))) {
			$stopat = "'".$attr['stopat']."'";
		}
		return "<?php echo TimeAgoTpl::getTimeAgo(".$dateField.','.$stopat.$l10nArgs.") ?>\n";
	}
			
	public static function BlogUpdateDate($attr)
	{
		return TimeAgoTpl::getElapsedCodeCall($attr,'$core->blog->upddt,$core->blog->settings->system->blog_timezone');
	}

	public static function EntryDate($attr)
	{
		return TimeAgoTpl::getElapsedCodeCall($attr,'$_ctx->posts->post_dt,$_ctx->posts->post_tz');
	}

	public static function EntryTime($attr)
	{
		return TimeAgoTpl::getElapsedCodeCall($attr,'$_ctx->posts->post_dt,$_ctx->comments->post_tz');
	}

	public static function CommentDate($attr)
	{
		return TimeAgoTpl::getElapsedCodeCall($attr,'$_ctx->comments->comment_dt,$_ctx->comments->comment_tz');
	}
	public static function CommentTime($attr)
	{
		return TimeAgoTpl::getElapsedCodeCall($attr,'$_ctx->comments->comment_dt,$_ctx->comments->comment_tz');
	}
	public static function PingDate($attr)
	{
		return TimeAgoTpl::getElapsedCodeCall($attr,'$_ctx->pings->comment_dt,$_ctx->comments->comment_tz');
	}
	public static function PingTime($attr)
	{
		return TimeAgoTpl::getElapsedCodeCall($attr,'$_ctx->pings->comment_dt,$_ctx->comments->comment_tz');
	}
}

class TimeAgoBehaviors {
	public static $overridenValues=array(
		'BlogUpdateDate','CommentDate','CommentTime',
		'EntryDate','EntryTime','PingDate','PingTime');

	public static function templateBeforeValue($core,$tag,$attr) {
		if (isset($attr['format']) && $attr['format']=='elapsed') {
			if (in_array($tag,TimeAgoBehaviors::$overridenValues)) {
				$core->tpl->addValue($tag,array('TimeAgoTpl',$tag));
			}
		}
	}

	public static function templateAfterValue($core,$tag,$attr) {
		if (isset($attr['format']) && $attr['format']=='elapsed') {
			if (in_array($tag,TimeAgoBehaviors::$overridenValues)) {
				$core->tpl->addValue($tag,array($core->tpl,$tag));
			}
		}
	}
}

?>
