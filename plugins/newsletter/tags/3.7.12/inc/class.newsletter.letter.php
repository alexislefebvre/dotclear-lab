<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Benoit de Marne.
# benoit.de.marne@gmail.com
# Many thanks to Association Dotclear and special thanks to Olivier Le Bris
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

define("POST_TYPE","newsletter");

if (!defined('DC_CONTEXT_ADMIN')) { return; }

class newsletterLetter
{
	protected $core;
	protected $blog;
	protected $meta;
	protected $letter_id;
	
	protected $letter_subject;
	protected $letter_header;
	protected $letter_body;
	protected $letter_body_text;
	protected $letter_footer;
	
	protected $post_id;
	protected $cat_id;
	protected $post_dt;
	protected $post_format;
	protected $post_password;
	protected $post_url;
	protected $post_lang;
	protected $post_title;
	protected $post_excerpt;
	protected $post_excerpt_xhtml;
	protected $post_content;
	protected $post_content_xhtml;
	protected $post_notes;
	protected $post_status;
	protected $post_selected;
	protected $post_open_comment;
	protected $post_open_tb;
	protected $post_meta;
	
	private static $post_type = 'newsletter';

	/**
	 * Class constructor. Sets new letter object
	 * @param dcCore $core
	 * @param $letter_id
	 */
	public function __construct(dcCore $core,$letter_id=null)
	{
		$this->core = $core;
		$this->blog = $core->blog;

		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$this->meta = $core->meta;
			$this->system_settings = $core->blog->settings->system;
		} else {
			$this->meta = new dcMeta($core);
			$this->system_settings = $core->blog->settings;
		}		

