<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# Localized string we find in template
__('Event');
__('Events');
__('Dates of events');
__('all');
__('ongoing');
__('outgoing');
__('notstarted');
__('scheduled');
__('started');
__('notfinished');
__('finished');
__('From %S to %E');

# Load _wigdets.php
require dirname(__FILE__).'/_widgets.php';

# Public urls, blocks, values, behaviors...
if ($core->blog->settings->eventdata_active) {

	$core->addBehavior('publicHeadContent',
		array('eventdataPublic','publicHeadContent'));
	$core->addBehavior('publicBeforeDocument',
		array('eventdataPublic','publicBeforeDocument'));
	$core->addBehavior('tplBeforeData',
		array('eventdataPublic','tplBeforeData'));

	if (!$core->blog->settings->eventdata_tpl_dis_bhv) {

		$core->addBehavior('publicEntryBeforeContent',
			array('eventdataPublic','publicEntryBeforeContent'));
		$core->addBehavior('publicEntryAfterContent',
			array('eventdataPublic','publicEntryAfterContent'));
	}

	if (!$core->tpl->valueExists('EntryUpdateDate')) {

		$core->tpl->addValue('EntryUpdateDate',
			array('eventdataPublic', 'EntryUpdateDate'));
	}

	$core->tpl->addBlock('EventdataEntries',
		array('eventdataPublic','EventdataEntries'));
	$core->tpl->addBlock('EventdataPagination',
		array('eventdataPublic','EventdataPagination'));
	$core->tpl->addValue('EventdataPageURL',
		array('eventdataPublic','EventdataPageURL'));
	$core->tpl->addValue('EventdataPageTitle',
		array('eventdataPublic','EventdataPageTitle'));
	$core->tpl->addValue('EventdataPageDescription',
		array('eventdataPublic','EventdataPageDescription'));
	$core->tpl->addValue('EventdataPageNav',
		array('eventdataPublic','EventdataPageNav'));

	$core->tpl->addBlock('EntryEventdataDates'
		,array('eventdataPublic','EntryEventdataDates'));
	$core->tpl->addBlock('EventdataDatesIf',
		array('eventdataPublic','EventdataDatesIf'));
	$core->tpl->addBlock('EventdataDatesHeader',
		array('eventdataPublic','EventdataDatesHeader'));
	$core->tpl->addBlock('EventdataDatesFooter',
		array('eventdataPublic','EventdataDatesFooter'));
	$core->tpl->addValue('EventdataFullDate',
		array('eventdataPublic','EventdataFullDate'));
	$core->tpl->addValue('EventdataStartDate',
		array('eventdataPublic','EventdataStartDate'));
	$core->tpl->addValue('EventdataStartTime',
		array('eventdataPublic','EventdataStartTime'));
	$core->tpl->addValue('EventdataEndDate',
		array('eventdataPublic','EventdataEndDate'));
	$core->tpl->addValue('EventdataEndTime',
		array('eventdataPublic','EventdataEndTime'));
	$core->tpl->addValue('EventdataDuration',
		array('eventdataPublic','EventdataDuration'));
	$core->tpl->addValue('EventdataPeriod',
		array('eventdataPublic','EventdataPeriod'));
	$core->tpl->addValue('EventdataLocation',
		array('eventdataPublic','EventdataLocation'));

	$core->tpl->addValue('EventdataThemeURL',
		array('eventdataPublic','EventdataThemeURL'));
	$core->tpl->addValue('EventdataFeedURL',
		array('eventdataPublic','EventdataFeedURL'));

# Hide public block
} else {
	$core->tpl->addBlock('EventdataEntries',array('eventdataPublic','disable'));
	$core->tpl->addBlock('EventdataPagination',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataPageURL',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataPageTitle',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataPageDescription',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataPageNav',array('eventdataPublic','disable'));

	$core->tpl->addBlock('EntryEventdataDates',array('eventdataPublic','disable'));
	$core->tpl->addBlock('EventdataDatesHeader',array('eventdataPublic','disable'));
	$core->tpl->addBlock('EventdataDatesFooter',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataFullDate',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataStartDate',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataStartTime',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataEndDate',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataEndTime',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataDuration',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataPeriod',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataLocation',array('eventdataPublic','disable'));

	$core->tpl->addValue('EventdataThemeURL',array('eventdataPublic','disable'));
	$core->tpl->addValue('EventdataFeedURL',array('eventdataPublic','disable'));
}

