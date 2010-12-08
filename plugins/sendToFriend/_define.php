<?php
/* BEGIN LICENSE BLOCK
This file is part of SendToFriend, a plugin for Dotclear.

Julien Appert
brol contact@brol.info

Licensed under the GPL version 2.0 license.
A copy of this license is available in LICENSE file or at
http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
END LICENSE BLOCK */
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
        /* Name */                      'Send to friend',
        /* Description*/                "Permet d'envoyer un mail avec un lien vers un billet",
        /* Author */                    "Julien Appert, brol",
        /* Version */                   '1.0.5',
        /* Permissions */               'usage,contentadmin'
);
?>
