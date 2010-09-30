<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of lunarPhase, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class lunarPhase
{
	# Astronomical constants.
	const epoch		= 2444238.5;							# 1980 January 0.0

	# Constants defining the Sun's apparent orbit.
	const elonge		= 278.833540;							# ecliptic longitude of the Sun at epoch 1980.0
	const elongp		= 282.596403;							# ecliptic longitude of the Sun at perigee
	const eccent		= 0.016718;							# eccentricity of Earth's orbit
	const sunsMax		= 1.495985e8;							# semi-major axis of Earth's orbit, km
	const sunAngSiz	= 0.533128;							# sun's angular size, degrees, at semi-major axis distance

	# Elements of the Moon's orbit, epoch 1980.0.
	const mmLong		= 64.975464;							# moon's mean longitude at the epoch
	const mmLongp		= 349.383063;							# mean longitude of the perigee at the epoch
	const mlNode		= 151.950429;							# mean longitude of the node at the epoch
	const mInc		= 5.145396;							# inclination of the Moon's orbit
	const mEcc		= 0.054900;							# eccentricity of the Moon's orbit
	const mAngSiz		= 0.5181;								# moon's angular size at distance a from Earth
	const msMax		= 384401.0;							# semi-major axis of Moon's orbit in km
	const mParallax	= 0.9507;								# parallax at distance a from Earth
	const synodic		= 29.53058868;							# synodic month (new Moon to new Moon)
	
	protected $live;
	protected $previsions;
	
	public function __construct()
	{
		$this->live = new ArrayObject;
		$this->previsions = new ArrayObject;
		
		$this->setLive();
		$this->setPrevisions();
	}
	
	public function getLive()
	{
		return $this->live;
	}
	
	public function getPrevisions()
	{
		return $this->previsions;
	}
	
	private function setLive()
	{
		$day = $this->jTime(time()) - self::epoch;
		
		# Calculate sun's position and angle
		$N = $this->fixAngle((360 / 365.2422) * $day);
		$M = $this->fixAngle($N + self::elonge - self::elongp);
		$Ec = $this->kepler($M, self::eccent);
		$Ec = sqrt((1 + self::eccent) / (1 - self::eccent)) * tan($Ec / 2);
		$Ec = 2 * $this->toDeg(atan($Ec));
		$lambdaSun = $this->fixAngle($Ec + self::elongp);
		$F = ((1 + self::eccent * cos($this->toRad($Ec))) / (1 - self::eccent * self::eccent));
		
		# Calculate moon's age, position and angle
		$ml = $this->fixAngle(13.1763966 * $day + self::mmLong);
		$MM = $this->fixAngle($ml - 0.1114041 * $day - self::mmLongp);
		$MN = $this->fixAngle(self::mlNode - 0.0529539 * $day);
		$Ev = 1.2739 * sin($this->toRad(2 * ($ml - $lambdaSun) - $MM));
		$Ae = 0.1858 * sin($this->toRad($M));
		$A3 = 0.37 * sin($this->toRad($M));
		$MmP = $MM + $Ev - $Ae - $A3;
		$mEc = 6.2886 * sin($this->toRad($MmP));
		$A4 = 0.214 * sin($this->toRad(2 * $MmP));
		$lP = $ml + $Ev + $mEc - $Ae + $A4;
		$V = 0.6583 * sin($this->toRad(2 * ($lP - $lambdaSun)));
		$lPP = $lP + $V;
		$NP = $MN - 0.16 * sin($this->toRad($M));
		$y = sin($this->toRad($lPP - $NP)) * cos($this->toRad(self::mInc));
		$x = cos($this->toRad($lPP - $NP));
		$lambdaMoon = $this->toDeg(atan2($y, $x));
		$lambdaMoon += $NP;
		$mage = $lPP - $lambdaSun;
		$BetaM = $this->toDeg(asin(sin($this->toRad($lPP - $NP)) * sin($this->toRad(self::mInc))));
		
		$this->live['illumination']	= (1 - cos($this->toRad($mage))) / 2;
		$this->live['age']			= self::synodic * ($this->fixAngle($mage) / 360.0); 
		$this->live['dist_to_earth']	= (self::msMax * (1 - self::mEcc * self::mEcc)) / (1 + self::mEcc * cos($this->toRad($MmP + $mEc)));
		$this->live['dist_to_sun']	= self::sunsMax / $F;
		$this->live['sun_angle']		= $F * self::sunAngSiz;
		$this->live['moon_angle']	= self::mAngSiz / ($this->live['dist_to_earth'] / self::msMax);
		$this->live['parallax']	= self::mParallax / ($this->live['dist_to_earth'] / self::msMax);
		
		$this->setPhase();
	}
	
	private function setPhase()
	{
		if ($this->live['age'] >= self::synodic && $this->live['age'] <= self::synodic/8) {
			$this->live['id'] = 'new_moon';
			$this->live['name'] = __('New moon');
		}
		elseif ($this->live['age'] >= self::synodic/8 && $this->live['age'] <= self::synodic/4) { 
			$this->live['id'] = 'waxing_crescent_moon';
			$this->live['name'] = __('Waxing crescent moon');
		}
		elseif ($this->live['age'] >= self::synodic/4 && $this->live['age'] <= self::synodic*3/8) {
			$this->live['id'] = 'first_quarter_moon';
			$this->live['name'] = __('First quarter moon');
		}
		elseif ($this->live['age'] >= self::synodic*3/8 && $this->live['age'] <= self::synodic/2) {
			$this->live['id'] = 'waxing_gibbous_moon';
			$this->live['name'] = __('Waxing gibbous moon');
		}
		elseif ($this->live['age'] >= self::synodic/2 && $this->live['age'] <= self::synodic*5/8) {
			$this->live['id'] = 'full_moon';
			$this->live['name'] = __('Full moon');
		}
		elseif ($this->live['age'] >= self::synodic*5/8 && $this->live['age'] <= self::synodic*3/4) {
			$this->live['id'] = 'waning_gibbous_moon';
			$this->live['name'] = __('Waning gibbous moon');
		}
		elseif ($this->live['age'] >= self::synodic*3/4 && $this->live['age'] <= self::synodic*7/8) {
			$this->live['id'] = 'last_quarter_moon';
			$this->live['name'] = __('Last quarter moon');
		}
		elseif ($this->live['age'] >= self::synodic*7/8 && $this->live['age'] <= self::synodic/8) {
			$this->live['id'] = 'waning_crescent_moon';
			$this->live['name'] = __('Waning crescent moon');
		}
	}
	
	private function setPrevisions()
	{
		$ts_day = 24*60*60;
		$ts_synodic = self::synodic * $ts_day;
		$start = time() - $this->live['age'] * $ts_day;
		
		$this->previsions = new ArrayObject;
		$this->previsions['waxing_crescent_moon'] = $start + $ts_synodic / 8;
		$this->previsions['first_quarter_moon'] = $start + $ts_synodic / 4;
		$this->previsions['waxing_gibbous_moon'] = $start + $ts_synodic * 3 / 8;
		$this->previsions['full_moon'] = $start + $ts_synodic / 2;
		$this->previsions['waning_gibbous_moon'] = $start + $ts_synodic * 5 / 8;
		$this->previsions['last_quarter_moon'] = $start + $ts_synodic * 3 / 4;
		$this->previsions['waning_crescent_moon'] = $start + $ts_synodic * 7 / 8;
		$this->previsions['new_moon'] = $start + $ts_synodic;
		
	}
	
	private function fixAngle($x)
	{
		return ($x - 360.0 * (floor($x / 360.0)));
	}
	
	private function toRad($x)
	{
		return ($x * (M_PI / 180.0));
	}
	
	private function toDeg($x)
	{
		return ($x * (180.0 / M_PI));
	}
	
	private function jTime($t)
	{
		return ($t / 86400) + 2440587.5;
	}
	
	private function kepler($m, $ecc)
	{
		$delta = null;
		$EPSILON = 1e-6;
		
		$m = $this->toRad($m);
		$e = $m;
		while (abs($delta) > $EPSILON)
		{
			$delta = $e - $ecc * sin($e) - $m;
			$e -= $delta / (1 - $ecc * cos($e));
		}
		return ($e);
	}
}

?>