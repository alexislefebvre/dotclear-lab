<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# Get new version
$new_version = $core->plugins->moduleInfo('kUtRL','version');
$old_version = $core->getVersion('kUtRL');

# Compare versions
if (version_compare($old_version,$new_version,'>=')) {return;}

# Install or update
try
{
	# Check DC version (dev on)
	if (!version_compare(DC_VERSION,'2.1.6','>='))
	{
		throw new Exception('Plugin called kUtRL requires Dotclear 2.1.6 or higher.');
	}
	# Check DC version (new settings)
	if (version_compare(DC_VERSION,'2.2','>='))
	{
		throw new Exception('Plugin called kUtRL requires Dotclear up to 2.2.');
	}

	# Table
	$s = null;
	$s = new dbStruct($core->con,$core->prefix);
	$s->kutrl
		->kut_id('bigint',0,false)
		->blog_id('varchar',32,false)
		->kut_service('varchar',32,false,"'kUtRL'")
		->kut_type('varchar',32,false)
		->kut_hash('varchar',32,false)
		->kut_url('text',0,false)
		->kut_dt('timestamp',0,false,'now()')
		->kut_password('varchar',32,true)
		->kut_counter('bigint',0,false,0)

		->primary('pk_kutrl','kut_id')
		->index('idx_kut_blog_id','btree','blog_id')
		->index('idx_kut_hash','btree','kut_hash')
		->index('idx_kut_service','btree','kut_service')
		->index('idx_kut_type','btree','kut_type');

	$si = new dbStruct($core->con,$core->prefix);
	$changes = $si->synchronize($s);

	# Settings
	$s = null;
	$s =& $core->blog->settings;
	$s->setNameSpace('kUtRL');
	$s->put('kutrl_active',false,'boolean','Enabled kutrl plugin',false,true);
	$s->put('kutrl_admin_service','local','string','Service to use to shorten links on admin',false,true);
	$s->put('kutrl_tpl_service','local','string','Service to use to shorten links on template',false,true);
	$s->put('kutrl_wiki_service','','string','Service to use to shorten links on contents',false,true);
	$s->put('kutrl_limit_to_blog',false,'boolean','Limited short url to current blog\'s url',false,true);
	# Settings for features related to others plugins
	$s->put('kutrl_extend_importexport',true,'boolean','Enabled import/export behaviors',false,true);
	$s->put('kutrl_extend_activityreport',true,'boolean','Enabled activiyReport behaviors',false,true);
	$s->put('kutrl_extend_dcadvancedcleaner',true,'boolean','Enabled activiyReport behaviors',false,true);
	# Settings for "local" service
	$local_css =
	".shortenkutrlwidget input { border: 1px solid #CCCCCC; }\n".
	".dc-kutrl input { border: 1px solid #CCCCCC; margin: 10px; }";
	$s->put('kutrl_srv_local_protocols','http:,https:,ftp:,ftps:,irc:','string','Allowed kutrl local service protocols',false,true);
	$s->put('kutrl_srv_local_public',false,'boolean','Enabled local service public page',false,true);
	$s->put('kutrl_srv_local_css',$local_css,'string','Special CSS for kutrl local service',false,true);
	# Settings for "bilbolinks" service
	$s->put('kutrl_srv_bilbolinks_base','http://tux-pla.net/','string','URL of bilbolinks service',false,true);
	$s->setNameSpace('system');

	# Version
	$core->setVersion('kUtRL',$new_version);

	# Get dcMiniUrl records as this plugin do the same
	if ($core->plugins->moduleExists('dcMiniUrl'))
	{
		require_once dirname(__FILE__).'/inc/patch.dcminiurl.php';
	}
	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
	return false;
}
?>