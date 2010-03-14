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

$planet = !empty($_REQUEST['planet']) ? $_REQUEST['planet'] : '';

$this_url = $p_url.'&amp;m=planet_posts&amp;planet='.rawurlencode($planet);

$galaxy = new dcGalaxy($core);

$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$nb_per_page =  30;

# Rename a planet
if (!empty($_POST['new_planet_id']))
{
	$new_id = dcGalaxy::sanitizePlanetID($_POST['new_planet_id']);
	try {
		if ($galaxy->updatePlanet($planet,$new_id)) {
			http::redirect($p_url.'&m=planet_posts&planet='.$new_id.'&renamed=1');
		}
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Delete a planet
if (!empty($_POST['delete']) && $core->auth->check('publish,contentadmin',$core->blog->id))
{
	try {
		$galaxy->delPlanet($planet);
		http::redirect($p_url.'&m=planets&del=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$params = array();
$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;

$params['planet_id'] = $planet;
$params['post_type'] = '';

# Get posts
try {
	$posts = $galaxy->getPostsByPlanet($params);
	$counter = $galaxy->getPostsByPlanet($params,true);
	$post_list = new adminPostList($core,$posts,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Actions combo box
$combo_action = array();
if ($core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('publish')] = 'publish';
	$combo_action[__('unpublish')] = 'unpublish';
	$combo_action[__('schedule')] = 'schedule';
	$combo_action[__('mark as pending')] = 'pending';
}
$combo_action[__('mark as selected')] = 'selected';
$combo_action[__('mark as unselected')] = 'unselected';
$combo_action[__('change category')] = 'category';
if ($core->auth->check('delete,contentadmin',$core->blog->id)) {
	$combo_action[__('delete')] = 'delete';
}
$combo_action[__('add to planets')] = 'planets';
if ($core->auth->check('delete,contentadmin',$core->blog->id)) {
	$combo_action[__('remove from planets')] = 'planets_remove';
}

?>
<html>
<head>
  <title>Planets</title>
  <link rel="stylesheet" type="text/css" href="index.php?pf=galaxy/style.css" />
  <script type="text/javascript">
  //<![CDATA[
  dotclear.msg.confirm_planet_delete = '<?php echo html::escapeJS(sprintf(__('Are you sure you want to remove this %s?'),'planet')) ?>';
  $(function() {
    $('#planet_delete').submit(function() {
      return window.confirm(dotclear.msg.confirm_planet_delete);
    });
  });
  //]]>
  </script>
</head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &rsaquo;
<?php echo __('Edit posts to planets'); ?></h2>

<?php
if (!empty($_GET['renamed'])) {
	echo '<p class="message">'.__('Planet has been successfully renamed').'</p>';
}

echo '<p><a href="'.$p_url.'&amp;m=planets">'.__('Back to planets list').'</a></p>';

if (!$core->error->flag())
{
	if (!$posts->isEmpty())
	{
		echo
		'<form action="'.$this_url.'" method="post">'.
		'<p><label class="classic">'.__('Rename this planet:').' '.
		form::field('new_planet_id',20,255,html::escapeHTML($planet)).
		'</label> <input type="submit" value="'.__('save').'" />'.
		$core->formNonce().'</p>'.
		'</form>';
	}
	
	# Show posts
	$post_list->display($page,$nb_per_page,
	'<form action="posts_actions.php" method="post" id="form-entries">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.__('Selected entries action:').' '.
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden('post_type','').
	form::hidden('redir',$p_url.'&amp;m=planet_posts&amp;planet='.
		str_replace('%','%%',rawurlencode($planet)).'&amp;page='.$page).
	$core->formNonce().
	'</div>'.
	'</form>');
	
	# Remove planet
	if (!$posts->isEmpty() && $core->auth->check('contentadmin',$core->blog->id)) {
		echo
		'<form id="planet_delete" action="'.$this_url.'" method="post">'.
		'<p><input type="submit" name="delete" value="'.__('Delete this planet').'" />'.
		$core->formNonce().'</p>'.
		'</form>';
	}
}
?>
</body>
</html>
