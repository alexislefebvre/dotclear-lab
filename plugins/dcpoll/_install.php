<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'DC Poll', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'DC Poll' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$label = 'dcpoll';

# Module version
$m_version = $core->plugins->moduleInfo($label,'version');

# Installed version
$i_version = $core->getVersion($label);

# OK, nothing to do
if (version_compare($i_version,$m_version,'>=')) {
	return;
}

# --INSTALL AND UPDATE PROCEDURES--

$s = new dbStruct($core->con,$core->prefix);
$settings = &$core->blog->settings;
$settings->setNamespace('dcpoll');

if ($i_version == '0.2') {
	$settings->put('dcp_ctrl_method','any','string','DC Poll control method');
}
elseif ($i_version !== null) {
	# Already installed, nothing to do
}
else {
	$settings->put('dcp_ctrl_method','any','string','DC Poll control method');
	
	# Polls questions
	$s->poll_q
		->q_uid		('integer',	0,	false)
		->q_title		('varchar',	255,	false)
		->q_notes		('text',		0,	true,	null)
		->q_multi		('smallint',	0,	false,	0)
		->q_status	('smallint',	0,	false,	0)
		->q_cdt		('timestamp',	0,	false,	'now()')
		->q_edt		('timestamp',	0,	false)
		->q_votes		('integer',	0,	false,	0)
		
		->primary('pk_poll_q','q_uid')
		;
	# Polls possible answers
	$s->poll_a
		->a_uid		('integer',	0,	false)
		->q_uid		('integer',	0,	false)
		->a_title		('varchar',	255,	false)
		->a_votes		('integer',	0,	false,	0)
		
		->primary('pk_poll_a','a_uid')
		->unique('uk_poll_a','q_uid','a_title')
		;
	# Polls votes control
	$s->poll_vote
		->vote_userid	('char',		32,	false)
		->q_uid		('integer',	0,	false)
		
		->unique('uk_poll_vote','vote_userid','q_uid')
		;
	
	# References
	$s->poll_a->reference('fk_poll_a2q','q_uid','poll_q','q_uid','cascade','cascade');
	$s->poll_vote->reference('fk_poll_vote2q','q_uid','poll_q','q_uid','cascade','cascade');
	
	# Indexes
	# Indexes will be created later, in the next version.
}

# --SCHEMA SYNC--

$si = new dbStruct($core->con,$core->prefix);
$si->synchronize($s); 

# --SETTING NEW VERSION--

$core->setVersion($label,$m_version);	

unset($label,$i_version,$m_version,$s,$si);
return true;
?>
