<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dctranslations, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Jean-Claude Dubacq, Franck Paul and contributors
# carnet.franck.paul@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

if (1==1) {// always

    $version = $core->plugins->moduleInfo('dctranslations','version');

    if (version_compare($core->getVersion('dctranslations'),$version,'>=')) {
        return;
    }

    /* Database schema
     -------------------------------------------------------- */
    $s = new dbStruct($core->con,$core->prefix);

    $s->translation
        ->translation_id	('bigint',	0,	false)
        ->post_id		('bigint',	0,	true)
        ->translation_lang	('varchar',	255,	false)
        ->translation_title	('varchar',	255,	false)
        ->translation_excerpt	('text',	0,	true, null)
        ->translation_excerpt_xhtml	('text',	0,	true, null)
        ->translation_content	('text',	0,	true, null)
        ->translation_content_xhtml	('text',	0,	true, null)
        ->translation_words	('text',	0,	true, null)
        ->translation_url	('varchar',	255,	false)

        ->primary('pk_translation','translation_id')
        ;
    $s->translation->index('idx_translation_post_id','btree','post_id');
    $s->translation->reference('fk_translation_post','post_id','post','post_id','cascade','cascade');

    $t = new dbStruct($core->con,$core->prefix);

    $t->word
        ->blog_id	('varchar',	32,	false)
        ->lang	('varchar',	5,	false)
        ->w_word	('text',	0,	true, null)
        ->w_result	('text',	0,	true, null)
	
        ;

    $t->word->index('idx_word_blog_id','btree','blog_id');

# Schema installation
    $si = new dbStruct($core->con,$core->prefix);
    $changes = $si->synchronize($s);
    $changes = $si->synchronize($t);

    $core->setVersion('dctranslations',$version);

    // Insert row with number 0 related to no post, will serve
    // as marker for "original post"
    // My SQL-foo ends up about there
    $core->con->select('DELETE FROM '.$core->prefix.
                       'translation WHERE translation_id = \'0\'');
    $cur = $core->con->openCursor($core->prefix.'translation');
    $core->con->begin();
    $cur->clean();
    $cur->translation_lang = '';
    $cur->translation_id = 0;
    $cur->translation_title='NULL';
    $cur->translation_excerpt='';
    $cur->translation_content='';
    $cur->translation_excerpt_xhtml='';
    $cur->translation_content_xhtml='';
    $cur->translation_words='';
    $cur->post_id=null;
    $cur->insert();
    $core->con->commit();
    return true;
 }
?>