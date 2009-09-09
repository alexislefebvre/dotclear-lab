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

# comments begin with "#"
# cheat of non-used code is commented by "//"
if (!defined('DC_CONTEXT_ADMIN')) {return;}

class DcTaskManager
{
	private $progress	= array(0); # Progress of tasks in an array
	private $taskName	= array(0); # Tasks names in an array
	private $nbrTasks	= 0; # number of tasks
	private $nbrObj		= array(0); # per task
	private $nbrObjTotal	= 0;
	private $objIdMax	= 0;
	private $taskDesc	= array(0); # description of tasks in a array
	private $barWidth	= 100; # bar width in pixels
	private $taskId		= array(0);

	private $objName	= array(array(0));
	private $objDesc	= array(array(0));
	private $objId		= array(array(0));
	# ex: two tasks, 3 objectives for the first, 2 for the 2nd
	# result: array(3,2)
	private $objFinished	= array(array(0)); # array of finished objects
	
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
	
	public function getObjIdMax() {
		global $core;
		
		$query='SELECT MAX(obj_id) AS nbr FROM ' . $core->blog->prefix . 'TM_object';
		$ObjIdMax = $core->con->select($query);
		return $this->ObjIdMax = $ObjIdMax->fetch()? $ObjIdMax->f('nbr'):0;
		
	}
	
	public function getNbrObjTotal() {
		global $core;
		
		$query='SELECT COUNT(obj_id) AS nbr FROM ' . $core->blog->prefix . 'TM_object';
		$nbrObjTotal = $core->con->select($query);
		return $this->nbrObjTotal= $nbrObjTotal->fetch()? $nbrObjTotal->f('nbr'):0;
	}
	
	public function showCSSandTasks() {
		return '<div id="taskManager"><div id="curseur" class="infobulle"></div>
		'. $this->_showCSS() . '
		<script type="text/javascript" src="index.php?pf=taskManager/js/public.js"></script>
		
		' . $this->_showTaskBar() . '</div><!-- End #taskManager -->';
		
	}
	
	public function changeObjState($id,$state) {
		global $core;
		$cur = $core->con->openCursor($core->blog->prefix . 'TM_object');
		$cur->obj_finished = $state;
		$cur->update('WHERE obj_id = ' . $id );
	}
	
	public function modObj($id,$name,$description) {
		global $core;
		$cur = $core->con->openCursor($core->blog->prefix . 'TM_object');
		$cur->obj_name = mysql_real_escape_string($name);
		$cur->obj_desc = mysql_real_escape_string(ucfirst($description));
		$cur->update('WHERE obj_id = ' . $id );
	}
	
	public function modTask($id,$name,$description) {
		global $core;
		$cur = $core->con->openCursor($core->blog->prefix . 'TM_task');
		$cur->task_name = mysql_real_escape_string($name);
		$cur->task_desc = mysql_real_escape_string(ucfirst($description));
		$cur->update('WHERE task_id = ' . $id );
	}
	
	public function addTask($name,$description,$id=-1) {
		global $core;
		$cur = $core->con->openCursor($core->blog->prefix . 'TM_task');
		$cur->task_name = mysql_real_escape_string($name);
		$cur->task_desc = mysql_real_escape_string(ucfirst($description));
		if ($id != -1) $cur->task_id = $id;
		$cur->insert();
	}

	public function addObj($name,$description,$linked_with,$id=-1) {
		global $core;
		$cur = $core->con->openCursor($core->blog->prefix . 'TM_object');
		$cur->obj_name = mysql_real_escape_string($name);
		$cur->obj_desc = mysql_real_escape_string(ucfirst($description));
		$cur->obj_linked_with= $linked_with;
		if ($id != -1) $cur->obj_id = $id;
		$cur->insert();
	}

	public function delTask($id) {
		global $core;
		$query = 'DELETE FROM ' . $core->blog->prefix . 'TM_task WHERE task_id = ' . $id . '';
		$core->con->execute($query);
		$query = 'DELETE FROM ' . $core->blog->prefix . 'TM_object WHERE obj_linked_with = ' . $id . '';
		$core->con->execute($query);
	}

