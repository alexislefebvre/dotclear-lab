<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of pollsFactory, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------


class pollsFactoryChart
{
	public $core;
	public $fact;
	protected $root;
	protected $g;
	public $folder = 'polls';

	public $api_url = 'http://chart.apis.google.com/chart';
	
	public function __construct($core)
	{
		$this->core = $core;
		$this->fact = new PollsFactory($core);
		
		$this->root = $core->blog->settings->pollsFactory_graph_path;
		if (!is_dir($this->root) || !is_writable($this->root)) {
			$this->root = null;
		}

		$this->g['trigger'] = (integer) $core->blog->settings->pollsFactory_graph_trigger;
		$this->g['width'] = self::size($core->blog->settings->pollsFactory_graph_width,'400');
		$this->g['ttcolor'] = self::color($core->blog->settings->pollsFactory_graph_ttcolor,'000000');
		$this->g['txcolor'] = self::color($core->blog->settings->pollsFactory_graph_txcolor,'000000');
		$this->g['bgcolor'] = self::color($core->blog->settings->pollsFactory_graph_bgcolor,'FFFFFF');
		$this->g['chcolor'] = self::color($core->blog->settings->pollsFactory_graph_chcolor,'F8F8F8');
		$this->g['barcolor'] = self::color($core->blog->settings->pollsFactory_graph_barcolor,'4D89F9');
	}

	public function serveChart($poll_id,$query_id,$http_cache=true,$http_etag=true)
	{
		$poll_id = (integer) $poll_id;
		$query_id = (integer) $query_id;

		$now = $ts = time();
		$need_update = $f_exists = false;

		$file = $this->root.'/'.$this->folder.'/'.$poll_id.'-'.$query_id.'.png';
		$dir = dirname($file);

		# If there is a cache folder
		if ($this->root === null) {
			$need_update = true;
		}
		# Check if old file exists
		if (file_exists($file)) {
			$f_exists = true;
		}
		else {
			$need_update = true;
		}
		# Get last date of vote 
		$upddt = $this->fact->lastUsersVote($poll_id);
		# Check if file needs update
		if ($f_exists) {
			$f_time = filemtime($file);

			# Settings change
			if ($this->g['trigger'] && $this->g['trigger'] > $f_time
			# Votes change
			 || $upddt && $upddt > $f_time) {
				$need_update = true;
			}
			else {
				$ts = $f_time;
			}
		}
		# Read file
		if (!$need_update) {
			$content = file_get_contents($file);
		}
		# Get file content from googleCharts
		else {
			$content = $this->sendRequest($poll_id,$query_id);
			# Write file to 'cache' folder
			if ($this->root !== null) {
				# Make dir
				if (!is_dir($dir)) {
					files::makeDir($dir,true);
				}
				# Write file
				if (($fp = @fopen($file,'wb')) !== false) {
					fwrite($fp,$content);
					fclose($fp);
					files::inheritChmod($file);
				}
			}
		}
		# Parse content
		if (!empty($content)) {
			http::head(200,'OK');
			header('Last-Modified: '.gmdate('D, d M Y H:i:s',$ts).' GMT');
			header('Cache-Control: must-revalidate, max-age=7200');
			header('Pragma:');
			header('Date: '.gmdate('D, d M Y H:i:s',$now).' GMT');
			header('Content-Type: image/png;');
			echo $content;
		}
		# Error occured
		else {
			http::head(404,'Not Found');
			header('Content-Type: text/plain;');
			echo 'file not found';
		}
		exit(1);
	}

	public function sendRequest($poll_id,$query_id)
	{
		$poll_id = (integer) $poll_id;
		$query_id = (integer) $query_id;

		$rs = $this->fact->getResponses(array('query_id'=>$query_id));
		if ($rs->isEmpty()) {
			return false;
		}
		
		$chd = $chxl = array();
		$max = 0;
		# Loop through responses and count results
		while($rs->fetch())
		{
			# Bar width (values)
			if (!isset($chd[$rs->response_text])) {
				$chd[$rs->response_text] = 0;
			}
			$chd[$rs->response_text] += 1;
			# Set maximal value
			if ($max < $chd[$rs->response_text]) {
				$max = $chd[$rs->response_text];
			}
		}
		# Lopp through options
		$options = $this->fact->getOptions(array('query_id'=>$query_id));
		while($options->fetch())
		{
			# Set bars with no responses
			$opt[$options->option_id] = $options->option_text;
			if (!isset($chd[$options->option_id])) {
				$chd[$options->option_id] = 0;
			}
			# Bar title
			$chxl[$options->option_id] = $options->option_text;
		}
		# go go go
		$data = array();
		$data['chd'] = 't:'.implode(',',$chd); // Bar values
		$data['chxt'] = 'x,y'; // Show axis
		$data['chxl'] = '1:|'.implode('|',$chxl); // Axis labels
		$data['chxs'] = '0,'.$this->g['txcolor'].'|1,'.$this->g['txcolor'];
		$data['chds'] = '0,'.$max; // Scale of max value
		$data['chm'] = 'N*,'.$this->g['txcolor'].',0,-1,11'; // Bars labels
		$data['chs'] = $this->g['width'].'x'.(count($chd) * 22 + 50); // Size = width x height(num_bar * bar_width + title_width)
		$data['chbh'] = '20,2'; // Bar width and space
		$data['chtt'] = $rs->query_title; // Title = query_title
		$data['chts'] = $this->g['ttcolor'].',12'; // title size and color
		$data['cht'] = 'bhs'; // Type = Horizontal bar charts
		$data['chco'] = $this->g['barcolor']; // Bar Color = #4D89F9
		$data['chf'] = 'c,s,'.$this->g['chcolor'].'|bg,s,'.$this->g['bgcolor']; // Chart color and background color;

		return $this->send($this->api_url,$data);
	}

	private static function send($url,$data)
	{
		try	{
			$path = '';
			$client = netHttp::initClient($url,$path);
			$client->setUserAgent('pollsFactory - http://dotclear.jcdenis.com/go/pollsFactory');
			$client->useGzip(false);
			$client->setPersistReferers(false);
			$client->post($url,$data);

			$response = $client->getContent();
		}
		catch (Exception $e) {
			throw new Exception('Failed to send request: '.$e->getMessage());
		}
		if ($client->getStatus() != 200) {
			throw new Exception('Failed to get content: '.$client->getContent());
			return false;
		}
		else {
			return $response;
		}
	}

	public static function size($s,$default=350)
	{
		return preg_match('/^([0-9]+)$/',$s) ? $s : $default;
	}

	public static function color($c,$default='CCCCCC')
	{
		if ($c === '') {
			return $default;
		}
		$c = strtoupper($c);

		if (preg_match('/^[A-F0-9]{6}$/',$c)) {
			return $c;
		}
		if (preg_match('/^[A-F0-9]{3}$/',$c)) {
			$e = explode('',$c);
			return $e[0].$e[0].$e[1].$e[1].$e[2].$e[2];
		}
		if (preg_match('/^#[A-F0-9]{6}$/',$c)) {
			return substr($c,1);
		}
		if (preg_match('/^#[A-F0-9]{3}$/',$c)) {
			$e = explode('',$c);
			return $e[1].$e[1].$e[2].$e[2].$e[3].$e[3];
		}
		return $default;
	}
}

?>