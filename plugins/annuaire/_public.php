<?php
# ***** BEGIN LICENSE BLOCK *****
# This file a plugin of DotClear.
# Copyright (c) Marc Vachette and Aurelien Gerits. All rights
# reserved.
#
#Annuaire is free software; you can redistribute it and/or modify
# it under the terms of the Creative Commons License BY SA
# see the page http://creativecommons.org/licenses/by-sa/3.0/ for more information
# 
# Annuaire is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# Creative Commons License for more details.
#
# ***** END LICENSE BLOCK *****

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