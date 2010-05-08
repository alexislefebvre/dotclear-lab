<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of doTwit, a plugin for Dotclear.
#
# Copyright (c) 2007 Valentin VAN MEEUWEN
# <adresse email>
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/admin');
		
require dirname(__FILE__).'/_widgets.php';
?>
