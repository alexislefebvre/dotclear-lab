<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Carnaval', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Osku and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Carnaval' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$id = $_REQUEST['id'];

try {
	$rs = dcCarnaval::getClass($id);
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

if ($core->error->flag() || $rs->isEmpty()) {
	$core->error->add(__('No such Class'));
} else {
	$comment_author = $rs->comment_author;
	$comment_author_mail = $rs->comment_author_mail;
	$comment_author_site = $rs->comment_author_site;
	$comment_class = $rs->comment_class;
}

# Update a link
if (isset($rs) && !empty($_POST['edit_class']))
{
	$comment_author = $_POST['comment_author'];
	$comment_author_mail = $_POST['comment_author_mail'];
	$comment_author_site = $_POST['comment_author_site'];
	$comment_class = $_POST['comment_class'];
	
	try {
		dcCarnaval::updateClass($id,$comment_author,$comment_author_mail,$comment_author_site,$comment_class);
		http::redirect($p_url.'&edit=1&id='.$id.'&upd=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

?>
<html><head>
  <title>Carnaval</title>
</head><body>
<?php
require dirname(__FILE__).'/forms.php';
echo '<p><a href="'.$p_url.'">'.__('Return to Carnaval').'</a></p>';

if (isset($rs))
{
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('CSS Class has been successfully updated').'</p>';
	}
	
	echo
	'<form action="plugin.php" method="post">'.
	'<fieldset class="two-cols"><legend>'.__('Edit Class').'</legend>'.
	$forms['form_fields'].
	'<p>'.form::hidden('edit',1).form::hidden('id',$id).
	form::hidden('p','carnaval').$core->formNonce().
	'<input type="submit" name="edit_class" class="submit" value="'.__('save').'"/></p>'.
	'</fieldset>'.
	'</form>';
}
?>
</body></html>
