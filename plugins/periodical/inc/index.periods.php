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

$action_redir = 'plugin.php?p=periodical&part=periods&msg='.$action;

# Delete periods and related posts links
if ($action == 'deleteperiods' && !empty($_POST['periods']))
{
	try
	{
		foreach($_POST['periods'] as $id)
		{
			$id = (integer) $id;
			$per->delPeriodPosts($id);
			$per->delPeriod($id);
		}
		http::redirect($action_redir);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
# Delete periods related posts links (whithout delete periods)
if ($action == 'emptyperiods' && !empty($_POST['periods']))
{
	try
	{
		foreach($_POST['periods'] as $id)
		{
			$id = (integer) $id;
			$per->delPeriodPosts($id);
		}
		http::redirect($action_redir);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

$sortby_combo = array(
	__('Next update') => 'periodical_curdt',
	__('End date') => 'periodical_enddt',
	__('Frequence') => 'periodical_pub_int'
);

$order_combo = array(
	__('Descending') => 'desc',
	__('Ascending') => 'asc'
);

# Actions combo box
$combo_action = array();
$combo_action[__('empty periods')] = 'emptyperiods';
$combo_action[__('delete periods')] = 'deleteperiods';

/* Filters
-------------------------------------------------------- */
$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'periodical_curdt';
$order = !empty($_GET['order']) ?		$_GET['order'] : 'desc';

$show_filters = false;

$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0)
{
	if ($nb_per_page != $_GET['nb'])
	{
		$show_filters = true;
	}
	$nb_per_page = (integer) $_GET['nb'];
}

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);

# - Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo))
{
	if ($order !== '' && in_array($order,$order_combo))
	{
		$params['order'] = $sortby.' '.$order;
	}
	
	if ($sortby != 'periodical_curdt' || $order != 'desc')
	{
		$show_filters = true;
	}
}

# Get periods
try
{
	$periods = $per->getPeriods($params);
	$counter = $per->getPeriods($params,true);
	$period_list = new adminPeriodicalList($core,$periods,$counter->f(0));
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

# Display
echo '
<html><head><title>'.__('Periodical').'</title>';
if (!$show_filters) {
	echo dcPage::jsLoad('js/filter-controls.js');
}
echo '
</head>
<body>
<h2>'.
html::escapeHTML($core->blog->name).
' &rsaquo; '.__('Periodical').
' - <a class="button" href="'.$p_url.'&amp;part=addperiod">'.__('New period').'</a>'.
'</h2>'.$msg.'

<p><a id="filter-control" class="form-control" href="#">'.__('Filters').'</a></p>


<form action="'.$p_url.'" method="get" id="filters-form">'.
'<fieldset><legend>'.__('Filters').'</legend>'.
'<div class="three-cols">'.
'<div class="col">'.
'<p><label>'.__('Order by:').
form::combo('sortby',$sortby_combo,$sortby).'</label></p>'.
'</div>'.

'<div class="col">'.
'<p><label>'.__('Sort:').
form::combo('order',$order_combo,$order).'</label></p>'.
'</div>'.

'<div class="col">'.
'<p><label class="classic">'.form::field('nb',3,3,$nb_per_page).' '.
__('Entries per page').'</label> '.
'<input type="submit" value="'.__('filter').'" />'.
form::hidden(array('p'),'periodical').
form::hidden(array('part'),'periods').'</p>'.
'</div>'.

'</div>'.
'<br class="clear" />'. //Opera sucks
'</fieldset>'.
'</form>';

# Show posts
echo $period_list->periodDisplay($page,$nb_per_page,
	'<form action="'.$p_url.'" method="post" id="form-periods">'.

	'%s'.

	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.

	'<p class="col right">'.__('Selected periods action:').' '.
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden(array('sortby'),$sortby).
	form::hidden(array('order'),$order).
	form::hidden(array('page'),$page).
	form::hidden(array('nb'),$nb_per_page).
	form::hidden(array('p'),'periodical').
	form::hidden(array('part'),'periods').
	$core->formNonce().
	'</div>'.
	'</form>'
);
?>