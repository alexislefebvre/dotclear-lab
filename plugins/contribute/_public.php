<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute, a plugin for Dotclear 2
# Copyright (C) 2008,2009,2010 Moe (http://gniark.net/)
#
# Contribute is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Contribute is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

# template tags
require_once(dirname(__FILE__).'/inc/lib.contribute.tpl.php');

$core->tpl->addValue('ContributeMessage',
	array('contributeTpl','ContributeMessage'));
	
$core->tpl->addValue('ContributeHelp',
	array('contributeTpl','ContributeHelp'));

$core->tpl->addBlock('ContributePreview',
	array('contributeTpl','ContributePreview'));
$core->tpl->addBlock('ContributeForm',
	array('contributeTpl','ContributeForm'));

$core->tpl->addBlock('ContributeIf',
	array('contributeTpl','ContributeIf'));
$core->tpl->addBlock('ContributeIfNameAndEmailAreNotRequired',
	array('contributeTpl','ContributeIfNameAndEmailAreNotRequired'));

$core->tpl->addBlock('ContributeFormaters',
	array('contributeTpl','ContributeFormaters'));

$core->tpl->addValue('ContributeFormat',
	array('contributeTpl','ContributeFormat'));

$core->tpl->addValue('ContributeEntryExcerpt',
	array('contributeTpl','ContributeEntryExcerpt'));
$core->tpl->addValue('ContributeEntryContent',
	array('contributeTpl','ContributeEntryContent'));

$core->tpl->addBlock('ContributeIfSelected',
	array('contributeTpl','ContributeIfSelected'));

$core->tpl->addValue('ContributeCategoryID',
	array('contributeTpl','ContributeCategoryID'));

$core->tpl->addValue('ContributeCategorySpacer',
	array('contributeTpl','ContributeCategorySpacer'));

$core->tpl->addBlock('ContributeEntryTagsFilter',
	array('contributeTpl','ContributeEntryTagsFilter'));

$core->tpl->addBlock('ContributeEntryMyMeta',
	array('contributeTpl','ContributeEntryMyMeta'));

$core->tpl->addBlock('ContributeEntryMyMetaIf',
	array('contributeTpl','ContributeEntryMyMetaIf'));

$core->tpl->addBlock('ContributeEntryMyMetaIfChecked',
	array('contributeTpl','ContributeEntryMyMetaIfChecked'));


$core->tpl->addValue('ContributeEntryMyMetaValue',
	array('contributeTpl','ContributeEntryMyMetaValue'));

$core->tpl->addBlock('ContributeEntryMyMetaValues',
	array('contributeTpl','ContributeEntryMyMetaValues'));
$core->tpl->addValue('ContributeEntryMyMetaValuesID',
	array('contributeTpl','ContributeEntryMyMetaValuesID'));
$core->tpl->addValue('ContributeEntryMyMetaValuesDescription',
	array('contributeTpl','ContributeEntryMyMetaValuesDescription'));
	
$core->tpl->addValue('ContributeEntryMyMetaID',
	array('contributeTpl','ContributeEntryMyMetaID'));
$core->tpl->addValue('ContributeEntryMyMetaPrompt',
	array('contributeTpl','ContributeEntryMyMetaPrompt'));
	
$core->tpl->addValue('ContributeEntryNotes',
	array('contributeTpl','ContributeEntryNotes'));

$core->addBehavior('coreBlogGetPosts',array('contributeBehaviors',
	'coreBlogGetPosts'));

/**
@ingroup Contribute
@brief Behaviors
@see planet/public.php
*/
class contributeBehaviors
{
	public static function coreBlogGetPosts($rs)
	{
		global $core;
		
		$core->blog->settings->addNamespace('contribute');
		if ($GLOBALS['core']->blog->settings->contribute->contribute_active)
		{
			$rs->extend('rsExtContributePosts');
		}
	}
}

/**
@ingroup Contribute
@brief Extend posts

EntryAuthorDisplayName and EntryAuthorURL can't be modified

@see planet/public.php
*/
class rsExtContributePosts extends rsExtPostPublic
{
	/**
	Get metadata of Contribute
	@param	rs	<b>recordset</b>	Recordset
	@param	info	<b>str</b>	Information
	@return	<b>string</b> Value
	*/
	public static function contributeInfo($rs,$info)
	{
		$rs = dcMeta::getMetaRecord($rs->core,$rs->post_meta,'contribute_'.$info);
		if (!$rs->isEmpty())
		{
			return $rs->meta_id;
		}
		# else
		return;
	}
	
	/**
	getAuthorLink
	@param	rs	<b>recordset</b>	Recordset
	@return	<b>string</b> String
	*/
	public static function getAuthorLink($rs)
	{
		$author = $rs->contributeInfo('author');
		$site = $rs->contributeInfo('site');
		
		# default display
		if (empty($author))
		{
			return(parent::getAuthorLink($rs));
		}
		else
		{
			$author_format = 
				$GLOBALS['core']->blog->settings->contribute_author_format;
			
			if (empty($author_format)) {$author_format = '%s';}
			
			if (!empty($site))
			{
				$str = sprintf($author_format,'<a href="'.$site.'">'.$author.'</a>');
			}
			else
			{
				$str = sprintf($author_format,$author);
			}
			return $str;
		}
	}
	
	/**
	getAuthorCN
	@param	rs	<b>recordset</b>	Recordset
	@return	<b>string</b> String
	*/
	public static function getAuthorCN($rs)
	{
		$author = $rs->contributeInfo('author');
		if (empty($author))
		{
			# default display
			return(parent::getAuthorCN($rs));
		} else {
			$author_format = $GLOBALS['core']->blog->settings->contribute_author_format;
			
			if (empty($author_format)) {$author_format = '%s';}
			
			return sprintf($author_format,$author);
		}
	}
	
	/**
	getAuthorEmail
	@param	rs	<b>recordset</b>	Recordset
	@param	encoded	<b>boolean</b>	Return encoded email address ?
	@return	<b>string</b> String
	*/
	public static function getAuthorEmail($rs,$encoded=true)
	{
		$mail = $rs->contributeInfo('mail');
		if (empty($mail))
		{
			# default display
			return(parent::getAuthorEmail($rs,$encoded));
		} else {
			if ($encoded) {
				return strtr($mail,array('@'=>'%40','.'=>'%2e'));
			}
			# else
			return $email;
		}
	}
	
	/**
	getAuthorURL
	@param	rs	<b>recordset</b>	Recordset
	@return	<b>string</b> String
	*/
	public static function getAuthorURL($rs)
	{
		$mail = $rs->contributeInfo('site');
		if (empty($mail))
		{
			# default display
			return(parent::getAuthorEmail($rs,$encoded));
		} else {
			if ($encoded) {
				return strtr($mail,array('@'=>'%40','.'=>'%2e'));
			}
			# else
			return $email;
		}
	}
}
?>