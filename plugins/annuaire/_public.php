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

$core->url->register('annuaire','annuaire','^annuaire(/(.*))?$',array('publicAnnuaire','load'));


//templates
$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');

$core->tpl->addValue('AnnuaireURL',array('dcAnnuaireTpl','url'));

$core->tpl->addBlock('Annuaire', array('dcAnnuaireTpl', 'annuaire'));

$core->tpl->addBlock('AnnuaireCategories', array('dcAnnuaireTpl', 'categories'));

$core->tpl->addValue('AnnuaireCatTitle', array('dcAnnuaireTpl', 'catTitle'));
$core->tpl->addValue('AnnuaireCatURL', array('dcAnnuaireTpl', 'catURL'));

$core->tpl->addBlock('AnnuaireSites', array('dcAnnuaireTpl', 'sites'));
$core->tpl->addBlock('AnnuaireSitesEntries', array('dcAnnuaireTpl', 'sitesEntries'));
$core->tpl->addBlock('AnnuaireSitesEntry', array('dcAnnuaireTpl', 'sitesEntry'));

$core->tpl->addValue('AnnuaireSiteTitle', array('dcAnnuaireTpl', 'siteTitle'));
$core->tpl->addValue('AnnuaireSiteURL', array('dcAnnuaireTpl', 'siteURL'));
$core->tpl->addValue('AnnuaireSiteAuthor', array('dcAnnuaireTpl', 'siteAuthor'));
$core->tpl->addValue('AnnuaireSiteDesc', array('dcAnnuaireTpl', 'siteDesc'));


class publicAnnuaire extends dcUrlHandlers
{
	public static function load($args) {
		$_ctx =& $GLOBALS['_ctx'];
		$_ctx->annuaire = basename($args);
		
		$catUrl = $_ctx->annuaire;
		if(empty($catUrl)) {
			self::serveDocument('annuaire_list.html');
		} else {
			self::serveDocument('annuaire_categorie.html');
		}
		exit;
	}
}




?>