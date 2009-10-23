<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# Loading plugin files
$__autoload['dcOpenSearch'] = dirname(__FILE__).'/inc/class.dc.open.search.php';
$__autoload['dcSearchEngine'] = dirname(__FILE__).'/inc/class.dc.search.engine.php';
$__autoload['dcSearchEngines'] = dirname(__FILE__).'/inc/class.dc.search.engines.php';
$__autoload['dcOpenSearchRsExtensions'] = dirname(__FILE__).'/inc/class.dc.open.search.rs.extensions.php';
# Loading search engines
$__autoload['dcEnginePosts'] = dirname(__FILE__).'/engines/class.dc.engine.posts.php';
$__autoload['dcEnginePages'] = dirname(__FILE__).'/engines/class.dc.engine.pages.php';
$__autoload['dcEngineComments'] = dirname(__FILE__).'/engines/class.dc.engine.comments.php';
$__autoload['dcEngineMedias'] = dirname(__FILE__).'/engines/class.dc.engine.medias.php';
# Loading admin search list
$__autoload['adminSearchList'] = dirname(__FILE__).'/inc/class.admin.search.list.php';
# Loading behaviors
$__autoload['dcOpenSearchBehaviors'] = dirname(__FILE__).'/inc/class.dc.open.search.behaviors.php';
$__autoload['dcOpenSearchURL'] = dirname(__FILE__).'/inc/class.dc.open.search.behaviors.php';

# Init search engines
$core->searchengines = array('dcEnginePosts','dcEnginePages','dcEngineComments','dcEngineMedias');
# Init public behavior
$core->addBehavior('publicBeforeDocument',array('dcOpenSearchBehaviors','addTplPath'));
$core->addBehavior('publicBeforeDocument',array('dcOpenSearchURL','getResults'));

require dirname(__FILE__).'/_widgets.php';

?>