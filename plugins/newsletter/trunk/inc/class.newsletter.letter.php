<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

class newsletterLetter
{
	// Variables
	protected $core;
	protected $blog;
	protected $meta;
	protected $letter_id;
	
	protected $letter_subject;
	//protected $letter_header;
	protected $letter_body;
	//protected $letter_footer;
	
	
	/**
	 * Class constructor. Sets new letter object
	 *
	 * @param:	$core	dcCore
	 */
	public function __construct(dcCore $core,$letter_id=null)
	{
		$this->core = $core;
		$this->blog = $core->blog;
		
		$this->meta = new dcMeta($core);

		$this->setLetterId($letter_id);
	}

	public function setLetterId($letter_id=null)
	{
		if ($letter_id) {
			$this->letter_id = $letter_id;
		}
	}

	public function getLetterId()
	{
		return $this->letter_id;
	}

	public function getLetter()
	{
		global $core;
		
		$params['post_type'] = 'newsletter';
		$params['post_id'] = $this->letter_id;
			
		$letter = $core->blog->getPosts($params);
			
		if ($letter->isEmpty())
		{
			$core->error->add(__('This letter does not exist.'));
		}
		else
		{
			return $letter;
			/*
			$post_id = $post->post_id;
			$cat_id = $post->cat_id;
			$post_dt = date('Y-m-d H:i',strtotime($post->post_dt));
			$post_format = $post->post_format;
			$post_password = $post->post_password;
			$post_url = $post->post_url;
			$post_lang = $post->post_lang;
			$post_title = $post->post_title;
			$post_excerpt = $post->post_excerpt;
			$post_excerpt_xhtml = $post->post_excerpt_xhtml;
			$post_content = $post->post_content;
			$post_content_xhtml = $post->post_content_xhtml;
			$post_notes = $post->post_notes;
			$post_status = $post->post_status;
			$post_selected = (boolean) $post->post_selected;
			$post_open_comment = (boolean) $post->post_open_comment;
			$post_open_tb = (boolean) $post->post_open_tb;
			$post_meta = $post->post_meta;
			*/
		}
	}
	
	
	public function getLetterToSend(){
		
		
		
	}
	
	
	public function displayTabLetter() 
	{
		global $core; 
		
		$p_url = 'plugin.php?p=newsletter';
		$redir_url = $p_url.'&m=letter';

		$post_id = '';
		$cat_id = '';
		$post_dt = '';
		$post_format = $core->auth->getOption('post_format');
		$post_password = '';
		$post_url = '';
		$post_lang = $core->auth->getInfo('user_lang');
		$post_title = '';
		$post_excerpt = '';
		$post_excerpt_xhtml = '';
		$post_content = '';
		$post_content_xhtml = '';
		$post_notes = '';
		$post_status = $core->auth->getInfo('user_post_status');
		$post_selected = false;
		$post_open_comment = false;
		$post_open_tb = false;
		
		$post_media = array();
		$post_meta = array();
		
		$page_title = __('New letter');
		
		$can_view_page = true;
		$can_edit_post = $core->auth->check('usage,contentadmin',$core->blog->id);
		$can_publish = $core->auth->check('publish,contentadmin',$core->blog->id);
		$can_delete = false;
		
		$post_headlink = '<link rel="%s" title="%s" href="'.html::escapeURL($redir_url).'&id=%s" />';
		$post_link = '<a href="'.html::escapeURL($redir_url).'&id=%s" title="%s">%s</a>';
		
		$next_link = $prev_link = $next_headlink = $prev_headlink = null;
		
		# If user can't publish
		if (!$can_publish) {
			$post_status = -2;
		}

		# Status combo
		foreach ($core->blog->getAllPostStatus() as $k => $v) {
			$status_combo[$v] = (string) $k;
		}

		# Formaters combo
		foreach ($core->getFormaters() as $v) {
			$formaters_combo[$v] = $v;
		}

		# Languages combo
		$rs = $core->blog->getLangs(array('order'=>'asc'));
		$all_langs = l10n::getISOcodes(0,1);
		$lang_combo = array('' => '', __('Most used') => array(), __('Available') => l10n::getISOcodes(1,1));
		while ($rs->fetch()) {
			if (isset($all_langs[$rs->post_lang])) {
				$lang_combo[__('Most used')][$all_langs[$rs->post_lang]] = $rs->post_lang;
				unset($lang_combo[__('Available')][$all_langs[$rs->post_lang]]);
			} else {
				$lang_combo[__('Most used')][$rs->post_lang] = $rs->post_lang;
			}
		}
		unset($all_langs);
		unset($rs);

		# Get letter informations
		if (!empty($_REQUEST['id']))
		{
			$params['post_type'] = 'newsletter';
			$params['post_id'] = $_REQUEST['id'];
			//$this->setLetterId($_REQUEST['id']);
			
			$post = $core->blog->getPosts($params);
			
			if ($post->isEmpty())
			{
				$core->error->add(__('This letter does not exist.'));
				$can_view_page = false;
			}
			else
			{
				$post_id = $post->post_id;
				$cat_id = $post->cat_id;
				$post_dt = date('Y-m-d H:i',strtotime($post->post_dt));
				$post_format = $post->post_format;
				$post_password = $post->post_password;
				$post_url = $post->post_url;
				$post_lang = $post->post_lang;
				$post_title = $post->post_title;
				$post_excerpt = $post->post_excerpt;
				$post_excerpt_xhtml = $post->post_excerpt_xhtml;
				$post_content = $post->post_content;
				$post_content_xhtml = $post->post_content_xhtml;
				/*
				$post_notes = $post->post_notes;
				//*/
				$post_status = $post->post_status;
				/*
				$post_selected = (boolean) $post->post_selected;
				$post_open_comment = (boolean) $post->post_open_comment;
				$post_open_tb = (boolean) $post->post_open_tb;
				//*/
				
				$page_title = __('Edit letter');
				
				$can_edit_post = $post->isEditable();
				$can_delete= $post->isDeletable();
				
				$next_rs = $core->blog->getNextPost($post,1);
				$prev_rs = $core->blog->getNextPost($post,-1);
				
				if ($next_rs !== null) {
					$next_link = sprintf($post_link,$next_rs->post_id,
						html::escapeHTML($next_rs->post_title),__('next letter').'&nbsp;&#187;');
					$next_headlink = sprintf($post_headlink,'next',
						html::escapeHTML($next_rs->post_title),$next_rs->post_id);
				}
				
				if ($prev_rs !== null) {
					$prev_link = sprintf($post_link,$prev_rs->post_id,
						html::escapeHTML($prev_rs->post_title),'&#171;&nbsp;'.__('previous letter'));
					$prev_headlink = sprintf($post_headlink,'previous',
						html::escapeHTML($prev_rs->post_title),$prev_rs->post_id);
				}
				
				/*try {
					$core->media = new dcMedia($core);
					$post_media = $core->media->getPostMedia($post_id);
				} catch (Exception $e) {}*/
				
				$post_meta = $post->post_meta;
				/*try {
					$core->meta = new dcMeta($core);
					$post_meta = self::getPostsLetter($post_id);
					
				} catch (Exception $e) {}				*/
				
			}
		}

		# Format excerpt and content
		if (!empty($_POST) && $can_edit_post)
		{
			$post_format = $_POST['post_format'];
			$post_excerpt = $_POST['post_excerpt'];
			$post_content = $_POST['post_content'];
			
			$post_title = $_POST['post_title'];
			
			/*
			$cat_id = (integer) $_POST['cat_id'];
			//*/
			
			if (isset($_POST['post_status'])) {
				$post_status = (integer) $_POST['post_status'];
			}
			
			if (empty($_POST['post_dt'])) {
				$post_dt = '';
			} else {
				$post_dt = strtotime($_POST['post_dt']);
				$post_dt = date('Y-m-d H:i',$post_dt);
			}
			
			/*
			$post_open_comment = !empty($_POST['post_open_comment']);
			$post_open_tb = !empty($_POST['post_open_tb']);
			$post_selected = !empty($_POST['post_selected']);
			//*/
			$post_lang = $_POST['post_lang'];
			$post_password = !empty($_POST['post_password']) ? $_POST['post_password'] : null;
			
			//$post_notes = $_POST['post_notes'];
			
			if (isset($_POST['post_url'])) {
				$post_url = $_POST['post_url'];
			}
			
			$core->blog->setPostContent(
				$post_id,$post_format,$post_lang,
				$post_excerpt,$post_excerpt_xhtml,$post_content,$post_content_xhtml
			);
		}

		# Create or update post
		if (!empty($_POST) && !empty($_POST['save']) && $can_edit_post)
		{
			$cur = $core->con->openCursor($core->prefix.'post');
			
			$cur->post_type = 'newsletter';
			$cur->post_title = $post_title;
			/*
			$cur->cat_id = ($cat_id ? $cat_id : null);
			//*/
			$cur->post_dt = $post_dt ? date('Y-m-d H:i:00',strtotime($post_dt)) : '';
			$cur->post_format = $post_format;
			$cur->post_password = $post_password;
			$cur->post_lang = $post_lang;
			$cur->post_title = $post_title;
			$cur->post_excerpt = $post_excerpt;
			$cur->post_excerpt_xhtml = $post_excerpt_xhtml;
			$cur->post_content = $post_content;
			$cur->post_content_xhtml = $post_content_xhtml;
			/*
			$cur->post_notes = $post_notes;
			//*/
			$cur->post_status = $post_status;
			/*
			$cur->post_selected = (integer) $post_selected;
			$cur->post_open_comment = (integer) $post_open_comment;
			$cur->post_open_tb = (integer) $post_open_tb;
			//*/
			
			if (isset($_POST['post_url'])) {
				$cur->post_url = $post_url;
			}
			
			# Update post
			if ($post_id)
			{
				try
				{
					# --BEHAVIOR-- adminBeforeLetterUpdate
					$core->callBehavior('adminBeforeLetterUpdate',$cur,$post_id);
					
					$core->blog->updPost($post_id,$cur);
					
					# --BEHAVIOR-- adminAfterLetterUpdate
					$core->callBehavior('adminAfterLetterUpdate',$cur,$post_id);
					
					http::redirect($redir_url.'&id='.$post_id.'&upd=1');
				}
				catch (Exception $e)
				{
					$core->error->add($e->getMessage());
				}
			}
			else
			{
				$cur->user_id = $core->auth->userID();
				
				try
				{
					# --BEHAVIOR-- adminBeforeLetterCreate
					$core->callBehavior('adminBeforeLetterCreate',$cur);
					
					$return_id = $core->blog->addPost($cur);
					
					# --BEHAVIOR-- adminAfterLetterCreate
					$core->callBehavior('adminAfterLetterCreate',$cur,$return_id);
					
					http::redirect($redir_url.'&id='.$return_id.'&crea=1');
				}
				catch (Exception $e)
				{
					$core->error->add($e->getMessage());
				}
			}
		}

		if (!empty($_POST['delete']) && $can_delete)
		{
			try {
				# --BEHAVIOR-- adminBeforeLetterDelete
				$core->callBehavior('adminBeforeLetterDelete',$post_id);
				$core->blog->delPost($post_id);
				http::redirect($p_url.'&m=letters');
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}

		/* DISPLAY
		-------------------------------------------------------- */
		if (!empty($_GET['upd'])) {
			echo '<p class="message">'.__('Entry has been successfully updated.').'</p>';
		}
		elseif (!empty($_GET['crea'])) {
			echo '<p class="message">'.__('Entry has been successfully created.').'</p>';
		}
		elseif (!empty($_GET['attached'])) {
			echo '<p class="message">'.__('File has been successfully attached.').'</p>';
		}
		elseif (!empty($_GET['rmattach'])) {
			echo '<p class="message">'.__('Attachment has been successfully removed.').'</p>';
		}
		
		if (!empty($_GET['creaco'])) {
			echo '<p class="message">'.__('Comment has been successfully created.').'</p>';
		}

		# XHTML conversion
		if (!empty($_GET['xconv']))
		{
			$post_excerpt = $post_excerpt_xhtml;
			$post_content = $post_content_xhtml;
			$post_format = 'xhtml';
			
			echo '<p class="message">'.__('Don\'t forget to validate your XHTML conversion by saving your post.').'</p>';
		}

		# Preview page
		if ($post_id && $post->post_status == 1) {
			echo '<p><a id="post-preview" href="'.$post->getURL().'" class="button">'.__('View letter').'</a></p>';
		} elseif ($post_id) {
			$preview_url =
			$core->blog->url.$core->url->getBase('letterpreview').'/'.
			$core->auth->userID().'/'.
			http::browserUID(DC_MASTER_KEY.$core->auth->userID().$core->auth->getInfo('user_pwd')).
			'/'.$post->post_url;
			echo '<p><a id="post-preview" href="'.$preview_url.'" class="button">'.__('Preview letter').'</a></p>';
		}
		
		# Exit if we cannot view page
		if (!$can_view_page) {
			exit;
		}
		
		if ($post_id)
		{
			echo '<p>';
			if ($prev_link) { echo $prev_link; }
			if ($next_link && $prev_link) { echo ' - '; }
			if ($next_link) { echo $next_link; }
			
			# --BEHAVIOR-- adminLetterNavLinks
			$core->callBehavior('adminLetterNavLinks',isset($post) ? $post : null);
			
			echo '</p>';
		}
		
		# Exit if we cannot view page
		if (!$can_view_page) {
			exit;
		}
		
		/* Post form if we can edit post
		-------------------------------------------------------- */
		if ($can_edit_post)
		{
			echo '<form action="'.html::escapeURL($redir_url).'&amp;m=letter" method="post" id="entry-form">';
			echo '<div id="entry-sidebar">';
			
			echo
			'<p><label>'.__('Entry status:').
			form::combo('post_status',$status_combo,$post_status,'',3,!$can_publish).
			'</label></p>'.
			
			'<p><label>'.__('Published on:').
			form::field('post_dt',16,16,$post_dt,'',3).
			'</label></p>'.
			
			'<p><label>'.__('Text formating:').
			form::combo('post_format',$formaters_combo,$post_format,'',3).
			($post_id && $post_format != 'xhtml' ? '<a href="'.html::escapeURL($redir_url).'&id='.$post_id.'&amp;xconv=1">'.__('Convert to XHTML').'</a>' : '').
			'</label></p>'.
			
			'<p><label>'.__('Entry lang:').
			form::combo('post_lang',$lang_combo,$post_lang,'',5).
			'</label></p>'.
			
			'<p><label>'.__('Entry password:').
			form::field('post_password',10,32,html::escapeHTML($post_password),'maximal',3).
			'</label></p>'.
			
			'<div class="lockable">'.
			'<p><label>'.__('Basename:').
			form::field('post_url',10,255,html::escapeHTML($post_url),'maximal',3).
			'</label></p>'.
			'<p class="form-note warn">'.
			__('Warning: If you set the URL manually, it may conflict with another entry.').
			'</p>'.
			'</div>';
			
			/* desactivate attachment in newsletter
			if ($post_id)
			{
				echo
				'<h3 class="clear">'.__('Attachments').'</h3>';
				foreach ($post_media as $f)
				{
					$ftitle = $f->media_title;
					if (strlen($ftitle) > 18) {
						$ftitle = substr($ftitle,0,16).'...';
					}
					echo
					'<div class="media-item">'.
					'<a class="media-icon" href="media_item.php&id='.$f->media_id.'">'.
					'<img src="'.$f->media_icon.'" alt="" title="'.$f->basename.'" /></a>'.
					'<ul>'.
					'<li><a class="media-link" href="media_item.php&id='.$f->media_id.'"'.
					'title="'.$f->basename.'">'.$ftitle.'</a></li>'.
					'<li>'.$f->media_dtstr.'</li>'.
					'<li>'.files::size($f->size).' - '.
					'<a href="'.$f->file_url.'">'.__('open').'</a>'.'</li>'.
					
					'<li class="media-action"><a class="attachment-remove" id="attachment-'.$f->media_id.'" '.
					'href="post_media.php?post_id='.$post_id.'&amp;media_id='.$f->media_id.'&amp;remove=1">'.
					'<img src="images/check-off.png" alt="'.__('remove').'" /></a>'.
					'</li>'.
					
					'</ul>'.
					'</div>';
				}
				unset($f);
				
				if (empty($post_media)) {
					echo '<p>'.__('No attachment.').'</p>';
				}
				echo '<p><a href="media.php?post_id='.$post_id.'">'.__('Add files to this entry').'</a></p>';
			}
			//*/

			# --BEHAVIOR-- adminLetterFormSidebar
			$core->callBehavior('adminLetterFormSidebar',isset($post) ? $post : null);
			
			echo '</div>';		// End #entry-sidebar
			
			echo '<div id="entry-content"><fieldset class="constrained">';
			
			echo
			'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Title:').
			form::field('post_title',20,255,html::escapeHTML($post_title),'maximal',2).
			'</label></p>'.
			
			'<p class="area" id="excerpt-area"><label for="post_excerpt">'.__('Excerpt:').'</label> '.
			form::textarea('post_excerpt',50,5,html::escapeHTML($post_excerpt),'',2).
			'</p>'.
			
			'<p class="area"><label class="required" title="'.__('Required field').'" '.
			'for="post_content">'.__('Content:').'</label> '.
			form::textarea('post_content',50,$core->auth->getOption('edit_size'),html::escapeHTML($post_content),'',2).
			'</p>'.
			
			# --BEHAVIOR-- adminLetterForm
			$core->callBehavior('adminLetterForm',isset($post) ? $post : null);
			
			echo
			'<p>'.
			($post_id ? form::hidden('id',$post_id) : '').
			'<input type="submit" value="'.__('save').' (s)" tabindex="4" '.
			'accesskey="s" name="save" /> '.
			($can_delete ? '<input type="submit" value="'.__('delete').'" name="delete" />' : '').
			$core->formNonce().
			'</p>';
			
			echo '</fieldset></div>';		// End #entry-content
			echo '</form>';
			
			/* desactivate attachment in newsletter
			if ($post_id && !empty($post_media))
			{
				echo
				'<form action="post_media.php" id="attachment-remove-hide" method="post">'.
				'<div>'.form::hidden(array('post_id'),$post_id).
				form::hidden(array('media_id'),'').
				form::hidden(array('remove'),1).
				$core->formNonce().'</div></form>';
			}
			//*/

			# attach posts
			if ($post_id)
			{
				echo
				'<div id="link_posts"><fieldset>';
				echo '<h3 class="clear">'.__('Entries linked').'</h3>';

				$meta = new dcMeta($core);
				
				$params=array();
				$params['no_content'] = true;
		
				$params['meta_id'] = $post_id;
				$params['meta_type'] = 'letter_post';
				$params['post_type'] = '';

				# Get posts
				try {
					$posts = $meta->getPostsByMeta($params);
					$counter = $meta->getPostsByMeta($params,true);
					$post_list = new adminLinkedPostList($core,$posts,$counter->f(0));
				} catch (Exception $e) {
					$core->error->add($e->getMessage());
				}

				//print($counter->f(0));
				
				$page = 1;
				$nb_per_page = 10;
				
				if (!$core->error->flag())
				{
					if (!$posts->isEmpty())
					{
						;
						/*
						echo
						'<form action="'.$this_url.'" method="post">'.
						'<p><label class="classic">'.__('Rename this tag:').' '.
						form::field('new_meta_id',20,255,html::escapeHTML($tag)).
						'</label> <input type="submit" value="'.__('save').'" />'.
						$core->formNonce().'</p>'.
						'</form>';
						*/
					}
	
					# Show posts
					$post_list->display($page,$nb_per_page,
					'%s'
					,$post_id);
				
				}
				echo '<p><a href="plugin.php?p=newsletter&amp;m=letter_associate&amp;post_id='.$post_id.'">'.__('Add many posts to this letter').'</a></p>';
				echo '</div>';
			}
		}
	}

