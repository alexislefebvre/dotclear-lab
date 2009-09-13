<?php 

# -- BEGIN LICENSE BLOCK -------------------------
# This file is part of taskManager, a plugin for Dotclear.
#
# Copyright (c) 2009 Louis Cherel
# cherel.louis@gmail.com
#
# Licensed under the GPL version 3.0 license.
# See COPIRIGHT file or
# http://www.gnu.org/licenses/gpl-3.0.txt
# -- END LICENCE BlOC -----------------------------

if (!defined('DC_CONTEXT_ADMIN')) {return;}

class DcTaskManager
{
	private $progress	= array(0);
	private $taskName	= array(0);
	private $nbrTasks	= 0;
	private $nbrObj		= array(0);
	private $nbrObjTotal	= 0;
	private $objIdMax	= 0;
	private $taskDesc	= array(0);
	private $barWidth	= 100;
	private $taskId		= array(0);
	private $taskPublicView = array(0);

	private $objOrder	= array(array(0));
	private $objName	= array(array(0));
	private $objDesc	= array(array(0));
	private $objId		= array(array(0));
	private $objFinished	= array(array(0));
	public function __construct() {
		$this->_getNbrTasks();
		$this->_getTasks();
		$this->_getProgress();
		$this->_getObj();
	}

	public function getTaskName() { return $this->taskName;}
	
	public function getObjName () { return $this->objName; }

	public function getTaskDesc() { return $this->taskDesc; }
	
	public function getObjDesc() { return $this->objDesc; }

	public function getTaskId() { return $this->taskId; }

	public function getObjId () { return $this->objId; }

	public function getNbrTasks() { return $this->nbrTasks; }
	
	public function getNbrObj() { return $this->nbrObj; }
	
	public function getObjFinished() { return $this->objFinished; }
	
	public function getObjOrder() { return $this->objOrder; }
	
	public function getTaskPublicView() {return $this->taskPublicView;}
	
	public function getObjIdMax() {
		global $core;
		$query='SELECT MAX(id) AS nbr FROM '.$core->blog->prefix.'TM_object';
		$ObjIdMax = $core->con->select($query);
		return $this->ObjIdMax = $ObjIdMax->fetch()? $ObjIdMax->f('nbr'):0;
		
	}
	
	public function getNbrObjTotal() {
		global $core;
		
		$query='SELECT COUNT(id) AS nbr FROM '.$core->blog->prefix.'TM_object';
		$nbrObjTotal = $core->con->select($query);
		return $this->nbrObjTotal= $nbrObjTotal->fetch()? $nbrObjTotal->f('nbr'):0;
	}
	
	public function getTaskNextId() {
		global $core;
		
		$q = 'SHOW TABLE STATUS LIKE \''.$core->blog->prefix.'TM_task\'';
		$rep = $core->con->select($q);
		while ($rep->fetch()){return $rep->f('Auto_increment');}
	}
	
	public function showCSSandTasks() {
		return '<div id="taskManager"><div id="curseur" class="infobulle"></div>
		'. $this->_showCSS() . '
		<script type="text/javascript" src="index.php?pf=taskManager/js/public.js"></script>
		
		' . $this->_showTaskBar() . '</div><!-- End #taskManager -->';
		
	}
	
	public function changeObjState($id,$linked) {
		global $core;
		$query = 'SELECT finished FROM '.$core->blog->prefix.'TM_object WHERE `order`='.$id.' AND task_id='.$linked;
		$rs = $core->con->select($query);
		$state = $rs->fetch()?$rs->f('finished'):0;
		$state = $state==1?0:1;
		$cur = $core->con->openCursor($core->blog->prefix.'TM_object');
		$cur->finished = $state;
		$cur->update("WHERE `order`='$id' AND task_id='$linked'");
	}
	
	public function taskPublicVisibility($id) {
		global $core;
		$query = 'SELECT public_show AS nbr FROM '.$core->blog->prefix.'TM_task WHERE id='.$id;
		$rs = $core->con->select($query);
		$state = $rs->fetch()?$rs->f('nbr'):0;
		$state = $state==1?0:1;
		$cur = $core->con->openCursor($core->blog->prefix.'TM_task');
		$cur->public_show = $state;
		$cur->update('WHERE id='.$id);
	}
	
	public function modObj($order,$name,$description,$linked) {
		global $core;
		$cur = $core->con->openCursor($core->blog->prefix.'TM_object');
		$cur->name = mysql_real_escape_string($name);
		$cur->desc = mysql_real_escape_string(ucfirst($description));
		$cur->update('WHERE `order`='.$order.' AND task_id='.$linked);
	}
	
	public function modTask($id,$name,$description) {
		global $core;
		$cur = $core->con->openCursor($core->blog->prefix.'TM_task');
		$cur->name = mysql_real_escape_string($name);
		$cur->desc = mysql_real_escape_string(ucfirst($description));
		$cur->update('WHERE id = '.$id );
	}
	