class eventdataPublic extends dcUrlHandlers
{
	private static function tpl_root()
	{
		$a = '/eventdata/default-templates/eventdata-';
		$a = explode(PATH_SEPARATOR, DC_PLUGINS_ROOT.$a);
		$a = array_pop($a);
		return $a;
	}

	public static function disable($a,$b=null)
	{
		return '';
	}

	# return tpl path if exist
	private static function eventdataTpl($file='',$strict=false)
	{
		global $core;
		if ($file) { $file = '/'.$file; }
		// default
		$default_dir = self::tpl_root().'default';
		// user
		$user_dir = self::tpl_root().$core->blog->settings->eventdata_tpl_theme;
		// theme
		$theme_dir = self::tpl_root().$core->blog->settings->theme;

		if (file_exists($theme_dir.$file))
			return $theme_dir;
		elseif (!$strict && file_exists($user_dir.$file))
			return $user_dir;
		elseif (!$strict && file_exists($default_dir.$file))
			return $default_dir;
		else
			return '';
	}
	# Find if $_ctx->eventdatas or $_ctx->posts exists
	private static function eventdataCtx($content)
	{
		return 
		'<?php if (!$_ctx->exists("eventdatas")) { $eventdatactx = "posts"; } '.
		'else { $eventdatactx = "eventdatas"; } ?>'.
		$content.
		'<?php unset($eventdatactx); ?>';
	}
	# Specific post_params for eventdata entries
	public static function eventdataParams()
	{
		global $core, $_ctx;
		$res = "<?php\n\$params = \$_ctx->post_params;\n?>";

		if (null === $core->blog->settings->eventdata_no_cats)
			return $res;

		$cats = @unserialize($core->blog->settings->eventdata_no_cats);

		if (!is_array($cats))
			return $res;

		$res .= "<?php\nif (!isset(\$params['sql'])) { \$params['sql'] = ''; }\n";
		foreach ($cats AS $k => $cat_id) {
			$res .= "\$params['sql'] .= \" AND P.cat_id != '$cat_id' \";\n";
		}
		return $res.'?>';
	}
	# Return full eventdata theme url (? don't need)
	public static function EventdataThemeURL($attr)
	{
		global $core;
		return self::eventdataTpl() ? 
			$core->blog->url.$core->url->getBase('eventdatafiles').'/' : '';
	}
	# Feed Url
	public static function EventdataFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';