	/**
	 */
	public function getPostsLetter($letter_id) 
	{
		global $core;

		$meta = new dcMeta($core);

		$params=array();
		$params['no_content'] = true;

		$params['meta_id'] = $letter_id;
		$params['meta_type'] = 'letter_post';
		$params['post_type'] = '';
		
		$rs = $meta->getPostsByMeta($params);
		return $rs;
	}

	/**
	 * link a post to a letter
	 */
	public function linkPost($letter_id,$link_id)
	{
		$this->meta->delPostMeta($link_id,'letter_post',$letter_id);
		$this->meta->setPostMeta($link_id,'letter_post',$letter_id);
	}
	
	/**
	 */
	public function unlinkPost($letter_id,$link_id)
	{
		$this->meta->delPostMeta($link_id,'letter_post',$letter_id);

		$this->core->error->add($link_id.',letter_post,'.$letter_id);
	}

	/**
	 * actions on letter
	 */
	public function letterActions()
	{
		$redir_url = 'plugin.php?p=newsletter&m=letter';
		$params = array();
		$entries = array();

		if (!empty($_POST['entries'])) {
			$entries = $_POST['entries'];
		} else if (!empty($_REQUEST['link_id'])) {
			$entries = array('post_id'=>$_REQUEST['link_id']);
		}

		/* Actions
		-------------------------------------------------------- */
		if (!empty($_POST['id']) && !empty($_POST['action']) && !empty($entries))
		{
			$action = $_POST['action'];
			$letter_id = $_POST['id'];
			
			foreach ($entries as $k => $v) {
				$entries[$k] = (integer) $v;
			}
			
			$params['sql'] = 'AND P.post_id IN('.implode(',',$entries).') ';
			$params['no_content'] = true;

			if (isset($_POST['post_type'])) {
				$params['post_type'] = $_POST['post_type'];
			}
			
			$posts = $this->core->blog->getPosts($params);
			
			# --BEHAVIOR-- adminPostsActions
			$this->core->callBehavior('adminLetterActions',$core,$posts,$action,$redir_url);

			if ($action == 'associate') {

				try {
					while ($posts->fetch()) {
						self::linkPost($letter_id,$posts->post_id);
					}
					unset($posts);
					http::redirect($redir_url.'&id='.$letter_id);
				} catch (Exception $e) {
					$this->core->error->add($e->getMessage());
				}
			} else if ($action == 'unlink') {
				
				try {
					while ($posts->fetch()) {
						self::unlinkPost($letter_id,$posts->post_id);
					}
					unset($posts);
					http::redirect($redir_url.'&id='.$letter_id);
				} catch (Exception $e) {
					$this->core->error->add($e->getMessage());
				}
			}
		} else {
			http::redirect('plugin.php?p=newsletter&m=letters');
		}
	}

