<?php

# ***** BEGIN LICENSE BLOCK *****
# Widget SnapMe for DotClear.
# Copyright (c) 2007 Ludovic Toinel, All rights
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

class dcSnapMe
{
	private $blog;
	private $con;
	private $table;
	
	/**
	@function  __construct
	Constructeur
	
	@param	object	blog	Référence objet type blog
	*/
	public function __construct(&$blog)
	{
		$this->blog =& $blog;
		$this->con =& $blog->con;
		$this->table = $this->blog->prefix.'snapme';
	}

	/**
	@function addSnap
	Ajout d'un nouveau Snap
	
	@param	string	pseudo	   Pseudonyme de la personne
	@param	string	blog_url   Url du blog de la personne
	@param	string	ip 	   Ip de la personne
	@param	string	file_name  Nom du fichier de l'image
	*/
	public function addSnap($pseudo,$blog_url,$ip,$file_name)
	{

		$rs = $this->con->select(
			'SELECT MAX(id) AS max_snapme_id '.
			'FROM '.$this->table 
		);

		$tz = $this->blog->settings->blog_timezone;
		$local_time = dt::addTimeZone($tz);

		$cur = $this->con->openCursor($this->table);
		
		$cur->id = (integer) $rs->f(0) + 1;
		$cur->pseudo = (string) $pseudo;
		$cur->blog_url = (string) $blog_url;
		$cur->ip = (string) $ip;
		$cur->file_name = (string) $file_name;
		$cur->post_time = (string) $local_time;
		
		try {
			$cur->insert();
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	@function updateSnap
	Modification d'un Snap existant
	
	@param	string	pseudo	   Pseudonyme de la personne
	@param	string	blog_url   Url du blog de la personne
	@param	string	ip 	   Ip de la personne
	@param	string	file_name  Nom du fichier de l'image
	@param	integer	id	   Identifiant
	*/
	public function updateSnap($pseudo,$blog_url,$ip,$file_name,$id)
	{
		$tz = $this->blog->settings->blog_timezone;
		$local_time = dt::addTimeZone($tz);

		$cur = $this->con->openCursor($this->table);
		
		$cur->pseudo = (string) $pseudo;
		$cur->blog_url = (string) $blog_url;
		$cur->ip = (string) $ip;
		$cur->file_name = (string) $file_name;
		$cur->post_time = (string) $local_time;
		
		try {
			$cur->update('WHERE id = '.(integer) $id);
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	@function deleteSnap
	Suppression d'un snap
	
	@param	integer	id		Identifiant
	*/
	public function deleteSnap($id)
	{
		$strReq = 'DELETE FROM '.$this->table.' '.
				  'WHERE id = '.(integer) $id;
		
		try {
			$this->con->execute($strReq);
		} catch (Exception $e) {
			throw $e;
		}
	}

	/**
	@function getSnap
	Récupération d'un snap
	
	@param	integer	id		Identifiant
	return object type recordset
	*/
	public function getSnap($id)
	{
		$strReq = 'SELECT pseudo, blog_url, ip, file_name, post_time FROM '.$this->table.' '.
				  'WHERE id = '.(integer) $id;
		
		try {
			$rs = $this->con->select($strReq);
		} catch (Exception $e) {
			throw $e;
			return null;
		}
	
		return $rs;
	}
	
	
	/**
	@function getAllSpnaps
	Récupération de toutes les données

	return la liste de type recordset
	*/
	public function getAllSnaps($max=0)
	{
		$strReq = 'SELECT id, pseudo, blog_url, ip, file_name, post_time FROM '.$this->table.' ORDER by post_time DESC LIMIT '.$max;
				 
		try {
			$rs = $this->con->select($strReq);
		} catch (Exception $e) {
			throw $e;
			return null;
		}

		return $rs;
	}
	
	
	/**
	@function getLastSnap
	Récupération du dernier snap
	
	return la liste de type recordset
	*/
	public function getLastSnap()
	{
		$strReq = 'SELECT id, pseudo, blog_url, ip, file_name, post_time FROM '.$this->table.' ORDER by post_time DESC limit 1';

		try {
			$rs = $this->con->select($strReq);
		} catch (Exception $e) {
			throw $e;
			return null;
		}
	
		return $rs;
	}

        /**
        @function getCountSnap
        Récupération du nombre de snap

        return la liste de type recordset
        */
        public function getCountSnap()
        {
                $strReq = 'SELECT count(id) as count FROM '.$this->table;

                try {
                        $rs = $this->con->select($strReq);
                } catch (Exception $e) {
                        throw $e;
                        return null;
                }

                return $rs;
        }


	/**
	@function getLastSnapByLogin
	Récupération du dernier snap pour le login fournit en entrée
	
	return le dernier snap
	*/
	public function getLastSnapByNickname($nickname)
	{
		$strReq = 'SELECT id, pseudo, blog_url, ip, file_name, post_time FROM '.$this->table.' WHERE pseudo=\''.$nickname.'\'  ORDER by post_time DESC limit 1';

		try {
			$rs = $this->con->select($strReq);
		} catch (Exception $e) {
			throw $e;
			return null;
		}
	
		return $rs;
	}


	/**
	@function getRandomSnap
	Récupération aléatoire d'un snap

	return un snap aléatoirement 
	*/
	public function getRandomSnap()
	{
		$strReq = 'SELECT id, pseudo, blog_url, ip, file_name, post_time FROM '.$this->table.' ORDER by rand() limit 1';

		try {
			$rs = $this->con->select($strReq);
		} catch (Exception $e) {
			throw $e;
			return null;
		}

		return $rs;
	}
}

?>