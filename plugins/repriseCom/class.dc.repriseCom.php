<?php
/* 
--- BEGIN LICENSE BLOCK --- 
This file is part of repriseCom, a plugin for migrate comments 
for gallery from Dotclear1 to DotClear2.
Copyright (C) 2008 Benoit de Marne,  and contributors

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
--- END LICENSE BLOCK ---
*/

class dcRepriseCom
{
	private $blog;
	private $con;
	private $table;
	private $post_table;
	private $prefix;

	// Nouvelles tables
	private $comment_table;
	private $media_table;
	
	// Anciennes tables
	private $galimage_table;
	private $galcomment_table;
	private $galgallery_table;
	private $reprisecom_old_prefix;
	
	// Tables temporaires
	private $media_table_tmp;
	private $reprise_table;
	private $repriseoff_table;		

	private $insert_comm_offset;
	private $insert_comm_limit;
	
	private $comm_offset;
	private $comm_limit;	

	/**
	Fonction d'init
	*/	
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->blog =& $core->blog;
		$this->con =& $this->blog->con;
		
		$this->prefix = $this->core->prefix;
		/*
		$this->table = $this->blog->prefix.'nomtable';
		//*/
		
		// Nouvelles tables
		$this->post_table = $this->blog->prefix.'post';
		$this->comment_table = $this->blog->prefix.'comment';
		$this->media_table = $this->blog->prefix.'media';

		// Anciennes tables
		$this->reprisecom_old_prefix = $core->blog->settings->reprisecom_old_prefix;
		if ($this->reprisecom_old_prefix === null) {
			$this->reprisecom_old_prefix = $this->blog->prefix;
		}

		/*
		$this->galimage_table = $this->blog->prefix.'galimage';
		$this->galcomment_table = $this->blog->prefix.'galcomment';
		$this->galgallery_table = $this->blog->prefix.'galgallery';
		//*/
		$this->galimage_table = $this->reprisecom_old_prefix.'galimage';
		$this->galcomment_table = $this->reprisecom_old_prefix.'galcomment';
		$this->galgallery_table = $this->reprisecom_old_prefix.'galgallery';		
		
		// Tables temporaires
		$this->media_table_tmp = $this->blog->prefix.'media_tmp';
		$this->reprise_table = $this->blog->prefix.'reprisecom';
		$this->repriseoff_table = $this->blog->prefix.'repriseoff';
		
		$this->url = 'plugin.php?p=repriseCom';
		
