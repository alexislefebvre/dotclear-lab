<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009-2011 Benoit de Marne.
# benoit.de.marne@gmail.com
# Many thanks to Association Dotclear and special thanks to Olivier Le Bris
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class newsletterLettersList extends adminGenericList
{
	/**
	 * Count letters
	 */	
	private static function countLetters($state = '1')
	{
		global $core;
		
		$params['post_type'] = 'newsletter';
		$params['post_status'] = $state;
		$params['no_content'] = true;

		$counter = $core->blog->getPosts($params,true);
		return $counter->f(0);
	}
		
	public static function fieldsetResumeLetters()
	{
		$state_combo = array(
					__('pending') => '-2',
					__('scheduled') => '-1',
					__('unpublish') => '0',
					__('publish') => '1'
				);			

		$resume_content =
				'<fieldset>'.
				'<legend>'.__('Statistics post type newsletter').'</legend>'.
				'<table summary="resume_letters" class="minimal">'.
				'<thead>'.
					'<tr>'.
			  			'<th>'.__('State').'</th>'.
			  			'<th>'.__('Count').'</th>'.
					'</tr>'.
				'</thead>'.
				'<tbody id="classes-list">';

				foreach($state_combo as $k=>$v) {
					$resume_content .= 
						'<tr class="line">'.
						'<td>'.$k.'</td>'.
						'<td>'.self::countLetters($v).'</td>'.
						'</tr>'.
						'';
				}

		$resume_content .= 
				'</tbody>'.
				'</table>'.
				'</fieldset>'.
				'';
		
		return $resume_content;
	}	
	
	/**
	 * Display list of newsletters
	 *
	 * @param	int		page
	 * @param	int		nb_per_page
	 * @param	string	url
	 */
	private function display($page,$nb_per_page,$enclose_block='')
	{
		global $core;
		
		if ($this->rs->isEmpty()) {
			echo '<p><strong>'.__('No letters for this blog.').'</strong></p>';
		} else {
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';

			$html_block =
			'<table class="clear"><tr>'.
			'<th colspan="2">'.__('Title').'</th>'.
			'<th>'.__('Mailing date').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th>'.__('Comments').'</th>'.
			'<th>'.__('Trackbacks').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch()) {
				echo $this->letterLine();
			}
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}

	/**
	 * Display a line
	 */	
	private function letterLine()
	{
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->post_status) {
			case 1:
				$img_status = sprintf($img,__('published'),'check-on.png');
				break;
			case 0:
				$img_status = sprintf($img,__('unpublished'),'check-off.png');
				break;
			case -1:
				$img_status = sprintf($img,__('scheduled'),'scheduled.png');
				break;
			case -2:
				$img_status = sprintf($img,__('pending'),'check-wrn.png');
				break;
		}		

		$protected = '';
		if ($this->rs->post_password) {
			$protected = sprintf($img,__('protected'),'locker.png');
		}
		
		$selected = '';
		if ($this->rs->post_selected) {
			$selected = sprintf($img,__('selected'),'selected.png');
		}

		$attach = '';
		$nb_media = $this->rs->countMedia();
		if ($nb_media > 0) {
			$attach_str = $nb_media == 1 ? __('%d attachment') : __('%d attachments');
			$attach = sprintf($img,sprintf($attach_str,$nb_media),'attach.png');
		}
		
		$res = '<tr class="line'.($this->rs->post_status != 1 ? ' offline' : '').'"'.
		' id="p'.$this->rs->post_id.'">';
		
		$res .=
		'<td class="nowrap">'.
		form::checkbox(array('letters_id[]'),$this->rs->post_id,'','','',!$this->rs->isEditable()).'</td>'.
		'<td class="maximal"><a href="'.$this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id).'">'.
		html::escapeHTML($this->rs->post_title).'</a></td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt).'</td>'.
		
		'<td class="nowrap">'.$this->rs->user_id.'</td>'.
		'<td class="nowrap">'.$this->rs->nb_comment.'</td>'.
		'<td class="nowrap">'.$this->rs->nb_trackback.'</td>'.
		'<td class="nowrap status">'.$img_status.' '.$selected.' '.$protected.' '.$attach.'</td>'.
		'</tr>';
		
		return $res;
	}

	/**
	* Onglet de la liste des newsletters du blog
	*/
	public static function displayTabLettersList()
	{
		global $core;
		
		try {

			$newsletter_settings = new newsletterSettings($core);

			# Creating filter combo boxes
			$sortby_combo = array(
				__('Mailing date') => 'post_dt',
				__('Title') => 'post_title',
				__('Author') => 'user_id',
				__('Status') => 'post_status'
			);
		
			$order_combo = array(
				__('Descending') => 'desc',
				__('Ascending') => 'asc'
			);

			# Actions combo box
			$combo_action = array();
			
			if ($core->auth->check('publish,contentadmin',$core->blog->id))
			{
				
				$combo_action[__('Newsletter')]=array(
					__('Send') => 'send'
				);
			
				$combo_action[__('Changing state')] = array(
					__('publish') => 'publish',
					__('unpublish') => 'unpublish',
					__('mark as pending') => 'pending',
					__('delete') => 'delete'
				);

				if ($core->auth->check('admin',$core->blog->id)) {
					$combo_action[__('Changing state')][__('change author')]='author';
				}
			}			

			$params = array(
				'post_type' => 'newsletter'
			);

			$show_filters = false;

			$nb = !empty($_GET['nb']) ? trim($_GET['nb']) : 0;
			$sortby = !empty($_GET['sortby']) ? $_GET['sortby'] : 'post_dt';
			$order = !empty($_GET['order']) ? $_GET['order'] : 'desc';
			$page = !empty($_GET['page']) ? $_GET['page'] : 1;
			$nb_per_page =  30;
		
			if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
				$nb_per_page = $_GET['nb'];
			}
			
			if ((integer) $nb > 0) {
				if ($nb_per_page != $nb) {
					$show_filters = true;
				}
				$nb_per_page = (integer) $nb;
			}
			
			# - Sortby and order filter
			if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
				if ($order !== '' && in_array($order,$order_combo)) {
					$params['order'] = $sortby.' '.$order;
					$show_filters = true;
				}
			}

			$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
			$params['no_content'] = true;

			// Request the letters list
			$rs = $core->blog->getPosts($params);
			$counter = $core->blog->getPosts($params,true);
			$letters_list = new newsletterLettersList($core,$rs,$counter->f(0));

			if (!$core->error->flag())
			{
				//echo '<p><a class="button" href="plugin.php?p=newsletter&amp;m=letter">'.__('New newsletter').'</a></p>';
				echo '<p class="top-add"><a class="button add" href="plugin.php?p=newsletter&amp;m=letter">'.__('New newsletter').'</a></p>';
				
				echo '<p><a id="filter-control" class="form-control" href="#">'.__('Filters').'</a></p>';
				
				echo
				'<form action="plugin.php" method="get" id="filters-form">'.
				'<fieldset><legend>'.__('Filters').'</legend>'.
				
				'<div class="three-cols">'.
				'<div class="col">'.
				'<p><label>'.__('Order by:').' '.
				form::combo('sortby',$sortby_combo,html::escapeHTML($sortby)).
				'</label> '.
				'<label>'.__('Sort:').' '.
				form::combo('order',$order_combo,html::escapeHTML($order)).
				'</label></p>'.
				'</div>'.
				
				'<div class="col">'.
				'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
				__('Letters per page').'</label> '.
				'<p>'.
				'<input type="hidden" name="p" value="'.newsletterPlugin::pname().'" />'.
				'<input type="hidden" name="m" value="letters" />'.
				'<input type="submit" value="'.__('Apply filters').'" /></p>'.
				'</div>'.
				
				'</div>'.
				'<br class="clear" />'. //Opera sucks
				'</fieldset>'.
				'</form>';

			}

			// Show letters
			$letters_list->display($page,$nb_per_page,
				'<form action="plugin.php?p=newsletter&amp;m=letters" method="post" id="letters_list">'.
				'<p>' .
	
				'%s'.
			
				'<div class="two-cols">'.
				'<p class="col checkboxes-helpers"></p>'.
				'<p class="col right">'.__('Selected letters action:').
				form::combo('action',$combo_action).
				form::hidden(array('m'),'letters').
				form::hidden(array('p'),newsletterPlugin::pname()).
				form::hidden(array('sortby'),$sortby).
				form::hidden(array('order'),$order).
				form::hidden(array('page'),$page).
				form::hidden(array('nb'),$nb_per_page).
				form::hidden(array('post_type'),'newsletter').
				form::hidden(array('redir'),html::escapeHTML($_SERVER['REQUEST_URI'])).
				$core->formNonce().
				'<input type="submit" value="'.__('ok').'" />'.
				'</p>'.
				'</div>'.	
				'</form>'
			);
				
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}


	/**
	 * --
	 *
	 */
	public static function lettersActions(array $letters_id)
	{
		global $core;
		
		$params = array();

		/* Get posts
		-------------------------------------------------------- */
		$user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : '';
		$cat_id = !empty($_POST['cat_id']) ? $_POST['cat_id'] : '';
		$status = isset($_POST['status']) ?	$_POST['status'] : '';
		$selected = isset($_POST['selected']) ?	$_POST['selected'] : '';
		$month = !empty($_POST['month']) ? $_POST['month'] : '';
		$lang = !empty($_POST['lang']) ? $_POST['lang'] : '';
		$sortby = !empty($_POST['sortby']) ? $_POST['sortby'] : '';
		$order = !empty($_POST['order']) ? $_POST['order'] : '';
		$page = !empty($_POST['page']) ? (integer) $_POST['page'] : 1;
		$nb = !empty($_POST['nb']) ? trim($_POST['nb']) : 0;
		
		/* Actions
		-------------------------------------------------------- */
		if (!empty($_POST['action']) && !empty($letters_id)) {
		
			$action = $_POST['action'];
			
			if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false) {
				$redir = $_POST['redir'];
			} else {
				$redir =
				'posts.php?user_id='.$user_id.
				'&cat_id='.$cat_id.
				'&status='.$status.
				'&selected='.$selected.
				'&month='.$month.
				'&lang='.$lang.
				'&sortby='.$sortby.
				'&order='.$order.
				'&page='.$page.
				'&nb='.$nb;
			}
			
			foreach ($letters_id as $k => $v) {
				$letters_id[$k] = (integer) $v;
			}
			
			$params['sql'] = 'AND P.post_id IN('.implode(',',$letters_id).') ';
			$params['no_content'] = true;
			
			if (isset($_POST['post_type'])) {
				$params['post_type'] = $_POST['post_type'];
			}
			
			$posts = $core->blog->getPosts($params);
			
			# --BEHAVIOR-- adminLettersActions
			$core->callBehavior('adminLettersActions',$core,$posts,$action,$redir);
			
			if (preg_match('/^(publish|unpublish|schedule|pending)$/',$action)) {
				switch ($action) {
					case 'unpublish' : $status = 0; break;
					case 'schedule' : $status = -1; break;
					case 'pending' : $status = -2; break;
					default : $status = 1; break;
				}
				
				try {
					while ($posts->fetch()) {
						$core->blog->updPostStatus($posts->post_id,$status);
					}
					http::redirect($redir);

				} catch (Exception $e) {
					$core->error->add($e->getMessage());
				}
			} elseif ($action == 'selected' || $action == 'unselected') {
				try {
					while ($posts->fetch()) {
						$core->blog->updPostSelected($posts->post_id,$action == 'selected');
					}
					http::redirect($redir);

				} catch (Exception $e) {
					$core->error->add($e->getMessage());
				}
			} elseif ($action == 'delete') {
				try	{
					while ($posts->fetch()) {
						# --BEHAVIOR-- adminBeforePostDelete
						$core->callBehavior('adminBeforeLetterDelete',$posts->post_id);				
						$core->blog->delPost($posts->post_id);
					}
					http::redirect($redir);
				
				} catch (Exception $e) {
					$core->error->add($e->getMessage());
				}
			} elseif ($action == 'category' && isset($_POST['new_cat_id'])) {
				try {
					while ($posts->fetch()) {
						$new_cat_id = (integer) $_POST['new_cat_id'];
						$core->blog->updPostCategory($posts->post_id,$new_cat_id);
					}
					http::redirect($redir);
				
				} catch (Exception $e) {
					$core->error->add($e->getMessage());
				}
			} elseif ($action == 'author' && isset($_POST['new_auth_id']) 
			&& $core->auth->check('admin',$core->blog->id))
			{
				$new_user_id = $_POST['new_auth_id'];
				
				try {
					if ($core->getUser($new_user_id)->isEmpty()) {
						throw new Exception(__('This user does not exist'));
					}
					
					while ($posts->fetch()) {
						$cur = $core->con->openCursor($core->prefix.'post');
						$cur->user_id = $new_user_id;
						$cur->update('WHERE post_id = '.(integer) $posts->post_id);
					}
					http::redirect($redir);
				} catch (Exception $e) {
					$core->error->add($e->getMessage());
				}
			} elseif (($action == 'send' || $action == 'send_old') 
						&& $core->auth->check('admin',$core->blog->id)) {
				echo '<fieldset>';
				echo '<legend>'.__('Send letters').'</legend>';
				echo '<p><input type="button" id="cancel" value="'.__('cancel').'" /></p>';
				echo '<h3>'.__('Requests').'</h3>';
				echo '<table id="request"><tr class="keepme"><th>ID</th><th>Action</th><th>Status</th></tr></table>';
				echo '<h3>'.__('Actions').'</h3>';
				echo '<table id="process"><tr class="keepme"><th>ID</th><th>Action</th><th>Status</th></tr></table>';
				echo '</fieldset>';

				echo '<p><a class="back" href="'.html::escapeURL($redir).'">'.__('back').'</a></p>';
			}
		}
			
		$hidden_fields = '';
		while ($posts->fetch()) {
			$hidden_fields .= form::hidden(array('letters_id[]'),$posts->post_id);
		}
		
		if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false) {
			$hidden_fields .= form::hidden(array('redir'),html::escapeURL($_POST['redir']));
		} else {
			$hidden_fields .=
			form::hidden(array('user_id'),$user_id).
			form::hidden(array('cat_id'),$cat_id).
			form::hidden(array('status'),$status).
			form::hidden(array('selected'),$selected).
			form::hidden(array('month'),$month).
			form::hidden(array('lang'),$lang).
			form::hidden(array('sortby'),$sortby).
			form::hidden(array('order'),$order).
			form::hidden(array('page'),$page).
			form::hidden(array('nb'),$nb);
		}
		
		if (isset($_POST['post_type'])) {
			$hidden_fields .= form::hidden(array('post_type'),$_POST['post_type']);
		}
			
		# --BEHAVIOR-- adminPostsActionsContent
		$core->callBehavior('adminPostsActionsContent',$core,$action,$hidden_fields);
		
		if ($action == 'author' && $core->auth->check('admin',$core->blog->id)) {
	
			echo '<fieldset>';
			echo '<legend>'.__('Change author for letters').'</legend>';
			echo
			//'<form action="posts_actions.php" method="post">'.
			'<form action="plugin.php?p=newsletter&amp;m=letters" method="post">'.
			
			'<p><label class="classic">'.__('Author ID:').' '.
			form::field('new_auth_id',20,255).
			'</label> ';
			echo
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'author').
			'<input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
			echo '</fieldset>';
			
			echo '<p><a class="back" href="'.html::escapeURL($redir).'">'.__('back').'</a></p>';	
		}
	}
}
?>