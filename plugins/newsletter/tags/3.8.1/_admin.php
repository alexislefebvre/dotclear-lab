<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009-2011 Benoit de Marne.
# benoit.de.marne@gmail.com
# Many thanks to Association Dotclear and special thanks to Olivier Le Bris
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

// Rights management
if (!defined('DC_CONTEXT_ADMIN')) { return; }

require dirname(__FILE__).'/_widgets.php';

// Admin menu integration
$_menu['Plugins']->addItem('Newsletter',
	'plugin.php?p=newsletter',
	'index.php?pf=newsletter/icon.png',
	preg_match('/plugin.php\?p='.newsletterPlugin::pname().'(&.*)?$/', $_SERVER['REQUEST_URI']),
	$core->auth->check('newsletter,contentadmin', $core->blog->id)
	);

// Adding permission
$core->auth->setPermissionType('newsletter',__('manage newsletter'));

if ($core->auth->check('newsletter,contentadmin',$core->blog->id)) {
	// Adding behaviors
	$core->addBehavior('pluginsBeforeDelete', array('newsletterBehaviors', 'pluginsBeforeDelete'));
	$core->addBehavior('adminAfterPostCreate', array('newsletterBehaviors', 'adminAutosend'));
	$core->addBehavior('adminAfterPostUpdate', array('newsletterBehaviors', 'adminAutosendUpdate'));
	
	// Adding import/export behavior
	$core->addBehavior('exportFull',array('newsletterBehaviors','exportFull'));
	$core->addBehavior('exportSingle',array('newsletterBehaviors','exportSingle'));
	$core->addBehavior('importInit',array('newsletterBehaviors','importInit'));
	$core->addBehavior('importFull',array('newsletterBehaviors','importFull'));
	$core->addBehavior('importSingle',array('newsletterBehaviors','importSingle'));
	
	// Dynamic method
	$core->rest->addFunction('prepareALetter', array('newsletterRest','prepareALetter'));
	$core->rest->addFunction('sendLetterBySubscriber', array('newsletterRest','sendLetterBySubscriber'));
}

?>