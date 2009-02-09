<?php
  // ***** BEGIN LICENSE BLOCK *****
  // This file is (c) Jean-Christophe Dubacq.
  // Licensed under CC-BY licence.
  //
  // ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$version = $core->plugins->moduleInfo('kezako','version');

if (version_compare($core->getVersion('kezako'),$version,'>=')) {
	return;
 }

/* Database schema
 -------------------------------------------------------- */
$s = new dbStruct($core->con,$core->prefix);

$s->kezako
->blog_id	('varchar',	32,	false)
->thing_id      ('varchar',	255,	false)
->thing_type	('varchar',	16,	false)
->thing_subtype	('varchar',	64,	true, null)
->thing_lang	('varchar',	5,	true, null)
->thing_text    ('text',        0,      true)

->primary('pk_kezako','blog_id','thing_id','thing_type','thing_subtype','thing_lang')
;

$s->kezako->reference('fk_kezako_blog','blog_id','blog','blog_id','cascade','cascade');


# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('kezako',$version);
return true;
?>