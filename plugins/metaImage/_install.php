<?php
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$label = 'metaImage';
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# Creating / updating settings
$settings = &$core->blog->settings;
$settings->setNamespace(strtolower($label));
$settings->put('mi_force',false,'boolean','Force usage of image',false,true);
$settings->put('mi_min_width',150,'integer','Min width',false,true);
$settings->put('mi_min_height',50,'integer','Min height',false,true);
$settings->put('mi_max_width',150,'integer','Max width',false,true);
$settings->put('mi_max_height',450,'integer','Max height',false,true);
 
$core->setVersion($label,$m_version);
unset($label,$m_version,$i_version);
return true;
?>