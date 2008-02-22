<?php
$m_version = $core->plugins->moduleInfo('Meta Image','version');
 
$i_version = $core->getVersion('metaimage');
 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# Création du setting (s'il existe, il ne sera pas écrasé)
$settings = new dcSettings($core,null);
$settings->setNamespace('metaimage');
$settings->put('must_have_image',false,'boolean','Force usage of image',false,true);
$settings->put('min_width',150,'integer','Min width',false,true);
$settings->put('min_height',50,'integer','Min height',false,true);
$settings->put('max_width',150,'integer','Max width',false,true);
$settings->put('max_height',450,'integer','Max height',false,true);
 
$core->setVersion('metaimage',$m_version);
return true;
?>