		$this->init();
		$this->setLetterId($letter_id);
		$this->letter_subject = '';
		$this->letter_header = '';
		$this->letter_body = '';
		$this->letter_footer = '';
		$this->letter_body_text = '';
	
	}

	private function init()
	{
		$this->post_id = '';
		$this->cat_id = '';
		$this->post_dt = '';
		$this->post_format = $this->core->auth->getOption('post_format');
		$this->post_password = '';
		$this->post_url = '';
		$this->post_lang = $this->core->auth->getInfo('user_lang');
		$this->post_title = '';
		$this->post_excerpt = '';
		$this->post_excerpt_xhtml = '';
		$this->post_content = '';
		$this->post_content_xhtml = '';
		$this->post_notes = '';
		$this->post_status = $this->core->auth->getInfo('user_post_status');
		$this->post_selected = false;
		$this->post_open_comment = false;
		$this->post_open_tb = false;
		
		$this->post_media = array();
		$this->post_meta = array();
	}
	
	/**
	 * Set id of the letter
	 * @param $letter_id
	 */
	public function setLetterId($letter_id=null)
	{
		if ($letter_id) {
			$this->letter_id = $letter_id;
		}
	}

	/**
	 * Get id of the letter
	 * @return integer
	 */
	public function getLetterId()
	{
		return (integer) $this->letter_id;
	}

	/**
	 * Get the ressource mysql result for the current letter
	 * @return mysql result
	 */
	private function getRSLetter()
	{
		$params['post_type'] = $this->post_type;
		$params['post_id'] = $this->letter_id;
			
		$rs_letter = $this->core->blog->getPosts($params);
			
		if ($rs_letter->isEmpty()) {
			$this->core->error->add(__('This letter does not exist.'));
		} else {
			return $rs_letter;
		}
	}

	/**
	 * Get the url of the letter
	 * @return string
	 */
	public static function getURL($letter_id)
	{
		global $core;
		
		$params['post_type'] = 'newsletter';
		$params['post_id'] = $letter_id;
			
		$rs = $core->blog->getPosts($params);
		
		if ($rs->isEmpty())	{
			return ' ';
		} else {
			$rs->fetch();
			return $rs->getURL();
		}
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
		
		/*
		 # If user can't publish
		if (!$can_publish) {
			$post_status = -2;
		}
		//*/
		# Default value
		$post_status = -2;

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

				# Settings compatibility test
				if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
					$meta = $core->meta;
				} else {
					$meta = new dcMeta($core);
				}				
				
				$params=array();
				$params['no_content'] = true;
		
				$params['meta_id'] = $post_id;
				$params['meta_type'] = 'letter_post';
				$params['post_type'] = '';

				# Get posts
				try {
					/*$posts = $meta->getPostsByMeta($params);
					$counter = $meta->getPostsByMeta($params,true);*/
					
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

			self::printKeywords();
			
		}
	}

	/**
	 * print the list of keywords
	 */	
	protected static function printKeywords() 
	{
		$tab_keywords = array('LISTPOSTS' => __('displays a list of posts attached'),
						'LINK_VISU_ONLINE' => __('displays the link to the newsletter up on your blog'),
						'USER_DELETE' => __('displays the delete link of the user subscription'),
						'USER_SUSPEND' => __('displays the link suspension of the user subscription'));		
		echo '<fieldset><legend>'.__('Information').'</legend>';
		echo '<div class="col">';
		echo '<h3>'.__('List of keywords').'</h3>';
		echo '<ul>';
		foreach ($tab_keywords as $k => $v) {
			echo '<li>'.html::escapeHTML($k.' = '.$v).'</li>';
		}			
		echo '</ul>';
		echo '</div>';
		echo '</fieldset>';	
	}
			
	
	public function getPostsLetter() 
	{
		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$meta = $this->meta;
		} else {
			$meta = new dcMeta($this->core);
		}				
		$newsletter_settings = new newsletterSettings($this->core);

		$params=array();
		$params['no_content'] = true;

		$params['meta_id'] = (integer) $this->letter_id;
		$params['meta_type'] = 'letter_post';
		$params['post_type'] = '';
		
		$rs = $meta->getPostsByMeta($params);
		unset($params);
		
		if($rs->isEmpty())
			return null;		
		
		// paramétrage de la récupération des billets
		$params = array();

		while ($rs->fetch()) {
			$params['post_id'][] = $rs->post_id;
		}

		// sélection du contenu
		//$params['no_content'] = ($newsletter_settings->getViewContentPost() ? false : true); 
		$params['no_content'] = (false);
		// sélection des billets
		$params['post_type'] = 'post';
		// uniquement les billets publiés, sans mot de passe
		$params['post_status'] = 1;
		// sans mot de passe
		$params['sql'] = ' AND P.post_password IS NULL';
			
		// définition du tris des enregistrements et filtrage dans le temps
		$params['order'] = ' P.'.$newsletter_settings->getOrderDate().' DESC';
			
		// récupération des billets
		$rs = $this->blog->getPosts($params, false);

		//throw new Exception('value is ='.$rs->count());
				
		return($rs->isEmpty()?null:$rs);
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



	###############################################
	# ACTIONS
	###############################################

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

	###############################################
	# FORMATTING LETTER FOR MAILING
	###############################################

/**
 * Multibyte capable wordwrap
 *
 * @param string $str
 * @param int $width
 * @param string $break
 * @return string
 */
