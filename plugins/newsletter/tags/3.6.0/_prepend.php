<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

global $__autoload, $core;

// autochargement de la classe
$__autoload['dcNewsletter'] = dirname(__FILE__).'/inc/class.dc.newsletter.php';
$__autoload['newsletterSettings'] = dirname(__FILE__).'/inc/class.newsletter.settings.php';
$__autoload['newsletterPlugin'] = dirname(__FILE__).'/inc/class.newsletter.plugin.php';
$__autoload['newsletterTools'] = dirname(__FILE__).'/inc/class.newsletter.tools.php';
$__autoload['newsletterCore'] = dirname(__FILE__).'/inc/class.newsletter.core.php';
$__autoload['newsletterSubscribersList'] = dirname(__FILE__).'/inc/class.newsletter.subscribers.php';

if(newsletterPlugin::isInstalled()) {
	// ajout de la gestion des url
	$core->url->register('newsletter','newsletter','^newsletter/(.+)$',array('urlNewsletter','newsletter'));
	
	$core->blog->dcNewsletter = new dcNewsletter($core);

}

?>
