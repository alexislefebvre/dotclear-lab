<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2007 gerits aurelien for clashdesign All rights
# reserved.
#
# Cette création est mise à disposition selon le Contrat Paternité-Pas 
# d'Utilisation Commerciale-Pas de Modification 2.0 Belgique disponible 
# en ligne http://creativecommons.org/licenses/by-nc-nd/2.0/be/ ou par 
# courrier postal à Creative Commons, 171 Second Street, Suite 300, San Francisco, 
# California 94105, USA.
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('eraseCache','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('eraseCache');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {
	return;
}
 
# La procédure d'installation commence vraiment là
$core->setVersion('eraseCache',$m_version);
return true;
?>