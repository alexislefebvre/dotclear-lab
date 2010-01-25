<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Blog this!, a plugin for Dotclear 2
# Copyright (C) 2007,2009,2010 Moe (http://gniark.net/)
#
# Blog this! is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Blog this! is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/admin');

$s = $core->blog->settings;

?>

<html>
<head>
	<title><?php echo('Blog this!'); ?></title>
</head>
<body>

	<h2><?php echo(html::escapeHTML($core->blog->name)); ?> &gt; <?php echo('Blog this!'); ?></h2>
	<?php if (!empty($_GET['dcb'])) { ?>
		<form method="post" action="post.php">
			<fieldset>
				<legend><?php echo(__('New entry')); ?></legend>
				<?php 
					$title = (!empty($_GET['ptitle'])) ? html::escapeHTML(utf8_encode($_GET['ptitle'])) : __('link');
					$text = (!empty($_GET['ptext'])) ? html::escapeHTML(utf8_encode($_GET['ptext'])) : '';
					if ($core->auth->getOption('post_format') == 'wiki')
					{
						$content = '['.$title.'|'.html::escapeHTML($_GET['purl']).']';
						$content .= (!empty($text)) ? "\n\n".$text : '';
					}
					else
					{
						$content = '<p><a href="'.html::escapeHTML($_GET['purl']).'">'.$title.'</a></p>'."\n\n";
						$content .= (!empty($text)) ? "\n\n".'<p>'.$text.'</p>' : '';
					}
					# from /dotclear/admin/post.php
					echo(
					'<p class="col"><label class="required" for="post_title">'.__('Title:').
					form::field('post_title',20,255,$title,'maximal').
					'</label></p>'.
					form::hidden('post_excerpt','').
					'<p class="area"><label class="required" for="post_content">'.__('Content:').
					form::textarea('post_content',80,5,$content,'maximal').'</p>'.
					'</label></p>'.
					
					form::hidden('cat_id','').
					form::hidden('post_format',
						html::escapeHTML($core->auth->getOption('post_format'))).
					form::hidden('post_open_comment',(integer) $s->allow_comments).
					form::hidden('post_open_tb',(integer) $s->allow_trackbacks).
					form::hidden('post_lang',
						html::escapeHTML($core->auth->getInfo('user_lang'))).
						
					form::hidden('post_notes','').
					'</fieldset>'.
					'<input type="submit" id="submit" value="'.__('send').'" /></p>'."\n".
					'<p>'.$core->formNonce().'</p>');
				?>
				<script type="text/javascript">
					document.getElementById('submit').click();
				</script>
		</form>
	<?php } ?>
	<h3><?php echo(__('Bookmarklet')); ?></h3>
	<p><?php echo(__('You can add the following boomarklet to your bookmarks.'));
		echo(' ');
		printf(__('When you will click on %s it will open up a popup window with the text you selected and a link to the site you\'re currently browsing to create a post about it.'),
			'<strong>'.__('Blog this!').'</strong>'); ?></p>
	<p><?php echo(__('Javascript must be activated in your browser.'));?></p>
	<p><a href="javascript:if(navigator.userAgent.indexOf('Safari') >= 0){Q=getSelection();}else{Q=document.selection?document.selection.createRange().text:document.getSelection();}void(window.open('<?php echo(http::getSelfURI()); ?>&amp;dcb=1'+'&amp;ptext='+escape(Q)+'&amp;purl='+escape(location.href)+'&amp;ptitle='+escape(document.title),'DotClear bookmarklet','resizable=yes,scrollbars=yes,width=700,height=460,left=100,top=150,status=yes'));"><?php echo(__('Blog this!').' - '.html::escapeHTML($core->blog->name)); ?></a></p>

</body>
</html>
