<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
#
#
class dateBlog
{
	private $blog;
	private $con;
	
	public function __construct($blog)
	{
		$this->blog =& $blog;
		$this->con =& $blog->con;
	}
	
	public function getdateBlog()
	{
		$req = "SELECT blog_creadt,blog_id ".
		"FROM ".$this->blog->prefix."blog ".
		"WHERE blog_id = '".$this->blog->con->escape($this->blog->id)."' and blog_status = 1";
		try {
			$rs = $this->con->select($req);
			$rs = $rs->toStatic();
		} catch (Exception $e) {
			throw $e;			
			return null;
		}
		
		return $rs;
		
	}
}
?>
