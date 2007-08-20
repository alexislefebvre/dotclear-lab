<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Spamplemousse2, a plugin for DotClear.  
# Copyright (c) 2007 Alain Vagner and contributors. All rights
# reserved.
#
# Spamplemousse2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Spamplemousse2 is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Spamplemousse2; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
/**
@ingroup PROGRESS
@brief Progress main class

This class implements a progress bar system for some lengthy php scripts.
*/
class progress
{
	private $title;
	private $urlprefix;
	private $urlreturn;
	private $func;
	private $start;
	private $stop;
	private $baseinc;
	private $pos;
	private $first_run = false;
	private $total_elapsed;
	private $total_time;
	private $percent = 0;
	private $eta;

	/**
	Constructor
		
	@param	title		<b>string</b>		Title of the page
	@param	urlprefix	<b>string</b>		Prefix for all urls
	@param	urlreturn	<b>string</b>		URL for quitting the interface
	@param	func		<b>array</b>		Static method to call (e.g. array('class', 'method'))
											this method must have two parameters "limit" and "offset" like in a sql query
	@param	start		<b>integer</b>		Id of the starting point
	@param	stop		<b>integer</b>		Id of the end point
	@param	baseinc		<b>integer</b>		Number of items to process on each loop
	@param	pos			<b>integer</b>		Current position (in order to resume processing)
 	*/
	public function __construct($title, $urlprefix, $urlreturn, $func, $start, $stop, $baseinc, $pos = '') {
		$this->start = !empty($_REQUEST['start']) ? $_REQUEST['start'] : $start;
		if ($_REQUEST['pos'] != '') {
			$this->pos = 	$_REQUEST['pos'];
		} else if ($pos != '') {
				$this->pos = $pos;
				$this->first_run = true;					
		} else {
			$this->pos = $start;
			$this->first_run = true;
		}
		$this->stop = !empty($_REQUEST['stop']) ? $_REQUEST['stop'] : $stop;
		$this->total_elapsed = !empty($_REQUEST['total_elapsed']) ? $_REQUEST['total_elapsed'] : 0;
		$this->total_time = ini_get('max_execution_time')/4;
		$this->title = $title;
		$this->urlprefix = $urlprefix;
		$this->urlreturn = $urlreturn;
		$this->func = $func;
		$this->baseinc = $baseinc;
	}

	/**
	Display the progress interface

	@param	content		<b>string</b>		Content of the page
	@return				<b>string</b>		Content after modification.
	*/
	public function gui(&$content) {
		$content .= '<h3>'.$this->title.'</h3>';		
		$error = '';

		$return .= '<a id="return" ';
		if ($this->pos < $this->stop) {	
			$return .= 'style="display: none;"';		
		}
		$return .= ' href="'.$this->urlreturn.'">'.__('Retour').'</a>';

		if (!$this->first_run) {
			if ($this->pos >= $this->stop) {
				$content .= $return;
				return $content;	
			}
			try {
				$this->compute();
			} catch (Exception $e) {
				$error = $e->toString();	
			}
		} 
		
		if ($error != '') {
			$content .= '<p class="message">'.__('Error:').' '.$error.'</p>';
		} else {
			$content .= '<script type="text/javascript" src="index.php?pf=spamplemousse2/progress.js"></script>';
				
			// display informations
			$content .='<p>'.__('Progression:').' <span id="percent">'.sprintf('%d', $this->percent).'</span> %</p>';
			$content .='<p>'.__('Time remaining:').' <span id="eta">';
			if ($this->percent != 0) {
				$content .= sprintf('%d', $this->eta).' s';
			}
			$content .= '</span></p>';	
			$url = $this->urlprefix.'&amp;pos='.$this->pos.'&amp;start='.$this->start.'&amp;stop='.$this->stop.'&amp;total_elapsed='.$this->total_elapsed;
			$content .= '<a id="next" href="'.$url.'">'.__('Continuer').'</a>';
			$content .= $return;

			$content .= '<script type="text/javascript">' .
					'$(function() {' .
					'	$(\'#next\').hide(); '.
					'	progressUpdate(\''.$this->func[0].'\', \''.$this->func[1].'\', '.$this->pos.', '.$this->start.', '.$this->stop.', '.$this->baseinc.');' .
					'});</script>';
		}	
		return $content;	
	}

	/**
	Rest interface

	@return				<b>XmlTag</b>	xml message		
	*/
	public function toXml() {
		$rsp = new xmlTag();
		$error	= '';
				
		if (!$this->first_run) {
			if ($this->pos >= $this->stop) {
				$return_xml =  new xmlTag('return');
				$return_xml->insertNode($this->urlreturn);
				$rsp->insertNode($return_xml);
				return $rsp;	
			}
			try {
				$this->compute();
			} catch (Exception $e) {
				$error = $e->getMessage();
			}
		}		
		
		if ($error != '') {
			$error_xml = new xmlTag('error');
			$error_xml->insertNode($error);
			$rsp->insertNode($error_xml);
		} else {
			$percent_xml = new xmlTag('percent');
			$percent_xml->insertNode(sprintf('%d', $this->percent));
			$rsp->insertNode($percent_xml);
			if ($this->percent != 0) {
				$eta_xml = new xmlTag('eta');
				$eta_xml->insertNode(sprintf('%d', $this->eta));
				$rsp->insertNode($eta_xml);				
			}
			$pos_xml = new xmlTag('pos');
			$pos_xml->insertNode($this->pos);
			$rsp->insertNode($pos_xml);		
				
			$total_xml = new xmlTag('total_elapsed');
			$total_xml->insertNode($this->total_elapsed);
			$rsp->insertNode($total_xml);
			
			$start_xml = new xmlTag('start');
			$start_xml->insertNode($this->start);
			$rsp->insertNode($start_xml);
			
			$stop_xml = new xmlTag('stop');
			$stop_xml->insertNode($this->stop);
			$rsp->insertNode($stop_xml);

			$baseinc_xml = new xmlTag('baseinc');
			$baseinc_xml->insertNode($this->baseinc);
			$rsp->insertNode($baseinc_xml);
			
			$funcClass_xml = new xmlTag('funcClass');
			$funcClass_xml->insertNode($this->func[0]);
			$rsp->insertNode($funcClass_xml);
			
			$funcMethod_xml = new xmlTag('funcMethod');
			$funcMethod_xml->insertNode($this->func[1]);
			$rsp->insertNode($funcMethod_xml);																
		}
		
		return $rsp;
	}

	/**
	Call the given method
		
	*/
	private function compute() {
		
		$elapsed = 0;
		do {
			$loopParams = array();
			$end = $this->pos + $this->baseinc;
			if ($end > $this->stop) {
				$end = $this->stop;	
			}
			$loopParams[] = $this->baseinc;
			$loopParams[] = $this->pos;
			$this->pos = $end;
			$tstart = microtime(true);
			call_user_func_array($this->func, $loopParams);
			$tend = microtime(true);
			$elapsed += $tend - $tstart;
		} while (($elapsed < $this->total_time) && ($this->pos < $this->stop));
		$this->total_elapsed += $elapsed;
		
		$this->percent = ($this->pos - $this->start) / ($this->stop - $this->start) * 100;
		if ($this->percent != 0) {
			$this->eta	= 100 * $this->total_elapsed / $this->percent - $this->total_elapsed;
		}
	}	
}
?>
