<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dayMode, a plugin for Dotclear 2.
#
# Copyright (c) 2006-2009 Pep and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class dcCalendar
{
	const	SUNDAY_TS = 1042329600;

	protected $base 	 = null;
	protected $dts  	 = null;
	protected $post_type = 'post';

	public $weekstart = 0;

	public function __construct($core, $_ctx, $post_type = 'post')
	{
		$this->core 	  = $core;
		$this->blog 	  = $core->blog;
		$this->con  	  = $core->blog->con;
		$this->post_type = $post_type;

		$year = $month = '';
		$this->cday = 0;
		if ($_ctx->exists('day')) {
			$month = $_ctx->day->month();
			$year  = $_ctx->day->year();
			$this->cday = (integer)$_ctx->day->day();
		}
		elseif ($_ctx->exists('archives')) {
			$month = $_ctx->archives->month();
			$year  = $_ctx->archives->year();
		}
		else {
			$recent = dcDayTools::getEarlierDate(array('post_type' => $this->post_type));
			$month = $recent->month();
			$year  = $recent->year();
		}

		$month_dates = $this->blog->getDates(
			array(
				'month'	  => $month,
				'year'	  => $year,
				'post_type' => $this->post_type
			)
		);

		$this->dts = array();
		while ($month_dates->fetch()) {
			$this->dts[] = $month_dates->dt;
		}

		$this->base = array(
			'dt'    => date('Y-m-01 00:00:00',strtotime($month_dates->dt)),
			'url'   => $month_dates->url($this->core),
			'month' => $month,
			'year'  => $year
		);

		$this->base['ts'] = strtotime($this->base['dt']);
	}


	public function draw()
	{
		$link_next = $link_prev = '';

		$l_next = $this->blog->getDates(
			array(
				'next'	  => $this->base['dt'],
				'type'	  => 'month',
				'post_type' => $this->post_type
			)
		);
		if (!$l_next->isEmpty()) {
			$link_next =
			' <a href="'.$l_next->url($this->core).'" title="'.
			dt::str('%B %Y', $l_next->ts()).'">&#187;</a>';
		}

		$l_prev = $this->blog->getDates(
			array(
				'previous' => $this->base['dt'],
				'type' => 'month',
				'post_type' => $this->post_type
			)
		);
		if (!$l_prev->isEmpty()) {
			$link_prev = '<a href="'.$l_prev->url($this->core).'" title="'.
			dt::str('%B %Y', $l_prev->ts()).'">&#171;</a> ';
		}

		$res =
			'<table summary="'.__('Calendar').'">'.
			'<caption>'.
			$link_prev.
			dt::str('%B %Y',$this->base['ts']).
			$link_next.
			'</caption>';

		$first_ts = self::SUNDAY_TS + ((integer)$this->weekstart * 86400);
		$last_ts = $first_ts + (6 * 86400);
		$first = date('w',$this->base['ts']);
		$first = ($first == 0)?7:$first;
		$first = $first - $this->weekstart;
		$limit = date('t',$this->base['ts']);

		$res .= '<thead><tr>';
		for ($j = $first_ts; $j <= $last_ts; $j = $j+86400) {
			$res .=
				'<th scope="col"><abbr title="'.dt::str('%A',$j).'">'.
				dt::str('%a',$j).'</abbr></th>';
		}

		$res .= '</tr></thead><tbody>';
		$d = 1; $i = 0; $dstart = false;
		$y = $this->base['year'];
		$m = $this->base['month'];

		while ($i < 42) {
			if ($i%7 == 0) {
				$res .= '<tr>';
			}
			if ($i == $first) {
				$dstart = true;
			}
			if ($dstart && !checkdate($m,$d,$y)) {
				$dstart = false;
			}
			if (in_array(sprintf('%4d-%02d-%02d 00:00:00',$y,$m,$d),$this->dts)) {
				$url = $this->base['url'].'/'.sprintf('%02d',$d);
				$link = '<a href="'.$url.'">%s</a>';
			} else {
				$link = '%s';
			}

			$class = ($this->cday == $d && $dstart)?' class="active"':'';

			$res .= '<td'.$class.'>';
			$res .= ($dstart)? sprintf($link,$d):' ';
			$res .= '</td>';

			if (($i+1)%7 == 0) {
				$res .= '</tr>';
				if ($d>=$limit) { $i = 42; }
			}
			$i++;
			if ($dstart) { $d++; }
		}

		$res .= '</tbody></table>';

		return $res;
	}
}

class dcDayTools
{
	public static function getEarlierDate($params = array())
	{
		global $core;

		$cat_field = $catReq = $limit = '';
		if (isset($params['ts_type']) && $params['ts_type'] == 'day') {
			$dt_f = '%Y-%m-%d 00:00:00';
		} else {
			$dt_f = '%Y-%m-%d %H:%M:%S';
		}

		if (!empty($params['cat_id'])) {
			$catReq = 'AND P.cat_id = '.(integer) $params['cat_id'].' ';
			$cat_field = ', C.cat_url ';
		}
		elseif (!empty($params['cat_url'])) {
			$catReq = "AND C.cat_url = '".$core->blog->con->escape($params['cat_url'])."' ";
			$cat_field = ', C.cat_url ';
		}

		$strReq = 'SELECT DISTINCT('.$core->blog->con->dateFormat('MAX(post_dt)',$dt_f).') AS dt '.
				'FROM '.$core->blog->prefix.'post P LEFT JOIN '.
				$core->blog->prefix.'category C '.
				'ON P.cat_id = C.cat_id '.
				"WHERE P.blog_id = '".$core->blog->con->escape($core->blog->id)."' ".
				$catReq;

		if (!$core->auth->check('contentadmin',$core->blog->id)) {
			$strReq .= 'AND ((post_status = 1 ';

			if ($core->blog->without_password) {
				$strReq .= 'AND post_password IS NULL ';
			}
			$strReq .= ') ';

			if ($core->auth->userID()) {
				$strReq .= "OR P.user_id = '".$core->blog->con->escape($core->auth->userID())."')";
			}
			else {
				$strReq .= ') ';
			}
		}

		if (!empty($params['post_type'])) {
			$strReq .= "AND post_type = '".$core->blog->con->escape($params['post_type'])."' ";
		}
				
		if (!empty($params['cat_id'])) {
			$strReq .= 'AND P.cat_id = '.(integer) $params['cat_id'].' ';
		}

		if (!empty($params['cat_url'])) {
			$strReq .= "AND C.cat_url = '".$core->blog->con->escape($params['cat_url'])."' ";
		}

		$rs = $core->blog->con->select($strReq);
		$rs->extend('rsExtDates');
		return $rs;
	}
}
?>