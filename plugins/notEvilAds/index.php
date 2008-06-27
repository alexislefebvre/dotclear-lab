<?php
/***************************************************************\
 *  This is 'Not Evil Ads', a plugin for Dotclear 2            *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along 'Not evil ads' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

try
{
	$fatal_error = false;
	$messages = array();
	$default_tab = empty($_GET['tab']) ? false : html::escapeHTML($_GET['tab']);
		
	/* Initialisation
	--------------------------------------------------- */

	// Vérification de l'installation
	/*
	$nea_Iversion = $core->getVersion('notEvilAds');
	if (!$nea_Iversion)
		throw new Exception(__('Not Evil Ads is not installed. '.
			'Please go to your dashboard to install this plugin.'));
	elseif ($core->plugins->moduleInfo('notEvilAds','version')
		!= $core->getVersion('notEvilAds'))
		throw new Exception(__('Not Evil Ads settings must be updated. '.
			'Please go to your dashboard to update settings.'));
	//*/

	// Chargement
	include_once dirname(__FILE__).'/lib/class.notevilads.php';
	$nea_settings = notEvilAds::loadSettings($core->blog->settings->get('nea_settings'));
	$nea_ads = notEvilAds::loadAds($core->blog->settings->get('nea_ads'));
	
	// Paramètres du plugin corrompus (mince alors)
	include_once dirname(__FILE__).'/functions.php';
	if ($nea_settings === 'MISSING')
	{
		$fatal_error = true;
		nea_install($core->blog->settings,true);
		throw new Exception(__('A setting was missing.').
			__('Please refresh this page to continue. This should be fixed now.'));
	}
	elseif ($nea_settings === 'CORRUPTED')
	{
		$fatal_error = true;
		nea_install($core->blog->settings,true);
		throw new Exception(__('Not Evil Ads settings were not installed or were corrupted.').
			__('Please refresh this page to continue. This should be fixed now.'));
	}
	elseif ($nea_ads === 'CORRUPTED')
	{
		nea_install($core->blog->settings,true,'ads');
		throw new Exception(__('Ads data were corrupted.').
			__('Please refresh this page to continue. This should be fixed now.'));
	}
	
	/* Paramètres par défaut pour les formulaires
	--------------------------------------------------- */
	
	// Pour l'ajout d'une nouvelle publicité
	if (empty($_REQUEST['edit']))
	$nea_newad = array(
		'title'=>__('Ads'),
		'identifier'=>'',
		'attr'=>'',
		'htmlcode'=>'',
		'nothome'=>$nea_settings['nothome'],
		'notevil'=>true,
		'notajax'=>false);

	// Pour l'édition d'une publicité existante
	else
	$nea_newad = array(
		'title'=>$nea_ads[$_REQUEST['edit']]['title'],
		'identifier'=>$nea_ads[$_REQUEST['edit']]['identifier'],
		'attr'=>$nea_ads[$_REQUEST['edit']]['attr'],
		'htmlcode'=>$nea_ads[$_REQUEST['edit']]['htmlcode'],
		'nothome'=>$nea_ads[$_REQUEST['edit']]['nothome'],
		'notevil'=>$nea_ads[$_REQUEST['edit']]['notevil'],
		'notajax'=>$nea_ads[$_REQUEST['edit']]['notajax'],
		'disable'=>$nea_ads[$_REQUEST['edit']]['disable']);

	// Rappeller les publicités séléctionnées
	if (empty($_POST['nea_selected']))
		$nea_selected = array();
	else
		$nea_selected = $_POST['nea_selected'];

	/* Réception des données depuis les formulaires
	--------------------------------------------------- */
	
	// Mis à jour de la configuration
	if (isset($_POST['nea_action_config']))
	{
		$nea_settings['default'] = empty($_POST['nea_default']) ? false : true;
		$nea_settings['nothome'] = empty($_POST['nea_nothome']) ? false : true;
		$nea_settings['notajax'] = empty($_POST['nea_nothome']) ? false : true;
	//	$nea_settings['identifiers'] = $_POST['nea_identifiers'];
		$nea_settings['cookiename'] = $_POST['nea_cookiename'];
		$nea_settings['cookiedays'] = (int) $_POST['nea_cookiedays'];
		if (isset($_POST['nea_cookiepath']))
			$nea_settings['cookiepath'] = $_POST['nea_cookiepath'];
		if (isset($_POST['nea_cookiedome']))
			$nea_settings['cookiedome'] = $_POST['nea_cookiedome'];
		$nea_settings['easycookie'] = empty($_POST['nea_easycookie']) ? false : true;
	}
	
	// Édition ou ajout d'une nouvelle publicité
	elseif (isset($_POST['nea_action_add']) || isset($_POST['nea_action_edit']))
	{
		$nea_newad['title'] = $_POST['nea_title'];
		$nea_newad['identifier'] = $_POST['nea_identifier'];
		$nea_newad['attr'] =  $_POST['nea_attr'];
		$nea_newad['htmlcode'] =  $_POST['nea_htmlcode'];
		$nea_newad['nothome'] = empty($_POST['nea_nothome']) ? false : true;
		$nea_newad['notevil'] = empty($_POST['nea_notevil']) ? false : true;
		$nea_newad['notajax'] = empty($_POST['nea_notajax']) ? false : true;
	}
	
	/* Traitement des requêtes
	--------------------------------------------------- */
	
	$core->blog->settings->setNameSpace('notevilads');

	// Mis à jour de la configuration
	if (isset($_POST['nea_action_config']))
	{
		$default_tab = 'nea_config';
		
		//if (!ereg('^[a-zA-Z0-9_-,]+$',$nea_settings['identifiers']))
		//	throw new Exception(__('Invalid format for ads identifiers'));
		
		$core->blog->settings->put('nea_settings',notEvilAds::storeSettings($nea_settings));
		
		http::redirect($p_url.'&up=settings');
	}
	
	// Création d'une nouvelle publicité
	elseif (isset($_POST['nea_action_add']))
	{
		$default_tab = 'nea_edit';
		
		if (empty($nea_newad['identifier']))
			throw new Exception(__('Identifier required.'));
			
		if (!ereg('^[a-zA-Z0-9_-]+$',$nea_newad['identifier']))
			throw new Exception(__('Invalid ad identifier format.'));
		
		if (empty($nea_newad['htmlcode']))
			throw new Exception(__('HTML code required.'));
			
		if (isset($nea_ads[$nea_newad['identifier']]))
			throw new Exception(__('This identifier is already used.'));

		$nea_newad['disable'] = false;
		$nea_ads[$nea_newad['identifier']] = $nea_newad;
		$nea_settings['identifiers'] = notEvilAds::getUpdatedIdentifiers($nea_ads);
		$core->blog->settings->put('nea_ads',notEvilAds::storeAds($nea_ads));
		$core->blog->settings->put('nea_settings',notEvilAds::storeSettings($nea_settings));
		
		http::redirect($p_url.'&up=newad');
	}
	
	// Édition d'une publicité existante
	elseif (isset($_POST['nea_action_edit']) && !empty($_REQUEST['edit']))
	{		
		if (empty($nea_newad['identifier']))
			throw new Exception(__('Identifier required.'));
			
		if (!ereg('^[a-zA-Z0-9_-]+$',$nea_newad['identifier']))
			throw new Exception(__('Invalid ad identifier format.'));
		
		if (empty($nea_newad['htmlcode']))
			throw new Exception(__('HTML code required.'));
			
		if ($nea_newad['identifier'] != $_REQUEST['edit'] && isset($nea_ads[$nea_newad['identifier']]))
			throw new Exception(sprintf(__('You tried to change identifier from \'%s\' to \'%s\', but \'%s\' is already used.'),
				$_REQUEST['edit'],$nea_newad['identifier'],$nea_newad['identifier']));

		if (!isset($nea_ads[$nea_newad['identifier']]))
		{
			unset($nea_ads[$_REQUEST['edit']]);
			$messages[] = sprintf(__('Warning ! Identifier changed from \'%s\' to \'%s\'.'),
				$_REQUEST['edit'],$nea_newad['identifier']);
			$_REQUEST['edit'] = $nea_newad['identifier'];
		}

		$nea_ads[$nea_newad['identifier']] = $nea_newad;
		$nea_settings['identifiers'] = notEvilAds::getUpdatedIdentifiers($nea_ads);
		$core->blog->settings->put('nea_ads',notEvilAds::storeAds($nea_ads));
		$core->blog->settings->put('nea_settings',notEvilAds::storeSettings($nea_settings));

		$messages[] = __('Ad succefully updated.');
	}
	
	// Édition groupée des propriétées
	elseif (isset($_POST['nea_action_fromlist']))
	{
		$default_tab = 'nea_list';
		
		if (empty($_POST['nea_selected']))
			throw new Exception(__('No ads selected.'));
		
		if ($_POST['nea_action'] == 'delete')
		{
			foreach ($_POST['nea_selected'] as $v)
			{
				if (isset($nea_ads[$v]))
					unset($nea_ads[$v]);
			}
			
			$nea_settings['identifiers'] = notEvilAds::getUpdatedIdentifiers($nea_ads);
			$core->blog->settings->put('nea_settings',notEvilAds::storeSettings($nea_settings));
			
			$messages[] = __('Selected ads deleted.');
		}
		elseif ($_POST['nea_action'] == 'mknotevil')
		{
			foreach ($_POST['nea_selected'] as $v)
			{
				if (isset($nea_ads[$v]))
					$nea_ads[$v]['notevil'] = true;
			}
			$messages[] = __('Selected ads are now not evil.');
		}
		elseif ($_POST['nea_action'] == 'mkevil')
		{
			foreach ($_POST['nea_selected'] as $v)
			{
				if (isset($nea_ads[$v]))
					$nea_ads[$v]['notevil'] = false;
			}
			$messages[] = __('Selected ads are now evil.');
		}
		elseif ($_POST['nea_action'] == 'disable')
		{
			foreach ($_POST['nea_selected'] as $v)
			{
				if (isset($nea_ads[$v]))
					$nea_ads[$v]['disable'] = true;
			}
			$messages[] = __('Selected ads are now disabled.');
		}
		elseif ($_POST['nea_action'] == 'enable')
		{
			foreach ($_POST['nea_selected'] as $v)
			{
				if (isset($nea_ads[$v]))
					$nea_ads[$v]['disable'] = false;
			}
			$messages[] = __('Selected ads are now enabled.');
		}
		else
		{
			throw new Exception(__('Unfortunately, I do not unterstand your request.'));
		}
		
		$core->blog->settings->put('nea_ads',notEvilAds::storeAds($nea_ads));
	}
	else
	{
		$default_tab = $default_tab ? $default_tab : 'nea_config';
	}
}

catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

/* DISPLAY
--------------------------------------------------- */

// Some messages
if (!empty($_GET['up']))
{
	switch ($_GET['up']):
	case 'settings':
		$messages[] = __('Configuration updated.');
		break;
	case 'newad':
		$messages[] = __('New ad added.');
		$default_tab = 'nea_list';
		break;
	default:
		$messages[] = __('Something was maybe updated. But what ?');
	endswitch;
}

// Headers
echo '
<html><head>
<title>'.__('Not Evil Ads').'</title>
<script type="text/javascript">
//<![CDATA[
dotclear.nea_xmlresponsefile = "'.$core->blog->url.'notEvilAdsXML";
//]]>
</script>
'.dcPage::jsToolMan().dcPage::jsLoad('index.php?pf=notEvilAds/js/admin.js').
($default_tab ? dcPage::jsPageTabs($default_tab) : '').'

</head><body>
<h2>'.__('Not Evil Ads configuration page').'</h2>'."\n\n";

if (!empty($messages))
{
	foreach ($messages as $message)
	{
		echo '	<p class="message">'.$message."</p>\n";
	}
}

// Content
include dirname(__FILE__).'/forms.php';

if ($fatal_error)
	echo '<p class="message">'.__('Fatal error occured. Can not continue.').'</p>';
elseif (!empty($_REQUEST['edit']))

	echo
		'<p><a href="'.$p_url.'&amp;tab=nea_list" title="'.__('Return to the main configuration page').'">'.
		__('Go back').'</a></p>'.
		$nea_forms['edit'];
else
	echo
		'<div class="multi-part" id="nea_config" title="'.__('Configuration').'">'.
		$nea_forms['config'].
		"</div>\n\n".
		'<div class="multi-part" id="nea_list" title="'.__('Ads').'">'.
		$nea_forms['list'].
		"</div>\n\n".
		'<div class="multi-part" id="nea_edit" title="'.__('New ad').'">'.
		$nea_forms['edit'].
		"</div>\n\n".
		'<div class="multi-part" id="nea_help" title="'.__('Help').'">'.
		$nea_forms['help'].
		"</div>\n\n";

echo '</body></html>';
?>