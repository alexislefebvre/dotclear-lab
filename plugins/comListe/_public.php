<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of comListe, a plugin for Dotclear.
# 
# Copyright (c) 2008-2009 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

require_once dirname(__FILE__).'/inc/class.dc.comListe.php';
 
$core->url->register('comListe','comListe','^comListe(?:/(.+))?$',array('urlcomListe','comListe'));
$core->tpl->addValue('ComListeURL',array('tplComListe','ComListeURL'));
$core->tpl->addValue('ComListePageTitle',array('tplComListe','ComListePageTitle'));
$core->tpl->addValue('ComListeCurrentPage',array('tplComListe','ComListeCurrentPage'));
$core->tpl->addValue('ComListeNbCommentsPerPage',array('tplComListe','ComListeNbCommentsPerPage'));
$core->tpl->addBlock('ComListeCommentsEntries',array('tplComListe','ComListeCommentsEntries'));
$core->tpl->addValue('ComListePaginationLinks',array('tplComListe','ComListePaginationLinks'));
$core->tpl->addValue('ComListeOpenPostTitle',array('tplComListe','ComListeOpenPostTitle'));

?>