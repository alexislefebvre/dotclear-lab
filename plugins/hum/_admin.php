<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of hum, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) {

	return null;
}

$core->blog->settings->addNamespace('hum');

if ($core->blog->settings->hum->active) {

	$core->addBehavior(
		'adminCommentsActionsPage',
		array('adminHum', 'adminCommentsActionsPage')
	);
	$core->addBehavior(
		'adminBeforeCommentCreate',
		array('adminHum', 'adminBeforeCommentSave')
	);
	$core->addBehavior(
		'adminBeforeCommentUpdate',
		array('adminHum', 'adminBeforeCommentSave')
	);
	$core->addBehavior(
		'adminAfterCommentDesc',
		array('adminHum', 'adminAfterCommentDesc')
	);

	$core->addBehavior(
		'coreBlogGetComments',
		array('adminHum', 'coreBlogGetComments')
	);
	$core->addBehavior(
		'coreBeforeCommentCreate',
		array('adminHum', 'coreBeforeCommentCreate')
	);

	$core->addBehavior(
		'adminPostsActionsPage',
		array('adminHum', 'adminPostsActionsPage')
	);
	$core->addBehavior(
		'adminPagesActionsPage',
		array('adminHum', 'adminPostsActionsPage')
	);

	# admin/posts.php Add actions on comments combo of entries
	//$core->addBehavior('adminPostsActionsCombo',array('adminHum','adminPostsActionsCombo'));
	# admin/posts_actions.php Save actions on comments of entries
	//$core->addBehavior('adminPostsActions',array('adminHum','adminPostsActions'));
}

/**
 * @ingroup DC_PLUGIN_HUM
 * @brief Extends getComments() only on admin side.
 * @since 2.6
 */
class rsExtHum extends rsExtComment
{
	public static function is_selected($rs)
	{
		$res = $rs->core->con->select(
			'SELECT comment_selected FROM '.$rs->core->prefix.'comment '.
			'WHERE comment_id = '.$rs->comment_id.' '.
			'LIMIT 1'
		);

		return $res->isEmpty() ? null : $res->f(0);
	}
}

/**
 * @ingroup DC_PLUGIN_HUM
 * @brief Admin methods to add comments actions.
 * @since 2.6
 */
class adminHum
{
	/**
	 * Add comment selection to record ressource
	 * 
	 * @param  record $rs record instance
	 */
	public static function coreBlogGetComments(record $rs)
	{
		$rs->extend('rsExtHum');
	}

	/**
	 * Add default selction of comment on comment creation
	 * 
	 * @param  dcBlog $blog dcBlog instance
	 * @param  cursor $cur  cursor instance
	 */
	public static function coreBeforeCommentCreate(dcBlog $blog, cursor $cur)
	{
		if (null === $cur->comment_selected) {
			$cur->comment_selected = (integer) $blog->settings->hum->comment_selected;
		}
	}

	/**
	 * Add actions to coments page combo
	 * 
	 * @param  dcCore                $core dcCore instance
	 * @param  dcCommentsActionsPage $ap   dcCommentsActionsPage instance
	 */
	public static function adminCommentsActionsPage(dcCore $core, dcCommentsActionsPage $pa)
	{
		if (!$core->auth->check('publish,contentadmin',$core->blog->id)) {

			return null;
		}

		$pa->addAction(
			array(
				__('Useless comments') => array(
					__('Mark as selected') => 'hum_selected'
				)
			),
			array('adminHum', 'callbackCommentsAction')
		);

		$pa->addAction(
			array(
				__('Useless comments') => array(
					__('Mark as unselected') => 'hum_unselected'
				)
			),
			array('adminHum', 'callbackCommentsAction')
		);
	}

