<?php
/* BEGIN LICENSE BLOCK
This file is part of SendToFriend, a plugin for Dotclear.

Julien Appert
brol contact@brol.info

Licensed under the GPL version 2.0 license.
A copy of this license is available in LICENSE file or at
http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
END LICENSE BLOCK */
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$m_version = $core->plugins->moduleInfo('sendToFriend','version');
 
$i_version = $core->getVersion('sendToFriend');
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

$settings = new dcSettings($core,$core->blog->id);
$settings->addNamespace('sendtofriend');
$settings->sendtofriend->put('sendtofriend_abstractType','firstWords','string');
$settings->sendtofriend->put('sendtofriend_firstWords',30,'integer');
$settings->sendtofriend->put('sendtofriend_subject','%post-title%','string');
$sContent = utf8_encode("
Bonjour %receiver-name%,

%sender-name% pense que cet article peut vous intéresser. En voici un extrait :  

%post-abstract%

Lire la suite : %post-url%

Bonne lecture.
");
$settings->sendtofriend->put('sendtofriend_content',$sContent,'string');
 
$core->setVersion('sendToFriend',$m_version);
?>
