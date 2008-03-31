<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Subscribe to comments.
# Copyright 2008 Moe (http://gniark.net/)
#
# Subscribe to comments is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Subscribe to comments is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://txfx.net/code/wordpress/subscribe-to-comments/
#
# ***** END LICENSE BLOCK *****

# On lit la version du plugin
$m_version = $core->plugins->moduleInfo('subscribeToComments','version');
 
# On lit la version du plugin dans la table des versions
$i_version = $core->getVersion('subscribeToComments');
 
# La version dans la table est supérieure ou égale à
# celle du module, on ne fait rien puisque celui-ci
# est installé
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# replace old tag with new tag
if (version_compare($i_version,'1.0-RC4','<')) {
	$core->blog->settings->setNameSpace('subscribetocomments');
	$core->blog->settings->put('subscribetocomments_email_subject',
	str_replace('%5$s','%6$s',$core->blog->settings->subscribetocomments_email_subject),
	'text','Email subject',true);
	$core->blog->settings->put('subscribetocomments_email_content',
	str_replace('%5$s','%6$s',$core->blog->settings->subscribetocomments_email_content),
	'text','Email subject',true);
}
 

# table
$s = new dbStruct($core->con,$core->prefix);
 
$s->comment_subscriber
	->id('bigint',0,false)
	->email('varchar',255,false)
	# key sent by email
	->user_key('varchar',40,false)
	# temporary key when changing email address
	->temp_key('varchar',40,true,null)
	# timestamp when the temporary key expire
	->temp_expire('timestamp',0,true,null)
	# status
	->status('smallint',0,false,0)

	->primary('pk_comment_subscriber','id','email','user_key')
;

$s->comment_notification
	->comment_id('bigint',0,false)
	->sent('smallint',0,false,0)

	->primary('pk_comment_notification','comment_id')
;

# indexes
$s->comment_subscriber->index('idx_id', 'btree', 'id');
$s->comment_notification->index('idx_comment_id', 'btree', 'comment_id');
# foreign keys
# delete notifications when a comment is deleted
$s->comment_notification->reference('fk_comment_notification_comment','comment_id',
	'comment','comment_id','cascade','cascade');

$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

# La procédure d'installation commence vraiment là
$core->setVersion('subscribeToComments',$m_version);
return true;
?>