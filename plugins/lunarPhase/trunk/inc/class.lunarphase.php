<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lunarPhase, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class lunarPhase
{
	protected $phase;
	protected $previsions;
	protected $references;

	/**
	 * Constructor of the class... What else? (Yes, i drink Nespresso ;))
	 */
	public function __construct($core,$w)
	{
		$this->core =& $core;
		$this->w    =& $w;

		$this->setReferences();
		$this->setPhase();
		$this->setPrevisions();
	}

	public function getPhase()
	{
		return $this->phase;
	}

	public function getPrevisions()
	{
		return $this->previsions;
	}

	/**
	 * Calculates and defines the current moon phase
	 */
	private function setPhase()
	{
		$ts = time() - $this->references->new_moon;

		$this->phase = new stdClass();
		$this->phase->value = abs(($ts % $this->references->day_in_sec) / $this->references->day_in_sec);

		if ($this->phase->value >= 0.474 && $this->phase->value <= 0.53) {
			$this->phase->id = 'new_moon';
			$this->phase->name = __('New moon');
		}
		elseif ($this->phase->value >= 0.53 && $this->phase->value <= 0.724) {
			$this->phase->id = 'waxing_crescent_moon';
			$this->phase->name = __('Waxing crescent moon');
		}
		elseif ($this->phase->value >= 0.724 && $this->phase->value <= 0.776) {
			$this->phase->id = 'first_quarter_moon';
			$this->phase->name = __('First quarter moon');
		}
		elseif ($this->phase->value >= 0.776 && $this->phase->value <= 0.974) {
			$this->phase->id = 'waxing_gibbous_moon';
			$this->phase->name = __('Waxing gibbous moon');
		}
		elseif ($this->phase->value >= 0.974 || $this->phase->value <= 0.026) {
			$this->phase->id = 'full_moon';
			$this->phase->name = __('Full moon');
		}
		elseif ($this->phase->value >= 0.026 && $this->phase->value <= 0.234) {
			$this->phase->id = 'waning_gibbous_moon';
			$this->phase->name = __('Waning gibbous moon');
		}
		elseif ($this->phase->value >= 0.234 && $this->phase->value <= 0.295) {
			$this->phase->id = 'last_quarter_moon';
			$this->phase->name = __('Last quarter moon');
		}
		elseif ($this->phase->value >= 0.295 && $this->phase->value <= 0.4739) {
			$this->phase->id = 'waning_crescent_moon';
			$this->phase->name = __('Waning crescent moon');
		}
	}

	/**
	 * Calculates all other previsions
	 */
	private function setPrevisions()
	{
		$this->previsions = new stdClass();

		$this->calcNewMoon();
		$this->calcFirstQuarterMoon();
		$this->calcFullMoon();
		$this->calcLastQuarterMoon();
		$this->calcIllumination();
	}

	/**
	 * Defines references for the plugin
	 */
	private function setReferences()
	{
		$this->references = new stdClass();

		$this->references->synodic_moon = (1/((1/27.322)-(1/365.25)))*24*60*60;
		$this->references->new_moon = mktime(6,39,0,2,16,1999);
		$this->references->day_in_sec = 60*60*24;
	}

	/**
	 * Calculates the next new moon
	 */
	private function calcNewMoon()
	{
		if ($this->phase->value < 0.5) {
			$ts = (0.5 - $this->phase->value) * $this->references->synodic_moon;
		}
		elseif ($this->phase->value >= 0.5) {
			$ts = (1.5 - $this->phase->value) * $this->references->synodic_moon;
		}

		$this->previsions->new_moon = new stdClass();
		$this->previsions->new_moon->id = 'new_moon';
		$this->previsions->new_moon->days = $this->tsToDays($ts);
		$this->previsions->new_moon->date = $this->tsToDate($ts);
	}

	/**
	 * Calculates the next first quarter moon
	 */
	private function calcFirstQuarterMoon()
	{
		if ($this->phase->value < 0.75) {
			$ts = (0.75 - $this->phase->value) * $this->references->synodic_moon;
		}
		elseif ($this->phase->value >= 0.75) {
			$ts = (1.75 - $this->phase->value) * $this->references->synodic_moon;
		}

		$this->previsions->first_quarter_moon = new stdClass();
		$this->previsions->first_quarter_moon->id = 'first_quarter_moon';
		$this->previsions->first_quarter_moon->days = $this->tsToDays($ts);
		$this->previsions->first_quarter_moon->date = $this->tsToDate($ts);
	}

	/**
	 * Calculates the next fulle moon
	 */
	private function calcFullMoon()
	{
		$ts = (1 - $this->phase->value) * $this->references->synodic_moon;
	
		$this->previsions->full_moon = new stdClass();
		$this->previsions->full_moon->id = 'full_moon';
		$this->previsions->full_moon->days = $this->tsToDays($ts);
		$this->previsions->full_moon->date = $this->tsToDate($ts);
	}

	/**
	 * Calculates the next last quarter moon
	 */
	private function calcLastQuarterMoon()
	{
		if ($this->phase->value < 0.25) {
			$ts = (0.25 - $this->phase->value) * $this->references->synodic_moon;
		}
		elseif ($this->phase->value >= 0.25) {
			$ts = (1.25 - $this->phase->value) * $this->references->synodic_moon;
		}

		$this->previsions->last_quarter_moon = new stdClass();
		$this->previsions->last_quarter_moon->id = 'last_quarter_moon';
		$this->previsions->last_quarter_moon->days = $this->tsToDays($ts);
		$this->previsions->last_quarter_moon->date = $this->tsToDate($ts);
	}

	/**
	 * Calculates the current illumination of the moon
	 */
	private function calcIllumination()
	{
		$this->previsions->illumination = new stdClass();
		$this->previsions->illumination->id = 'illumination';
		$this->previsions->illumination->value = round(((1.0 + cos(2.0 * M_PI * $this->phase->value)) / 2.0) * 100,1);
	}

	/**
	 * Returns date according to the timestamp in argument
	 *
	 * @param	timestamp	ts
	 *
	 * @return	timestamp
	 */
	private function tsToDate($ts)
	{
		$format = !empty($this->w->format_date) ? $this->w->format_date : $this->core->blog->settings->date_format;

		return dt::str($format,$ts + time());
	}

	/**
	 * Return the time passed an argument converting in days
	 *
	 * @param	time	ts
	 *
	 * @return	float
	 */
	private function tsToDays($ts)
	{
		return round($ts / $this->references->day_in_sec,1);
	}
}

?>