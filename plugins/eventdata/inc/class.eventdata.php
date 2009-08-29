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

class eventdata
{
	const	SUNDAY_TS = 1042329600;

	public static function getReadableDuration($str,$format=null)
	{
	    $str = (integer) $str;
	    $time = '';

	    $sec = $str % 60; $str -= $sec; $str /= 60;
	    $min = $str % 60; $str -= $min; $str /= 60;
	    $hou = $str % 24; $str -= $hou; $str /= 24;
	    $day = $str;

	    if ($day>1) $time .= sprintf(__('%s days'),$day).' ';
	    if ($day==1) $time .=__('one day').' ';
	    if ($hou>1) $time .= sprintf(__('%s hours'),$hou).' ';
	    if ($hou==1) $time .= __('one hour').' ';
	    if ($min>1) $time .= sprintf(__('%s minutes'),$min).' ';
	    if ($min==1) $time .= __('one minute').' ';
	    if (!$day && !$min && !$day && !$hou) $time .= __('instantaneous');

	    return $time;
	}

	public static function getThemes($type='all')
	{
		global $core;

		$url = 'plugin.php?p=eventdata';
		$path = array_pop(explode(PATH_SEPARATOR, DC_PLUGINS_ROOT.'/eventdata'));
		$tpl = $thm = $tpl_dirs = array();

		# Template
		if ($type !='themes') {

			$dir = $path.'/default-templates/';
			if ($dir && is_dir($dir) && is_readable($dir)) {			
				$d = dir($dir);
				while (($f = $d->read()) !== false) {
					if (is_dir($dir.'/'.$f) && !preg_match('/^\./',$f)) {
						$tpl_dirs[] = $f;
					}
				}
			}
			foreach($tpl_dirs AS $v) {
				$k = str_replace('eventdata-','',$v);
				$tpl[$k] = array(
					'name' => $k,
					'template_exists' => true,
					'template_file' => (file_exists($dir.$v.'/eventdatas.html') ? 
						$dir.$v.'/eventdatas.html' : ''),
					'theme_exists' => false,
					'theme_file' => '',
					'selected' => false
				);
			}
			if ($type == 'templates') return $tpl;
		}
		# Theme
		if ($type !='templates') {

			$themes = new dcThemes($core);
			$themes->loadModules($core->blog->themes_path,null);
			$tpl_thm = $themes->getModules();
			foreach($tpl_thm AS $v => $p) {
				$thm[$v] = array(
					'name' => $p['name'],
					'template_exists' => false,
					'template_file' => '',
					'theme_exists' => true,
					'theme_file' => (file_exists($p['root'].'/tpl/eventdatas.html') ? 
						$p['root'].'/tpl/eventdatas.html' : ''),
					'selected' => $core->blog->settings->theme == $v ? true : false
				);
			}
			if ($type == 'themes') return $thm;
		}
		# All
		if ($type !='templates' && $type != 'themes') {

			foreach($thm AS $k => $v) {
				$tpl[$k] = array(
					'name' => $v['name'],
					'template_exists' => isset($tpl[$k]['template_exists']) ? $tpl[$k]['template_exists'] : '',
					'template_file' => isset($tpl[$k]['template_file']) ? $tpl[$k]['template_file'] : '',
					'theme_exists' => $v['theme_exists'],
					'theme_file' => $v['theme_file'],
					'selected' => $v['selected']);
			}
			return $tpl;
		}
		return null;
	}

