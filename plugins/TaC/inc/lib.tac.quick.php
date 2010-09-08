<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of TaC, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

/* Functions to use on user twitter account */
class tacQuick
{
	public $tac;
	
	public function __Construct($tac)
	{
		if (!($tac instanceof TaC)) {
			throw New Exception(__('tacQuick required TaC'));
		}
		$this->tac = $tac;
	}
	
	private function check()
	{
		# Auth not done
		if (!$this->tac->checkAccess()) {
			throw New Exception(__('You must log on before use tacQuick.'));
		}
	}
	
	/**
	 * Timeline Methods.
	 */
	
	public function statusesPublicTimeline() { return false; } //get
	public function statusesHomeTimeline() { return false; } //get
	public function statusesFriendsTimeline() { return false; } //get
	public function statusesUserTimeline() { return false; } //get
	public function statusesMentions() { return false; } //get
	public function statusesRetweetedByMe() { return false; } //get
	public function statusesRetweetedToMe() { return false; } //get
	public function statusesretweetsofMe() { return false; } //get
	
	/**
	 * Status Methods.
	 */
	
	# Send message $str to user status (timeline)
	public function statusesUpdate($message,$shorten=false)
	{
		$this->check();
		
		# Clean message
		$message = (string) $message;
		$message = trim($message);
		# Empty message
		if (!$message) {
			throw New Exception(__('Nothing to send.'));
		}
		# Shorten urls
		if ($shorten) {
			throw new Exception(__('Not yet implemented'));
		}
		# Split into smaller messages
		$parts = tacTools::splitStr($message,140);
		$count = count($parts);
		# Loop throught lines of message
		foreach($parts as $k => $line)
		{
			if ($count > 1) {
				$line = ($k+1).'/'.$count.' ';
			}
			$params = array('status' => (string) $line);
			# Send message
			$status_id[] = $this->tac->post('statuses/update',$params);
		}
		return $status_id;
	}
	
	public function statusesShow($status_id) { return false; } //get
	public function statusesDestroy($status_id) { return false; } //delete
	public function statusesReweet($status_id) { return false; } //post
	public function statusesRetweets($status_id) { return false; } //get
	
	/**
	 * User Methods.
	 */
	
	public function usersShow($user_id) { return false; } //get
	public function usersSearch($needle,$params=array('q'=>'oauth')) { return false; } //get
	public function usersFriends($user_id) { return false; } //get
	public function usersFollowers($user_id) { return false; } //get
	
	/**
	 * List Methods.
	 */
	
	/**
	 * List Members Methods.
	 */
	
	/**
	 * List Subscribers Methods.
	 */
	
	/**
	 * Direct Message Methdos.
	 */
	
	/**
	 * Friendships Methods.
	 */
	
	/**
	 * Social Graph Methods.
	 */
	
	/**
	 * Account Methods.
	 */
	
	public function accountVerifyCreditentials()
	{
		$this->ckeck();
		return $this->tac->get('account/verify_creditentials');
	}
	
	public function accountRateLimitStatus()
	{ 
		$this->check();
		return $this->tac->get('account/rate_limit_status');
	}
	public function accountUpdateProfileColors($params=array('profile_background_color'=>'FFF')) { return false; } //post
	public function accountUpdateProfile($params=array('location'=>'Into Dotclear')) { return false; } //post
}
?>