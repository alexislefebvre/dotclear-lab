<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of DotClear.
#
# Plugin Bloganniv by Francis Trautmann
# Contributor: Pierre Van Glabeke
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# ***** END LICENSE BLOCK *****

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