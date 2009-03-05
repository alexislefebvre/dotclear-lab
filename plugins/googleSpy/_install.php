<?php 

# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$label = 'googleSpy';
$m_version = $core->plugins->moduleInfo($label,'version');
$i_version = $core->getVersion($label);

if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--

$settings = &$core->blog->settings;
$settings->setNamespace(strtolower($label));

# New install / update
$settings->put('num_posts','5','integer',__('Number of purposed posts'),false);
$settings->put('num_keywords','3','integer',__('Number of analysed keywords'),false);
$settings->put('title','A Lire :','string',__('Title'),false);
$settings->put('description','','string',__('Description'),false);
$settings->put('active',true,'boolean',__('Plugin enabled'),false);
$settings->put('ignored_words','un,une,de,du,des,la,le,pour,sans,avec,sous,dessus,tu,je,il,elle,on,nous,vous,ils,elles,mes,mon,ton,son,ses,tes,mes,as,ai,ont,avons,avez,suis,es,est,sont,?tes,on,i,your,it,its,my,she,he,you,the,a,we', 'string',__('Ignored words'),false);

# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);
unset($label,$i_version,$m_version);

return true;

?>