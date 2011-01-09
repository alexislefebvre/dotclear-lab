<?php
# ***** BEGIN LICENSE BLOCK *****
# This file a plugin of DotClear.
# Copyright (c) Marc Vachette All rights
# reserved.
#
#Subscription is free software; you can redistribute it and/or modify
# it under the terms of the Creative Commons License "Attribution"
# see the page http://creativecommons.org/licenses/by/2.0/ for more information
# 
# Subscription is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# Creative COmmons License for more details.
#
# ***** END LICENSE BLOCK *****

$_menu['Plugins']->addItem('Subscription','plugin.php?p=subscription','index.php?pf=subscription/icon.png',
		preg_match('/plugin.php\?p=subscription(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('',$core->blog->id));

?>