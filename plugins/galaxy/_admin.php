<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear Galaxy plugin.
#
# Dotclear Galaxy plugin is free software: you can redistribute it
# and/or modify  it under the terms of the GNU General Public License
# version 2 of the License as published by the Free Software Foundation.
#
# Dotclear Galaxy plugin is distributed in the hope that it will be
# useful, but WITHOUT ANY WARRANTY; without even the implied warranty
# of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Dotclear Galaxy plugin.
# If not, see <http://www.gnu.org/licenses/>.
#
# Copyright (c) 2010 Mounir Lamouri.
# Based on the Dotclear metadata plugin by Olivier Meunier.
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Blog']->addItem(__('Planets'),'plugin.php?p=galaxy&amp;m=planets','index.php?pf=galaxy/icon.png',
		preg_match('/plugin.php\?p=galaxy&m=planet(s|_posts)?(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));

$core->addBehavior('adminPostFormSidebar',array('galaxyBehaviors','planetsField'));

$core->addBehavior('adminAfterPostCreate',array('galaxyBehaviors','setPlanets'));
$core->addBehavior('adminAfterPostUpdate',array('galaxyBehaviors','setPlanets'));

$core->addBehavior('adminPostsActionsCombo',array('galaxyBehaviors','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActions',array('galaxyBehaviors','adminPostsActions'));
$core->addBehavior('adminPostsActionsContent',array('galaxyBehaviors','adminPostsActionsContent'));

$core->addBehavior('exportFull',array('galaxyBehaviors','exportFull'));
$core->addBehavior('exportSingle',array('galaxyBehaviors','exportSingle'));
$core->addBehavior('importInit',array('galaxyBehaviors','importInit'));
$core->addBehavior('importSingle',array('galaxyBehaviors','importSingle'));
$core->addBehavior('importFull',array('galaxyBehaviors','importFull'));
$core->addBehavior('importPrepareDC12',array('galaxyBehaviors','importPrepareDC12'));

# BEHAVIORS
class galaxyBehaviors
{
	public static function planetsField(&$post)
	{
		$galaxy = new dcGalaxy($GLOBALS['core']);
		
		$planets = $galaxy->getPlanets();

		if (isset($post))
			$selected = $galaxy->getPlanets(null, null, $post->post_id);

		// TODO: this code should be optimized !
		while ($planets->fetch())
		{
			$v .= '<option ';
			if (isset($post))
			{
				while ($selected->fetch())
				{
					if ($planets->planet_id == $selected->planet_id)
						$v .= "selected";
				}
			}
			$v .= '>' . $planets->planet_id . "</option>";
		}
		
		echo
		'<h3><label for="post_planets">'.__('Post in planets:').'</label></h3>'.
		'<div class="p"><select name="post_planets[]" multiple>' .$v. '</select></div>'.
		'<label>OR new planet name<input type="text" name="new_planet"></label>';
	}
	
	public static function setPlanets(&$cur,&$post_id)
	{
		$post_id = (integer) $post_id;
		$new_planet = '';
		if (isset($_POST['new_planet'])) {
			$new_planet = $_POST['new_planet'];
		}
		
		$galaxy = new dcGalaxy($GLOBALS['core']);

		if ($new_planet != '') {
			$galaxy->setPostPlanet($post_id, $new_planet);
		}
		elseif (isset($_POST['post_planets'])) {
			$planets = $_POST['post_planets'];
			
			$galaxy->delPostPlanet($post_id);

			foreach ($planets as $planet) {
				$galaxy->setPostPlanet($post_id,$planet);
			}
		}
	}
	
	public static function adminPostsActionsCombo(&$args)
	{
		$args[0][__('add to planets')] = 'planets';
		
		if ($GLOBALS['core']->auth->check('delete,contentadmin',$GLOBALS['core']->blog->id)) {
			$args[0][__('remove from planets')] = 'planets_remove';
		}
	}
	
	public static function adminPostsActions(&$core,$posts,$action,$redir)
	{
		if ($action == 'planets' && !empty($_POST['new_planets']))
		{
			try
			{
				$galaxy = new dcGalaxy($core);
				$planets = $_POST['new_planets'];
				
				while ($posts->fetch())
				{
					# Get planets for post
					$post_planet = $galaxy->getPlanets(null,null,$posts->post_id);
					$pm = array();
					while ($post_planet->fetch()) {
						$pm[] = $post_planet->planet_id;
					}
					
					foreach ($planets as $planet) {
						if (!in_array($planet,$pm)) {
							$galaxy->setPostPlanet($posts->post_id,$planet);
						}
					}
				}
				
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		elseif ($action == 'planets_remove' && !empty($_POST['planet_id']) && $core->auth->check('delete,contentadmin',$core->blog->id))
		{
			try
			{
				$galaxy = new dcGalaxy($core);
				while ($posts->fetch())
				{
					foreach ($_POST['planet_id'] as $v)
					{
						$galaxy->delPostPlanet($posts->post_id,$v);
					}
				}
				
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}

	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if ($action == 'planets')
		{
			$galaxy = new dcGalaxy($core);
			$planets = $galaxy->getPlanets();
			$pString = '';
			while($planets->fetch())
			{
				$pString .= '<option>' . $planets->planet_id . '</option>';
			}
			echo
			'<h2>'.__('Add entries to planets').'</h2>'.
			'<form action="posts_actions.php" method="post">'.
			'<p><label class="area">'.__('Planets to add:').' '.
			'<select name="new_planets[]" id="new_planets" multiple>' .$pString. '</select>'.
			'</label> '.
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'planets').
			'<input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
		}
		elseif ($action == 'planets_remove')
		{
			$galaxy = new dcGalaxy($core);
			$planets = array();
			
			foreach ($_POST['entries'] as $id) {
				$planet_posts = $galaxy->getPlanets(null,null,(integer) $id)->rows();
				foreach ($planet_posts as $v) {
					if (isset($planets[$v['planet_id']])) {
						$planets[$v['planet_id']]++;
					} else {
						$planets[$v['planet_id']] = 1;
					}
				}
			}
			
			echo '<h2>'.__('Remove selected planets from entries').'</h2>';
			
			if (empty($planets)) {
				echo '<p>'.__('No planets associated to selected entries').'</p>';
				return;
			}
			
			$posts_count = count($_POST['entries']);
			
			echo
			'<form action="posts_actions.php" method="post">'.
			'<fieldset><legend>'.__('Entries are associated to following planets:').'</legend>'.
			'<select name="planet_id[]" multiple>';
			
			foreach ($planets as $k => $n) {
				echo '<option>'.$k.'</option';
			}
			
			echo
			'</select>'.
			'<p><input type="submit" value="'.__('ok').'" /></p>'.
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'planets_remove').
			'</fieldset></form>';
		}
	}
	
	public static function exportFull(&$core,&$exp)
	{
		$exp->exportTable('galaxy');
	}
	
	public static function exportSingle(&$core,&$exp,$blog_id)
	{
		$exp->export('galaxy',
			'SELECT planet_id, M.post_id '.
			'FROM '.$core->prefix.'galaxy M, '.$core->prefix.'post P '.
			'WHERE P.post_id = M.post_id '.
			"AND P.blog_id = '".$blog_id."'"
		);
	}
	
	public static function importInit(&$bk,&$core)
	{
		$bk->cur_galaxy = $core->con->openCursor($core->prefix.'galaxy');
		$bk->galaxy = new dcGalaxy($core);
	}
	
	public static function importFull(&$line,&$bk,&$core)
	{
		if ($line->__name == 'galaxy')
		{
			$bk->cur_galaxy->clean();
			
			$bk->cur_galaxy->planet_id   = (string) $line->planet_id;
			$bk->cur_galaxy->post_id   = (integer) $line->post_id;

			$bk->cur_galaxy->insert();
		}
	}
	
	public static function importSingle(&$line,&$bk,&$core)
	{
		if ($line->__name == 'galaxy' && isset($bk->old_ids['post'][(integer) $line->post_id]))
		{
			$line->post_id = $bk->old_ids['post'][(integer) $line->post_id];
			$bk->galaxy->setPostPlanet($line->post_id,$line->planet_id);
		}
	}
	
}

