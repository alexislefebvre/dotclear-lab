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

if (!defined('DC_RC_PATH')) { return; }

global $__autoload, $core;

# autoload classes
$__autoload['dcNewsletter'] = dirname(__FILE__).'/inc/class.dc.newsletter.php';
$__autoload['newsletterSettings'] = dirname(__FILE__).'/inc/class.newsletter.settings.php';
$__autoload['newsletterPlugin'] = dirname(__FILE__).'/inc/class.newsletter.plugin.php';
$__autoload['newsletterTools'] = dirname(__FILE__).'/inc/class.newsletter.tools.php';
$__autoload['newsletterCore'] = dirname(__FILE__).'/inc/class.newsletter.core.php';
$__autoload['newsletterSubscribersList'] = dirname(__FILE__).'/inc/lib.newsletter.subscribers.list.php';
$__autoload['newsletterLetter'] = dirname(__FILE__).'/inc/class.newsletter.letter.php';
$__autoload['newsletterLettersList'] = dirname(__FILE__).'/inc/lib.newsletter.letters.list.php';
$__autoload['newsletterLinkedPostList'] = dirname(__FILE__).'/inc/lib.newsletter.linked.post.list.php';
$__autoload['newsletterCSS'] = dirname(__FILE__).'/inc/class.newsletter.css.php';
$__autoload['newsletterTabs'] = dirname(__FILE__).'/inc/class.newsletter.tabs.php';
$__autoload['newsletterRest'] = dirname(__FILE__).'/inc/class.newsletter.rest.php';
$__autoload['newsletterBehaviors'] = dirname(__FILE__).'/inc/class.newsletter.behaviors.php';

# Settings compatibility test
if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
	$core->blog->settings->addNamespace('newsletter');
	$blog_settings =& $core->blog->settings->newsletter;
} else {
	$core->blog->settings->setNamespace('newsletter');
	$blog_settings =& $core->blog->settings;
}

if ($blog_settings->newsletter_flag) {
	
	$core->url->register('newsletter','newsletter','^newsletter/(.+)$',array('urlNewsletter','newsletter'));
	$core->url->register('letterpreview','letterpreview','^letterpreview/(.+)$',array('urlNewsletter','letterpreview'));
	$core->url->register('letter','letter','^letter/(.+)$',array('urlNewsletter','letter'));
	
	$core->blog->dcNewsletter = new dcNewsletter($core);
	$core->setPostType('newsletter','plugin.php?p=newsletter&m=letter&id=%d',$core->url->getBase('newsletter').'/%s');

	$core->url->register('newsletters','newsletters','^newsletters(.*)$',array('urlNewsletter','newsletters'));	
}

?>