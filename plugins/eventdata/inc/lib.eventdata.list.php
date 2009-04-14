<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventdata, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) return;

class eventdataExtList
{
	protected $core;
	protected $rs;
	protected $rs_count;

	public function __construct(&$core,&$rs,$rs_count)
	{
		$this->core =& $core;
		$this->rs =& $rs;
		$this->rs_count = $rs_count;
		$this->html_prev = __('&#171;prev.');
		$this->html_next = __('next&#187;');

		$this->html_none = '<p><strong>'.__('No entry').'</strong></p>';
		$this->html = '%1$s';
		$this->html_pager =  '<p>'.__('Page(s)').' : %1$s</p>';
		$this->html_table = '<table class="clear">%1$s%2$s</table>';
		$this->html_headline = '<tr %2$s>%1$s</tr>';
		$this->html_headcell = '<th %2$s>%1$s</th>';
		$this->html_line = '<tr %2$s>%1$s</tr>';
		$this->html_cell = '<td %2$s>%1$s</td>';
		$this->headlines = '';
		$this->headcells = '';
		$this->lines = '';
		$this->cells = '';
		
		$this->init();
	}

	public function headline($cells,$head='')
	{
		$line = '';
		foreach($cells AS $content => $extra) {
			$line .= sprintf($this->html_headcell,$content,$extra);
		}
		$this->headlines .= sprintf($this->html_headline,$line,$head);
	}

	public function line($cells,$head='')
	{
		$line = '';
		foreach($cells AS $content => $extra) {
			$line .= sprintf($this->html_cell,$content,$extra);
		}
		$this->lines .= sprintf($this->html_line,$line,$head);
	}

	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty()) {
			echo $this->html_none;
		} else {
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			while ($this->rs->fetch()) {
				$this->setLine();
			}

			echo
			sprintf($this->html,
				sprintf($enclose_block,
					sprintf($this->html_pager,$pager->getLinks()).
						sprintf($this->html_table,$this->headlines,$this->lines).
					sprintf($this->html_pager,$pager->getLinks())));
		}
	}
}

# Display admin posts list class
class eventdataEventdataList extends eventdataExtList
{
	public function init()
	{
		self::headline(array(
			__('Title') => 'colspan="2"',
			__('Edit') => '',
			__('Start') => '',
			__('End') => '',
			__('Location') => '',
			__('Period') => '',
			__('Published on') => '',
			__('Category') => '',
			__('Author') => '',
			__('Status') => ''));
	}
	
	public function setLine()
	{
		if ($this->rs->cat_title)
			$cat_title = html::escapeHTML($this->rs->cat_title);
		else
			$cat_title = __('None');

		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->post_status) {
			case 1:  $img_status = sprintf($img,__('published'),'check-on.png'); break;
			case 0:  $img_status = sprintf($img,__('unpublished'),'check-off.png'); break;
			case -1: $img_status = sprintf($img,__('scheduled'),'scheduled.png'); break;
			case -2: $img_status = sprintf($img,__('pending'),'check-wrn.png'); break;
		}

		$protected = '';
		if ($this->rs->post_password)
			$protected = sprintf($img,__('protected'),'locker.png');

		$selected = '';
		if ($this->rs->post_selected)
			$selected = sprintf($img,__('selected'),'selected.png');

		$attach = '';
		$nb_media = $this->rs->countMedia();
		if ($nb_media > 0) {
			$attach_str = $nb_media == 1 ? __('%d attachment') : __('%d attachments');
			$attach = sprintf($img,sprintf($attach_str,$nb_media),'attach.png');
		}

		$img = '<img alt="%1$s" title="%1$s" src="index.php?pf=eventdata/inc/img/%2$s" />';
		if (time() < strtotime($this->rs->eventdata_start))
			$img_period = sprintf($img,__('scheduled'),'scheduled.png');
		elseif (time() > strtotime($this->rs->eventdata_end))
			$img_period = sprintf($img,__('finished'),'red.png');
		else
			$img_period = sprintf($img,__('ongoing'),'green.png');

		self::line(
			array(
				# Title
				form::checkbox(array('entries[]'),$this->rs->post_id,'','','',!$this->rs->isEditable()) =>  'class="nowrap"',
				'<a href="'.$this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id).'">'.html::escapeHTML($this->rs->post_title).'</a>' =>  'class="maximal nowrap"',
				# Edit
				'<a title="'.__('Edit this event for all entries').'" href="plugin.php?p=eventdata&eventdata='.
					dcEventdata::serializeURL('eventdata',null,$this->rs->eventdata_start,$this->rs->eventdata_end,$this->rs->eventdata_location).
					'"><img src="index.php?pf=eventdata/inc/img/edit-all.png">'.
				'</a> <a title="'.__('Edit this event for this entry').'" href="plugin.php?p=eventdata&eventdata='.
					dcEventdata::serializeURL('eventdata',$this->rs->post_id,$this->rs->eventdata_start,$this->rs->eventdata_end,$this->rs->eventdata_location).
					'"><img src="index.php?pf=eventdata/inc/img/edit-one.png"></a>' => 'class="nowrap"',
				# Start
				dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->eventdata_start) => 'class="nowrap"',
				# End
				dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->eventdata_end) => 'class="nowrap"',
				# Location
				$this->rs->eventdata_location => 'class="nowrap"',
				# Period
				$img_period => 'class="nowrap"',
				# Post date
				dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt) => 'class="nowrap"',
				# Category
				$cat_title => 'class="nowrap"',
				# Author
				$this->rs->user_id => 'class="nowrap"',
				# Status
				$img_status.' '.$selected.' '.$protected.' '.$attach => 'class="nowrap status"'
			),
			'class="line'.($this->rs->post_status != 1 ? ' offline' : '').'" id="p'.$this->rs->post_id.'"'
		);
	}
}

?>