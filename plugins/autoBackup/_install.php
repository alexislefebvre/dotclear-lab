<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of autoBackup, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }
 
$m_version = $core->plugins->moduleInfo('autoBackup','version');
 
$i_version = $core->getVersion('autoBackup');
 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# Init config
$config['importexportclasspath'] = realpath($core->plugins->moduleRoot('importExport').'/inc/flat/class.db.export.php');
# Backup on file group:
$config['backup_onfile'] = false;
$config['backup_onfile_repository'] = $core->blog->public_path;
$config['backup_onfile_compress_gzip'] = false;
$config['backup_onfile_deleteprev'] = false;
# Backup sent by mail group:
$config['backup_onemail'] = false;
$config['backup_onemail_adress'] = '';
$config['backup_onemail_compress_gzip'] = true;
$config['backup_onemail_header_from'] = $core->blog->name.' <your@email.com>';
# Backup characteristics:
$config['backuptype'] = 'full';
$config['backupblogid'] = $core->blog->id;
$config['interval'] = 3600*24;
# Last backups done:
$config['backup_onfile_last'] = array('date' => 0, 'file' => '');
$config['backup_onemail_last'] = array('date' => 0);
# Running backup flag:
$config['backup_running'] = false;
# Backup ASAP flag:
$config['backup_asap'] = false;
# Backup errors
$config['errors'] = array('config' => '','file' => '','email' => '');
# Activity
$config['activity_count'] = 0;
$config['activity_threshold'] = 0;

$settings = new dcSettings($core,null);
$settings->setNamespace('autobackup');
$settings->put('autobackup_config',serialize($config),'string','autoBackup settings',false);

$core->setVersion('autoBackup',$m_version);

?>