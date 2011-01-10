<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Annuaire, a plugin for Dotclear.
# 
# Copyright (c) 2010 Marc Vachette
# marc.vachette@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcAnnuaire {
		
	// test si la table est installée
	static public function isInstalled() {
		global $core;
		$con =& $core->con;

		$s = new dbStruct($con,$core->prefix);
		$s->reverse();
		$tables = $s->getTables();
		if (!$s->tableExists($core->prefix.'annuaire'))
			return FALSE;

		return TRUE;
	}

	// récupçre la categorie par son identifiant
	static public function get($_id)
	{
		global $core;
		$result = array();
		
		$strReq =
			'SELECT *'.
			' FROM '.$core->prefix.'annuaire'.
			' WHERE category_id='.(integer)$_id;
		$rs = $core->con->select($strReq);
		if ($rs->isEmpty())
			return 0;

		$result['category_id'] = $rs->f('category_id');
		$result['title'] = $rs->f('title');
		$result['url'] = $rs->f('url');
			
		return $result;
	}
	
	static public function getFromURL($_url)
	{
		global $core;
		//$_result = array();
		
		$strReq =
				'SELECT *'.
				' FROM '.$core->prefix.'annuaire'.
				' WHERE url =\''.(string)$_url.'\'';
			$rs = $core->con->select($strReq);
			if ($rs->isEmpty()) {
				return;
			}
			/*
			var_dump($rs->f);
			$_result['category_id'] = $rs->f('category_id');
			$_result['title'] = $rs->f('title');
			$_result['url'] = $rs->f('url');
			*/
			//$rs2=$rs->rows();
			
			//print_r($rs2);
			return $rs;
	}
	
	// test si la categorie éxiste par son identifiant
	static public function isExist($_id)
	{
		if (is_numeric($_id) === FALSE)
			return FALSE;
			
		if (dcAnnuaire::get($_id) == 0)
			return FALSE;
		
		return TRUE;
	}
	
	// met à jour la categorie
	static public function update($_id, $_title)
	{
		if (self::isExist($_id) == FALSE)
			return FALSE;
			
		global $core;
		
		$strReq =
			'UPDATE '.$core->prefix.'annuaire'.
			' SET title=\''.$core->con->escape((string)$_title).'\', url=\''.text::str2URL($_title).'\' '.
			' WHERE category_id='.$_id;
			

		$rs = $core->con->execute($strReq);
		if ($rs)
			return TRUE;
			
		return FALSE;
	}

	/**
	* génère le prochain id de categorie
	*/
    static public function nextid()
    {
        global $core;
        
        $strReq =
			'SELECT max(category_id)' .
			' FROM ' . $core->prefix . 'annuaire';

		$rs = $core->con->select($strReq);
        if ($rs->isEmpty())
            return 0;

        $id = ( (integer) $rs->f(0) ) +1;

        return $id;
    }
	
	// ajoute une categorie
	static public function add($_title) {
		global $core;
		
		$id = self::nextid();
		$strReq =
			'INSERT INTO '.$core->prefix.'annuaire'.
			' (category_id, title, url)'.
			' VALUES (\'' . (integer)$id . '\', \''.$core->con->escape((string)$_title).'\',\''.text::str2URL($_title).'\')';

		$rs = $core->con->execute($strReq);
		if ($rs)
			return TRUE;
			
		return FALSE;
	}

	// efface une categorie
	public static function delete($_id) {
		if (self::isExist($_id) == FALSE)
			return FALSE;
			
		global $core;

		$strReq =
			'DELETE FROM '.$core->prefix.'annuaire'.
			' WHERE category_id='.(integer)$_id;

		$rs = $core->con->execute($strReq);
		if ($rs)
			return TRUE;
			
		return FALSE;
	}
	
	

	// renvoi la liste des categories
	static public function getList() {
		global $core;
	
		$strReq =
			'SELECT * FROM '.$core->prefix.'annuaire'.
			' ORDER BY category_id';

		$rs = $core->con->select($strReq);
		if ($rs->isEmpty())
			return 0;

		return $rs;
	}
	
	static public function getBlogs($_category, $limit=null) {
		global $core;
		
		$strReq = 'SELECT *'.
				' FROM '.$core->prefix.'setting, '.$core->prefix.'blog '. 
				' WHERE setting_ns =\'annuaire\' AND setting_value=\''.$_category.'\''.
				' AND '.$core->prefix.'setting.blog_id = '.$core->prefix.'blog.blog_id'.
				' AND '.$core->prefix.'blog.blog_status = \'1\'';
		if(isset($limit)) $strReq .= ' LIMIT '.$limit;
		
		$rs = $core->con->select($strReq);	
		
		return $rs;	
	}
	
	static public function shortURL($url) 
	{
		preg_match('@^(?:http://)?([^/]+)@i',   $url, $matches);
		return $matches[1];
	}
	
	
	
	
}
?>