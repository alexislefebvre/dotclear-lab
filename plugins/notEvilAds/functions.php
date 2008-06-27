<?php
include_once dirname(__FILE__).'/lib/class.notevilads.php';

function nea_install(&$settings,$override=false,$whattofix=null)
{	
	$override = (bool) $override;
	// Configuration par défaut
	$nea_settings = array(
		'default'=>true,
		'nothome'=>false,
		'notajax'=>false,
		'identifiers'=>'',
		'cookiename'=>'nea_show',
		'cookiedays'=>30,
		'cookiepath'=>'/',
		'cookiedome'=>$_SERVER['HTTP_HOST'],
		'easycookie'=>true);
	$nea_ads = array();
	
	// Enregistrement de la configuration
	$settings->setNamespace('notevilads');
	if ($whattofix != 'ads')
		$settings->put('nea_settings',notEvilAds::storeSettings($nea_settings),'string','Not Evil Ads settings',$override);
	if ($whattofix != 'settings')
		$settings->put('nea_ads',notEvilAds::storeAds($nea_ads),'string','Not evil ads (code)',$override);
	
	// On annule l'installation forcée
	if ($settings->get('nea_forceinstall'))
		$settings->drop('nea_forceinstall');
	
	// C'est bon ;-)
	return true;
}
?>