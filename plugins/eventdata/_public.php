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

if (!defined('DC_RC_PATH')) return;

# Localized string we find in template
__('Event');
__('Events');
__('Dates of events');
__('scheduled');
__('finished');
__('ongoing');
__('From %S to %E');

# Load _wigdets.php
require dirname(__FILE__).'/_widgets.php';

# Public urls, blocks, values, behaviors...
if ($core->blog->settings->event_option_active) {
	$core->addBehavior('publicHeadContent',array('eventdataPublic','publicHeadContent'));
	$core->addBehavior('publicBeforeDocument',array('eventdataPublic','publicBeforeDocument'));
	$core->addBehavior('tplBeforeData',array('eventdataPublic','tplBeforeData'));

	if (!$core->blog->settings->event_tpl_dis_bhv) {
		$core->addBehavior('publicEntryBeforeContent',array('eventdataPublic','publicEntryBeforeContent'));
		$core->addBehavior('publicEntryAfterContent',array('eventdataPublic','publicEntryAfterContent'));
	}

	if ($core->blog->settings->event_option_public) {
		$u = $core->blog->settings->event_tpl_url ? $core->blog->settings->event_tpl_url : 'events';
		$core->url->register($u,$u,'^'.$u.'(|/.+)$',array('eventdataPublic','events'));
	}
	$core->url->register('eventstheme','eventstheme','^eventstheme/(.+)$',array('eventdataPublic','eventstheme'));

	$core->tpl->addBlock('EventEntries',array('eventdataPublic','EventEntries'));
	$core->tpl->addBlock('EventPagination',array('eventdataPublic','EventPagination'));
	$core->tpl->addValue('EventPageURL',array('eventdataPublic','EventPageURL'));
	$core->tpl->addValue('EventPageTitle',array('eventdataPublic','EventPageTitle'));
	$core->tpl->addValue('EventPageDescription',array('eventdataPublic','EventPageDescription'));

	$core->tpl->addBlock('EntryEventDates',array('eventdataPublic','EntryEventDates'));
	$core->tpl->addBlock('EventDatesHeader',array('eventdataPublic','EventDatesHeader'));
	$core->tpl->addBlock('EventDatesFooter',array('eventdataPublic','EventDatesFooter'));
	$core->tpl->addValue('EventFullDate',array('eventdataPublic','EventFullDate'));
	$core->tpl->addValue('EventStartDate',array('eventdataPublic','EventStartDate'));
	$core->tpl->addValue('EventStartTime',array('eventdataPublic','EventStartTime'));
	$core->tpl->addValue('EventEndDate',array('eventdataPublic','EventEndDate'));
	$core->tpl->addValue('EventEndTime',array('eventdataPublic','EventEndTime'));
	$core->tpl->addValue('EventPeriod',array('eventdataPublic','EventPeriod'));

	$core->tpl->addValue('EventThemeURL',array('eventdataPublic','EventThemeURL'));
	$core->tpl->addValue('EventFeedURL',array('eventdataPublic','EventFeedURL'));

} else {
	$core->tpl->addBlock('EventEntries',array('eventdataPublic','EventDisableBlock'));
	$core->tpl->addBlock('EventPagination',array('eventdataPublic','EventDisableBlock'));
	$core->tpl->addValue('EventPageURL',array('eventdataPublic','EventDisableValue'));
	$core->tpl->addValue('EventPageTitle',array('eventdataPublic','EventDisableValue'));
	$core->tpl->addValue('EventPageDescription',array('eventdataPublic','EventDisableValue'));

	$core->tpl->addBlock('EntryEventDates',array('eventdataPublic','EventDisableBlock'));
	$core->tpl->addBlock('EventDatesHeader',array('eventdataPublic','EventDisableBlock'));
	$core->tpl->addBlock('EventDatesFooter',array('eventdataPublic','EventDisableBlock'));
	$core->tpl->addValue('EventFullDate',array('eventdataPublic','EventDisableValue'));
	$core->tpl->addValue('EventStartDate',array('eventdataPublic','EventDisableValue'));
	$core->tpl->addValue('EventStartTime',array('eventdataPublic','EventDisableValue'));
	$core->tpl->addValue('EventEndDate',array('eventdataPublic','EventDisableValue'));
	$core->tpl->addValue('EventEndTime',array('eventdataPublic','EventDisableValue'));
	$core->tpl->addValue('EventPeriod',array('eventdataPublic','EventDisableValue'));

	$core->tpl->addValue('EventThemeURL',array('eventdataPublic','EventDisableValue'));
	$core->tpl->addValue('EventFeedURL',array('eventdataPublic','EventDisableValue'));
}