		// nombre de lignes traitées à chaque passage pour éviter le timeout php
		$this->insert_comm_limit = $core->blog->settings->reprisecom_limit_insert_nbcom;
		if ($this->insert_comm_limit === null) {
			$this->insert_comm_limit = 120;
		}
		
		
	}
	// Fin de la fonction __construct

	/**
	Fonction reprise de dcImportDC1 pour le formatage des champs
	*/
	private function cleanStr($str) 
	{
		return text::cleanUTF8(@text::toUTF8($str));
	}
	// Fin de la fonction cleanStr

	/**
	Fonction reprise de dcIeModule le formatage de l'URL
	*/
	final public function getURL($escape=false)
	{
		if ($escape) {
			return html::escapeHTML($this->url);
		}
		return $this->url;
	}
	
	/**
	Fonction pour compter le nombre de commentaires dans la table comment
	*/
	private function countComments ()
	{
		$nb_comm = 0;
		$strReq = 'SELECT COUNT(comment_id) AS counter FROM '.$this->comment_table.'';
		$rs = $this->con->select($strReq);
		if ($rs->fetch()) {
			$nb_comm = $rs->f(0);	
		}
		return $rs->counter;
	}
	// Fin de la fonction countComments

	/**
	Fonction pour compter le nombre de commentaires dans la table galcomment
	*/
	private function countGalcomments ()
	{
		try {
			$nb_comm = 0;
			$strReq = 'SELECT COUNT(comment_id) AS counter FROM '.$this->galcomment_table.'';
			$rs = $this->con->select($strReq);
			if ($rs->fetch()) {
				$nb_comm = $rs->f(0);	
			}
			return $rs->counter;	
		} catch (Exception $e) {
			/*
			echo '<i>'.$e.'</i>';
			//*/
			//return $e->getMessage();
			//throw new Exception(__("Table galcomment inexistante"));
			return '('.$this->galcomment_table.' : Table not found)';
		} 
	}
	// Fin de la fonction countGalcomments

	/**
	Fonction pour compter le nombre de commentaires dans la table reprisecom
	*/
	private function countRepriseComments ()
	{
		$nb_comm = 0;
		try {
			$strReq = 'SELECT COUNT(comment_id) AS counter FROM '.$this->reprise_table.'';
			$rs = $this->con->select($strReq);
			if ($rs->fetch()) {
				$nb_comm = $rs->f(0);	
			}
			return $rs->counter;
		
		} catch (Exception $e) {
			/*
			echo '<i>'.$e.'</i>';
			//*/
			//return $e->getMessage();
			return 0;
		} 
	}
	// Fin de la fonction countRepriseComments

	/**
	Fonction pour compter le nombre de commentaires qui ont été repris
	*/
	private function countInsertComments ()
	{
		try {
			$counterCom = dcRepriseCom::getOffset('counterComments');
			return $counterCom;
		} catch (Exception $e) {
			//return $e->getMessage();
			return 0;
		} 
	}
	// Fin de la fonction countInsertComments


	/**
	Affiche le nombre d'enregistrements dans les tables
	*/
	public function printCounters ()
	{
		echo '<p>';

		echo __('Number of comments in your database').' = '.dcRepriseCom::countComments().'<br>';		
		echo __('Number of comments in your old table for gallery').' = '.dcRepriseCom::countGalcomments().'<br>';
		echo __('Number of comments to resume').' = '.dcRepriseCom::countRepriseComments().'<br>';
		echo __('Number of comments included').' = '.dcRepriseCom::countInsertComments().'<br>';

		echo '</p>';
	}
	// Fin de la fonction printCounters

	/**
	Suppression de la table temporaire reprisecom
	*/
	private function deleteTableReprisecom ()
	{
		$req = "DROP TABLE IF EXISTS ".$this->reprise_table."";
		$rs = $this->con->execute($req);
		return $rs;
	}
	// Fin de la fonction deleteTableReprisecom

	/**
	Suppression de la table temporaire media_table_tmp
	*/
	private function deleteTableMediatmp ()	
	{
		$req = "DROP TABLE IF EXISTS ".$this->media_table_tmp."";
		$rs = $this->con->execute($req);
		return $rs;
	}
	// Fin de la fonction deleteTableMediatmp

	/**
	Suppression de la table temporaire media_table_tmp
	*/
	private function deleteTableRepriseoff ()	
	{
		$req = "DROP TABLE IF EXISTS ".$this->repriseoff_table."";
		$rs = $this->con->execute($req);
		return $rs;
	}
	// Fin de la fonction deleteTableMediatmp

	/**
	Suppression des tables temporaires
	*/
	public function deleteTemporariesTables ()	
	{
		try {
			dcRepriseCom::deleteTableReprisecom();
			dcRepriseCom::deleteTableMediatmp();
			dcRepriseCom::deleteTableRepriseoff();
		} catch (Exception $e) {
			///*
			echo '<i>'.$e.'</i>';
			//*/
			
			//return $e->getMessage();
			;
		} 
	}
	// Fin de la fonction deleteTemporariesTables
	
	/**
	Création de la table temporaire repriseoff
	*/
	private function createTableRepriseoff ()	
	{
		$s = new dbStruct($this->con,$this->prefix);
 
		$s->repriseoff
			->fonction		('varchar',	255,	false)
			->offset			('bigint',	0,	false)
			
		//->primary('pk_repriseoff','fonction','offset')
		;
		$si = new dbStruct($this->con,$this->prefix);
		$changes = $si->synchronize($s);
		
		// Ajout des valeurs
		dcRepriseCom::initRepriseoff();

	}
	// Fin de la fonction createTableRepriseoff

	/**
	Initialisation des valeurs par defaut dans repriseoff
	*/
	private function initRepriseoff ()
	{
		$query = 'INSERT INTO '.$this->repriseoff_table.' VALUES '.
		'( \'updateMedia\' , \'0\' )';
		$this->con->execute($query);
		
		$query = 'INSERT INTO '.$this->repriseoff_table.' VALUES '.
		'( \'importComments\' , \'0\' )';
		$this->con->execute($query);

		$query = 'INSERT INTO '.$this->repriseoff_table.' VALUES '.
		'( \'counterComments\' , \'0\' )';
		$this->con->execute($query);
	}
	// Fin de la fonction initRepriseoff

	/**
	Positionnement d'un offset
	*/
	private function setOffset ($fonction,$valeur)	
	{
		$query = 'UPDATE '.$this->repriseoff_table.' '.
			'SET offset = '.(integer) $valeur .' '.
			'WHERE fonction = \''.$fonction.'\' ';
				
		$this->con->execute(
				$query
		);
	}
	// Fin de la fonction setOffset

	/**
	Récupération de la valeur d'un offset
	*/
	private function getOffset ($fonction)	
	{
		$offset = $this->con->select('SELECT offset FROM '.$this->repriseoff_table.' WHERE fonction like \''.$fonction.'\' ')->f(0);
		
		return $offset;		
	}
	// Fin de la fonction getOffset

	/**
	Creation des tables temporaires avec toutes les informations nécessaires et suffisantes 
	pour remplir la table des commentaires
	*/
	public function createTemporariesTable () 
	{
		dcRepriseCom::createTableMediatmp();
		/* Deplacer a la suite de l'update de la table media tmp car elle utilise ses valeurs
		dcRepriseCom::createTableReprisecom();
		//*/
		dcRepriseCom::createTableRepriseoff();
	}
	// Fin de la fonction createTableRepriseoff

	/**
	Creation de la table media_tmp pour traiter le nom des fichiers
	*/
	private function createTableMediatmp () 
	{
		$strReq = 'CREATE TABLE '.$this->media_table_tmp.' AS SELECT * '.
		'FROM '.$this->media_table.' ';
		$rs = $this->con->execute($strReq);
	}
	// Fin de la fonction createTableMediatmp

	/**
	Creation de la table reprisecom qui contient les champs nécessaires à la reprise des commentaires
	*/
	protected function createTableReprisecom () 
	{
		$strReq = 'CREATE TABLE '.$this->reprise_table.' AS SELECT gali.img_name, gali.img_content, galc.* '.
		'FROM '.$this->galimage_table.' gali, '.$this->galcomment_table.' galc '.
		'WHERE gali.img_id IN (SELECT gali.img_id '.
		'FROM '.$this->galimage_table.' gali, '.$this->media_table_tmp.' m '.
		'WHERE m.media_file = gali.img_name) '.
		'AND gali.img_id = galc.img_id '.
		'GROUP BY gali.img_name';

		$rs = $this->con->execute($strReq);
		return $rs;
	}
	// Fin de la fonction createTableReprisecom

	/**
	Mise à jour de la table media_tmp
	Gestion du traitement par lot
	*/
	public function updateMedia_table_tmp ()
	{
		try {
			// nombre de lignes traitées à chaque passage pour éviter le timeout php
			$comm_limit=300; 
			
			// nombre de lignes total
			$nb_comm = $this->con->select('SELECT COUNT(media_id) FROM '.$this->media_table_tmp.' ')->f(0);
			
			// récupération de la position actuelle des traitements
			$comm_offset = dcRepriseCom::getOffset('updateMedia');
			
			// si on n'a pas traité tous les enregistrements
			if ( $comm_offset < $nb_comm ) 
			{
					// lancement de la mise à jour
					dcRepriseCom::updateMedia_table_tmp_step($comm_offset,$comm_limit);
					$comm_offset += $comm_limit;
					
					// positionne l'offset avant le rechargement
					dcRepriseCom::setOffset('updateMedia',$comm_offset);
					
					// rechargement de la page tant que ce n'est pas fini
					http::redirect($this->url.'&action=updatetmp');
			}
		
			// une fois la table media_tmp à jour, on remplit la table de reprise
			dcRepriseCom::createTableReprisecom();
			return 0;
			
		} catch (Exception $e) {
			//echo '<i>'.$e.'</i>';
			return $e->getMessage();
		} 
	}
	// Fin de la fonction updateMedia_table_tmp

	/**
	Mise à jour de la table media_tmp
	Lancement du traitement d'un lot
	*/
	private function updateMedia_table_tmp_step ($offset, $limit)
	{
		try {		
			// update de la table en modifiant le champ media_file
			$strReq = 'SELECT media_id, media_file from '.$this->media_table_tmp.' '.
			'ORDER BY media_id '.
			'LIMIT '.$offset.','.$limit.' ';
				
			$rs = $this->con->select($strReq);

		

			while ($rs->fetch())
			{
				$query = 'UPDATE '.$this->media_table_tmp.' '.
					'SET media_file = \''.basename($rs->media_file).'\' '.
					'WHERE media_id = '.(integer) $rs->media_id .' ';
				$this->con->execute(
					$query
				);
			}
			return 0;

		} catch (Exception $e) {
			return $e->getMessage();
			//throw $e;
		}
	}
	// Fin de la fonction updateMedia_table_tmp_step

	/**
	Mise à jour de la table comment
	Gestion du traitement par lot
	*/
	public function importComments ()	
	{
		try {

			// nombre de lignes traitées à chaque passage pour éviter le timeout php
			//$insert_comm_limit=120; 
			
			// nombre de lignes total
			$query = 'SELECT COUNT(*) '.
			'FROM '.$this->reprise_table.' rc, '.$this->post_table.' p '.
			'WHERE rc.img_name = p.post_title';
			$nb_comm = $this->con->select($query)->f(0);
			
			// récupération de la position actuelle des traitements
			$insert_comm_offset = dcRepriseCom::getOffset('importComments');
			
			// si on n'a pas traité tous les enregistrements
			if ( $insert_comm_offset < $nb_comm ) 
			{
					// lancement de la mise à jour
					dcRepriseCom::importComments_step($insert_comm_offset,$this->insert_comm_limit);
					$insert_comm_offset += $this->insert_comm_limit;
					
					// positionne l'offset avant le rechargement
					dcRepriseCom::setOffset('importComments',$insert_comm_offset);

					// rechargement de la page tant que ce n'est pas fini
					http::redirect($this->url.'&action=insertcom');
			}

			// it's over !
			return 0;
			
		} catch (Exception $e) {
			/*
			echo '<i>'.$e.'</i>';
			//*/
			return $e->getMessage();
		} 
	}
	// Fin de la fonction importComments

	/**
	Import des commentaires depuis la table temporaire de reprise $prefix.reprisecom
	NB : reprise et modification de la fonction d'import/export "protected function importComments($post_id,$new_post_id,&$db)"
	*/
	private function importComments_step ($offset, $limit)
	{
		$count_c = $count_t = 0;

		// requête de selection des valeurs nécessaires pour l'insertion des commentaires au nouveau format
		$strReq = 'SELECT \'NULL\' AS comment_id, p.post_id, rc.comment_dt, \'Europe/Paris\' AS comment_tz, rc.comment_upddt, '.
		'rc.comment_auteur, rc.comment_email, rc.comment_site, rc.comment_content, \'\' AS comment_words, rc.comment_ip, rc.comment_pub, '.
		'\'0\' AS comment_spam_status, \'NULL\' AS comment_spam_filter, \'0\' AS comment_trackback '.
		'FROM '.$this->reprise_table.' rc, '.$this->post_table.' p '.
		'WHERE rc.img_name = p.post_title '.
		'LIMIT '.$offset.','.$limit.' ';

		$rs = $this->con->select($strReq);
		
		// Parcours et traitement des anciens commentaires
		while ($rs->fetch())
		{
			// Definition des valeurs du commentaires
			$cur = $this->con->openCursor($this->comment_table);
			$cur->post_id           = $rs->post_id;
			$cur->comment_author    = $this->cleanStr($rs->comment_auteur);
			$cur->comment_status    = (integer) $rs->comment_pub;
			$cur->comment_dt        = $rs->comment_dt;
			$cur->comment_upddt     = $rs->comment_upddt;
			$cur->comment_email     = $this->cleanStr($rs->comment_email);
			$cur->comment_content   = $this->cleanStr($rs->comment_content);
			$cur->comment_ip        = $rs->comment_ip;
			$cur->comment_trackback = (integer) $rs->comment_trackback;
			
			$cur->comment_site = $this->cleanStr($rs->comment_site);
			if ($cur->comment_site != '' && !preg_match('!^http://.*$!',$cur->comment_site)) {
				$cur->comment_site = substr('http://'.$cur->comment_site,0,255);
			}
			
			if ($rs->exists('spam') && $rs->spam && $rs->comment_status = 0) {
				$cur->comment_status = -2;
			}
			
			$cur->comment_words = implode(' ',text::splitWords($cur->comment_content));
			
			$cur->comment_id = $this->con->select(
				'SELECT MAX(comment_id) FROM '.$this->comment_table
			)->f(0) + 1;
			
			// Reprise de l'ancien commentaire
			$cur->insert();

			// Suppression du commentaire courant dans la table de reprise
			/*
			$query = 'DELETE FROM '.$this->reprise_table.' '.
				'WHERE comment_id = '.(integer) $rs->comment_id.' ';
			
			$this->con->execute($query);
			//*/
		
			// Mise à niveau du nombre de commentaires du post
			// utilisation de la fonction countAllComments
			///*
			$strReq = 'SELECT nb_comment FROM '.$this->post_table.' '.
			'WHERE post_id = '.(integer) $cur->post_id .' ';
			
			$count_c = $this->con->select($strReq)->f(0) + 1;

			$strReq = 'UPDATE '.$this->post_table.' '.
				'SET nb_comment = '.(integer) $count_c.' '.
				'WHERE post_id = '.(integer) $cur->post_id .' ';
			
			$this->con->execute($strReq);
			//*/
			
			
			// Mise à niveau du nombre de trackback du post			
			///*
			$strReq = 'SELECT nb_trackback FROM '.$this->post_table.' '.
			'WHERE post_id = '.(integer) $cur->post_id .' ';
			
			if ($cur->comment_trackback && $cur->comment_status == 1) {
				$count_t = $this->con->select($strReq)->f(0) + 1;

				$strReq = 'UPDATE '.$this->post_table.' '.
					'SET nb_trackback = '.(integer) $count_t.' '.
					'WHERE post_id = '.(integer) $cur->post_id .' ';
			
				$this->con->execute($strReq);
			}
			//*/

			// Remplissage du compteur des commentaires insérés
			$counterCom = dcRepriseCom::getOffset('counterComments')+1;
			dcRepriseCom::setOffset('counterComments',$counterCom);

		}
	}
	// Fin de la fonction importComments_step

}	
?>