	public function addTask($name,$description,$id) {
		global $core;
		$q= 'SELECT COUNT(*) AS cnt,MAX(`order`) AS maxi FROM '.$core->blog->prefix.'TM_task';
		$rs = $core->con->select($q);
		if ($rs->fetch())
		     $order = $rs->f('cnt')==0?0:($rs->f('maxi')+1);
		$cur = $core->con->openCursor($core->blog->prefix.'TM_task');
		$cur->name = mysql_real_escape_string($name);
		$cur->desc = mysql_real_escape_string(ucfirst($description));
		$cur->order = $order;
		$cur->public_show = 1;
		$cur->insert();
		return View::new_task($this->getTaskNextId());
	}

	public function addObj($name,$description,$linked_with) {
		global $core;
		$q= 'SELECT COUNT(*) AS cnt,MAX(`order`) AS maxi FROM '.$core->blog->prefix.'TM_object WHERE task_id='.$linked_with;
		$rs = $core->con->select($q);
		if ($rs->fetch())
		     $order = $rs->f('cnt')==0?0:($rs->f('maxi')+1);
		$cur = $core->con->openCursor($core->blog->prefix.'TM_object');
		$cur->name = mysql_real_escape_string($name);
		$cur->desc = mysql_real_escape_string(ucfirst($description));
		$cur->task_id = $linked_with;
		$cur->order = $order;
		$cur->insert();
		return View::new_objective($order+1,$linked_with);
	}

	public function delTask($id) {
		global $core;
		$q1 = 'DELETE FROM '.$core->blog->prefix.'TM_task WHERE id = '.$id;
		$q2 = 'DELETE FROM '.$core->blog->prefix.'TM_object WHERE task_id='.$id;
		$core->con->execute($q1);
		$core->con->execute($q2);
	}

	public function delObj($id,$linked) {
		global $core;
		$core->con->execute('DELETE FROM '.$core->blog->prefix.'TM_object WHERE `order`='.$id.' AND task_id = '.$linked);
	}

	private function _getTasks() {
		global $core;
		$query = 'SELECT * FROM '.$core->blog->prefix.'TM_task ORDER BY id';
		
		$rs = $core->con->select($query);
		$i=0;
		while($rs->fetch())
		{
			$this->taskName[$i] = stripslashes($rs->f('name'));
			$this->taskDesc[$i] = stripslashes($rs->f('desc'));
			$this->taskId[$i] = $rs->f('id');
			$this->taskPublicView[$i] = $rs->f('public_show');
			$i++;
		}
	}
	
	private function _getObj() {
		global $core;
		
		$query = 'SELECT * FROM '.$core->blog->prefix.'TM_object ORDER BY `task_id`, `order`';
		
		$obj = $core->con->select($query);
		$y=0; # incrementeur 2
		$k="init"; # témoin de condition
		while($obj->fetch()) {
			$k = $i!=$k ? $i:$k;
			foreach($this->taskId as $key => $value) {
				if ($value == $obj->f('task_id')) {
					$i =  $key;
					$k = $k == "init" ? $i:$k;
				}
			}
			$y = $i != $k ? 0:$y;
			$this->objName[$i][$y]		= stripslashes($obj->f('name'));
			$this->objDesc[$i][$y]		= stripslashes($obj->f('desc'));
			$this->objId[$i][$y]		= $obj->f('id');
			$this->objFinished[$i][$y] 	= $obj->f('finished');
			$this->objOrder[$i][$y] 	= $obj->f('order');
			$y++;
		}
	}
	
	private function _showObj() {
		for($i=0;$i<count($this->objName);$i++) {
			$toReturn[$i] = '<ul class="toHide" id="object'.$i.'">';
			for ($y=0;$y<count($this->objName[$i]);$y++) {
				if ($this->objFinished[$i][$y]) {
					$toReturn[$i] .= '<li onmouseover="montre(\'' . addslashes($this->objDesc[$i][$y]) . '\');" onmouseout="cache();" >
					    <span style="text-decoration:line-through;">' . $this->objName[$i][$y] . '</span> 
					    <img src="index.php?pf=taskManager/img/OK.png" alt="OK"/></li>';
				}
				else {
					$toReturn[$i] .= '<li onmouseover="montre(\'' . addslashes($this->objDesc[$i][$y]) . '\');" onmouseout="cache();" >' . $this->objName[$i][$y] . '</li>';
					// <img src="index.php?pf=taskManager/img/No.png" alt="NO"/></li>';
				}
			}
			$toReturn[$i] .= '</ul>';
		}
		return $toReturn;
	}
	
