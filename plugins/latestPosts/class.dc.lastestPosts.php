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
# D'apr� le plugin LastComment de Vincent Simonin et un billet de documentation 
# plugin du m�e auteur 
# visible sur http://www.forx.fr/post/2006/07/07/Doctclear-2-%3A-creation-de-plugin-premiere-partie
#
class lastestPosts
{
	private $blog;
	private $con;
	
	public function __construct(&$blog)
	{
		$this->blog =& $blog;
		$this->con =& $blog->con;
	}
	
	public function getLastestPosts($limit=10, $categ_show=0, $protect_show=0 )
	{
		
		//$req = "SELECT * ";
		$req = "SELECT post_title, cat_title, post_url ";
		$req.='FROM '.$this->blog->prefix.'post P '.
		'INNER JOIN '.$this->blog->prefix.'user U ON U.user_id = P.user_id '.
		'LEFT OUTER JOIN '.$this->blog->prefix.'category C ON P.cat_id = C.cat_id ';
		$req.="WHERE P.blog_id = '".$this->blog->id."' AND post_status = 1 AND post_type = 'post' ";
		if ($protect_show==0) {
		    $req.=" AND post_password IS NULL "; 
		}
		// Si affichage des catégories alors nous classons par ordre de catégorie
		if ($categ_show==0){
		    $req.="ORDER BY post_url DESC LIMIT 0 , ".(integer) $limit;
		} else {
			$req.="ORDER BY P.cat_id,P.post_id DESC LIMIT 0 , ".(integer) $limit;
		}
		//echo $req; 
		//echo "protect_show".$protect_show."<br />";
		try {
			$rs = $this->con->select($req);
			$rs = $rs->toStatic();
		} catch (Exception $e) {
			throw $e;			
			return null;
		}
		
		return $rs; 
		/*
		// Si il faut afficher les categories on applique un tri et pas de limites
		
		$req = "SELECT * ";
		$req.='FROM '.$this->blog->prefix.'post P '.
		'INNER JOIN '.$this->blog->prefix.'user U ON U.user_id = P.user_id '.
		'LEFT OUTER JOIN '.$this->blog->prefix.'category C ON P.cat_id = C.cat_id ';
		$req.="WHERE post_status = 1 ".
		"ORDER BY post_url DESC LIMIT 0 , ".(integer) $limit;
		echo $req;
		
		if($categ_show==1){
			$param = array(
				'order' => 'cat_title',
				'sql' => $req
		);
		} else {  
			$param = array(
				'limit' => (integer) $limit,
				'sql' => $req
			);
		}
		
		return $this->blog->getPosts($param,false); */
		
	}
}
?>