	public static function arrayCalendar($core,$year=null,$month=null,$weekstart=null)
	{
		$res = new ArrayObject();

		# Parse date in
		if (null === $weekstart)
			$weekstart = 0;

		if (null === $year || 4 != strlen($year))
			$year = date('Y',time());

		if (null === $month || 2 != strlen($month))
			$month = date('m',time());

		$day = date('d',time());

		# ts
		$ts = strtotime(date('Y-m-01 00:00:00',strtotime($year.'-'.$month.'-01 00:00:00')));

		$prev_ts = strtotime(date('Y-m-01 00:00:00',strtotime($year.'-'.($month - 1).'-01 00:00:00')));
		$next_ts = strtotime(date('Y-m-01 00:00:00',strtotime($year.'-'.($month + 1).'-01 00:00:00')));

		$res->year = $year;
		$res->month = $month;
		$res->day = $day;

		# caption
		$res->caption = array(
			'prev_txt' => dt::str('%B %Y',$prev_ts),
			'current' => dt::str('%B %Y',$ts),
			'prev_txt' => dt::str('%B %Y',$next_ts)
		);

		# days of week
		$first_ts = self::SUNDAY_TS + ((integer)$weekstart * 86400);
		$last_ts = $first_ts + (6 * 86400);
		$first = date('w',$ts);
		$first = ($first == 0)?7:$first;
		$first = $first - $weekstart;
		$limit = date('t',$ts);

		$i = 0;
		for ($j = $first_ts; $j <= $last_ts; $j = $j+86400) {
			$res->head[$i]['day_txt'] = dt::str('%a',$j);
			$i++;
		}

		# every days
		$d = 1;
		$i = $row = $field = 0;
		$dstart = false;

		while ($i < 42) {

			if ($i%7 == 0) {
				$row++;
				$field = 0;
			}
			if ($i == $first) $dstart = true;

			if ($dstart && !checkdate($month,$d,$year)) $dstart = false;

			$res->rows[$row][$field] = $dstart ? $d :' ';
			$field++;

			if (($i+1)%7 == 0 && $d >= $limit) $i = 42;

			if ($dstart) $d++;

			$i++;
		}
		return $res;
	}

	public static function drawCalendar($core,$rs)
	{
		$eventdata = new dcEventdata($core);

		$res = "\n<table summary=\"".__('Calendar')."\">\n";

		# Caption
		if ($rs->caption) {
			$res .= " <caption>\n";
			if (!empty($rs->caption['prev_url']))
				$res .= "  <a href=\"".$rs->caption['prev_url']."\">".$rs->caption['prev_txt']."</a>&nbsp;\n";

			$res .= "  ".$rs->caption['current']."\n";

			if (!empty($rs->caption['next_url']))
				$res .= "  <a href=\"".$rs->caption['next_url']."\">".$rs->caption['next_txt']."</a>&nbsp;\n";

			$res .= " </caption>\n";
		}

		# Head line
		if ($rs->head) {
			$res .= " <thead>\n  <tr>\n";
			foreach($rs->head as $d) {
				$res .= "   <th>".$d['day_txt']."</th>\n";
			}
			$res .= "  </tr>\n </thead>\n";
		}

		# Rows
		if ($rs->rows) {
			$res .= " <tbody>\n";

			foreach($rs->rows as $r => $fields) {
				$res .= "  <tr>\n";
				foreach($fields as $f => $field) {
					if (' ' != $field) {
						$count = $eventdata->countEventOfDay($rs->year,$rs->month,$field);

						if ($count != 0) {
							$field = 
							'<a href="'.
							$core->blog->url.$core->url->getBase('eventdatapage').'/ongoing/'.
							urlencode(sprintf('%4d-%02d-%02d 00:00:00',$rs->year,$rs->month,$field)).'/'.
							urlencode(sprintf('%4d-%02d-%02d 00:00:00',$rs->year,$rs->month,$field)).
							'" title="'.
							($count == 1 ? __('one event') : sprintf(__('%s events'),$count)).
							'">'.$field.'</a>';
						}
					}
					$res .= "   <td".(2 < strlen($field) ? ' class="eventsday"' : '').">".$field."</td>\n";
				}
				$res .= "  </tr>\n";
			}
			$res .= " </tbody>\n";
		}
		$res .= "</table>\n";

		return $res;
	}
}
?>