	/**
	 * Display tab to select associate posts with letter
	 */
	public function displayTabLetterAssociate() 
	{
		global $core;
		
		$redir_url = 'plugin.php?p=newsletter&m=letter';

		$letter_id = !empty($_GET['post_id']) ? (integer) $_GET['post_id'] : null;
		
		if ($letter_id) {
			$post = $core->blog->getPosts(array('post_id'=>$letter_id,'post_type'=>''));
			if ($post->isEmpty()) {
				$letter_id = null;
			}
			$post_title = $post->post_title;
			$post_type = $post->post_type;
			unset($post);

			echo 
			'<fieldset>'.
			'<legend>'.__('Associate posts for this letter').'</legend>';
			echo '<h3>'.__('Title of letter :').' '.$post_title.'</h3>';

			self::displayPostsList($letter_id);

			echo '</fieldset>';
			echo '<p><a class="back" href="'.html::escapeURL($redir_url).'&amp;id='.$letter_id.'">'.__('back').'</a></p>';	

		} else {
			echo 
				'<fieldset>'.
				'<legend>'.__('Associate posts for this letter').'</legend>'.
				'no letter active'.
				'</fieldset>';
			echo '<p><a class="back" href="'.html::escapeURL('plugin.php?p=newsletter&m=letters').'">'.__('back').'</a></p>';
		}
	}