public static function mb_wordwrap($str, $width=74, $break="\r\n")
{
	// todo optimisation -- fonction trop lente si le post est long ...
	//throw new Exception('point E - '.$str_width);
	
    // Return short or empty strings untouched
    if(empty($str) || mb_strlen($str, 'UTF-8') <= $width)
        return $str;
  
    $br_width  = mb_strlen($break, 'UTF-8');
    $str_width = mb_strlen($str, 'UTF-8');
    $return = '';
    $last_space = false;
    
    for($i=0, $count=0; $i < $str_width; $i++, $count++)
    {
        // If we're at a break
        if (mb_substr($str, $i, $br_width, 'UTF-8') == $break)
        {
            $count = 0;
            $return .= mb_substr($str, $i, $br_width, 'UTF-8');
            $i += $br_width - 1;
            continue;
        }

        // Keep a track of the most recent possible break point
        if(mb_substr($str, $i, 1, 'UTF-8') == " ")
        {
            $last_space = $i;
        }

        // It's time to wrap
        if ($count > $width)
        {
            // There are no spaces to break on!  Going to truncate :(
            if(!$last_space)
            {
                $return .= $break;
                $count = 0;
            }
            else
            {
                // Work out how far back the last space was
                $drop = $i - $last_space;

                // Cutting zero chars results in an empty string, so don't do that
                if($drop > 0)
                {
                    $return = mb_substr($return, 0, -$drop);
                }
               
                // Add a break
                $return .= $break;

                // Update pointers
                $i = $last_space + ($br_width - 1);
                $last_space = false;
                $count = 0;
            }
        }

        // Add character from the input string to the output
        $return .= mb_substr($str, $i, 1, 'UTF-8');
    }
    return $return;
}		
	
	/**
	 * Define the links content for a subscriber
	 *
	 * @param	string	scontent
	 * @param	string	sub_email
	 * @return String
	 */	
	public static function renderingSubscriber($scontent, $sub_email = '')
	{
		global $core;
		$newsletter_settings = new newsletterSettings($core);
		
		/* replace tags to the current user */
		$patterns[0] = '/USER_DELETE/';
		$patterns[1] = '/USER_SUSPEND/';
		
		if('' == $sub_email) {
			$replacements[0] = '';
			$replacements[1] = '';
		} else {
			$style_link_disable = $newsletter_settings->getStyleLinkDisable();
			$style_link_suspend = $newsletter_settings->getStyleLinkSuspend();
			$replacements[0] = '<a href='.newsletterCore::url('disable/'.newsletterTools::base64_url_encode($sub_email)).'" style="'.$style_link_disable.'">';
			$replacements[0] .= html::escapeHTML($newsletter_settings->getTxtDisable()).'</a>';
			$replacements[1] = '<a href='.newsletterCore::url('suspend/'.newsletterTools::base64_url_encode($sub_email)).'" style="'.$style_link_suspend.'">';
			$replacements[1] .= html::escapeHTML($newsletter_settings->getTxtSuspend()).'</a>';
			/*
			$replacements[0] = '<a href='.newsletterCore::url('disable/'.newsletterTools::base64_url_encode($sub_email)).'>';
			$replacements[0] .= html::escapeHTML($newsletter_settings->getTxtDisable()).'</a>';
			$replacements[1] = '<a href='.newsletterCore::url('suspend/'.newsletterTools::base64_url_encode($sub_email)).'>';
			$replacements[1] .= html::escapeHTML($newsletter_settings->getTxtSuspend()).'</a>';
			//*/
		}
		
		/* chaine initiale */
		$count = 0;
		$scontent = preg_replace($patterns, $replacements, $scontent, 1, $count);
		//$scontent = newsletterLetter::mb_wordwrap($scontent);		

		return $scontent;		
	}
	
	


	/**
	 * define the style
	 * @return String
	 */ 
	public static function letter_style() {
		global $core;

		$css_style = '<style type="text/css">';
		$letter_css = new newsletterCSS($core);
		$css_style .= $letter_css->getLetterCSS();
		$css_style .= '</style>';
		
		return $css_style; 
	}

	/**
	 * add the header
	 * @param $title	title of the newsletter
	 * @return String
	 */ 
	public function letter_header($title)
	{
		$res  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"' . "\r\n"; 
		$res .= '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\r\n";
		$res .= '<html>' . "\r\n";
		$res .= '<head>' . "\r\n";
		$res .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . "\r\n";
		$res .= '<meta name="MSSmartTagsPreventParsing" content="TRUE" />' . "\r\n";
		$res .= '<title>'.$title.'</title>' . "\r\n";
		$res .= '</head>' . "\r\n";
		$res .= '<body class="dc-letter">' . "\r\n";
		$res .= $this->letter_style() . "\r\n";		
		return $res;

		/*
		$res .= $this->letter_style() . "\r\n";
		$res .= '</head>' . "\r\n";
		$res .= '<body class="dc-letter">' . "\r\n";
		//*/
	}

	/**
	 * add the footer
	 * @return String
	 */
	public function letter_footer()
	{
		$res  = '</body> ' . "\r\n";
		$res .= '</html> ' . "\r\n";
		return $res;
	}

	/**
	 * copie de la fonction context::ContentFirstImageLookup
	 * @param $root
	 * @param $img
	 * @param $size
	 * @return String of false
	 */
	public static function ContentFirstImageLookup($root,$img,$size)
	{
		# Get base name and extension
		$info = path::info($img);
		$base = $info['base'];
		
		if (preg_match('/^\.(.+)_(sq|t|s|m)$/',$base,$m)) {
			$base = $m[1];
		}
		
		$res = false;
		if ($size != 'o' && file_exists($root.'/'.$info['dirname'].'/.'.$base.'_'.$size.'.jpg'))
		{
			$res = '.'.$base.'_'.$size.'.jpg';
		}
		else
		{
			$f = $root.'/'.$info['dirname'].'/'.$base;
			if (file_exists($f.'.'.$info['extension'])) {
				$res = $base.'.'.$info['extension'];
			} elseif (file_exists($f.'.jpg')) {
				$res = $base.'.jpg';
			} elseif (file_exists($f.'.png')) {
				$res = $base.'.png';
			} elseif (file_exists($f.'.gif')) {
				$res = $base.'.gif';
			}
		}
		
		if ($res) {
			return $res;
		}
		return false;
	}	
	
	/**
	 * Replace keywords
	 * @param String $scontent
	 * @return String
	 */
	public function rendering($scontent = null, $url_visu_online = null) 
	{
		$replacements = array();
		$patterns = array();
		
		$newsletter_settings = new newsletterSettings($this->core);
		
		/*
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		//*/
		$format = $newsletter_settings->getDateFormatPostInfo();

		/* Preparation de la liste des billets associes */
		$rs_attach_posts = '';
		$rs_attach_posts = $this->getPostsLetter();
	
		if ('' != $rs_attach_posts)
		{
			$replacements[0]= '';
			while ($rs_attach_posts->fetch())
			{
				$replacements[0] .= '<div class="letter-post">';
				
				$replacements[0] .= '<h2 class="post-title">';
				$replacements[0] .= '<a href="'.$rs_attach_posts->getURL().'">'.$rs_attach_posts->post_title.'</a>';
				$replacements[0] .= '</h2>';

				$replacements[0] .= '<p class="post-info">';
				$replacements[0] .= '('.$rs_attach_posts->getDate($format).'&nbsp;'.__('by ').'&nbsp;'.$rs_attach_posts->getAuthorCN().')';
				$replacements[0] .= '</p>';
			
				// Affiche les miniatures
				if ($newsletter_settings->getViewThumbnails()) {
					
					// reprise du code de context::EntryFirstImageHelper et adaptation
					$size=$newsletter_settings->getSizeThumbnails();
					if (!preg_match('/^sq|t|s|m|o$/',$size)) {
						$size = 's';
					}
					$class = !empty($attr['class']) ? $attr['class'] : '';
					
					$p_url = $this->system_settings->public_url;
					$p_site = preg_replace('#^(.+?//.+?)/(.*)$#','$1',$this->core->blog->url);
					$p_root = $this->core->blog->public_path;
				
					$pattern = '(?:'.preg_quote($p_site,'/').')?'.preg_quote($p_url,'/');
					$pattern = sprintf('/<img.+?src="%s(.*?\.(?:jpg|gif|png))"[^>]+/msu',$pattern);
				
					$src = '';
					$alt = '';
				
					# We first look in post content
					$subject = $rs_attach_posts->post_excerpt_xhtml.$rs_attach_posts->post_content_xhtml.$rs_attach_posts->cat_desc;
						
					if (preg_match_all($pattern,$subject,$m) > 0)
					{
						foreach ($m[1] as $i => $img) {
							if (($src = self::ContentFirstImageLookup($p_root,$img,$size)) !== false) {
								//$src = $p_url.(dirname($img) != '/' ? dirname($img) : '').'/'.$src;
								if (dirname($img) != '/' && dirname($img) != '\\') {
									$src = $p_url.dirname($img).'/'.$src;
								} else {
									$src = $p_url.'/'.$src;
								}
								
								if (preg_match('/alt="([^"]+)"/',$m[0][$i],$malt)) {
									$alt = $malt[1];
								}
								break;
							}
						}
					}

					# No src, look in category description if available
					if (!$src && $rs_attach_posts->cat_desc)
					{
						if (preg_match_all($pattern,$rs_attach_posts->cat_desc,$m) > 0)
						{
							foreach ($m[1] as $i => $img) {
								if (($src = self::ContentFirstImageLookup($p_root,$img,$size)) !== false) {
									//$src = $p_url.(dirname($img) != '/' ? dirname($img) : '').'/'.$src;
									if (dirname($img) != '/' && dirname($img) != '\\') {
										$src = $p_url.dirname($img).'/'.$src;
									} else {
										$src = $p_url.'/'.$src;
									}
										
									if (preg_match('/alt="([^"]+)"/',$m[0][$i],$malt)) {
										$alt = $malt[1];
									}
									break;
								}
							}
						};
					}

						
					if ($src) {
						$replacements[0] .= html::absoluteURLs('<img alt="'.$alt.'" src="'.$src.'" class="'.$class.'" />',$rs_attach_posts->getURL()); 
					}				
					// end reprise context::EntryFirstImageHelper
				}						

				// Contenu des billets
				$news_content = '';
				if ($newsletter_settings->getExcerptRestriction()) {
					// Get only Excerpt
					$news_content = $rs_attach_posts->getExcerpt($rs_attach_posts,true);
					$news_content = html::absoluteURLs($news_content,$rs_attach_posts->getURL());
				} else {
					if ($newsletter_settings->getViewContentPost()) {
						$news_content = $rs_attach_posts->getExcerpt($rs_attach_posts,true).' '.$rs_attach_posts->getContent($rs_attach_posts,true);
						$news_content = html::absoluteURLs($news_content,$rs_attach_posts->getURL());
					}
				}
				
				if(!empty($news_content)) {
					if($newsletter_settings->getViewContentInTextFormat()) {
						$news_content = context::remove_html($news_content);
						$news_content = text::cutString($news_content,$newsletter_settings->getSizeContentPost());
						$news_content = html::escapeHTML($news_content);
						$news_content = $news_content.' ... ';
					} else {
						//$news_content = text::cutString($news_content,$newsletter_settings->getSizeContentPost());
						$news_content = newsletterTools::cutHtmlString($news_content,$newsletter_settings->getSizeContentPost());
						$news_content = html::decodeEntities($news_content);
						$news_content = preg_replace('/<\/p>$/',"...</p>",$news_content);
					}

					// Affichage
					$replacements[0] .= '<p class="post-content">';
					$replacements[0] .= $news_content;
					$replacements[0] .= '</p>';
				}
				
				// Affiche le lien "read more"
				$style_link_read_it = $newsletter_settings->getStyleLinkReadIt();
				$replacements[0] .= '<p class="read-it">';
				$replacements[0] .= '<a href="'.$rs_attach_posts->getURL().'" style="'.$style_link_read_it.'">Read more - Lire la suite</a>';
				$replacements[0] .= '</p>';

				$replacements[0] .= '<br /><br />';
				$replacements[0] .= '</div>';
			}
		} else {
			$replacements[0]= '';
		}
		
		if (isset($url_visu_online)) {
			$text_visu_online = $newsletter_settings->getTxtLinkVisuOnline();
			$style_link_visu_online = $newsletter_settings->getStyleLinkVisuOnline();
			$replacements[1] = '';
			$replacements[1] .= '<p>';
			$replacements[1] .= '<span class="letter-visu"><a href="'.$url_visu_online.'" style="'.$style_link_visu_online.'">'.$text_visu_online.'</a></span>';
			$replacements[1] .= '</p>';
		}
		
		/* Liste des chaines a remplacer */
		$patterns[0] = '/LISTPOSTS/';
		$patterns[1] = '/LINK_VISU_ONLINE/';

		// Lancement du traitement
		$count = 0;
		$scontent = preg_replace($patterns, $replacements, $scontent, -1, $count);

		return $scontent;
	}

	/**
	 * Replace keywords
	 * @param String $scontent
	 * @return String
	 */
	public function rendering_text($scontent = null, $url_visu_online = null) 
	{
		$replacements = array();
		$patterns = array();
		
		$newsletter_settings = new newsletterSettings($this->core);
		
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}

		/* Preparation de la liste des billets associes */
		$rs_attach_posts = '';
		$rs_attach_posts = $this->getPostsLetter();
	
		if ('' != $rs_attach_posts)
		{
			$replacements[0]= '';
			
			while ($rs_attach_posts->fetch())
			{
				$replacements[0] .= $rs_attach_posts->post_title.'<br/>';
				$replacements[0] .= '('.$rs_attach_posts->getDate($format).' '.__('by ').' '.$rs_attach_posts->getAuthorCN().')<br/>';
			
				// On n'affiche pas les miniatures en mode texte

				// Contenu des billets
				$news_content = '';
				if ($newsletter_settings->getExcerptRestriction()) {
					// Get only Excerpt
					$news_content = $rs_attach_posts->getExcerpt($rs_attach_posts,true);
					$news_content = html::absoluteURLs($news_content,$rs_attach_posts->getURL());
				} else {
					if ($newsletter_settings->getViewContentPost()) {
						$news_content = $rs_attach_posts->getExcerpt($rs_attach_posts,true).' '.$rs_attach_posts->getContent($rs_attach_posts,true);
						$news_content = html::absoluteURLs($news_content,$rs_attach_posts->getURL());
					}
				}
				
				if(!empty($news_content)) {
					$news_content = context::remove_html($news_content);
					$news_content = text::cutString($news_content,$newsletter_settings->getSizeContentPost());
					$news_content = html::escapeHTML($news_content);
					$news_content = $news_content.' ... ';

					// Affichage
					$replacements[0] .= $news_content;
				}
				
				// Affiche le lien "read more"
				$replacements[0] .= '<br/>Read more - Lire la suite<br/>';
				$replacements[0] .= '('.$rs_attach_posts->getURL().')<br/>';
			}
		} else {
			$replacements[0]= '';
		}

		if (isset($url_visu_online)) {
			$text_visu_online = $newsletter_settings->getTxtLinkVisuOnline();
			$replacements[1] = '';
			$replacements[1] = $text_visu_online;
			$replacements[1] .= '<br/>('.$url_visu_online.')<br/>';
		}
		
		/* Liste des chaines a remplacer */
		$patterns[0] = '/LISTPOSTS/';
		$patterns[1] = '/LINK_VISU_ONLINE/';

		// Lancement du traitement
		$count = 0;
		$scontent = preg_replace($patterns, $replacements, $scontent, -1, $count);
		
		$convertisseur = new html2text();
		$convertisseur->set_html($scontent);
		//$convertisseur->labelLinks = __('Links:');
		$scontent = $convertisseur->get_text();
		
		throw new Exception('content='.$scontent);
	
		return $scontent;
	}	
	
	/**
	 * - define the letter's content
	 * - format the letter
	 * - create the XML tree corresponding to the newsletter
	 * @return xmlTag
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
		$header=$this->letter_header($rs->post_title);
		$footer=$this->letter_footer();
		
		// mode html
		$body=$this->rendering(html::absoluteURLs($rs->post_content_xhtml,$rs->getURL()), $rs->getURL());
		$body = text::toUTF8($body);
		$this->letter_body=$body;
		
		// mode texte		
		$body_text=$body;
		$this->letter_body_text = $body_text; 

		// creation de l'arbre xml correspondant
		$rsp = new xmlTag('letter');
		$rsp->letter_id = $rs->post_id;
		$rsp->letter_subject($subject);
		$rsp->letter_header($header);
		$rsp->letter_footer($footer);

		// Version html
		$rsp->letter_body($body);
		
		// Version text
		$rsp->letter_body_text($body_text);
		
		return $rsp;		
	}	

	/**
	 */
	public function getLetterBody($mode = 'html')
	{
		if ($mode == 'text')
			return $this->letter_body_text;
		else
			return $this->letter_body;
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

	public function insertOldLetter($subject,$body)
	{
		global $core;
		
		# Create or update post
		$cur = $core->con->openCursor($core->prefix.'post');
		$cur->post_type = 'newsletter';
		$cur->post_title = $subject;
		$cur->post_content = $body;
		$cur->post_status = 1;
		$cur->user_id = $core->auth->userID();
		
		try
		{
			# --BEHAVIOR-- adminBeforeLetterCreate
			$core->callBehavior('adminBeforeLetterCreate',$cur);
					
			$return_id = $core->blog->addPost($cur);
					
			# --BEHAVIOR-- adminAfterLetterCreate
			$core->callBehavior('adminAfterLetterCreate',$cur,$return_id);
					
			//http::redirect($redir_url.'&id='.$return_id.'&crea=1');
			$this->letter_id = $cur->post_id;
		} catch (Exception $e) {
			$core->blog->dcNewsletter->addError($e->getMessage());
		}
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

class newsletterCSS
{
	protected $core;
	protected $blog;
	protected $file_css;
	protected $path_css;
	protected $f_content;
	protected $f_name;
	
	public function __construct(dcCore $core)
	{
		$this->core = $core;
		$this->blog = $core->blog;
		
		$this->file_css = 'style_letter.css';
		$this->setPathCSS();
		$this->f_content = '';
			
		$this->f_name = $this->path_css.'/'.$this->file_css;
		$this->readFileCSS();
	}	
	
	public function setLetterCSS($new_content) 
	{
		$this->f_content = $new_content;
		return($this->writeFileCSS());
	}

	public function getLetterCSS()
	{
		return $this->f_content;
	}	

	public function getFilenameCSS()
	{
		return $this->path_css.'/'.$this->file_css;
	}

	private function setPathCSS()
	{
		$this->path_css = newsletterTools::requestPathFileCSS($this->core,$this->file_css);
	}	
	
	public function getPathCSS()
	{
		return $this->path_css;
	}	
	
	public function isEditable() 
	{
		if (!is_file($this->f_name) || !file_exists($this->f_name) || 
			!is_readable($this->f_name) || !is_writable($this->f_name)) {
			return false;
		} else {
			return true;
		}
	}
		
	private function readFileCSS() 
	{
		if($this->isEditable()) {
			// lecture du fichier et test d'erreur
			$this->f_content = @file_get_contents($this->f_name);
		}		
	
	}
	
	private function writeFileCSS() 
	{
		try
		{
			$fp = @fopen($this->path_css.'/'.$this->file_css,'wb');
			if (!$fp) {
				throw new Exception('tocatch');
			}
			
			$content = preg_replace('/(\r?\n)/m',"\n",$this->f_content);
			//$content = preg_replace('/\r/m',"\n",$this->f_content);
			
			fwrite($fp,$content);
			fclose($fp);
		}
		catch (Exception $e)
		{
			throw new Exception(sprintf(__('Unable to write file %s. Please check your theme files and folders permissions.'),$f));
		}		
		return __('Document saved');
	}	
	
}


?>