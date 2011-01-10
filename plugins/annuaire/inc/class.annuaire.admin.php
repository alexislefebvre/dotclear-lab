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

if (!defined('DC_CONTEXT_ADMIN')) exit;

// librairies
require_once dirname(__FILE__).'/class.annuaire.php';

// gestion de categories sur les blogs
class dcAnnuaireAdmin {
	/* -----------------------------------------------------------------
		fonctions techniques
	 ----------------------------------------------------------------- */
	
	/**
	*Installation de la table
	*/
	static public function Install() {
		global $core;
		$con =& $core->con;

		// instanciation d'un objet dbStruct
		$s = new dbStruct($core->con,$core->prefix);

		// création du schéma de la table
		$s->annuaire
			->category_id('integer', 0, true)
			->title('varchar', 255, false)
			->url('varchar', 255, false)
			;

		// ajout de la clée primaire
		$s->annuaire->primary('pk_annuaire','category_id');

		// on synchronise le schéma de la table
		$si = new dbStruct($core->con,$core->prefix);
		$changes = $si->synchronize($s);

		return TRUE;
	}

	/**
	*Desinstalaltion de la table
	*/
	static public function Uninstall() {
		global $core;

		$strReq =
			'DROP TABLE '.$core->prefix.'annuaire';

		$rs = $core->con->execute($strReq);
		if ($rs === FALSE)
			return FALSE;
						
		return TRUE;
	}
		
	/* -----------------------------------------------------------------
		fonctions pour l'interface d'administration
	 ----------------------------------------------------------------- */
	
	/**
	*affiche l'onglet d'installation
	*/
	public static function displayTabInstall() {
		global $core;
		if (dcAnnuaire::isInstalled() === FALSE)
			$link =
				'<a href="'.html::escapeHTML("plugin.php?p=annuaire&op=install").'">'.
				__('Clear here to install.').
				'</a>';
		else
			$link =
				'<a href="'.html::escapeHTML("plugin.php?p=annuaire&op=uninstall").'">'.
				__('Clear here to uninstall.').
				'</a>';
	
		echo '<p>'.$link.'</p>';	
	}
	
	
		
		
	/** 
	* affiche l'onglet de la liste des categories
	*/
	static public function displayTabList() {
	global $core;
		if (dcAnnuaire::isInstalled() === FALSE)
			return;
	
		// récupçre la liste complète des categories
		$categories = dcAnnuaire::getList();
		
		if (is_object($categories) === FALSE)
			echo __('No categories');
		else {
			echo
				'<form action="plugin.php" method="post">'.
					'<table class="clear">'.
					
						'<tr>'.
							'<th>&nbsp;</th>'.
							'<th class="nowrap" colspan="2">'.__('Name').'</th>'.
						'</tr>';
			
			// parcours la liste des citations pour les afficher
			$categories->moveStart();
			while ($categories->fetch()) {
				$k = (integer) $categories->category_id;

				$gui_link =
					'<a href="'.html::escapeHTML("plugin.php?p=annuaire&op=edit&id=".$k).'">'.
						'<img src="images/edit-mini.png" alt="'.__('Edit category').'" '.'title="'.__('Edit category').'" />'.
					'</a>';
				
				echo
					'<tr class="line">'.
						'<td>'.form::checkbox(array('category['.html::escapeHTML($k).']'),1).'</td>'.
						'<td class="maximal nowrap">'.html::escapeHTML(text::cutString($categories->title, 50)).'</td>'.
						'<td class="status">'.$gui_link.'</td>'.
					'</tr>';	
			}
		
			echo
					'</table>'.
					
				'<p>'.
					'<input type="hidden" name="p" value="annuaire" />'.
					'<input type="submit" name="do_remove" value="'.__('Remove selected').'" />'.
				'</p>'.
				
				$core->formNonce().
				
			'</form>';		
		}
	}
	
	/**
	* affiche l'onglet d'ajout de categorie
	*/
	static public function displayTabAdd() {
	global $core;
		if (dcAnnuaire::isInstalled() === FALSE)
			return;
	
		echo
			'<p>'.
				'<a href="plugin.php?p=annuaire">'.__('Back to categories list').'</a>'.
			'</p>'.
			
			'<form action="plugin.php" method="post" id="category">'.
			
				'<p>'.
					'<label for "title">'.__('Title').'</label>'.
					'<input type="text" name="title" value="">'.
				'</p>'.
				
				'<p>'.
					'<input type="hidden" name="p" value="annuaire" />'.
					'<input type="submit" name="do_add" value="'.__('Add').'" />'.
				'</p>'.
				
				$core->formNonce().
				
			'</form>';			
	}

	/**
	* affiche l'onglet de modification de categorie
	*/
	static public function displayTabEdit($_id) {
	global $core;
		if (dcAnnuaire::isInstalled() === FALSE)
			return;
	
		if (dcAnnuaire::isExist($_id) === FALSE)
			return FALSE;
			
		$category = dcAnnuaire::get($_id);

		echo
			'<p>'.
				'<a href="plugin.php?p=annuaire">'.__('Back to categories list').'</a>'.
			'</p>';

		echo
			'<form action="plugin.php" method="post" id="category">'.
			
				'<p>'.
					'<label for "title">'.__('Title').'</label>'.
					'<input type="text" name="title" value="'.$category['title'].'">'.
				'</p>'.
				
				'<p>'.
					'<input type="hidden" name="p" value="annuaire" />'.
					'<input type="hidden" name="category_id" value="'.$_id.'" />'.
					'<input type="submit" name="do_update" value="'.__('Update').'" />'.
				'</p>'.
				
				$core->formNonce().
				
			'</form>';			
	}
}
?>