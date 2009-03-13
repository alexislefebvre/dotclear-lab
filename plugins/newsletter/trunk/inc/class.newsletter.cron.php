<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Newsletter, a plugin for Dotclear 2.
# Copyright (C) 2009 Benoit de Marne, and contributors. All rights
# reserved.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 3
# of the License, or (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# ***** END LICENSE BLOCK *****

class newsletterCron
{
	protected $blog;
	protected $dcCron;
	protected $taskNameId;
	
	/**
	 * Class constructor
	 *
	 * @param:	$core	dcCore
	 */
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->blog =& $core->blog;
		$this->dcCron =& $this->blog->dcCron;
		$this->taskNameId = 'NewsletterPlan';
	}

	// ajoute une tache pour l'envoi de la newsletter
	public function add($interval = 604800, $first_run = null)
	{
		return $this->dcCron->put($this->taskNameId,$interval,array('newsletterCore','cronSendNewsletter'),$first_run);
	}
	
	// supprime la tache d'envoi de la newsletter
	public function del()
	{
		if ($this->dcCron->taskExists($this->taskNameId)) {
			$this->dcCron->del(array('NewsletterPlan'));
		}
	}

	// active la tache pour l'envoi de la newsletter
	public function enable()
	{
		if ($this->dcCron->taskExists($this->taskNameId)) {
				$this->dcCron->enable($this->taskNameId);
		}
	}

	// désactive la tâche pour l'envoi de la newsletter
	public function disable()
	{
		if ($this->dcCron->taskExists($this->taskNameId)) {
				$this->dcCron->disable($this->taskNameId);
		}
	}

	// retourne le nom de la tâche planifiée
	public function getTaskName()
	{
		return $this->taskNameId;
	}	
	
	// retourne l'état de la tâche planifiée
	public function getState()
	{
		$this->tasks = $this->dcCron->getTasks();
		
		if (array_key_exists($this->taskNameId,$this->tasks)) {
			return $this->tasks[$this->taskNameId]['enabled'];
		}
	}	

	// affiche l'état de la tâche planifiée
	public function printState()
	{
		$this->tasks = $this->dcCron->getTasks();
		
		if (array_key_exists($this->taskNameId,$this->tasks)) {
			return (($this->tasks[$this->taskNameId]['enabled'] == true) ? 'enabled' : 'disabled');
		}
	}	

	// redéfini la fonction getInterval
	public function getInterval($interval) 
	{
		return dcCronEnableList::getInterval($interval);
	}
		
	// affiche l'intervalle de temps
	public function printTaskInterval()
	{
		return self::getInterval($this->dcCron->getTaskInterval($this->taskNameId));
	}

	// retourne l'intervalle de temps	
	public function getTaskInterval() {
		return $this->dcCron->getTaskInterval($this->taskNameId);
	}

	// affiche la date de la prochaine exécution
	public function printNextRunDate()
	{
		$this->tasks = $this->dcCron->getTasks();

		if (array_key_exists($this->taskNameId,$this->tasks)) {
			$format = $this->blog->settings->date_format.' - '.$this->blog->settings->time_format;
			
			$next_run = ($this->tasks[$this->taskNameId]['last_run'] == 0 ?
				dt::str(
				$format,
				$this->tasks[$this->taskNameId]['first_run']
				) : 
				dt::str(
				$format,
				$this->dcCron->getNextRunDate($this->taskNameId)
				));
			return $next_run;
		}
		return '';
	}

	// affiche le temps restant avant la prochaine exécution
	public function printRemainingTime()
	{
		return self::getInterval($this->dcCron->getRemainingTime($this->taskNameId));
	}

	// affiche la date de la dernière exécution
	public function printLastRunDate()
	{
		$this->tasks = $this->dcCron->getTasks();
		
		if (array_key_exists($this->taskNameId,$this->tasks)) {
			$format = $this->blog->settings->date_format.' - '.$this->blog->settings->time_format;
			
			$last_run = ($this->tasks[$this->taskNameId]['last_run'] == 0 ?
			__('Never') :
			dt::str(
				$format,
				$this->tasks[$this->taskNameId]['last_run']
			));
			
			return $last_run;
		}
		return '';
	}

	// retourne la date de la première exécution
	public function getFirstRun()
	{
		$this->tasks = $this->dcCron->getTasks();
		if (array_key_exists($this->taskNameId,$this->tasks)) {				
			$first_run = date('Y-m-j H:i',$this->tasks[$this->taskNameId]['first_run']);
			return $first_run;
		}
		return '';
	}

}
	
?>
