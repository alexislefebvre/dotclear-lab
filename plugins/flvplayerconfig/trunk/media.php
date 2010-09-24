<?php if (!defined('DC_CONTEXT_ADMIN')) {return;}
/**
 * @author kévin lepeltier [lipki] (kevin@lepeltier.info)
 * @license http://creativecommons.org/licenses/by-sa/3.0/deed.fr
 */

/* HTML page
-------------------------------------------------------- */
require_once dirname(__FILE__).'/../../inc/admin/prepend.php';

dcPage::check('media,media_admin');

$post_id = !empty($_GET['post_id']) ? (integer) $_GET['post_id'] : null;
if ($post_id) {
	$post = $core->blog->getPosts(array('post_id'=>$post_id,'post_type'=>''));
	if ($post->isEmpty()) {
		$post_id = null;
	}
	$post_title = $post->post_title;
	$post_type = $post->post_type;
	unset($post);
}

$d = isset($_REQUEST['d']) ? $_REQUEST['d'] : null;
$dir = null;

$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$nb_per_page =  30;

# We are on home not comming from media manager
if ($d === null && isset($_SESSION['media_manager_dir'])) {
	# We get session information
	$d = $_SESSION['media_manager_dir'];
}

if (!isset($_GET['page']) && isset($_SESSION['media_manager_page'])) {
	$page = $_SESSION['media_manager_page'];
}

# We set session information about directory and page
if ($d) {
	$_SESSION['media_manager_dir'] = $d;
} else {
	unset($_SESSION['media_manager_dir']);
}
if ($page != 1) {
	$_SESSION['media_manager_page'] = $page;
} else {
	unset($_SESSION['media_manager_page']);
}

$page_url = 'plugin.php?p=flvplayerconfig&media=1&popup=1&post_id='.$post_id;

$core_media_writable = false;
try {
	$core->media = new dcMedia($core, 'video');
	$core->media->chdir($d);
	$core->media->getDir();
	$core_media_writable = $core->media->writable();
	$dir =& $core->media->dir;
	if  (!$core_media_writable)
		throw new Exception('you do not have sufficient permissions to write to this folder: ');
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

/* DISPLAY Main page
-------------------------------------------------------- */

echo
"</head>\n".
'<body id="dotclear-admin" class="popup">'."\n";

if ($core->error->flag()) {
	echo
	'<div class="error"><strong>'.__('Errors:').'</strong>'.
	$core->error->toHTML().
	'</div>';
}

echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; <a href="'.html::escapeURL($page_url.'&d=').'">'.__('Media manager').'</a>'.
' / '.(isset($core->media) ? $core->media->breadCrumb(html::escapeURL($page_url).'&amp;d=%s') : '').'</h2>';

if (!$dir) {
	call_user_func($close_f);
	exit;
}

if ($post_id) {
	echo '<p><strong>'.sprintf(__('Choose a file to attach to entry %s by clicking on %s.'),
	'<a href="'.$core->getPostAdminURL($post_type,$post_id).'">'.html::escapeHTML($post_title).'</a>',
	'<img src="images/plus.png" alt="'.__('Attach this file to entry').'" />').'</strong></p>';
}
if ($popup) {
	echo '<p><strong>'.sprintf(__('Choose a file to insert into entry by clicking on %s.'),
	'<img src="images/plus.png" alt="'.__('Attach this file to entry').'" />').'</strong></p>';
}


$items = array_values(array_merge($dir['dirs'],$dir['files']));
if (count($items) == 0) {
	echo '<p><strong>'.__('No file.').'</strong></p>';
} else {
	$pager = new pager($page,count($items),$nb_per_page,10);
	
	echo
	'<div class="media-list">'.
	'<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
	
	for ($i=$pager->index_start, $j=0; $i<=$pager->index_end; $i++, $j++)
		echo mediaItemLine($items[$i],$j);
	
	echo
	'<p class="clear">'.__('Page(s)').' : '.$pager->getLinks().'</p>'.
	'</div>';
}


# Empty remove form (for javascript actions)
echo
'<form id="media-remove-hide" action="'.html::escapeURL($page_url).'" method="post"><div class="clear">'.
form::hidden('rmyes',1).form::hidden('d',html::escapeHTML($d)).
form::hidden('remove','').
$core->formNonce().
'</div></form>';



echo '</body></html>';

/* ----------------------------------------------------- */
function mediaItemLine($f,$i)
{
	global $core, $page_url, $popup, $post_id;
	
	$fname = $f->basename;
	
	if ($f->d) {
		$link = html::escapeURL($page_url).'&amp;d='.html::sanitizeURL($f->relname);
		if ($f->parent) {
			$fname = '..';
		}
	} else {
		$link =
		'plugin.php?p=flvplayerconfig&id='.$f->media_id.'&amp;popup=1&amp;post_id='.$post_id;
	}
	
	$class = 'media-item media-col-'.($i%2);
	
	$res =
	'<div class="'.$class.'"><a class="media-icon media-link" href="'.$link.'">'.
	'<img src="'.$f->media_icon.'" alt="" /></a>'.
	'<ul>'.
	'<li><a class="media-link" href="'.$link.'">'.$fname.'</a></li>';
	
	if (!$f->d) {
		$res .=
		'<li>'.$f->media_title.'</li>'.
		'<li>'.
		$f->media_dtstr.' - '.
		files::size($f->size).' - '.
		'<a href="'.$f->file_url.'">'.__('open').'</a>'.
		'</li>';
	}
	
	$res .= '<li class="media-action">&nbsp;';
	
	if ($post_id && !$f->d) {
		$res .= '<form action="post_media.php" method="post">'.
		'<input type="image" src="images/plus.png" alt="'.__('Attach this file to entry').'" '.
		'title="'.__('Attach this file to entry').'" /> '.
		form::hidden('media_id',$f->media_id).
		form::hidden('post_id',$post_id).
		form::hidden('attach',1).
		$core->formNonce().
		'</form>';
	}
	
	if ($popup && !$f->d) {
		$res .= '<a href="'.$link.'"><img src="images/plus.png" alt="'.__('Insert this file into entry').'" '.
		'title="'.__('Insert this file into entry').'" /></a> ';
	}
	
	$res .= '</li>';
	
	$res .= '</ul></div>';
	
	return $res;
}
/**/