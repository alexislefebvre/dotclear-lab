<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Carnaval a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Me and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('carnaval','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('carnaval');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé

if (version_compare($i_version,$m_version,'>=')) {
        return;
}
 
# La procédure d'installation commence vraiment là
# Création de la nouvelle table
$s = new dbStruct($core->con,$core->prefix);
 
$s->carnaval
	->class_id('integer',0,false)
	->blog_id('varchar',32,	false)
	->comment_author('varchar',255,false)
	->comment_author_mail('varchar',255,false)
	->comment_author_site('varchar',255,true)      
	->comment_class('varchar',255,false)
	->comment_text_color('varchar',7,false)
	->comment_background_color('varchar',7,false)
	
	->primary('pk_carnaval','class_id')
	->index('idx_class_blog_id','btree','blog_id')
	;

# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$si->synchronize($s);

$core->blog->settings->setNamespace('carnaval');
$s =& $core->blog->settings;
$s->put('carnaval_active',false,'boolean','Carnaval activation flag',true,true);
$s->put('carnaval_colors',false,'boolean','Use colors defined with Carnaval plugin',true,true);

$core->setVersion('carnaval',$m_version);
return true;
?>
