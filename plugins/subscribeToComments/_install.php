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

# transfer the notifications to (dc_)comment and
# delete the table (dc_)comment_notification 
if (version_compare($i_version,'1.0.4','<')) {
	$s = new dbStruct($core->con,$core->prefix);
	$s->comment
		->notification_sent('smallint',0,false,0)
	;
	$si = new dbStruct($core->con,$core->prefix);
	$changes = $si->synchronize($s);

	$comment_ids = '';
	$rs = $core->con->select('SELECT comment_id FROM '.$core->prefix.'comment_notification '.
	'WHERE (sent = 1);');
	if (!$rs->isEmpty())
	{
		while ($rs->fetch())
		{
			if ($comment_ids == '') {$comment_ids = $rs->comment_id;}
			else {$comment_ids .= ','.$rs->comment_id;}
		}
		$core->con->execute('UPDATE '.$core->prefix.'comment SET notification_sent = 1 '.
		'WHERE comment_id in ('.$comment_ids.');');
	}
	$core->con->execute('DROP TABLE '.$core->prefix.'comment_notification;');
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

$s->comment
	->notification_sent('smallint',0,false,0)
;

# indexes
$s->comment_subscriber->index('idx_id', 'btree', 'id');
$s->comment_subscriber->index('idx_email', 'btree', 'email');

$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

# La procédure d'installation commence vraiment là
$core->setVersion('subscribeToComments',$m_version);
return true;
?>