class eventdataPublic extends dcUrlHandlers
{
	private static function tpl_root()
	{
		return array_pop(explode(PATH_SEPARATOR, DC_PLUGINS_ROOT.'/eventdata/default-templates/eventdata-'));
	}
	# Plugin disabled
	public static function EventDisablePage($a)
	{
		self::p404(); exit;
	}
	public static function EventDisableBlock($a,$b)
	{
		return '';
	}
	public static function EventDisableValue($a)
	{
		return '';
	}
	# return tpl path if exist
	private static function eventTpl($file='',$strict=false)
	{
		global $core;
		if ($file) { $file = '/'.$file; }
		// default
		$default_dir = self::tpl_root().'default';
		// user
		$user_dir = self::tpl_root().$core->blog->settings->event_tpl_theme;
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
	# Find if $_ctx->events or $_ctx->posts exists
	private static function eventCtx($content)
	{
		return '<?php if (!$_ctx->exists("events")) { $eventctx = "posts"; } else { $eventctx = "events"; } ?>'.$content.'<?php unset($eventctx); ?>';
	}
	# Specific post_params for event entries
	public static function eventParams()
	{
		global $core, $_ctx;
		$res = "<?php\n\$params = \$_ctx->post_params;\n?>";

		if (null === $core->blog->settings->event_no_cats)
			return $res;

		$cats = @unserialize($core->blog->settings->event_no_cats);

		if (!is_array($cats))
			return $res;

		$res .= "<?php\nif (!isset(\$params['sql'])) { \$params['sql'] = ''; }\n";
		foreach ($cats AS $k => $cat_id) {
			$res .= "\$params['sql'] = \" AND P.cat_id != '$cat_id' \";\n";
		}
		return $res.'?>';
	}
	# Return full eventdata theme url (? don't need)
	public static function EventThemeURL($attr)
	{
		global $core;
		return self::eventTpl() ?  $core->blog->url.'eventstheme/' : '';
	}
	# Feed Url
	public static function EventFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';

		if (!preg_match('#^(rss2|atom)$#',$type))
			$type = 'atom';
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->blog->settings->event_tpl_url."/feed/'.$type.'"').'; ?>';
	}
	# List of events of a post
	public static function EntryEventDates($attr,$content)
	{
		global $core, $_ctx;

		# Not on post page
		if ('post.html' != $_ctx->current_tpl || !$_ctx->posts->post_id) return;

		$type = !empty($attr['event_type']) ? '"'.addslashes($attr['event_type']).'"' : '"event"';
		$lastn = !empty($attr['lastn']) ? abs((integer) $attr['lastn'])+0 : 'null';
		$period = isset($attr['period']) ? '"'.addslashes($attr['period']).'"' : 'null';
		$start = isset($attr['start']) ? '"'.addslashes($attr['start']).'"' : 'null';
		$end = isset($attr['end']) ? '"'.addslashes($attr['end']).'"' : 'null';

		return
		"<?php\n".
		'if (!isset($event)) { $event = new dcEvent($core); }'."\n".
		"\$_ctx->events = \$event->getEvent($type,$lastn,$start,$end,\$_ctx->posts->post_id,$period);\n".
		'while ($_ctx->events->fetch()) : ?>'.$content.'<?php endwhile; '."\n".
		'$_ctx->events = null;'."\n".
		'?>';
	}
	# Start of loop of EntryEventDates
	public static function EventDatesHeader($attr,$content)
	{
		return self::eventCtx('<?php if ($_ctx->{$eventctx}->isStart()) : ?>'.$content.'<?php endif; ?>');
	}
	# End of loop of EntryEventDates
	public static function EventDatesFooter($attr,$content)
	{
		return self::eventCtx('<?php if ($_ctx->{$eventctx}->isEnd()) : ?>'.$content.'<?php endif; ?>');
	}
	# Full date of an event (friendly read)
	public static function EventFullDate($attr)
	{
		$format = !empty($attr['format']) ? addslashes($attr['format']) : '';
		$start_format = !empty($attr['start_format']) ? addslashes($attr['start_format']) : '';
		$end_format = !empty($attr['end_format']) ? addslashes($attr['end_format']) : '';
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$fs = $fe = '';

		if (!empty($attr['rfc822']))
			$fs = sprintf($f,"dt::rfc822(strtotime(\$_ctx->{\$eventctx}->event_start),\$_ctx->posts->post_tz)");
		elseif (!empty($attr['iso8601']))
			$fs = sprintf($f,"dt::iso8601(strtotime(\$_ctx->{\$eventctx}->event_start),\$_ctx->posts->post_tz)");
		elseif ($format)
			$fs = sprintf($f,"dt::dt2str('".$format."',\$_ctx->{\$eventctx}->event_start)");
		else 
			$fs = sprintf($f,"dt::dt2str(\$core->blog->settings->date_format,\$_ctx->{\$eventctx}->event_start)");

		if (!empty($attr['rfc822']))
			$fe = sprintf($f,"dt::rfc822(strtotime(\$_ctx->{\$eventctx}->event_end),\$_ctx->posts->post_tz)");
		elseif (!empty($attr['iso8601']))
			$fe = sprintf($f,"dt::iso8601(strtotime(\$_ctx->{\$eventctx}->event_end),\$_ctx->posts->post_tz)");
		elseif ($format)
			$fe = sprintf($f,"dt::dt2str('".$format."',\$_ctx->{\$eventctx}->event_end)");
		else
			$fe = sprintf($f,"dt::dt2str(\$core->blog->settings->date_format,\$_ctx->{\$eventctx}->event_end)");

		return self::eventCtx("<?php echo str_replace(array('%S','%E','%%'),array($fs,$fe,'%'),__('From %S to %E')); ?>");
	}
	# Start date of an event
	public static function EventStartDate($attr)
	{
		$format = !empty($attr['format']) ? addslashes($attr['format']) : '';
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if (!empty($attr['rfc822']))
			$res = sprintf($f,"dt::rfc822(strtotime(\$_ctx->{\$eventctx}->event_start),\$_ctx->posts->post_tz)");
		elseif (!empty($attr['iso8601']))
			$res = sprintf($f,"dt::iso8601(strtotime(\$_ctx->{\$eventctx}->event_start),\$_ctx->posts->post_tz)");
		elseif ($format)
			$res = sprintf($f,"dt::dt2str('".$format."',\$_ctx->{\$eventctx}->event_start)");
		else 
			$res = sprintf($f,"dt::dt2str(\$core->blog->settings->date_format,\$_ctx->{\$eventctx}->event_start)");

		return self::eventCtx('<?php echo '.$res.'; ?>');
	}
	# Start time of an event
	public static function EventStartTime($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$res = sprintf($f,"dt::dt2str(".(!empty($attr['format']) ? "'".addslashes($attr['format'])."'" : '$core->blog->settings->time_format').",\$_ctx->{\$eventctx}->event_start)");

		return self::eventCtx('<?php echo '.$res.'; ?>');
	}
	# End date of an event
	public static function EventEndDate($attr)
	{
		$format = !empty($attr['format']) ? addslashes($attr['format']) : '';
		$f = $GLOBALS['core']->tpl->getFilters($attr);

		if (!empty($attr['rfc822']))
			$res = sprintf($f,"dt::rfc822(strtotime(\$_ctx->{\$eventctx}->event_end),\$_ctx->posts->post_tz)");
		elseif (!empty($attr['iso8601']))
			$res = sprintf($f,"dt::iso8601(strtotime(\$_ctx->{\$eventctx}->event_end),\$_ctx->posts->post_tz)");
		elseif ($format)
			$res = sprintf($f,"dt::dt2str('".$format."',\$_ctx->{\$eventctx}->event_end)");
		else
			$res = sprintf($f,"dt::dt2str(\$core->blog->settings->date_format,\$_ctx->{\$eventctx}->event_end)");

		return self::eventCtx('<?php echo '.$res.'; ?>');
	}
	# End time of an event
	public static function EventEndTime($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$res = sprintf($f,"dt::dt2str(".(!empty($attr['format']) ? "'".addslashes($attr['format'])."'" : '$core->blog->settings->time_format').",\$_ctx->{\$eventctx}->event_end)");

		return self::eventCtx('<?php echo '.$res.'; ?>');
	}
	# Period of an event
	public static function EventPeriod($attr)
	{
		$format = !empty($attr['format']) ? addslashes($attr['format']) : '';
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		$res = 
		"if (strtotime(\$_ctx->{\$eventctx}->event_start) > time()) {\n".
		" echo ".sprintf($f,(empty($attr['strict']) ? "__('scheduled')" : "'scheduled'"))."; }\n".
		"elseif (strtotime(\$_ctx->{\$eventctx}->event_start) < time() && strtotime(\$_ctx->{\$eventctx}->event_end) > time()) {\n".
		" echo ".sprintf($f,(empty($attr['strict']) ? "__('ongoing')" : "'ongoing'"))."; }\n".
		"elseif (strtotime(\$_ctx->{\$eventctx}->event_end) < time()) {\n".
		" echo ".sprintf($f,(empty($attr['strict']) ? "__('finished')" : "'finished'"))."; }\n";

		return self::eventCtx('<?php '.$res.'; ?>');
	}
	# Return events page url
	public static function EventPageURL($attr)
	{
		return '<?php echo $core->blog->url.$core->blog->settings->event_tpl_url; ?>';
	}
	# Title of public page
	public static function EventPageTitle($attr)
	{
		global $core,$_ctx;
		$f = $core->tpl->getFilters($attr);
		$cats = unserialize($core->blog->settings->event_tpl_cats);
		$cat_id = @$_ctx->categories->cat_id;

		if (is_array($cats) && in_array($cat_id,$cats))
			return '<?php echo '.sprintf($f,'$_ctx->categories->cat_title').'; ?>';
		else
			return '<?php echo '.sprintf($f,'$core->blog->settings->event_tpl_title').'; ?>';
	}
	# Description of public page
	public static function EventPageDescription($attr)
	{
		global $core,$_ctx;
		$f = $core->tpl->getFilters($attr);
		$cats = unserialize($core->blog->settings->event_tpl_cats);
		$cat_id = @$_ctx->categories->cat_id;

		if (is_array($cats) && in_array($cat_id,$cats))
			return '<?php echo '.sprintf($f,'$_ctx->categories->cat_desc').'; ?>';
		else
			return '<?php echo '.sprintf($f,'$core->blog->settings->event_tpl_desc').'; ?>';
	}
	# Posts list with events (like Entries)
	public static function EventEntries($attr,$content)
	{
		$res = self::eventParams()."<?php\n";

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
		# Event type
		if (!empty($attr['event_type']))
			$res .= "\$params['event_type'] = preg_split('/\s*,\s*/','".addslashes($attr['event_type'])."',-1,PREG_SPLIT_NO_EMPTY);\n";
		else
			$res .= "\$params['event_type'] = 'event';\n";

		# Sort
		$sortby = 'event_start';
		$order = 'desc';
		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'title': $sortby = 'post_title'; break;
				case 'selected' : $sortby = 'post_selected'; break;
				case 'author' : $sortby = 'user_id'; break;
				case 'date' : $sortby = 'post_dt'; break;
				case 'id' : $sortby = 'post_id'; break;
				case 'start' : $sortby = 'event_start'; break;
				case 'end' : $sortby = 'event_end'; break;
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
		'$event = new dcEvent($core);'."\n".
		'$_ctx->posts = $event->getPostsByEvent($params);'."\n".
		'unset($params);'."\n".
		'while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '."\n".
		'$_ctx->posts = null; $_ctx->post_params = null;'."\n".
		'?>';

		return $res;
	}
	# Pagination
	public function EventPagination($attr,$content)
	{
		$res = self::eventParams().
		"<?php\n\$_ctx->pagination = \$event->getPostsByEvent(\$params,true); unset(\$params);\n?>";
		
		if (isset($attr['no_context'])) return $res.$content;

		return
		$res.
		'<?php if ($_ctx->pagination->f(0) > $_ctx->posts->count()) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	# Public page
	public static function events($args)
	{
		$core =& $GLOBALS['core'];

		$n = self::getPageNumber($args);

		# Feeds
		if (preg_match('%(^|/)feed/(rss2|atom)$%',$args,$m)){
			$args = preg_replace('#(^|/)feed/(rss2|atom)$#','',$args);
			$type = $m[2];
			$file = 'events-'.$type.'.xml';
			$mime = 'application/xml';
			$core->tpl->setPath($core->tpl->getPath(),self::tpl_root().'default/feed/');
		# Normal
		} else {
			if ($n)
				$GLOBALS['_page_number'] = $n;

			if (preg_match('%(^|/)(started|notstarted|scheduled|ongoing|outgoing|finished|notfinished|all)$%',$args,$m))
				$GLOBALS['_ctx']->post_params = array('period' => $m[2]);

			$file = 'events.html';
			$mime='text/html';
		}

		self::serveDocument($file,$mime);
		exit;
	}
	# Return file from eventdata theme
	public static function eventstheme($args)
	{
		global $core;

		if (!preg_match('#([^/]+)$#',$args,$m)) {
			self::p404();
			exit;
		}

		$f = $m[1];

		if (strstr($f,"..") !== false) {
			self::p404();
			exit;
		}

		$path = self::eventTpl($f);
		if (!$path) {
			self::p404();
			exit;
		}
		$file = $path.'/'.$f;

		$allowed_types = array('png','jpg','jpeg','gif','css','js','swf');
		if (!file_exists($file) || !in_array(files::getExtension($file),$allowed_types)) {
			self::p404();
			exit;
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
		exit;
	}
	# Set tpl path
	public static function publicBeforeDocument(&$core)
	{
		if ('' != ($path = self::eventTpl()))
		    $core->tpl->setPath($core->tpl->getPath(),$path);
	}
	# Include css
	public static function publicHeadContent(&$core)
	{
		if (!file_exists($core->blog->themes_path.'/'.$core->blog->settings->theme.'/tpl/events.html') 
		 && '' != self::eventTpl('eventdata.css'))
			echo "<style type=\"text/css\">\n@import url(".$core->blog->url."eventstheme/eventdata.css);\n</style>\n";
	}
	# Reordered categories redirection to public page
	public static function tplBeforeData($core)
	{
		$_ctx =& $GLOBALS['_ctx'];
		if (null === $core->blog->settings->event_tpl_cats) return;

		$cats = @unserialize($core->blog->settings->event_tpl_cats);

		if (!is_array($cats) 
			|| 'category.html' != $_ctx->current_tpl 
			|| !in_array($_ctx->categories->cat_id,$cats)) return;

		self::serveDocument('events.html');
		exit;
	}
	# Include evententrybeforecontent.html of a theme if exists
	public static function publicEntryBeforeContent($core,$_ctx)
	{
		if ('' != self::eventTpl('evententrybeforecontent.html',true)) {
			if ('' != ($fc = $core->tpl->getData('evententrybeforecontent.html'))) {
				if (preg_match('|<body[^>]*?>(.*?)</body>|ms',$fc,$matches))
					echo $matches[1];
			}
		}
	}
	# Include evententryaftercontent.html of a theme if exists
	public static function publicEntryAfterContent($core,$_ctx)
	{
		if ('' != self::eventTpl('evententryaftercontent.html',true)) {
			if ('' != ($fc = $core->tpl->getData('evententryaftercontent.html'))) {
				if (preg_match('|<body[^>]*?>(.*?)</body>|ms',$fc,$matches))
					echo $matches[1];
			}
		}
	}
}
?>