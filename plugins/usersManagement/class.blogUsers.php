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

class blogUsers
{
	private $blog;
	private $con;
	private $tableUsers;
	private $tablePerms;

	private $users = array();
	
	public function __construct(&$blog)
	{
		$this->blog =& $blog;
		$this->con =& $blog->con;
		$this->tableUsers = $this->blog->prefix.'user';
		$this->tablePerms = $this->blog->prefix.'permissions';
	}
	
	public function addUser(&$cur)
	{
		if ($cur->user_id == '') {
			throw new Exception(__('No user ID given'));
		}
		
		if ($cur->user_pwd == '') {
			throw new Exception(__('No password given'));
		}
		
		$this->getUserCursor($cur);
		
		if ($cur->user_creadt === null) {
			$cur->user_creadt = array('NOW()');
		}
		
		$cur->insert();
				
		return $cur->user_id;
	}
	public function addDefaultPerm($user_id,$blog_id)
	{
		$cur = $this->con->openCursor($this->tablePerms);
		
		$cur->user_id = (string) $user_id;
		$cur->blog_id = (string) $blog_id;
		$cur->permissions = "|usage|";
		
		$cur->insert();
	}
	public function getFormPermission($user_perm,$perm_types,$user_id,$blog_id)
	{
		$permissionsForm="";
		
		foreach ($perm_types as $perm_id => $perm)
		{
			$checked = false;
			$checked = isset($user_perm[$blog_id]['p'][$perm_id]) && $user_perm[$blog_id]['p'][$perm_id];
			
			$permissionsForm.=
			'<p><label class="classic">'.
			form::checkbox(array('perm['.html::escapeHTML($blog_id).']['.html::escapeHTML($perm_id).']'),
			1,$checked).' '.
			__($perm).'</label></p>';
		}
		
		return $permissionsForm;
	}
	public function setPermission($user_id,$blog_id,$perms)
	{		
		$permissions=(Array) $perms[(string)$blog_id];
		$perms = '|'.implode('|',array_keys($permissions)).'|';
		$cur = $this->con->openCursor($this->tablePerms);
		
		$cur->user_id = (string) $user_id;
		$cur->blog_id = (string) $blog_id;
		$cur->permissions = $perms;
		
		$strReq = 'DELETE FROM '.$this->tablePerms.
				" WHERE blog_id = '".$this->con->escape($blog_id)."' ".
				" AND user_id = '".$this->con->escape($user_id)."' ";
		$this->con->execute($strReq);
		
		if ($perms!="||") 
		{
			$cur->insert();
		}
	}
	
	private function getUserCursor(&$cur)
	{
		if ($cur->isField('user_id')
		&& !preg_match('/^[A-Za-z0-9._-]{2,}$/',$cur->user_id)) {
			throw new Exception(__('User ID must contain at least 2 characters using letters, numbers or symbols.'));
		}
		
		if ($cur->user_url !== null && $cur->user_url != '') {
			if (!preg_match('|^http(s?)://|',$cur->user_url)) {
				$cur->user_url = 'http://'.$cur->user_url;
			}
		}
		
		if ($cur->isField('user_pwd')) {
			if (strlen($cur->user_pwd) < 6) {
				throw new Exception(__('Password must contain at least 6 characters.'));
			}
			$cur->user_pwd = crypt::hmac(DC_MASTER_KEY,$cur->user_pwd);
		}
		
		if ($cur->user_upddt === null) {
			$cur->user_upddt = array('NOW()');
		}
		
		if ($cur->user_options !== null) {
			$cur->user_options = serialize((array) $cur->user_options);
		}
	}
	
}

?>