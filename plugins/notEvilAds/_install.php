<?php
/***************************************************************\
 *  This is 'Not Evil Ads', a plugin for Dotclear 2            *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along 'Not evil ads' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

/*
	Ce fichier a été créé uniquement dans le but
	de permettre à des prochaines version du plugin
	Not Evil Ads d'effectuer des mises à jour automatiques
	sans perte de données.
*/

if (!defined('DC_CONTEXT_ADMIN'))
	exit;

$nea_Pversion = $core->plugins->moduleInfo('notEvilAds','version');

$core->setVersion('notEvilAds',$nea_Pversion);

?>
