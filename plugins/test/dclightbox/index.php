<?php
# ***** BEGIN LICENSE BLOCK *****
# Widget Tribune Libre for DotClear.
# Copyright (c) 2006 Antoine Libert. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

require dirname(__FILE__).'/class.dclb.wizard.php';

try 
{
	$dclb = new dclbWizard($core);
	if (!empty($_POST['add_to_admin']) && $core->auth->isSuperAdmin())
	$dclb->copyFiles();
	if (!empty($_POST['del_from_admin']) && $core->auth->isSuperAdmin())
	$dclb->deleteFiles();
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

if( ( $dclb_dir = basename(dirname(__FILE__)) ) != 'dclightbox')
$core->error->add(__('dcLightbox\'s directory name is').' "'.$dclb_dir.'" '.__('it MUST be "dclightbox". Please rename it.'));

$header_file = $dclb->checkHeaderFile();
$admin_checkdir = $dclb->checkWritableDir();
$admin_checkfiles = $dclb->checkInstalledFiles();
?>
<html>
<head>
  <title>dcLightbox</title>

<style rel="text/css">
	
	#content h3{margin-left:1em;margin-top:0.5em;}
	#content h4{margin-left:2em;}
	#content h5{margin-left:3em;}
	#content h6{margin-left:4em;}
	#content p, #content pre {margin-left:4em;}
	#content ul{padding-left:8em; list-style-type:none;}
	#content ul.part-tabs{padding-left:0;}
	
	#content pre {
		border:1px solid #888;
		padding:5px;
		background-color:#ccc;
		}
	#content pre:focus, #content pre:active {border:1px solid #222;}
	
	
	#content p.writable{padding-left:16px; background:url(images/check-on.png) no-repeat left center;}
	#content p.unwritable{padding-left:16px; background:url(images/check-off.png) no-repeat left center;}
	#content li.installed {padding-left:16px; background:url(images/check-on.png) no-repeat left center;}
	#content li.uninstalled {padding-left:16px; background:url(images/check-off.png) no-repeat left center;}
	
</style>

<script type="text/javascript">
//<![CDATA[
<?php echo dcPage::jsVar('dotclear.msg.confirm_delete_files',__('Are you sure you want to delete these files?')); ?>
$(function() {
	$('#deladmin').submit(function() {
		return window.confirm(dotclear.msg.confirm_delete_files);
	});
});
//]]>
</script>
<?php echo dcPage::jsPageTabs($part); ?>

</head>
  
<body>
<?php
echo
'<h2>dcLightbox</h2>'.
'<p><a href="http://www.huddletogether.com/projects/lightbox2/">Lightbox</a> '.__('embedded in').' DotClear 2<p>'.


'<div id="wizard" class="multi-part" title="'.__('Wizard').'">'.

'<h3>'.__('Wizard').'</h3>'.

'<h4>'.__('Add to theme').'</h4>'.

'<h5>'.__('Setup').'</h5>'.
'<p>'.__('Copy/Paste the following code at the end of the <strong>_head.html</strong> of your theme').'<br /><code>'.$header_file.'</code></p>'.
'<pre class="code">{{tpl:include src="_dclightbox.html"}}</pre>'.

'<h5>'.__('Remove').'</h5>'.
'<p>'.__('Delete the following code from the <strong>_head.html</strong> of your theme').'<br /><code>'.$header_file.'</code></p>'.
'<pre class="code">{{tpl:include src="_dclightbox.html"}}</pre>'.

'<h5>'.__('Warning').'</h5>'.
'<p>'.__('The name of this plugin\'s directory MUST be "dclightbox".').'<p>'.
'<p>'.__('Since the templates are temporary saved in cache, modifications of these files may not appear right now. To clear the cache, go to').' "<a href="'.DC_ADMIN_URL.'/blog_theme.php">'.__('Theme settings').'</a>" '.__('and click on the "save" button.').'<p>'.

'<h4>'.__('Add to toolbar').'</h4>'.

'<h5>'.__('Files status').'</h5>'.
$admin_checkdir.
$admin_checkfiles.

'<h5>'.__('Setup').'</h5>'.
'<form action="'.$p_url.'" method="post">'.
'<p><input type="submit" id="add_to_admin" name="add_to_admin" value="'.__('Copy files').'" /></p>'.
'</form>'.

'<h5>'.__('Remove').'</h5>'.

'<form action="'.$p_url.'" method="post" id="deladmin">'.
'<p><input type="submit" id="del_from_admin" name="del_from_admin" value="'.__('Delete files').'" /></p>'.
'</form>'.

'</div>'.


'<div id="guide" class="multi-part" title="'.__('Guide').'">'.

'<h3>'.__('Guide').'</h3>'.


'<h4>'.__('Lightbox').'</h4>'.

