<?php if (!defined('DC_CONTEXT_ADMIN')) {return;}
/**
 * @author kévin lepeltier [lipki] (kevin@lepeltier.info)
 * @license http://creativecommons.org/licenses/by-sa/3.0/deed.fr
 */

$m_version = $core->plugins->moduleInfo('flvplayerconfig','1');
$i_version = $core->getVersion('flvplayerconfig');




$args = array(
	'margin' => 1,
	'showvolume' => 1,
	'showtime' => 1,
	'showfullscreen' => 1,
	'buttonovercolor' => 'ff9900',
	'slidercolor1' => 'cccccc',
	'slidercolor2' => '999999',
	'sliderovercolor' => '0066cc'
);

$settings =& $core->blog->settings;
$settings = new dcSettings($core,null);
$settings->addNameSpace('themes');
$settings->themes->put('flvplayer_style', serialize($args), 'string', 'flvplayer config', false);

$core->setVersion('flvplayerconfig',$m_version);
