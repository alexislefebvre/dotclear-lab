<?php
/* BEGIN LICENSE BLOCK
This file is part of Contact, a plugin for Dotclear.

K-net
Pierre Van Glabeke

Licensed under the GPL version 2.0 license.
A copy of this license is available in LICENSE file or at
http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
END LICENSE BLOCK */
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('contactWidget','initWidgets'));

class contactWidget
{
	public static function initWidgets($w)
	{
		global $core;
    $w->create('contact',__('Contact'),array('tplContact','contactWidget'));
		$w->contact->setting('title',__('Title: (Use %I for Contact icon)'),'%I Contact');
		$w->contact->setting('homeonly',__('Home page only'),0,'check');
		$w->contact->setting('usesubtitle',__('Display the link in a subtitle'),0,'check');
		$w->contact->setting('subtitle',__('If yes, subtitle :'),__('Contact me!'));
	}
}

?>