	public function delObj($id) {
		global $core;
		$query = 'DELETE FROM ' . $core->blog->prefix . 'TM_object WHERE obj_id = ' . $id . '';
		$core->con->execute($query);
	}
	
	private function _showCSS() {
		
		if($this->nbrTasks>1) # Most Complicate Way: if there is more than 1 task
		{
			# There are spans and not divs because 
			# in the case where a theme indicate margins for #blogextra divs
			# this widget becomes very ugly .
			# Thats not semantiquely correct, I know .
			
			$spans = $spans2 = $spans3 = '';
			
			for($i=0;$i<$this->nbrTasks;$i++) 
			{	
				# "#a" is the background.
				# "#b" is the text & the progress bar
				# "#c" is the show of the %
				# Their name is : aX ( X is a number )
				# We don't write the last #aX here, because of the ","
				$spans  = $spans  . "#a" . ($i+1) . ",";
				$spans2 = $spans2 . "#b" . ($i+1) . ",";
				$spans3 = $spans3 . "#c" . ($i+1) . ",";
			}
			# Here We write the last .
			$spans  .= "#a" . ($this->nbrTasks+1);
			$spans2 .= "#b" . ($this->nbrTasks+1);
			$spans3 .= "#c" . ($this->nbrTasks+1);
			
			$toReturn = '<style type="text/css">
				'. $spans .'{display:block;background-color:lightgray;border:1px solid black;width:' . $this->barWidth . 'px;margin-bottom: 3px;position:relative;top:-17px;}
				'. $spans2 .'{display:block;border-right:1px solid black;}';
				
				# Div color ups to light green with percents
				for ($i=0;$i<$this->nbrTasks;$i++)
				{
					$toReturn .= '
				#b' . ($i+1) . '{background-color:rgb(0,' . floor($this->progress[$i]*2.5) . ',0);height:13px;}';
				}
				$toReturn .= '
				' . $spans3; 
			
		}
		else # Or Basically if There is only 1 task
		{
			$toReturn = '<style type="text/css">
				#a1{display:block;background-color:lightgray;border:1px solid black;width:' . $this->barWidth . 'px;margin-bottom: 1px;position:relative;top:-17px;}
				#b1{display:block;border-right:1px solid black;background-color:rgb(0,' . floor($this->progress[0]*2.5) . ',0);height:13px;}
				#c1';
		}
		# Common Values
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
		
		for($i=0;$i<$this->nbrTasks;$i+=1)
		{
			$count = count(str_split($this->taskName[$i]));
			if ($count>18)
			{
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
		# Divs which are going to represent TaskBars .
		# There is also begin of JS function's call .
		$toReturn ="";
		for($i=0;$i<$this->nbrTasks;$i+=1)
		{
		$toReturn .= '<img onclick="showObj('.$i.')" id="img'.$i.'" src="./?pf=taskManager/img/plus.png" alt="see more"/>
			      <img onclick="hideObj('.$i.')" id="img2'.$i.'" class="toHide" src="./?pf=taskManager/img/moins.png" alt="see less"/>
		<span id="a'. ($i+ 1) . '" onmouseover="montre(\'' . addslashes($this->taskDesc[$i]) . '\');" onmouseout="cache();" align="left" alt="\'' . addslashes($this->taskDesc[$i]) . '\'" ><span id="b'. ($i+1) .'"><span id="c'. ($i+1) .'" alt="'.$this->progress[$i].'"></span>
		<span class="taskName">' . $taskNameSplit[$i] . '</span></span></span>
		'. $showObj[$i];
		}
		$toReturn.='<script type="text/javascript">
			';
		for($i=0;$i<$this->nbrTasks;$i+=1)
		{
			$toReturn.='progressBar('.$this->progress[$i].','.$i.');
			';
		}
		$toReturn.='</script><noscript>Your Browser may have JavaScript enabled to see this widget</noscript>';
		return $toReturn;
	}
	
	private function _showObj() {
		
		for($i=0;$i<count($this->objName);$i++)
		{
			$toReturn[$i] = '<ul class="toHide" id="object'.$i.'">';
			for ($y=0;$y<count($this->objName[$i]);$y++)
			{
				if ($this->objFinished[$i][$y])
				{
					$toReturn[$i] .= '
					<li 
					    onmouseover="montre(\'' . addslashes($this->objDesc[$i][$y]) . '\');" 
					    onmouseout="cache();" >
					    <span style="text-decoration:line-through;">' . $this->objName[$i][$y] . '</span> 
					    <img src="index.php?pf=taskManager/img/OK.png" alt="OK"/>
					</li>';
				}
				else
				{
					$toReturn[$i] .= '<li 
					onmouseover="montre(\'' . addslashes($this->objDesc[$i][$y]) . '\');" onmouseout="cache();" >' . $this->objName[$i][$y] . '</li>';
					// <img src="index.php?pf=taskManager/img/No.png" alt="NO"/></li>';
				}
			}
			$toReturn[$i] .= '</ul>';
		}
		
		return $toReturn;
	}
	
	private function _getTasks() {
		global $core;
		$prefix = 'task_';

		$query = 'SELECT ' . $prefix . 'name,' . $prefix . 'desc,' . $prefix . 'id  
		FROM ' . $core->blog->prefix . 'TM_task ORDER BY ' . $prefix . 'id';

		$rs = $core->con->select($query);
		$i=0;
		while($rs->fetch())
		{
			$this->taskName[$i] = stripslashes($rs->f($prefix . 'name'));
			$this->taskDesc[$i] = stripslashes($rs->f( $prefix . 'desc'));
			$this->taskId[$i] = $rs->f($prefix . 'id');
			//$this->progress[$i] = 50 ;
			$i++;
		}

	}
	
	private function _getNbrTasks() {
		global $core;
		$query = 'SELECT COUNT("task_id") AS nb FROM ' . $core->blog->prefix . 'TM_task ';
		$rs = $core->con->select($query);
		$this->nbrTasks = $rs->fetch() ? $rs->f('nb'): 0;
	}
	
	private function _getObj() {
		global $core;
		
		$query = 'SELECT obj_name, obj_desc, obj_id, obj_linked_with, obj_finished
		FROM ' . $core->blog->prefix . 'TM_object 
		ORDER BY obj_linked_with';
		
		$obj = $core->con->select($query);
		$y=0;
		$k="init";
		while($obj->fetch())
		{
			$k = $i!=$k ? $i:$k;
			foreach($this->taskId as $key => $value)
			{
				if ($value == $obj->f('obj_linked_with'))
				{
					$i =  $key;
					$k = $k == "init" ? $i:$k;
				}
			}
			$y = $i != $k ? 0:$y;
			$this->objName[$i][$y]		= stripslashes($obj->f('obj_name'));
			$this->objDesc[$i][$y]		= stripslashes($obj->f('obj_desc'));
			$this->objId[$i][$y]		= $obj->f('obj_id');
			$this->objFinished[$i][$y] 	= $obj->f('obj_finished');
			$y++;
		}
	}
	
	private function _getProgress() {
		global $core;
		for($i=0;$i<$this->nbrTasks;$i++)
		{
			$query = 'SELECT COUNT("obj_id") 
				  AS nbrObj_finished 
				  FROM ' . $core->blog->prefix . 'TM_object 
				  WHERE obj_linked_with = ' . $this->taskId[$i] . ' 
				  AND obj_finished = 1';
			$query2 = 'SELECT COUNT("obj_id") 
				   AS nbrObj 
				   FROM ' . $core->blog->prefix . 'TM_object 
				   WHERE obj_linked_with=' . $this->taskId[$i];
			$finished = $core->con->select($query);
			$total_nbr = $core->con->select($query2);
			while($total_nbr->fetch())
			{
				if (  $total_nbr->f('nbrObj') > 0)
				{
					# 100 is nbr max of percents
					# $rs2->f('nb') is the total nbr of objectives
					# $rs->f('nb') is the nbr of finished objectives
					# think there is 2 of 3 tasks finished
					# ( 100 / 3 ) * 2
					$this->progress[$i] = ( 100 / $total_nbr->f('nbrObj') ) * $finished->f('nbrObj_finished');
				}
				else # 100 / 0 isn't possible, we may encounter a bug
				{
					$this->progress[$i] = 0;
				}
				$this->nbrObj[$i] = $total_nbr->f('nbrObj');
			}
			
		}
	}


}?>