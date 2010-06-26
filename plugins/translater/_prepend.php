<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of translater, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}
if (version_compare(DC_VERSION,'2.2-alpha','<')){return;}

global $__autoload,$core;

# Main class
$__autoload['dcTranslater'] = dirname(__FILE__).'/inc/class.dc.translater.php';
# Admin rest function
$__autoload['translaterRest'] = dirname(__FILE__).'/inc/class.translater.rest.php';

# google tools
$__autoload['googleProposal'] = dirname(__FILE__).'/inc/lib.translater.google.php';
$core->addBehavior('dcTranslaterAddProposal','addGoogleProposalTool');
function addGoogleProposalTool($core,$proposal)
{
	$proposal->addTool('google','Google translation',array('googleProposal','init'));
}
# yahoo babelfish tools
$__autoload['babelfishProposal'] = dirname(__FILE__).'/inc/lib.translater.babelfish.php';
$core->addBehavior('dcTranslaterAddProposal','addBabelfishProposalTool');
function addBabelfishProposalTool($core,$proposal)
{
	$proposal->addTool('babelfish','Babelfish translation',array('babelfishProposal','init'));
}
?>