'<h5>'.__('Simple mode').'</h5>'.
'<p>'.__('Simple Lightbox effect on an image.').'</p>'.

'<h5>'.__('Album mode').'</h5>'.
'<p>'.__('Group related images under a "Groupe name", thus you\'ll be able to navigate through them with ease.').'</p>'.

'<h5>'.__('More information').'</h5>'.
'<p>'.__('Please go to:').' <a href="http://www.huddletogether.com/projects/lightbox2/">www.huddletogether.com</a>.</p>'.


'<h4>'.__('Toolbar').'</h4>'.

'<p>'.__('dcLightbox modifies the behavior of existing buttons, it does NOT add some mysterious buttons.').'</p>'.
'<p>'.__('More precisely, it slightly modifies the interface displayed in the popup when you click on the "Link" or "Image chooser" buttons to add a few options.').'</p>'.

'<h5>'.__('Link with Lightbox effect').'</h5>'.

'<ol>'.
'<li>'.__('click on the "Link" button and fill the required fields.').'</li>'.
'<li>'.__('in the "Lightbox effect" area, select "yes".').'</li>'.
'<li>'.__('fill the "Groupe name" field if you want to use the "Album mode", else leave blank.').'</li>'.
'</ol>'.

'<h5>'.__('Image chooser with Lightbox effect').'</h5>'.

'<ol>'.
'<li>'.__('click on the "Image chooser" button and select your image.').'</li>'.
'<li>'.__('at the end of the "Insert image" panel, in the "Lightbox effect" area, select "As a link to original image with Lightbox effect".').'</li>'.
'<li>'.__('fill the "Groupe name" field if you want to use the "Album mode", else leave blank.').'</li>'.
'</ol>'.

'<h4>'.__('Syntaxe').'</h4>'.

'<h5>'.__('Wiki').'</h5>'.

'<h6>'.__('Simple mode').'</h6>'.
'<p>'.__('Add <code>lbox:</code> before the picture\'s url.').'<br />'.

__('Example:').' </p>'.
'<pre class="code">[((public/thumb_my_picture.jpg))|lbox:public/my_picture.jpg|hreflang|title]</pre>'.

'<h6>'.__('Album mode').'</h6>'.
'<p>'.__('Add <code>lbox:groupename:</code> before the picture\'s url.').'<br />'.

__('Example:').' </p>'.
'<pre class="code">[((public/thumb_my_picture.jpg))|lbox:groupename:public/my_picture.jpg|hreflang|title]</pre>'.

'<h5>'.__('XHTML').'</h5>'.

'<h6>'.__('Simple mode').'</h6>'.

'<p>'.__('Add the <code>rel="lightbox"</code> attribute to your picture\'s links.').'<br />'.
__('Example:').' </p>'.
'<pre class="code">'.
html::escapeHTML('
<a href="public/my_picture.jpg" rel="lightbox" title="title">
	<img src="public/thumb_my_picture.jpg" alt="alternative text" />
</a>
').
'</pre>'.

'<h6>'.__('Album mode').'</h6>'.
'<p>'.__('Add the <code>rel="lightbox[groupename]"</code> attribute to your picture\'s links.').'<br />'.
__('Example:').' </p>'.
'<pre class="code">'.
html::escapeHTML('
<a href="public/my_picture1.jpg" rel="lightbox[groupename]" title="title">
	<img src="public/thumb_my_picture1.jpg" alt="alternative text" />
</a>
<a href="public/my_picture2.jpg" rel="lightbox[groupename]" title="title">
	<img src="public/thumb_mon_image2.jpg" alt="alternative text" />
</a>
<a href="public/my_picture3.jpg" rel="lightbox[groupename]" title="title">
	<img src="public/thumb_mon_image3.jpg" alt="alternative text" />
</a>
').
'</pre>'.

'<h6>'.__('Note').'</h6>'.
'<p>'.__('Use the "title" attribute of the link if you want to show a caption.').'</p>'.
'<p>'.__('You should use alphanumeric characters for the "Groupe name".').'</p>'.

'</div>'.


'<div id="about" class="multi-part" title="'.__('About').'">'.

'<h3>'.__('About').'</h3>'.

'<p>'.__('More information on Lightbox:').' <a href="http://www.huddletogether.com/projects/lightbox2/">www.huddletogether.com</a></p>'.
'<p>'.__('Made by:').' <a href="http://monoceros01.free.fr">Yann Granjon (monoceros01)</a></p>'.
'<p>'.__('Contributors:').' </p>'.
'<ul>'.
'<li><a href="http://web.saymonz.net/">[SiMON]</a></li>'.
'<li><a href="http://www.elaboration.be/">Olivier</a> (another one)</li>'.
'<li><a href="http://sauzade.info/">Adrian Sauzade</a></li>'.
'</ul>'.

'</div>';
?>
</body>
</html>