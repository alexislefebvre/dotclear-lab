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

class newsletterSubscribersList extends adminGenericList
{
	/**
	 * Display data table for subscribers
	 *
	 * @param	int		page
	 * @param	int		nb_per_page
	 * @param	string	url
	 */
	private function display($page,$nb_per_page,$enclose_block='')
	{
		global $core;
		
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No subscriber for this blog.').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			$html_block =
			'<table class="maximal" id="userslist"><tr>'.
				'<th>&nbsp;</th>'.
				'<th class="nowrap">'.__('Subscriber').'</th>'.
				'<th class="nowrap">'.__('Subscribed').'</th>'.
				'<th class="nowrap">'.__('Last sent').'</th>'.
				'<th class="nowrap">'.__('Mode send').'</th>'.
				'<th class="nowrap">'.__('Status').'</th>'.
			'</tr>%s</table>'.
			'';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->subscriberLine();
			}
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			
		}
	}

	/**
	 * Display a line
	 */	
	private function subscriberLine()
	{
		$subscriber_id = (integer)$this->rs->subscriber_id;
		
		if ($this->rs->subscribed != null) 
			$subscribed = dt::dt2str('%d/%m/%Y', $this->rs->subscribed).' '.dt::dt2str('%H:%M', $this->rs->subscribed);
		else 
			$subscribed = __('Never');
						
		if ($this->rs->lastsent != null) 
			$lastsent = dt::dt2str('%d/%m/%Y', $this->rs->lastsent).' '.dt::dt2str('%H:%M', $this->rs->lastsent);
		else 
			$lastsent = __('Never');		

		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->state) {
			case 'enabled':
				$img_status = sprintf($img,__('enabled'),'check-on.png');
				break;
			case 'disabled':
				$img_status = sprintf($img,__('disabled'),'check-off.png');
				break;
			case 'pending':
				$img_status = sprintf($img,__('pending'),'scheduled.png');
				break;
			case 'suspended':
				$img_status = sprintf($img,__('suspended'),'check-wrn.png');
				break;
		}
		
		$res =
		'<tr class="line">'.
		'<td>'.
		form::checkbox(array('subscriber[]'),$this->rs->subscriber_id,'','','',0).'</td>'.
		'<td class="nowrap"><a href="plugin.php?p=newsletter&amp;m=addedit&amp;id='.$this->rs->subscriber_id.'">'.
		html::escapeHTML($this->rs->email).'</a></td>'.
		'<td class="nowrap">'.$subscribed.'</td>'.
		'<td class="nowrap">'.$lastsent.'</td>'.
		'<td class="nowrap">'.__($this->rs->modesend).'</td>'.
		'<td class="nowrap status">'.$img_status.'</td>'.
		'</tr>';
		
		return $res;
	}

	/**
	* Onglet de la liste des abonnés du blog
	*/
	public static function tabSubscribersList()
	{
		global $core;
		
		try {

		
			$newsletter_settings = new newsletterSettings($core);

			# Creating filter combo boxes
			$sortby_combo = array(
				__('Email') => 'email',
				__('Subscribed') => 'subscribed',
				__('Last sent') => 'lastsent',
				__('State') => 'state'
			);
		
			$order_combo = array(
				__('Descending') => 'desc',
				__('Ascending') => 'asc'
			);

			# Actions combo box
			$combo_action = array();
			
			if ($core->auth->check('publish,contentadmin',$core->blog->id))
			{
				
				if ($newsletter_settings->getCheckUseSuspend()) {
					$combo_action[__('Email to send')]=array(
						__('Newsletter') => 'send',
						__('Activation') => 'sendenable',
						__('Confirmation') => 'sendconfirm',
						__('Suspension') => 'sendsuspend',
						__('Desactivation') => 'senddisable'
					);
			
					$combo_action[__('Changing state')] = array(
						__('Enable') => 'enable',
						__('Suspend') => 'suspend',
						__('Disable') => 'disable',
						__('Delete') => 'remove'
					);

				} else {
					$combo_action[__('Email to send')]=array(
						__('Newsletter') => 'send',
						__('Activation') => 'sendenable',
						__('Confirmation') => 'sendconfirm',
						__('Desactivation') => 'senddisable'
					);		
			
					$combo_action[__('Changing state')] = array(
						__('Enable') => 'enable',
						__('Disable') => 'disable',
						__('Delete') => 'remove'
					);
					
				}
				
				$combo_action[__('Changing format')] = array(
					__('html') => 'changemodehtml',
					__('text') => 'changemodetext'
				);	

				$combo_action[__('Raz last sent')] = array(
					__('Last sent') => 'lastsent'

				);	
				
			}			

			$show_filters = false;

			$nb = !empty($_GET['nb']) ?     	trim($_GET['nb']) : 0;
			$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'subscribed';
			$order = !empty($_GET['order']) ?		$_GET['order'] : 'desc';

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
			
			// Request the subscribers list
			$rs = newsletterCore::getSubscribers($params);
			$counter = newsletterCore::getSubscribers($params,true);
			$subscribers_list = new newsletterSubscribersList($core,$rs,$counter->f(0));

			if (!$core->error->flag())
			{	
				//if (!$show_filters) {
					echo '<p><a id="filter-control" class="form-control" href="#">'.__('Filters').'</a></p>';
				//}

				echo
				'<form action="plugin.php" method="get" id="filters-form">'.
				'<fieldset class="two-cols"><legend>'.__('Filters').'</legend>'.
				
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
				__('Subscribers per page').'</label> '.
				
				'<p><input type="hidden" name="p" value="'.newsletterPlugin::pname().'" />'.
				'<input type="submit" value="'.__('filter').'" /></p>'.
				'</div>'.
				
				'<br class="clear" />'. //Opera sucks
				'</fieldset>'.
				'</form>';

			}

			// Show subscribers
			$subscribers_list->display($page,$nb_per_page,
				'<form action="plugin.php?p=newsletter&amp;m=subscribers" method="post" id="subscribers_list">'.
				'<p>' .
	
				'%s'.
			
				'<div class="two-cols">'.
				'<p class="col checkboxes-helpers"></p>'.
				'<p class="col right">'.__('Selected subscribers action:').
				form::combo('op',$combo_action).
				form::hidden(array('p'),newsletterPlugin::pname()).
				form::hidden(array('sortby'),$sortby).
				form::hidden(array('order'),$order).
				form::hidden(array('page'),$page).
				form::hidden(array('nb'),$nb_per_page).
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

	public static function subcribersActions()
	{
		global $core;

		$params = array();

		# Getting letters
		try {
			$params = array(
				'post_type' => 'newsletter',
				//'post_status' => 1,
			);
			
			$rs_letters = $core->blog->getPosts($params);
			$counter = $core->blog->getPosts($params,true);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}		

		$letters_combo = array();		
		$letters_combo['-'] = '';
		
		while ($rs_letters->fetch()) {
			$letters_combo[html::escapeHTML($rs_letters->post_title).' ('.$rs_letters->post_id.')'] = $rs_letters->post_id;
		}		
		
		/* Actions
		-------------------------------------------------------- */
		if (!empty($_POST['op']) && !empty($_POST['subscriber']))
		{
			//$entries = $_POST['subscriber'];
			$action = $_POST['op'];

			if ($action == 'send' && $core->auth->check('admin',$core->blog->id)) {
			
				$entries = $_POST['subscriber'];
				foreach ($entries as $k => $v) {
					// check if users are enabled
					if ($subscriber = newsletterCore::get((integer) $v)){
						if ($subscriber->state == 'enabled') {
							$subscribers_id[$k] = (integer) $v;
						}
					}
				}			
			
				//$core->error->add('Launch lettersActions on '.count($subscribers_id));
				if(isset($subscribers_id)) {			
					$hidden_fields = '';
					foreach ($subscribers_id as $k => $v) {
						$hidden_fields .= form::hidden(array('subscribers_id[]'),(integer) $v);
					}			
					
					$letters_id = array();
					echo '<fieldset>';
					echo '<legend>'.__('Select letter to send').'</legend>';
					echo '<form action="plugin.php?p=newsletter&amp;m=letters" method="post">';
					
					echo 
					'<p><label class="classic">'.__('Letter:').'&nbsp;'.
					form::combo(array('letters_id[]'),$letters_combo,$letters_id).
					'</label> '.
					'</p>';
					
					echo 
					$hidden_fields.
					$core->formNonce().
					form::hidden(array('action'),'send').
					form::hidden(array('m'),'letters').
					form::hidden(array('p'),newsletterPlugin::pname()).
					form::hidden(array('post_type'),'newsletter').
					form::hidden(array('redir'),html::escapeHTML($_SERVER['REQUEST_URI'])).
					'<input type="submit" value="'.__('send').'" /></p>';
					echo '</form>';
					echo '</fieldset>';
					
					echo '<fieldset>';
					echo '<p>'.__('<strong>Caution :</strong> Currently, in this semi-automatic mode, the links "suspend, disable, visualization online" are badly formatted.')."</p>";
					echo '<legend>'.__('Send auto letter').'</legend>';
					echo '<form action="plugin.php?p=newsletter&amp;m=letters" method="post">';
		
					echo 
					$hidden_fields.
					$core->formNonce().
					form::hidden(array('action'),'send_old').
					form::hidden(array('m'),'letters').
					form::hidden(array('p'),newsletterPlugin::pname()).
					form::hidden(array('post_type'),'newsletter').
					form::hidden(array('redir'),html::escapeHTML($_SERVER['REQUEST_URI'])).
					'<input type="submit" value="'.__('send').'" /></p>';
		
					echo '</form>';

					echo '</fieldset>';
				} else {
					echo '<fieldset>';
					echo '<legend>'.__('Select letter to send').'</legend>';					
					echo '<p><strong>'.__('No enabled subscriber in your selection.').'</strong></p>';
					echo '</fieldset>';
				}
				
				echo '<p><a class="back" href="plugin.php?p=newsletter&amp;m=subscribers">'.__('back').'</a></p>';	
			}
		
		}
		
	}

}

?>