<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Micro-Blogging, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jeremie Patonnier
# jeremie.patonnier@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

/**
 * mbMock is a class that mock up a service to make some test
 * 
 * @author jeremie Patonnier
 * @package microBlog
 * @subpackage microBlogService
 */
class mbMock extends microBlogService
{	
	public function __construct($user,$pwd)
	{
		parent::__construct($user, $pwd);
		
		$this->serviceId = md5("mock".$this->user);
	}
	
	public static function getServiceName(){return "Mock";}
	public static function requireKey(){return false;}

	public function sendNote($txt)
	{
		return false;
	}
	
	public function getUserTimeline($limit=20,$page=1,$since=NULL,$user=NULL)
	{
		$fake_id = time();
		 
		$out = array(
			$fake_id         => 'USER MOCK 1',
			$fake_id-3600    => 'USER MOCK 2',
			$fake_id-3600*2  => 'USER MOCK 3',
			$fake_id-3600*3  => 'USER MOCK 4',
			$fake_id-3600*4  => 'USER MOCK 5',
			$fake_id-3600*5  => 'USER MOCK 6',
			$fake_id-3600*6  => 'USER MOCK 7',
			$fake_id-3600*7  => 'USER MOCK 8',
			$fake_id-3600*8  => 'USER MOCK 9',
			$fake_id-3600*9  => 'USER MOCK 10',
			$fake_id-3600*10 => 'USER MOCK 11',
			$fake_id-3600*11 => 'USER MOCK 12',
			$fake_id-3600*12 => 'USER MOCK 13',
			$fake_id-3600*13 => 'USER MOCK 14',
			$fake_id-3600*14 => 'USER MOCK 15',
			$fake_id-3600*15 => 'USER MOCK 16',
			$fake_id-3600*16 => 'USER MOCK 17',
			$fake_id-3600*17 => 'USER MOCK 18',
			$fake_id-3600*18 => 'USER MOCK 19',
			$fake_id-3600*19 => 'USER MOCK 20',	
		);
		
		return $out;
	}
	
	public function getFriendsTimeline($limit=20,$page=1,$since=NULL)
	{
		$fake_id = time();
		 
		$out = array(
			$fake_id         => 'FRIEND MOCK 1',
			$fake_id-3600    => 'FRIEND MOCK 2',
			$fake_id-3600*2  => 'FRIEND MOCK 3',
			$fake_id-3600*3  => 'FRIEND MOCK 4',
			$fake_id-3600*4  => 'FRIEND MOCK 5',
			$fake_id-3600*5  => 'FRIEND MOCK 6',
			$fake_id-3600*6  => 'FRIEND MOCK 7',
			$fake_id-3600*7  => 'FRIEND MOCK 8',
			$fake_id-3600*8  => 'FRIEND MOCK 9',
			$fake_id-3600*9  => 'FRIEND MOCK 10',
			$fake_id-3600*10 => 'FRIEND MOCK 11',
			$fake_id-3600*11 => 'FRIEND MOCK 12',
			$fake_id-3600*12 => 'FRIEND MOCK 13',
			$fake_id-3600*13 => 'FRIEND MOCK 14',
			$fake_id-3600*14 => 'FRIEND MOCK 15',
			$fake_id-3600*15 => 'FRIEND MOCK 16',
			$fake_id-3600*16 => 'FRIEND MOCK 17',
			$fake_id-3600*17 => 'FRIEND MOCK 18',
			$fake_id-3600*18 => 'FRIEND MOCK 19',
			$fake_id-3600*19 => 'FRIEND MOCK 20',	
		);
		
		return $out;
	}
	
	public function search($query,$limit=20,$page=1,$since=NULL)
	{
		$fake_id = time();
		 
		$out = array(
			$fake_id         => 'SEARCH MOCK 1',
			$fake_id-3600    => 'SEARCH MOCK 2',
			$fake_id-3600*2  => 'SEARCH MOCK 3',
			$fake_id-3600*3  => 'SEARCH MOCK 4',
			$fake_id-3600*4  => 'SEARCH MOCK 5',
			$fake_id-3600*5  => 'SEARCH MOCK 6',
			$fake_id-3600*6  => 'SEARCH MOCK 7',
			$fake_id-3600*7  => 'SEARCH MOCK 8',
			$fake_id-3600*8  => 'SEARCH MOCK 9',
			$fake_id-3600*9  => 'SEARCH MOCK 10',
			$fake_id-3600*10 => 'SEARCH MOCK 11',
			$fake_id-3600*11 => 'SEARCH MOCK 12',
			$fake_id-3600*12 => 'SEARCH MOCK 13',
			$fake_id-3600*13 => 'SEARCH MOCK 14',
			$fake_id-3600*14 => 'SEARCH MOCK 15',
			$fake_id-3600*15 => 'SEARCH MOCK 16',
			$fake_id-3600*16 => 'SEARCH MOCK 17',
			$fake_id-3600*17 => 'SEARCH MOCK 18',
			$fake_id-3600*18 => 'SEARCH MOCK 19',
			$fake_id-3600*19 => 'SEARCH MOCK 20',	
		);
		
		return $out;
	}
}