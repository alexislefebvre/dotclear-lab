<?php
# ***** BEGIN LICENSE BLOCK *****
#
# Tribune Libre is a small chat system for Dotclear 2
# Copyright (C) 2007  Antoine Libert
# 
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****
// Highly based on blogroll ;-)

class dcTribune
{
	private $blog;
	private $con;
	private $table;
	
	public function __construct(&$blog)
	{
		$this->blog =& $blog;
		$this->con =& $blog->con;
		$this->table = $this->blog->prefix.'tribune';
	}
	
	public function addMsg($nick, $message, $time, $ip, $state=1)
	{
    	$cur = $this->con->openCursor($this->table);
		
		$cur->blog_id = (string) $this->blog->id;
		$cur->tribune_nick = (string) $nick;
		$cur->tribune_msg = (string) $message;
		$cur->tribune_dt = $time ? date('Y-m-d H:i:s',$time) : '';
		$cur->tribune_ip = (string) $ip;
		$cur->tribune_state = (integer) $state;
		
		if ($cur->tribune_nick == '') {
			throw new Exception(__('You must provide a nick'));
		}
		
		if ($cur->tribune_msg == '') {
			throw new Exception(__('You must provide a message'));
		}
		
		if ($cur->tribune_ip == '') {
			throw new Exception(__('You must provide a ip'));
		}
		
		$strReq = 'SELECT MAX(tribune_id) FROM '.$this->table;
		$rs = $this->con->select($strReq);
		$cur->tribune_id = (integer) $rs->f(0) + 1;
		
		$cur->insert();
		$this->blog->triggerBlog();
		return true;
  }

	public function delMsg($id)
	{
		$strReq = 'DELETE FROM '.$this->table.' '.
				"WHERE blog_id = '".$this->con->escape($this->blog->id)."' ".
				'AND tribune_id = '.(integer) $id.' ';
		
		$this->con->execute($strReq);
		$this->blog->triggerBlog();
	}

	public function updateMsg($id, $nick, $message)
	{
		$cur = $this->con->openCursor($this->table);
		
		$cur->tribune_nick = (string) $nick;
		$cur->tribune_msg = (string) $message;
		
		if ($cur->tribune_nick == '') {
			throw new Exception(__('You must provide a nick'));
		}
		
		if ($cur->tribune_msg == '') {
			throw new Exception(__('You must provide a message'));
		}
		
		$cur->update('WHERE tribune_id = '.(integer) $id.
			" AND blog_id = '".$this->con->escape($this->blog->id)."'");
		$this->blog->triggerBlog();
	}
  
	public function changeState($id, $state, $check=false, $time, $deltime, $ip)
	{
		if ($check) {
			$strReq = 'SELECT tribune_id FROM '.$this->table." WHERE tribune_dt > '".(string) date('Y-m-d H:i',$time - $deltime)."' AND tribune_ip = '".(string) $this->con->escape($ip)."' AND tribune_id = '".(integer) $_GET['tribdel']."' ORDER BY tribune_id DESC";
			$strReq .= $this->con->limit(1);
			$rs = $this->con->select($strReq);
			$rs = $rs->toStatic();
			
			if (empty($rs))
				return false;
		}
	
		$cur = $this->con->openCursor($this->table);
		
		$cur->tribune_state = (string) $state;
			
		$cur->update('WHERE tribune_id = '.(integer) $id.
			" AND blog_id = '".$this->con->escape($this->blog->id)."'");
		$this->blog->triggerBlog();
		return true;
	}
	
	public function getMsg($limit, $orderasc=false, $mode=1)
	{
		$strReq = 'SELECT *';
		
		$strReq .= ' FROM '.$this->table.
			" WHERE blog_id = '".$this->con->escape($this->blog->id)."'";
		
		if ($mode == 1) {
			$strReq .= ' AND tribune_state = 1';
		} else if ($mode == 0) {
			$strReq .= ' AND tribune_state = 0';
		} else {
			$strReq .= ' AND tribune_state IS NOT NULL';
		}
		
		$strReq .= ' ORDER BY tribune_id DESC';
		$strReq .= ($limit > 0) ? $this->con->limit($limit) : null;
		
		$rs = $this->con->select($strReq);
		$rs = $rs->toStatic();
				
		if ($orderasc) {
			$rs->sort('tribune_time','asc');
		}
		
		return $rs;
	}
	
	public function getOneMsg($id)
	{
		# On récupère une seule ligne
		$strReq = 'SELECT tribune_id, tribune_nick, tribune_msg FROM '.$this->table." WHERE tribune_id = '".(integer) $id."'";
		
		$rs = $this->con->select($strReq);
		$rs = $rs->toStatic();
		
		return $rs;
	}
	
	public function cleanMsg($msg,$chcut)
	{
		# Nettoyage
		$msg = strip_tags($msg);
		
		# Tronquage des mots longs SANS couper les URLs... 
		$words = explode(" ",$msg);
		foreach($words as $key => $value_copy) {
		$value =& $words[$key];
	
			if (!ereg("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]",$value)) {
				$value = wordwrap($value,(integer) $chcut," ",1);
			}
		}
	
		$msg = implode(" ",$words);
		
		# Toutes les séquences de 2 espaces consécutifs ou plus sont remplacées par un espace unique.
		$msg = ereg_replace("[ ]{2,}"," ",$msg);
		
		# url2link
		$msg = eregi_replace(
			"([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])",
			"<a href=\"\\1://\\2\\3\" title=\"\\1://\\2\\3\" rel=\"nofollow\">[url]</a>",$msg);
		
		# mail2link
		$msg = eregi_replace( "(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))",
			"<a href=\"mailto:\\1\" title=\"\\1\">[mail]</a>",$msg);
		
		return $msg;
	}
}
?>
