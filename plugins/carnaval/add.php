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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$comment_author = $comment_author_mail = $comment_author_site = $comment_class = 
$comment_text_color = $comment_background_color ='';

$can_write_images = carnavalConfig::canWriteImages();

# Add CSS Class
if (!empty($_POST['add_class']))
{
	$comment_author = $_POST['comment_author'];
	$comment_author_mail = $_POST['comment_author_mail'];
	$comment_author_site = $_POST['comment_author_site'];
	$comment_class = $_POST['comment_class'];
	$comment_text_color = carnavalConfig::adjustColor($_POST['comment_text_color']);
	$comment_background_color = carnavalConfig::adjustColor($_POST['comment_background_color']);
	
	try {
		dcCarnaval::addClass($comment_author,$comment_author_mail,$comment_author_site,$comment_text_color,$comment_background_color,$comment_class);
		if ($can_write_images || $comment_background_color )
		{
			carnavalConfig::createImages($comment_background_color,$comment_class);
		}
		http::redirect($p_url.'&addclass=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
?>
<html><head>
  <title>Carnaval</title>
  <?php echo dcPage::jsColorPicker(); ?>
</head><body>
<?php
require dirname(__FILE__).'/forms.php';
echo '<p><a class="back" href="'.$p_url.'">'.__('Return to Carnaval').'</a></p>';
echo '<form action="plugin.php" method="post">
	<fieldset class="two-cols"><legend>'.__('Add a new CSS Class').'</legend>
	'.$forms['form_fields'].'
	<p>'.form::hidden('add',1).
	form::hidden(array('p'),'carnaval').$core->formNonce().
	'<input type="submit" name="add_class" accesskey="a" value="'.__('Add').' (a)" tabindex="6" /></p>
	</fieldset>
	</form>';
?>
<?php dcPage::helpBlock('carnaval');?>
</body>
</html>
