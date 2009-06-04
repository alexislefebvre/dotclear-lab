<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of entryCSS, a plugin for Dotclear.
# 
# Copyright (c) 2009 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
$m_version = $core->plugins->moduleInfo('entryCSS','version');

$i_version = $core->getVersion('entryCSS');

if (version_compare($i_version,$m_version,'>=')) {
	return;
}
 
# Ajout du champ dans la base de donnée
$s = new dbStruct($core->con,$core->prefix);

$s->post
	->post_css('text', 0, true)
	;

# Mise à jour du schéma
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('entryCSS',$m_version);
return true;
?>
