<?php 
/***************************************************************\
 *  This is 'comCtrl', a plugin for Dotclear 2 by Laurent Alacoque 
 *  <laureck@users.sourceforge.net>     *
 *                  
 *  Most of this file was borrowed and adapted from 'Canaval'  *
 *  plugin from Osku and contributors.                         *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Carnaval' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('comCtrl','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('comCtrl');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé

if (version_compare($i_version,$m_version,'>=')) {
        return;
}

# La procédure d'installation commence vraiment là
# Création de la nouvelle table
$s = new dbStruct($core->con,$core->prefix);
 
# We create the comctrl table with comment_id as a foreign key from dc_comment:comment_id and comment_ranking as a new smallint column
$s->comctrl
	->comment_id('bigint',0,false)
	->comment_ranking('smallint',0,true)
    ->reference('fk_comment_id','comment_id','comment','comment_id','cascade','cascade');
	;

# Schéma d'installation
$si = new dbStruct($core->con,$core->prefix);
$si->synchronize($s);


$core->setVersion('comCtrl',$m_version);
return true;
?>