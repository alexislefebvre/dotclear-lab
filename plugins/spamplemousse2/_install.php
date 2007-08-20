<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Spamplemousse2, a plugin for DotClear.  
# Copyright (c) 2007 Alain Vagner and contributors. All rights
# reserved.
#
# Spamplemousse2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Spamplemousse2 is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Spamplemousse2; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

#plugin label
$label = 'spamplemousse2';

# We read the plugin version
$m_version = $core->plugins->moduleInfo($label,'version');
 
# We read the plugin version in the version table
$i_version = $core->getVersion($label);
 
# If the version in the version table is greater than
# the one of this module, -> do nothing 
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

$s = new dbStruct($core->con,$core->prefix);

# spam_token table creation
$s->spam_token
	->token_id('varchar',255,false,0)
	->token_nham('integer',0,false,0)
	->token_nspam('integer',0,false,0)
	->token_mdate('timestamp',0,false,'now()')
	->token_p('float', 0, false,0)
	->token_mature('smallint', 0, false,0)
	->primary('pk_spam_token','token_id')
	;
	
# we add two columns on the comment table
$s->comment
	->comment_bayes('smallint',0,false,0)
	->comment_bayes_err('smallint',0,false,0)	
	;

# schema sync
$si = new dbStruct($core->con,$core->prefix);
$si->synchronize($s); 

$core->setVersion($label,$m_version);	

return true;		
?>