	private function _showCSS() {
		
		if($this->nbrTasks>1) {
			$spans = $spans2 = $spans3 = '';
			for($i=0;$i<$this->nbrTasks;$i++) {
				# "#a" is the background.
				# "#b" is the text & the progress bar
				# "#c" is the show of the %
				$spans  = $spans  . "#a" . ($i+1) . ",";
				$spans2 = $spans2 . "#b" . ($i+1) . ",";
				$spans3 = $spans3 . "#c" . ($i+1) . ",";
			}
			$spans  .= "#a" . ($this->nbrTasks+1);
			$spans2 .= "#b" . ($this->nbrTasks+1);
			$spans3 .= "#c" . ($this->nbrTasks+1);
			
			$toReturn = '<style type="text/css">
				'. $spans .'{display:block;background-color:lightgray;border:1px solid black;width:' . $this->barWidth . 'px;margin-bottom: 3px;position:relative;top:-17px;}
				'. $spans2 .'{display:block;border-right:1px solid black;}';
				for ($i=0;$i<$this->nbrTasks;$i++) {
					$toReturn .= '
				#b' . ($i+1) . '{background-color:rgb(0,' . floor($this->progress[$i]*2.5) . ',0);height:13px;}';
				}
				$toReturn .= '
				' . $spans3; 
		}
		else {
			$toReturn = '<style type="text/css">
				#a1{display:block;background-color:lightgray;border:1px solid black;width:' . $this->barWidth . 'px;margin-bottom: 1px;position:relative;top:-17px;}
				#b1{display:block;border-right:1px solid black;background-color:rgb(0,' . floor($this->progress[0]*2.5) . ',0);height:13px;}
				#c1';
		}
		$toReturn .= '{ display:block;color: black;text-align: center;font-size:12px;
				width: ' . $this->barWidth . 'px;height:13px;}
				.taskName {margin-left: ' . ($this->barWidth+5) . 'px;
					text-align: left;display:block;
					color: black;font-size: 10px;
					font-weight: bold;width: ' . $this->barWidth*2 . 'px;margin-top: -13px;}
				.infobulle {position: relative;top: -10px;border: 1px solid Black;width:' . ($this->barWidth+40) . 'px;
					height:50px;padding:0px;font-family: Verdana, Arial;font-size: 10px;}
				.toHide {display:none;}
				#taskManager > img {position:relative;left:-14px;}
				#taskManager ul {position:relative;top:-17px;}
			</style>';
		return $toReturn;
	}

	private function _showTaskBar() {
		$showObj = $this->_showObj();
		for($i=0;$i<$this->nbrTasks;$i+=1) {
			$count = count(str_split($this->taskName[$i]));
			if ($count>18) {
				$start= substr($this->taskName[$i], 0,13);
				$taskNameSplit[$i] ='<span id="taskbar'.$i.'" 
					onmouseover="showTaskName(\''.$this->taskName[$i].'\','.$i.');" 
					onmouseout="hideTaskName(\''.$start.'\','.$i.');"
					>' . $start . 
				'...</span>';
			}
			else {
				$taskNameSplit[$i] = $this->taskName[$i];
			}
		}
		$toReturn ="";

		for($i=0;$i<$this->nbrTasks;$i+=1) {
			if ($this->taskPublicView[$i] == 1) {
				$toReturn .= '<img onclick="showObj('.$i.')" id="img'.$i.'" src="./?pf=taskManager/img/plus.png" alt="see more"/>
				<img onclick="hideObj('.$i.')" id="img2'.$i.'" class="toHide" src="./?pf=taskManager/img/moins.png" alt="see less"/>
				<span id="a'. ($i+ 1) . '" onmouseover="montre(\'' . addslashes($this->taskDesc[$i]) . '\');" onmouseout="cache();" align="left" alt="\'' . addslashes($this->taskDesc[$i]) . '\'" ><span id="b'. ($i+1) .'"><span id="c'. ($i+1) .'" alt="'.$this->progress[$i].'"></span>
				<span class="taskName">' . $taskNameSplit[$i] . '</span></span></span>
				'. $showObj[$i];
			}
		}
		$toReturn.='<script type="text/javascript">
			';
		for($i=0;$i<$this->nbrTasks;$i+=1) {
		    if ($this->taskPublicView[$i] == 1) {
			$toReturn.='progressBar('.$this->progress[$i].','.$i.');';
		    }
		}
		$toReturn.='</script><noscript>Your Browser may have JavaScript enabled to see this widget</noscript>';
		return $toReturn;
	}
	
	private function _getNbrTasks() {
		global $core;
		$query = 'SELECT COUNT(id) AS nb FROM '.$core->blog->prefix.'TM_task ';
		$rs = $core->con->select($query);
		$this->nbrTasks = $rs->fetch() ? $rs->f('nb'): 0;
	}
	
	private function _getProgress() {
		global $core;
		for($i=0;$i<$this->nbrTasks;$i++)
		{
			$query = 'SELECT COUNT("id") 
				  AS nbrObj_finished 
				  FROM '.$core->blog->prefix.'TM_object 
				  WHERE task_id = ' . $this->taskId[$i] . ' 
				  AND finished = 1';
			$query2 = 'SELECT COUNT("id") 
				   AS nbrObj 
				   FROM '.$core->blog->prefix.'TM_object 
				   WHERE task_id=' . $this->taskId[$i];
			$finished = $core->con->select($query);
			$total_nbr = $core->con->select($query2);
			while($total_nbr->fetch())
			{
				if (  $total_nbr->f('nbrObj') > 0) {
					$this->progress[$i] = ( 100 / $total_nbr->f('nbrObj') ) * $finished->f('nbrObj_finished');
				}
				else {
					$this->progress[$i] = 0;
				}
				$this->nbrObj[$i] = $total_nbr->f('nbrObj');
			}
		}
	}


}?>