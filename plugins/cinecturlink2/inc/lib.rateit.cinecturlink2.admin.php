<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# localized l10n
__('Rate this');

class adminRateItCinecturlink2
{
	public static function adminHeader($core)
	{
		echo
		'<script type="text/javascript"> '."\n".
		'$(function() { '."\n".
		"\$('#cinecturlink2-options-title').toggleWithLegend(\$('#cinecturlink2-options-content'),{cookie:'dcx_rateit_admin_cinecturlink2_options'}); \n".
		"\$('#cinecturlink2-entries-title').toggleWithLegend(\$('#cinecturlink2-entries-content'),{cookie:'dcx_rateit_admin_cinecturlink2_entries'}); \n".
		"}); \n".
		"</script>\n";
	}

	public static function adminTabs($core)
	{
		if (!$core->auth->check('usage,contentadmin',$core->blog->id)) return;

		if ($core->auth->check('admin',$core->blog->id)
		&& !empty($_POST['save']['cinecturlink2']) && isset($_POST['s']))
		{
			try
			{
				$core->blog->settings->setNamespace('rateit');
				$core->blog->settings->put('rateit_cinecturlink2_active',$_POST['s']['rateit_cinecturlink2_active'],'boolean','Enabled cinecturlink2 rating',true,false);
				$core->blog->settings->put('rateit_cinecturlink2_widget',$_POST['s']['rateit_cinecturlink2_widget'],'boolean','Enabled rating on cinecturlink2 widget',true,false);
				$core->blog->settings->put('rateit_cinecturlink2_page',$_POST['s']['rateit_cinecturlink2_page'],'boolean','Enabled rating on cinecturlink2 page',true,false);
				$core->blog->triggerBlog();
				http::redirect('plugin.php?p=rateIt&t=cinecturlink2&done=1');
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}

		try
		{
			$rateIt = new rateIt($core);
			$C2 = new cinecturlink2($core);
			$links = $C2->getLinks();
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}

		echo '<div class="multi-part" id="cinecturlink2" title="'.__('Cinecturlink 2').'">';

		if ($core->auth->check('admin',$core->blog->id))
		{
			echo 
			'<h2 id="cinecturlink2-options-title">'.__('Settings for cinecturlink').'</h2>'.
			'<div id="cinecturlink2-options-content">'.
			'<form method="post" action="plugin.php?p=rateIt">'.
			'<table>'.
			'<tr><td>'.__('Enable cinecturlink2 rating').'</td><td>'.form::combo(array('s[rateit_cinecturlink2_active]'),array(__('no')=>0,__('yes')=>1),$core->blog->settings->rateit_cinecturlink2_active).'</td></tr>'.
			'<tr><td>'.__('Include on cinecturlink widget').'</td><td>'.form::combo(array('s[rateit_cinecturlink2_widget]'),array(__('no')=>0,__('yes')=>1),$core->blog->settings->rateit_cinecturlink2_widget).'</td></tr>'.
			'<tr><td>'.__('Include on cinecturlink behaviors').'</td><td>'.form::combo(array('s[rateit_cinecturlink2_page]'),array(__('no')=>0,__('yes')=>1),$core->blog->settings->rateit_cinecturlink2_page).'</td></tr>'.
			'</table>'.
			'<p>'.
			form::hidden(array('p'),'rateIt').
			form::hidden(array('t'),'cinecturlink2').
			$core->formNonce().
			'<input type="submit" name="save[cinecturlink2]" value="'.__('Save').'" /></p>'.
			'</form>'.
			'</div>';
		}

		echo 
		'<h2 id="cinecturlink2-entries-title">'.__('List of cinecturlink').'</h2>'.
		'<div id="cinecturlink2-entries-content">';

		if ($links->isEmpty())
		{
			echo '<p class="message">'.__('There is no cinecturlink rating at this time').'</p>';
		}
		else
		{
			$table = '';
			while ($links->fetch())
			{
				$rs = $rateIt->get('cinecturlink2',$links->link_id);
				if (!$rs->total) continue;
				$table .= 
				'<tr class="line">'.
				'<td class="nowrap">'.form::checkbox(array('entries[]'),$links->link_id,'','','',false).'</td>'.
				'<td class="maximal">'.html::escapeHTML($links->link_title).'</td>'.
				'<td class="nowrap">'.$rs->note.'</td>'.
				'<td class="nowrap"><a title="'.__('Show rating details').'" href="plugin.php?p=rateIt&amp;t=details&amp;type=cinecturlink2&amp;id='.$links->link_id.'">'.$rs->total.'</a></td>'.
				'<td class="nowrap">'.$rs->max.'</td>'.
				'<td class="nowrap">'.$rs->min.'</td>'.
				'<td class="nowrap">'.$links->link_id.'</td>'.
				'<td class="nowrap">'.$links->link_creadt.'</td>'.
				'<td class="nowrap">'.$links->link_note.'/10</td>'.
				'</tr>';
			}

			echo 
			'<p>'.__('This is a list of all the cinecturlink having rating').'</p>'.
			'<form action="plugin.php" method="post" id="form-cinecturlink2">';

			if ($table=='')
			{
				echo '<p class="message">'.__('There is no cinecturlink rating at this time').'</p>';
			}
			else
			{
				echo 
				'<table class="clear"><tr>'.
				'<th colspan="2">'.__('Title').'</th>'.
				'<th>'.__('Note').'</th>'.
				'<th>'.__('Votes').'</th>'.
				'<th>'.__('Higher').'</th>'.
				'<th>'.__('Lower').'</th>'.
				'<th>'.__('Id').'</th>'.
				'<th>'.__('Date').'</th>'.
				'<th>'.__('My note').'</th>'.
				'</tr>'.
				$table.
				'</table>';
			}

			if ($core->auth->check('delete,contentadmin',$core->blog->id))
			{
				echo 
				'<div class="two-cols">'.
				'<p class="col checkboxes-helpers"></p>'.
				'<p class="col right">'.__('Selected cinecturlink action:').' '.
				form::combo(array('action'),array(__('delete rating') => 'rateit_cinecturlink2_empty')).
				'<input type="submit" name="save[cinecturlink2]" value="'.__('ok').'" />'.
				form::hidden(array('p'),'rateIt').
				form::hidden(array('t'),'cinecturlink2').
				$core->formNonce().
				'</p>'.
				'</div>';
			}
			echo '</form>';
		}
		echo '</div></div>';
	}
}
?>