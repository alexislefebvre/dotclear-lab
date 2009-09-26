<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

dcOpenSearch::initEngines();
$engines = dcOpenSearch::$engines->getEngines();
$engines_available = array();

foreach ($engines as $eid => $e) {
	if ($e->active) {
		$engines_available[$eid] = $e;
	}
}

# Init
$p_name		= 'dcOpenSearch';
$p_url		= 'plugin.php?p='.$p_name;
$page_name	= __('Advanced search');
$search_res	= '';
$nb_per_page	= isset($_GET['nb_per_page']) ? $_GET['nb_per_page'] : 10;
$engine_gui	= false;
$page		= !empty($_GET['page']) ? $_GET['page'] : 1;
$tab			= isset($_GET['t']) ? $_GET['t'] : 'search';
$q 			= !empty($_GET['q']) ? $_GET['q'] : null;
$se			= !empty($_GET['$se']) ? $_GET['$se'] : $core->searchengines;

try
{
	# Search something
	if ($q !== null)
	{
		$rs = dcOpenSearch::search($q,$se);
		
		if ($rs->count() > 0) {
			$search_res .= sprintf('<h3>'.
			($rs->count() == 1 ? __('%d result found') : __('%d results found')).
			'</h3>',$rs->count());
		}
		
		$search_list = new adminSearchList($core,$rs,$rs->count());
			
		$search_res .= $search_list->display($page,$nb_per_page);
	}
	
	# Show engine configuration GUI
	if (!empty($_GET['e']))
	{
		if (!isset($engines[$_GET['e']])) {
			throw new Exception(__('Engine does not exist.'));
		}
		
		if (!$engines[$_GET['e']]->hasGUI()) {
			throw new Exception(__('Engine has no user interface.'));
		}
		
		$engine = $engines[$_GET['e']];
		$engine_gui = $engine->gui($engine->guiURL());
	}
	
	# Update engines
	if (isset($_POST['engines_upd']))
	{
		$engines_opt = array();
		$i = 0;
		foreach ($engines as $eid => $e) {
			$engines_opt[$eid] = array(false,$i);
			$i++;
		}
		
		# Enable active engines
		if (isset($_POST['engines_active']) && is_array($_POST['engines_active'])) {
			foreach ($_POST['engines_active'] as $v) {
				$engines_opt[$v][0] = true;
			}
		}
		
		# Order filters
		if (!empty($_POST['e_order']) && empty($_POST['engines_order']))
		{
			$order = $_POST['e_order'];
			asort($order);
			$order = array_keys($order);
		}
		elseif (!empty($_POST['engines_order']))
		{
			$order = explode(',',trim($_POST['engines_order'],','));
		}
		
		if (isset($order)) {
			foreach ($order as $i => $e) {
				$engines_opt[$e][1] = $i;
			}
		}

		dcOpenSearch::$engines->saveEngineOpts($engines_opt);
		http::redirect($p_url.'&t=config&upd=1');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
?>
<html>
<head>
  <title><?php echo $page_name; ?></title>
  <?php
  echo
  dcPage::jsToolMan().
  dcPage::jsPageTabs($tab).
  dcPage::jsLoad('index.php?pf=dcOpenSearch/js/dcOpenSearch.min.js');
  ?>
  <link rel="stylesheet" type="text/css" href="index.php?pf=antispam/style.css" />
</head>
<body>

<?php
echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.$page_name.'</h2>'.
'<div class="multi-part" id="search" title="'.$page_name.'" >';

if (count($engines_available) != 0) {
	echo
	'<form action="'.$p_url.'" method="get">'.
	form::hidden('p',$p_name).
	'<fieldset><legend>'.__('Search options').'</legend>'.
	'<p><label class="classic">'.__('Query:').' '.form::field('q',30,255,html::escapeHTML($q)).'</label>'.
	'</p><p>'.__('in:').'&nbsp;';
	
	foreach ($engines_available as $eid => $e) {
		echo
		'<label class="classic">'.
		form::checkbox(array('se[]'),$e->name,in_array($e->name,$se)).' '.
		$e->label.'</label> ';
	}
	
	echo
	'</p><p><label class="classic">'.__('Number per page:').' '.
	form::combo('nb_per_page',array('10' => '10','20' => '20','30' => '30','50' => '50'),$nb_per_page).'</label>'.
	'</p><p><input type="submit" value="'.__('ok').'" /></p>'.
	'</fieldset>'.
	'</form>'.
	$search_res;
}
else {
	echo '<p>'.__('No search engine available').'</p>';
}

echo '</div>';

if ($core->auth->check('admin',$core->blog->id)) {
	
	echo '<div class="multi-part" id="config" title="'.__('Configuration').'" >';

	if ($engine_gui !== false)
	{
		echo '<p><a href="'.$p_url.'&amp;t=config">'.__('Return to filters').'</a></p>';
		echo '<h3>'.$engine->description.' &rsaquo; '.__('Configuration').'</h3>';
		
		echo $engine_gui;
	}
	else
	{
		# Filters
		echo
		'<form action="'.$p_url.'" method="post">'.
		'<fieldset><legend>'.__('Available search engines').'</legend>';
		
		if (!empty($_GET['upd'])) {
			echo '<p class="message">'.__('Engines configuration has been successfully saved.').'</p>';
		}
		
		echo
		'<table class="dragable">'.
		'<thead><tr>'.
		'<th>'.__('Order').'</th>'.
		'<th>'.__('Active').'</th>'.	
		'<th class="nowrap">'.__('Engine name').'</th>'.
		'<th class="nowrap">'.__('Engine type').'</th>'.
		'<th colspan="2">'.__('Description').'</th>'.
		'</tr></thead>'.
		'<tbody id="engines-list" >';
		
		$i = 0;
		foreach ($engines as $eid => $e)
		{
			$gui_link = '&nbsp;';
			if ($e->hasGUI()) {
				$gui_link =
				'<a href="'.html::escapeHTML($e->guiURL()).'">'.
				'<img src="images/edit-mini.png" alt="'.__('Engine configuration').'" '.
				'title="'.__('Engine configuration').'" /></a>';
			}
			
			echo
			'<tr class="line'.($e->active ? '' : ' offline').'" id="f_'.$eid.'">'.
			'<td class="handle">'.form::field(array('e_order['.$eid.']'),2,5,(string) $e->order).'</td>'.
			'<td class="nowrap">'.form::checkbox(array('engines_active[]'),$eid,$e->active).'</td>'.	
			'<td class="nowrap">'.$e->name.'</td>'.	
			'<td class="nowrap">'.$e->label.'</td>'.
			'<td class="maximal">'.$e->description.'</td>'.
			'<td class="status">'.$gui_link.'</td>'.
			'</tr>';
			$i++;
		}
		echo
		'</tbody></table>'.
		'<p>'.form::hidden('engines_order','').
		$core->formNonce().
		'<input type="submit" name="engines_upd" value="'.__('Save').'" /></p>'.
		'</fieldset></form>';
	}

	echo '</div>';

}

?>

</body>
</html>