		if (!preg_match('#^(rss2|atom)$#',$type))
			$type = 'atom';
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("eventdatapage")."/feed/'.$type.'"').'; ?>';
	}
	# Missing dc value!
	public static function EntryUpdateDate($attr)
	{
		$format = !empty($attr['format']) ?  addslashes($attr['format']) : '%Y-%m-%d %H:%M:%S';
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if (!empty($attr['rfc822']))
			$res = sprintf($f,"dt::rfc822(\$_ctx->{\$eventdatactx}->post_upddt,\$core->blog->settings->blog_timezone)");
		elseif (!empty($attr['iso8601']))
			$res = sprintf($f,"dt::iso8601(\$_ctx->{\$eventdatactx}->post_upddt,\$core->blog->settings->blog_timezone)");
		else
			$res = sprintf($f,"dt::str('".$format."',\$_ctx->{\$eventdatactx}->post_upddtt)");

		return self::eventdataCtx('<?php echo '.$res.'; ?>');
	}
	# List of eventdatas of a post
	public static function EntryEventdataDates($attr,$content)
	{
		global $core, $_ctx;

		# Not on post page
		if ('post.html' != $_ctx->current_tpl || !$_ctx->posts->post_id) return;

		$type = !empty($attr['eventdata_type']) ? '"'.addslashes($attr['eventdata_type']).'"' : '"eventdata"';
		$lastn = !empty($attr['lastn']) ? abs((integer) $attr['lastn'])+0 : 'null';
		$period = isset($attr['period']) ? '"'.addslashes($attr['period']).'"' : 'null';
		$start = isset($attr['start']) ? '"'.addslashes($attr['start']).'"' : 'null';
		$end = isset($attr['end']) ? '"'.addslashes($attr['end']).'"' : 'null';
		$sort = isset($attr['order']) && strtoupper($attr['order']) == 'ASC' ? '"ASC"' : '"DESC"';

		return
		"<?php\n".
		'if (!isset($eventdata)) { $eventdata = new dcEventdata($core); }'."\n".
		"\$_ctx->eventdatas = \$eventdata->getEventdata($type,$lastn,$start,$end,\$_ctx->posts->post_id,$period,$sort);\n".
		'while ($_ctx->eventdatas->fetch()) : ?>'.$content.'<?php endwhile; '."\n".
		'$_ctx->eventdatas = null;'."\n".
		'?>';
	}
	# Condition
	public static function EventdataDatesIf($attr,$content)
	{
		$operator = isset($attr['operator']) ? self::getOperator($attr['operator']) : '&&';

		if (isset($attr['oneday'])) {

			$sign = 1 == $attr['oneday'] ? '=' : '!';
			$if[] = "dt::dt2str('%Y%j',\$_ctx->{\$eventdatactx}->eventdata_start) ".$sign."= dt::dt2str('%Y%j',\$_ctx->{\$eventdatactx}->eventdata_end) ";
		}

		if (empty($if))
			return $content;

		return self::eventdataCtx("<?php if(".implode(' '.$operator.' ',$if).") : ?>\n".$content."<?php endif; ?>\n");
	}
	# Start of loop of EntryEventdataDates
	public static function EventdataDatesHeader($attr,$content)
	{
		return self::eventdataCtx('<?php if ($_ctx->{$eventdatactx}->isStart()) : ?>'.$content.'<?php endif; ?>');
	}
	# End of loop of EntryEventdataDates
	public static function EventdataDatesFooter($attr,$content)
	{
		return self::eventdataCtx('<?php if ($_ctx->{$eventdatactx}->isEnd()) : ?>'.$content.'<?php endif; ?>');
	}
	# Full date of an eventdata (friendly read)
	public static function EventdataFullDate($attr)
	{
		$format = !empty($attr['format']) ? addslashes($attr['format']) : '';
		$start_format = !empty($attr['start_format']) ? addslashes($attr['start_format']) : '';
		$end_format = !empty($attr['end_format']) ? addslashes($attr['end_format']) : '';
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$fs = $fe = '';

		if (!empty($attr['rfc822']))
			$fs = sprintf($f,"dt::rfc822(strtotime(\$_ctx->{\$eventdatactx}->eventdata_start),\$_ctx->posts->post_tz)");
		elseif (!empty($attr['iso8601']))
			$fs = sprintf($f,"dt::iso8601(strtotime(\$_ctx->{\$eventdatactx}->eventdata_start),\$_ctx->posts->post_tz)");
		elseif ($format)
			$fs = sprintf($f,"dt::dt2str('".$format."',\$_ctx->{\$eventdatactx}->eventdata_start)");
		else 
			$fs = sprintf($f,"dt::dt2str(\$core->blog->settings->date_format,\$_ctx->{\$eventdatactx}->eventdata_start)");

		if (!empty($attr['rfc822']))
			$fe = sprintf($f,"dt::rfc822(strtotime(\$_ctx->{\$eventdatactx}->eventdata_end),\$_ctx->posts->post_tz)");
		elseif (!empty($attr['iso8601']))
			$fe = sprintf($f,"dt::iso8601(strtotime(\$_ctx->{\$eventdatactx}->eventdata_end),\$_ctx->posts->post_tz)");
		elseif ($format)
			$fe = sprintf($f,"dt::dt2str('".$format."',\$_ctx->{\$eventdatactx}->eventdata_end)");
		else
			$fe = sprintf($f,"dt::dt2str(\$core->blog->settings->date_format,\$_ctx->{\$eventdatactx}->eventdata_end)");

		return self::eventdataCtx("<?php echo str_replace(array('%S','%E','%%'),array($fs,$fe,'%'),__('From %S to %E')); ?>");
	}
	# Start date of an eventdata
	public static function EventdataStartDate($attr)
	{
		$format = !empty($attr['format']) ? addslashes($attr['format']) : '';
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if (!empty($attr['rfc822']))
			$res = sprintf($f,"dt::rfc822(strtotime(\$_ctx->{\$eventdatactx}->eventdata_start),\$_ctx->posts->post_tz)");
		elseif (!empty($attr['iso8601']))
			$res = sprintf($f,"dt::iso8601(strtotime(\$_ctx->{\$eventdatactx}->eventdata_start),\$_ctx->posts->post_tz)");
		elseif ($format)
			$res = sprintf($f,"dt::dt2str('".$format."',\$_ctx->{\$eventdatactx}->eventdata_start)");
		else 
			$res = sprintf($f,"dt::dt2str(\$core->blog->settings->date_format,\$_ctx->{\$eventdatactx}->eventdata_start)");

		return self::eventdataCtx('<?php echo '.$res.'; ?>');
	}
	# Start time of an eventdata
	public static function EventdataStartTime($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$res = sprintf($f,"dt::dt2str(".(!empty($attr['format']) ? "'".addslashes($attr['format'])."'" : '$core->blog->settings->time_format').",\$_ctx->{\$eventdatactx}->eventdata_start)");

		return self::eventdataCtx('<?php echo '.$res.'; ?>');
	}
	# End date of an eventdata
	public static function EventdataEndDate($attr)
	{
		$format = !empty($attr['format']) ? addslashes($attr['format']) : '';
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if (!empty($attr['rfc822']))
			$res = sprintf($f,"dt::rfc822(strtotime(\$_ctx->{\$eventdatactx}->eventdata_end),\$_ctx->posts->post_tz)");
		elseif (!empty($attr['iso8601']))
			$res = sprintf($f,"dt::iso8601(strtotime(\$_ctx->{\$eventdatactx}->eventdata_end),\$_ctx->posts->post_tz)");
		elseif ($format)
			$res = sprintf($f,"dt::dt2str('".$format."',\$_ctx->{\$eventdatactx}->eventdata_end)");
		else
			$res = sprintf($f,"dt::dt2str(\$core->blog->settings->date_format,\$_ctx->{\$eventdatactx}->eventdata_end)");

		return self::eventdataCtx('<?php echo '.$res.'; ?>');
	}
	# End time of an eventdata
	public static function EventdataEndTime($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$res = sprintf($f,"dt::dt2str(".(!empty($attr['format']) ? "'".addslashes($attr['format'])."'" : '$core->blog->settings->time_format').",\$_ctx->{\$eventdatactx}->eventdata_end)");

		return self::eventdataCtx('<?php echo '.$res.'; ?>');
	}
	# Duration of eventdata
	public static function EventdataDuration($attr)
	{
		$format = !empty($attr['format']) ? addslashes($attr['format']) : '';
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$res = sprintf($f,"eventdata::getReadableDuration(
			(strtotime(\$_ctx->{\$eventdatactx}->eventdata_end) - strtotime(\$_ctx->{\$eventdatactx}->eventdata_start)),
			".$attr['format'].")");

		return self::eventdataCtx('<?php echo '.$res.'; ?>');
	}
	# Period of an eventdata
	public static function EventdataPeriod($attr)
	{
		$format = !empty($attr['format']) ? addslashes($attr['format']) : '';
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$res = 
		"if (strtotime(\$_ctx->{\$eventdatactx}->eventdata_start) > time()) {\n".
		" echo ".sprintf($f,(empty($attr['strict']) ? "__('scheduled')" : "'scheduled'"))."; }\n".
		"elseif (strtotime(\$_ctx->{\$eventdatactx}->eventdata_start) < time() && strtotime(\$_ctx->{\$eventdatactx}->eventdata_end) > time()) {\n".
		" echo ".sprintf($f,(empty($attr['strict']) ? "__('ongoing')" : "'ongoing'"))."; }\n".
		"elseif (strtotime(\$_ctx->{\$eventdatactx}->eventdata_end) < time()) {\n".
		" echo ".sprintf($f,(empty($attr['strict']) ? "__('finished')" : "'finished'"))."; }\n";

		return self::eventdataCtx('<?php '.$res.' ?>');
	}
	# Location of an eventdata
	public static function EventdataLocation($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$ics = !empty($attr['ics']) && 1 == $attr['ics'] ?
			'if ("" != $_ctx->{$eventdatactx}->eventdata_location) { echo "LOCATION;CHARSET=UTF-8:"; } ' : '';

		return self::eventdataCtx('<?php '.$ics.' echo '.sprintf($f,'$_ctx->{$eventdatactx}->eventdata_location').'; ?>');
	}
	# Return eventdatas page url
	public static function EventdataPageURL($attr)
	{
		return "<?php echo \$core->blog->url.\$core->url->getBase('eventdatapage'); if (\$_ctx->exists('categories')) { echo '/category/'.\$_ctx->categories->cat_url; } ?>";
	}
	# Title of public page
	public static function EventdataPageTitle($attr)
	{
		global $core,$_ctx;
		$f = $core->tpl->getFilters($attr);
		$cats = @unserialize($core->blog->settings->eventdata_tpl_cats);
		$cat_id = @$_ctx->categories->cat_id;

		if (is_array($cats) && in_array($cat_id,$cats) && !empty($cat_id))
			return '<?php echo '.sprintf($f,'$_ctx->categories->cat_title').'; ?>';
		else
			return '<?php echo '.sprintf($f,'$core->blog->settings->eventdata_tpl_title').'; ?>';
	}
	# Description of public page
	public static function EventdataPageDescription($attr)
	{
		global $core,$_ctx;
		$f = $core->tpl->getFilters($attr);
		$cats = unserialize($core->blog->settings->eventdata_tpl_cats);
		$cat_id = @$_ctx->categories->cat_id;

		if (is_array($cats) && in_array($cat_id,$cats))
			return '<?php echo '.sprintf($f,'$_ctx->categories->cat_desc').'; ?>';
		else
			return '<?php echo '.sprintf($f,'$core->blog->settings->eventdata_tpl_desc').'; ?>';
	}
	# Navigation menu for public page
	public static function EventdataPageNav($attr)
	{
		global $core,$_ctx;
		$f = $core->tpl->getFilters($attr);
		
		$menu = array(
			__('All') => 'all',
			__('Ongoing') => 'ongoing',
			__('Outgoing') => 'outgoing',
			__('Not started') => 'notstarted',
			__('Scheduled') => 'scheduled',
			__('Started') => 'started',
			__('Not finished') => 'notfinished',
			__('Finished') => 'finished'
		);

		if (isset($attr['menus'])) {
			$attr_menu = array();
			$attr_menus = explode(',',$attr['menus']);
			foreach($menu AS $k => $v) {
				if (in_array($v,$attr_menus))
					$attr_menu[$k] = $v;
			}
			if (!empty($attr_menu))
				$menu = $attr_menu;
		}
		$res = '';
		foreach($menu AS $k => $v) {
			$res .= $_ctx->post_params['period'] == $v ? '<li class="active">' : '<li>';
			$res .= '<a href="'.self::EventdataPageURL('').'/'.$v.'"><?php echo '.sprintf($f,'"'.$k.'"').'; ?></a></li>';
		}

		return empty($res) ? '' : '<div id="eventdata_nav"><ul>'.$res.'</ul></div>';
	}
	# Posts list with eventdatas (like Entries)
	public static function EventdataEntries($attr,$content)
	{
		$res = self::eventdataParams()."<?php\n";

		# Limit
		$lastn = 0;
		if (isset($attr['lastn']))
			$lastn = abs((integer) $attr['lastn'])+0;

		$res .= 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		if ($lastn > 0)
			$res .= "\$params['limit'] = ".$lastn.";\n";
		else
			$res .= "\$params['limit'] = \$_ctx->nb_entry_per_page;\n";

		# Pagination
		if (!isset($attr['ignore_pagination']) || $attr['ignore_pagination'] == "0")
			$res .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
		else
			$res .= "\$params['limit'] = array(0, \$params['limit']);\n";

		# Author
		if (isset($attr['author']))
			$res .= "\$params['user_id'] = '".addslashes($attr['author'])."';\n";

		# Cat
		if (isset($attr['category']))
			$res .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\ncontext::categoryPostParam(\$params);\n";

		# No cat
		if (isset($attr['no_category']))
			$res .= "@\$params['sql'] .= ' AND P.cat_id IS NULL ';\nunset(\$params['cat_url']);\n";

		# Post type
		if (!empty($attr['type']))
			$res .= "\$params['post_type'] = preg_split('/\s*,\s*/','".addslashes($attr['type'])."',-1,PREG_SPLIT_NO_EMPTY);\n";

		# Post url
		if (!empty($attr['url']))
			$res .= "\$params['post_url'] = '".addslashes($attr['url'])."';\n";

		# Context
		if (empty($attr['no_context']))
		{
			$res .=
			'if ($_ctx->exists("users")) { '.
				"\$params['user_id'] = \$_ctx->users->user_id; ".
			"}\n";
			
			$res .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";
			
			$res .=
			'if ($_ctx->exists("archives")) { '.
				"\$params['post_year'] = \$_ctx->archives->year(); ".
				"\$params['post_month'] = \$_ctx->archives->month(); ".
				"unset(\$params['limit']); ".
			"}\n";
			
			$res .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['post_lang'] = \$_ctx->langs->post_lang; ".
			"}\n";
			
			$res .=
			'if (isset($_search)) { '.
				"\$params['search'] = \$_search; ".
			"}\n";
		}
		# Eventdata type
		if (!empty($attr['eventdata_type']))
			$res .= "\$params['eventdata_type'] = preg_split('/\s*,\s*/','".addslashes($attr['eventdata_type'])."',-1,PREG_SPLIT_NO_EMPTY);\n";
		else
			$res .= "\$params['eventdata_type'] = 'eventdata';\n";

		# Sort
		$sortby = 'eventdata_start';
		$order = 'desc';
		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'title': $sortby = 'post_title'; break;
				case 'selected' : $sortby = 'post_selected'; break;
				case 'author' : $sortby = 'user_id'; break;
				case 'date' : $sortby = 'post_dt'; break;
				case 'id' : $sortby = 'post_id'; break;
				case 'start' : $sortby = 'eventdata_start'; break;
				case 'end' : $sortby = 'eventdata_end'; break;
				case 'location' : $sortby = 'eventdata_location'; break;
			}
		}
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order']))
			$order = $attr['order'];

		$res .= "\$params['order'] = '".$sortby." ".$order."';\n";
		# No content
		if (isset($attr['no_content']) && $attr['no_content'])
			$res .= "\$params['no_content'] = true;\n";

		# Selected
		if (isset($attr['selected']))
			$res .= "\$params['post_selected'] = ".(integer) (boolean) $attr['selected'].";";

		# Period
		if (isset($attr['period'])) //could exists by url
			$res .= "if (!isset(\$params['period'])) { \$params['period'] = '".addslashes($attr['period'])."'; }\n";

		# Tag
		if (!empty($attr['meta_id']))
			$res .= "\$params['meta_id'] = preg_split('/\s*,\s*/','".addslashes($attr['meta_id'])."',-1,PREG_SPLIT_NO_EMPTY);\n";

		$res .= 
		'$_ctx->post_params = $params;'."\n".
		'$eventdata = new dcEventdata($core);'."\n".
		'$_ctx->posts = $eventdata->getPostsByEventdata($params);'."\n".
		'unset($params);'."\n".
		'while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '."\n".
		'$_ctx->posts = null; $_ctx->post_params = null;'."\n".
		'?>';

		return $res;
	}
	# Pagination
	public static function EventdataPagination($attr,$content)
	{
		$res = self::eventdataParams().
		"<?php\n\$_ctx->pagination = \$eventdata->getPostsByEventdata(\$params,true); unset(\$params);\n?>";
		
		if (isset($attr['no_context'])) return $res.$content;

		return
		$res.
		'<?php if ($_ctx->pagination->f(0) > $_ctx->posts->count()) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	# Public page
	public static function eventdatas($args)
	{
		$core =& $GLOBALS['core'];
		$_ctx =& $GLOBALS['_ctx'];
		$post_params = array('period'=>'');

		$n = self::getPageNumber($args);

		# Feeds rss & atom
		if (preg_match('%(^|/)feed/(rss2|atom)$%',$args,$m)){
			# clean url
			$args = preg_replace('#(^|/)feed/(rss2|atom)$#','',$args);

			$file = 'eventdatas-'.$m[2].'.xml';
			$mime = 'application/xml';
			$core->tpl->setPath($core->tpl->getPath(),self::tpl_root().'default/feed/');
		# Feeds ics
		} elseif (preg_match('%(^|/)feed/(.*?).ics$%',$args,$m)){
			# clean url
			$args = preg_replace('#(^|/)feed/(.*?).ics$#','$2',$args);
			# Period
			if (preg_match('%(^|/)(started|notstarted|scheduled|ongoing|outgoing|finished|notfinished|all)(.*?)$%',$args,$m))
				$post_params['period'] = $m[2];
			# Category
			if (preg_match('%(^|/)category/([^/]*)(.*?)$%',$args,$m))
				$post_params['cat_url'] = $m[2];

			$file = 'eventdatas-ical.ics';
			$mime = 'text/calendar';
			$core->tpl->setPath($core->tpl->getPath(),self::tpl_root().'default/feed/');
		# Normal
		} else {
			# Page number
			if ($n)
				$GLOBALS['_page_number'] = $n;

			# Period
			if (preg_match('%(^|/)(started|notstarted|scheduled|ongoing|outgoing|finished|notfinished|all)(.*?)$%',$args,$m)) {
				$post_params['period'] = $m[2];
			
				if ('' != $m[3]) {
					$exp = explode('/',$m[3]);

					if (isset($exp[1]) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/',urldecode($exp[1])))
						$post_params['eventdata_start'] = urldecode($exp[1]);

					if (isset($exp[2]) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/',urldecode($exp[2])))
						$post_params['eventdata_end'] = urldecode($exp[2]);
				}
			
			}
			# Category
			if (preg_match('%(^|/)category/([^/]*)(.*?)$%',$args,$m))
				$post_params['cat_url'] = $m[2];
			
			$file = 'eventdatas.html';
			$mime='text/html';
		}

		$_ctx->post_params = $post_params;
		self::serveDocument($file,$mime);
		return;
	}
	# Return file from eventdata theme
	public static function eventdatastheme($args)
	{
		global $core;

		if (!preg_match('#([^/]+)$#',$args,$m)) {
			self::p404();
			return;
		}

		$f = $m[1];

		if (strstr($f,"..") !== false) {
			self::p404();
			return;
		}

		$path = self::eventdataTpl($f);
		if (!$path) {
			self::p404();
			return;
		}
		$file = $path.'/'.$f;

		$allowed_types = array('png','jpg','jpeg','gif','css','js','swf');
		if (!file_exists($file) || !in_array(files::getExtension($file),$allowed_types)) {
			self::p404();
			return;
		}

		http::cache(array_merge(array($file),get_included_files()));
		$type = files::getMimeType($file);
		header('Content-Type: '.$type);
		header('Content-Length: '.filesize($file));
		if ($type != "text/css" || $core->blog->settings->url_scan == 'path_info') {
			readfile($file);
		} else {
			echo preg_replace('#url\((?!(http:)|/)#','url('.$core->blog->url.'eventstheme/',file_get_contents($file));
		}
		return;
	}
	# Set tpl path
	public static function publicBeforeDocument($core)
	{
		if ('' != ($path = self::eventdataTpl()))
		    $core->tpl->setPath($core->tpl->getPath(),$path);
	}
	# Include css
	public static function publicHeadContent($core)
	{
		if (!file_exists($core->blog->themes_path.'/'.$core->blog->settings->theme.'/tpl/eventdatas.html') 
		 && '' != self::eventdataTpl('eventdata.css'))
			echo 
			"<style type=\"text/css\">\n@import url(".
			$core->blog->url.$core->url->getBase('eventdatafiles').
			"/eventdata.css);\n</style>\n";
	}
	# Reordered categories redirection to public page
	public static function tplBeforeData($core)
	{
		$_ctx =& $GLOBALS['_ctx'];
		if (null === $core->blog->settings->eventdata_tpl_cats) return;

		$cats = @unserialize($core->blog->settings->eventdata_tpl_cats);

		if (!is_array($cats) 
			|| 'category.html' != $_ctx->current_tpl 
			|| !in_array($_ctx->categories->cat_id,$cats)) return;

		self::serveDocument('eventdatas.html');
		return;
	}
	# Include eventdataentrybeforecontent.html of a theme if exists
	public static function publicEntryBeforeContent($core,$_ctx)
	{
		if ('' == self::eventdataTpl('eventdataentrybeforecontent.html',true)) return;
		if ('' == ($fc = $core->tpl->getData('eventdataentrybeforecontent.html'))) return;
		if (!preg_match('|<body[^>]*?>(.*?)</body>|ms',$fc,$matches)) return;

		echo $matches[1];
	}
	# Include eventdataentryaftercontent.html of a theme if exists
	public static function publicEntryAfterContent($core,$_ctx)
	{
		if ('' == self::eventdataTpl('eventdataentryaftercontent.html',true)) return;
		if ('' == ($fc = $core->tpl->getData('eventdataentryaftercontent.html'))) return;
		if (!preg_match('|<body[^>]*?>(.*?)</body>|ms',$fc,$matches)) return;

		echo $matches[1];
	}
}
?>