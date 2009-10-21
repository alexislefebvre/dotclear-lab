<?php
# ***** BEGIN LICENSE BLOCK *****
# Widget Management for DotClear.
# Copyright (c) 2008 Gerits Aurelien. All rights
# reserved.
#

# Cette création est mise à disposition selon le Contrat Paternité-Pas 
# d'Utilisation Commerciale-Pas de Modification 2.0 Belgique disponible 
# en ligne http://creativecommons.org/licenses/by-nc-nd/2.0/be/ ou par 
# courrier postal à Creative Commons, 171 Second Street, Suite 300, San Francisco, 
# California 94105, USA.

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('adsense','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('adsense');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {
	return;
}
#le setting commence ici
 $settings = new dcSettings($core,null);
 $settings->setNamespace('adsense');
 $settings->put('google_ad_client', '', 'string','google_ad_client :16 digits');
 $settings->put('google_ad_width', '234', 'string','width');
 $settings->put('google_ad_height', '60', 'string','height');
 $settings->put('google_color_border', '000000', 'string','google_color_border');
 $settings->put('google_color_bg', 'FFFFFF', 'string','google_color_bg');
 $settings->put('google_color_link', '0000FF', 'string','google_color_link');
 $settings->put('google_color_text', '000000', 'string','google_color_text');
 $settings->put('google_color_url', 'FF0000', 'string','google_color_url');
 $settings->put('position', 'left', 'string','position');
 $settings->put('google_ui_features', '0', 'string','google_ui_features');
 
# La procédure d'installation commence vraiment là
$core->setVersion('adsense',$m_version);
return true;
?>