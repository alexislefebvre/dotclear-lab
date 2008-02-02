<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Carnaval', a plugin for Dotclear 2          *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'dcCommentClass' (see COPYING.txt);     *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

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

$s = new dbStruct($core->con,$core->prefix);
 
$s->carnaval
		->class_id('integer',0,false)
		->blog_id('varchar',32,	false)
        ->comment_author('varchar',255,false)
        ->comment_author_mail('varchar',255,false)
        ->comment_author_site('varchar',255,true)      
        ->comment_class('varchar',255,false)

        ->primary('pk_carnaval','class_id')
        ;

$s->carnaval->index('idx_class_blog_id','btree','blog_id');

# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('carnaval',$m_version);
return true;
?>