	/**
	 * Comments actions callback to add hum actions
	 * 
	 * @param  dcCore                $core dcCore instance
	 * @param  dcCommentsActionsPage $ap   dcCommentsActionsPage instance
	 * @param  ArrayObject           $post POST actions
	 */
	public static function callbackCommentsAction(dcCore $core, dcCommentsActionsPage $ap, ArrayObject $post)
	{
		# No entry
		$ids = $ap->getIDs();
		if (empty($ids)) {
			throw new Exception(__('No comment selected'));
		}

		# No action
		if (!in_array($ap->getAction(), array('hum_selected', 'hum_unselected'))) {

			$ap->redirect(false);
			return null;
		}

		$selected = $ap->getAction() == 'hum_selected';

		foreach($posts_ids as $post_id) {
			self::selectComment($core, $id, $selected);
		}

		dcPage::addSuccessNotice(__('Comments selection changed.'));
		$ap->redirect(true);
	}

	/**
	 * Add comment selection to cursor
	 * 
	 * @param  cursor $cur         cursor insatnce
	 * @param  integer $comment_id Comment Id or null
	 */
	public static function adminBeforeCommentSave(cursor $cur, $comment_id=null)
	{
		$cur->comment_selected = (integer) !empty($_POST['hum_selected']);
	}

	/**
	 * Add hum to comment details page
	 * 
	 * @param  record $rs record instance
	 * @return string     Form field or info about comment selection
	 */
	public static function adminAfterCommentDesc(record $rs)
	{
		if (!empty($rs->core->rest) && !empty($rs->core->rest->functions['getCommentById'])) {
			return '<br /><strong>'.__('Selected:').' </strong> '.
			($rs->is_selected() ? __('yes') : __('no')).'<br />';
		}
		else {
			return 
			'<p><label class="classic" for="hum_selected">'.
			form::checkbox('hum_selected', 1, $rs->is_selected()).
			__('Selected comment').'</label></p>';
		}
	}

	/**
	 * Add actions to posts page combo
	 * 
	 * @param  dcCore             $core dcCore instance
	 * @param  dcPostsActionsPage $ap   dcPostsActionsPage instance
	 */
	public static function adminPostsActionsPage(dcCore $core, dcPostsActionsPage $ap)
	{
		if (!$core->auth->check('publish,contentadmin', $core->blog->id)) {

			return null;
		}

		$ap->addAction(
			array(
				__('Useless comments') => array(
					__('Mark as selected') => 'hum_selected'
				)
			),
			array('adminHum', 'callbackPostsAction')
		);

		$ap->addAction(
			array(
				__('Useless comments') => array(
					__('Mark as unselected') => 'hum_unselected'
				)
			),
			array('adminHum', 'callbackPostsAction')
		);
	}

	/**
	 * Posts actions callback to add hum actions
	 * 
	 * @param  dcCore             $core dcCore instance
	 * @param  dcPostsActionsPage $ap   dcPostsActionsPage instance
	 * @param  ArrayObject        $post POST actions
	 */
	public static function callbackPostsAction(dcCore $core, dcPostsActionsPage $ap, ArrayObject $post)
	{
		# No entry
		$ids = $ap->getIDs();
		if (empty($ids)) {
			throw new Exception(__('No entries selected'));
		}

		# No action
		if (!in_array($ap->getAction(), array('hum_selected', 'hum_unselected'))) {

			$ap->redirect(true);
			return null;
		}

		$rs = $core->blog->getComments(array(
			'sql' => 'AND P.post_id '.$core->con->in($ids),
			'comment_trackback' => 0
		));

		if ($rs->isEmpty()) {
			throw new Exception(__('No comments'));
		}

		$selected = $ap->getAction() == 'hum_selected';

		while($rs->fetch()) {
			self::selectComment($core, $rs->comment_id, $selected);
		}

		dcPage::addSuccessNotice(__('Comments selection changed.'));
		$ap->redirect(true);
	}

	/**
	 * Save comment selection in db
	 * 
	 * @param  dcCore  $core     dcCore instance
	 * @param  integer $id       Comment Id
	 * @param  boolean $selected Comment selection
	 */
	protected static function selectComment(dcCore $core, $id, $selected)
	{
		try {
			$cur = $core->con->openCursor($core->prefix.'comment');
			$cur->comment_selected = abs((integer) $selected);
			$core->blog->updComment($co->comment_id, $cur);
		}
		catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
}