	/**
	 * Display list of posts for associate
	 */
	private static function displayPostsList($letter_id = null)
	{
		global $core;

		# Getting categories
		try {
			$categories = $core->blog->getCategories(array('post_type'=>'post'));
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
		
		# Getting authors
		try {
			$users = $core->blog->getPostsUsers();
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
		
		# Getting dates
		try {
			$dates = $core->blog->getDates(array('type'=>'month'));
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
		
		# Getting langs
		try {
			$langs = $core->blog->getLangs();
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
		
		# Creating filter combo boxes
		if (!$core->error->flag())
		{
			# Filter form we'll put in html_block
			$users_combo = $categories_combo = array();
			$users_combo['-'] = $categories_combo['-'] = '';
			while ($users->fetch())
			{
				$user_cn = dcUtils::getUserCN($users->user_id,$users->user_name,
				$users->user_firstname,$users->user_displayname);
				
				if ($user_cn != $users->user_id) {
					$user_cn .= ' ('.$users->user_id.')';
				}
				
				$users_combo[$user_cn] = $users->user_id; 
			}
			
			while ($categories->fetch()) {
				$categories_combo[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
					html::escapeHTML($categories->cat_title).
					' ('.$categories->nb_post.')'] = $categories->cat_id;
			}
			
			$status_combo = array(
			'-' => ''
			);
			foreach ($core->blog->getAllPostStatus() as $k => $v) {
				$status_combo[$v] = (string) $k;
			}
			
			$selected_combo = array(
			'-' => '',
			__('selected') => '1',
			__('not selected') => '0'
			);
			
			# Months array
			$dt_m_combo['-'] = '';
			while ($dates->fetch()) {
				$dt_m_combo[dt::str('%B %Y',$dates->ts())] = $dates->year().$dates->month();
			}
			
			$lang_combo['-'] = '';
			while ($langs->fetch()) {
				$lang_combo[$langs->post_lang] = $langs->post_lang;
			}
			
			$sortby_combo = array(
			__('Date') => 'post_dt',
			__('Title') => 'post_title',
			__('Category') => 'cat_title',
			__('Author') => 'user_id',
			__('Status') => 'post_status',
			__('Selected') => 'post_selected'
			);
			
			$order_combo = array(
			__('Descending') => 'desc',
			__('Ascending') => 'asc'
			);
		}
		
		# Actions combo box
		$combo_action = array();
		
		if ($core->auth->check('admin',$core->blog->id)) {
			$combo_action[__('associate')] = 'associate';
		}
		
		# --BEHAVIOR-- adminPostsActionsCombo
		$core->callBehavior('adminLetterActionsCombo',array(&$combo_action));
		
		/* Get posts
		-------------------------------------------------------- */
		$user_id = !empty($_GET['user_id']) ?	$_GET['user_id'] : '';
		$cat_id = !empty($_GET['cat_id']) ?	$_GET['cat_id'] : '';
		$status = isset($_GET['status']) ?	$_GET['status'] : '';
		$selected = isset($_GET['selected']) ?	$_GET['selected'] : '';
		$month = !empty($_GET['month']) ?		$_GET['month'] : '';
		$lang = !empty($_GET['lang']) ?		$_GET['lang'] : '';
		$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'post_dt';
		$order = !empty($_GET['order']) ?		$_GET['order'] : 'desc';
		
		$show_filters = false;
		
		$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
		$nb_per_page =  30;
		
		if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
			if ($nb_per_page != $_GET['nb']) {
				$show_filters = true;
			}
			$nb_per_page = (integer) $_GET['nb'];
		}
		
		$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
		$params['no_content'] = true;
		
		# - User filter
		if ($user_id !== '' && in_array($user_id,$users_combo)) {
			$params['user_id'] = $user_id;
			$show_filters = true;
		}
		
		# - Categories filter
		if ($cat_id !== '' && in_array($cat_id,$categories_combo)) {
			$params['cat_id'] = $cat_id;
			$show_filters = true;
		}
		
		# - Status filter
		if ($status !== '' && in_array($status,$status_combo)) {
			$params['post_status'] = $status;
			$show_filters = true;
		}
		
		# - Selected filter
		if ($selected !== '' && in_array($selected,$selected_combo)) {
			$params['post_selected'] = $selected;
			$show_filters = true;
		}
		
		# - Month filter
		if ($month !== '' && in_array($month,$dt_m_combo)) {
			$params['post_month'] = substr($month,4,2);
			$params['post_year'] = substr($month,0,4);
			$show_filters = true;
		}
		
		# - Lang filter
		if ($lang !== '' && in_array($lang,$lang_combo)) {
			$params['post_lang'] = $lang;
			$show_filters = true;
		}
		
		# - Sortby and order filter
		if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
			if ($order !== '' && in_array($order,$order_combo)) {
				$params['order'] = $sortby.' '.$order;
			}
			
			if ($sortby != 'post_dt' || $order != 'desc') {
				$show_filters = true;
			}
		}
		
		# Get posts
		try {
			$posts = $core->blog->getPosts($params);
			$counter = $core->blog->getPosts($params,true);
			$post_list = new adminPostList($core,$posts,$counter->f(0));
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
		
		if (!$core->error->flag())
		{
			if (!$show_filters) {
				echo '<p><a id="filter-control" class="form-control" href="#">'.
				__('Filters').'</a></p>';
			}
			
			echo
			'<form action="plugin.php" method="get" id="filters-form">'.
			//'<form action="posts.php" method="get" id="filters-form">'.
			
			
			'<fieldset><legend>'.__('Filters').'</legend>'.
			'<div class="three-cols">'.
			'<div class="col">'.
			'<label>'.__('Author:').
			form::combo('user_id',$users_combo,$user_id).'</label> '.
			'<label>'.__('Category:').
			form::combo('cat_id',$categories_combo,$cat_id).'</label> '.
			'<label>'.__('Status:').
			form::combo('status',$status_combo,$status).'</label> '.
			'</div>'.
			
			'<div class="col">'.
			'<label>'.__('Selected:').
			form::combo('selected',$selected_combo,$selected).'</label> '.
			'<label>'.__('Month:').
			form::combo('month',$dt_m_combo,$month).'</label> '.
			'<label>'.__('Lang:').
			form::combo('lang',$lang_combo,$lang).'</label> '.
			'</div>'.
			
			'<div class="col">'.
			'<p><label>'.__('Order by:').
			form::combo('sortby',$sortby_combo,$sortby).'</label> '.
			'<label>'.__('Sort:').
			form::combo('order',$order_combo,$order).'</label></p>'.
			'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
			__('Entries per page').'</label> '.
			'<input type="hidden" name="p" value="'.newsletterPlugin::pname().'" />'.
			'<input type="hidden" name="m" value="letter_associate" />'.
			'<input type="hidden" name="post_id" value='.$letter_id.' />'.
			'<input type="submit" value="'.__('filter').'" /></p>'.
			'</div>'.
			'</div>'.
			'<br class="clear" />'. //Opera sucks
			'</fieldset>'.
			'</form>';
			
			# Show posts
			$post_list->display($page,$nb_per_page,
			'<form action="plugin.php?p=newsletter&amp;m=letter" method="post" id="letter_associate">'.
			
			'%s'.
			
			'<div class="two-cols">'.
			'<p class="col checkboxes-helpers"></p>'.
			
			'<p class="col right">'.__('Selected entries action:').' '.
			form::combo('action',$combo_action).
			'<input type="submit" value="'.__('ok').'" /></p>'.
			
			form::hidden(array('m'),'letter').
			form::hidden(array('p'),newsletterPlugin::pname()).	
			form::hidden(array('id'),$letter_id).
			$core->formNonce().
			'</div>'.
			'</form>'
			);
		}
	}

	public function letter_header($title)  {
		$res  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		$res .= '<html>';
		$res .= '<head>';
		$res .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		$res .= '<meta name="MSSmartTagsPreventParsing" content="TRUE" />';
		$res .= '<title>'.$title.'</title>';
		$res .= '</head>';
		$res .= '<body>';
		return $res;
	}

	public function letter_footer()  {
		$res  = '</body>';
		$res .= '</html>';
		return $res;
	}


	public function rendering($body = null) {
		
		$patterns[0] = '/LISTPOSTS/';
		$replacements[0] = 'liste des billets';

		$scontent=$body;
		
		$count = 0;
		$scontent = preg_replace($patterns, $replacements, $scontent, 1, $count);
		return $scontent;
	}

	/* 1 - recupere les valeurs de la letter
	 * 2 - formatte les champs de la letter pour l'envoi
	 * 3 - creation de l'arbre xml correspondant
	 */
	public function getXmlLetterById()
	{
		$subject='';
		$body='';
		$mode='html';
		
		// recupere le contenu de la letter
		$params = array();
		$params['post_type'] = 'newsletter';
		$params['post_id'] = (integer) $this->letter_id;
	
		$rs = $this->core->blog->getPosts($params);
		
		if ($rs->isEmpty()) {
			throw new Exception('No post for this ID');
		}
		
		// formatte les champs de la letter pour l'envoi
		$subject=text::toUTF8($rs->post_title);
		$header=self::letter_header($rs->post_title);
		$footer=self::letter_footer();
		$body=self::rendering($rs->post_content);
		$body=text::toUTF8($body);

		// creation de l'arbre xml correspondant
		$rsp = new xmlTag('letter');
		$rsp->letter_id = $rs->post_id;
		
		//$rsp->letter_subject = $subject;
		$rsp->letter_subject($subject);
		$rsp->letter_header($header);
		$rsp->letter_body($body);
		$rsp->letter_footer($footer);
		/*
		$rsp->blog_id($rs->blog_id);
		$rsp->user_id($rs->user_id);
		$rsp->cat_id($rs->cat_id);
		$rsp->post_dt($rs->post_dt);
		$rsp->post_creadt($rs->post_creadt);
		$rsp->post_upddt($rs->post_upddt);
		$rsp->post_format($rs->post_format);
		$rsp->post_url($rs->post_url);
		$rsp->post_lang($rs->post_lang);
		$rsp->post_title($rs->post_title);
		$rsp->post_excerpt($rs->post_excerpt);
		$rsp->post_excerpt_xhtml($rs->post_excerpt_xhtml);
		$rsp->post_content($rs->post_content);
		$rsp->post_content_xhtml($rs->post_content_xhtml);
		$rsp->post_notes($rs->post_notes);
		$rsp->post_status($rs->post_status);
		$rsp->post_selected($rs->post_selected);
		$rsp->post_open_comment($rs->post_open_comment);
		$rsp->post_open_tb($rs->post_open_tb);
		$rsp->nb_comment($rs->nb_comment);
		$rsp->nb_trackback($rs->nb_trackback);
		$rsp->user_name($rs->user_name);
		$rsp->user_firstname($rs->user_firstname);
		$rsp->user_displayname($rs->user_displayname);
		$rsp->user_email($rs->user_email);
		$rsp->user_url($rs->user_url);
		$rsp->cat_title($rs->cat_title);
		$rsp->cat_url($rs->cat_url);
		
		$rsp->post_display_content($rs->getContent(true));
		$rsp->post_display_excerpt($rs->getExcerpt(true));
		
		$metaTag = new xmlTag('meta');
		if (($meta = @unserialize($rs->post_meta)) !== false)
		{
			foreach ($meta as $K => $V)
			{
				foreach ($V as $v) {
					$metaTag->$K($v);
				}
			}
		}
		$rsp->post_meta($metaTag);
		*/
		
		
		return $rsp;
	}	
	
}

class adminLinkedPostList extends adminGenericList
{
	protected $letter_id;
	
	public function display($page,$nb_per_page,$enclose_block='',$letter_id)
	{
		if ($letter_id)
			$this->letter_id = $letter_id;
		
		if (!$this->rs->isEmpty())
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			$html_block =
			'<table class="clear"><tr>'.
			'<th>'.__('Remove').'</th>'.
			'<th>'.__('Title').'</th>'.
			'<th>'.__('Date').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			//echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->postLine();
			}
			
			echo $blocks[1];
			
			//echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	private function postLine()
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
			'<form action="plugin.php?p=newsletter&amp;m=letter" method="post" id="letter_detach">'.
			'<input type="image" src="images/minus.png" alt="'.__('Remove').'" style="border: 0px;" '.
			'title="'.__('Remove').'" />&nbsp;'.__('Remove').' '.
			form::hidden(array('link_id'),$this->rs->post_id).
			form::hidden(array('m'),'letter').
			form::hidden(array('p'),newsletterPlugin::pname()).	
			form::hidden(array('id'),$this->letter_id).
			form::hidden(array('action'),'unlink').
			$this->core->formNonce().
			'</form>'.
		'</td>'.
		'<td class="maximal"><a href="'.$this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id).'" '.
		'title="'.html::escapeHTML($this->rs->getURL()).'">'.
		html::escapeHTML($this->rs->post_title).'</a></td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt).'</td>'.
		'<td class="nowrap">'.$this->rs->user_id.'</td>'.
		'<td class="nowrap status">'.$img_status.' '.$selected.' '.$protected.' '.$attach.'</td>'.
		'</tr>';

		return $res;
	}
}


?>