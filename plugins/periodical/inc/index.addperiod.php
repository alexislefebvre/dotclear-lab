<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of periodical, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

$period_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : -1;
$action_redir = $p_url.'&part=editperiod&tab=period&id=%s&msg='.$action;

$period_title = isset($_POST['period_title']) ? $_POST['period_title'] : __('One post per day');
$period_pub_nb = isset($_POST['period_pub_nb']) ? abs((integer) $_POST['period_pub_nb']) : 1;
$period_pub_int = isset($_POST['period_pub_int']) ? (string) $_POST['period_pub_int'] : 'day';
$period_curdt = isset($_POST['period_curdt']) ? date('Y-m-d H:i:00',strtotime($_POST['period_curdt'])) : date('Y-m-d H:i:00',time());
$period_enddt = isset($_POST['period_enddt']) ? date('Y-m-d H:i:00',strtotime($_POST['period_enddt'])) : date('Y-m-d H:i:00',time()+31536000); //one year

# Update period
if ($action == 'createperiod' && !empty($_POST))
{
	try {
		$old_titles = $per->getPeriods(array('periodical_title'=>$period_title));
		if (!$old_titles->isEmpty()) {
			throw New Exception(__('Period title is already taken'));
		}
		if (empty($period_title)) {
			throw New Exception(__('Period title is required'));
		}
		if (strtotime($period_strdt) > strtotime($period_enddt)) {
			throw New Exception(__('Start date must be older than end date'));
		}

		$cur = $per->openCursor();
		$cur->periodical_title = $period_title;
		$cur->periodical_curdt = $period_curdt;
		$cur->periodical_enddt = $period_enddt;
		$cur->periodical_pub_int = $period_pub_int;
		$cur->periodical_pub_nb = $period_pub_nb;
		$period_id = $per->addPeriod($cur);

		http::redirect(sprintf($action_redir,$period_id));
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Display
echo '
<html><head><title>'.__('Periodical').'</title>'.
dcPage::jsDatePicker().
dcPage::jsLoad('index.php?pf=periodical/js/period.js').
'</head>
<body>
<h2>'.
html::escapeHTML($core->blog->name).
' &rsaquo; <a href="'.$p_url.'&amp;part=periods">'.__('Periodical').'</a>'.
' &rsaquo; '.__('New period').
'</h2>'.$msg;

# Period
echo '
<form method="post" action="plugin.php">
<p><label>'.__('Title:').
form::field('period_title',60,255,html::escapeHTML($period_title),'maximal',3).'</label></p>
<div class="two-cols">
<div class="col">
<p><label>'.__('Next update:').
form::field('period_curdt',16,16,date('Y-m-d H:i',strtotime($period_curdt)),'',3).'</label></p>
<p><label>'.__('End date:').
form::field('period_enddt',16,16,date('Y-m-d H:i',strtotime($period_enddt)),'',3).'</label></p>
</div><div class="col">
<p><label>'.__('Publication frequency:').
form::combo('period_pub_int',$per->getTimesCombo(),$period_pub_int,'',3).'</label></p>
<p><label>'.__('Number of entries to publish every time:').
form::field('period_pub_nb',10,3,html::escapeHTML($period_pub_nb),'',3).'</label></p>
</div></div>
<div class="clear">
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('action'),'createperiod').
form::hidden(array('id'),$period_id).
form::hidden(array('p'),'periodical').
form::hidden(array('part'),'addperiod').'
</p>
</div>
</form>';
?>