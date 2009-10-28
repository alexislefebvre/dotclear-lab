<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Puzzle, a plugin for Dotclear.
# 
# Copyright (c) 2009 kévin lepeltier
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class tplPuzzle extends dcTemplate {

	public function Entries($attr,$content) {
		
		global $core;
		$core->blog->settings->setNameSpace('puzzle');
		
		if( $core->blog->settings->puzzle_active && $core->blog->settings->puzzle_active != '' ) {
		
			if( $attr['no_content'] != true )
				$content = '{{tpl:PuzzlePart b="open"}}'.$content.'{{tpl:PuzzlePart b="close"}}';
			
			$res = ''."\r".
			'require_once ("'.dirname(__FILE__).'/class.puzzle.php");'."\r".
			'$puzzle = new puzzle( $core );'."\r".
			
			'$cat_id = $_ctx->categories->cat_id;'."\r".
			'if($core->url->type == "default")'."\r".
			'	$cat_id = "home";'."\r".
			
			'if( count($puzzle->puzzle_active) && in_array($cat_id, $puzzle->puzzle_active) == true ) {'."\r".
			
			'  $puzzle_parts_cat = $puzzle->{"puzzle_parts_".$cat_id};'."\r".
			'  foreach ($puzzle_parts_cat as $puzzle_id=>$puzzle_part)'."\r".
			'    $puzzle_values[] = "(".$puzzle_part[2].",".$puzzle_id.")";'."\r".
			
			'  $core->blog->con->execute ("DROP TABLE IF EXISTS `tmp_puzzle`;"); '."\r".
			'  $core->blog->con->execute ("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_puzzle (`id` integer,`order` integer, UNIQUE KEY `order` (`order`));"); '."\r".
			'  $core->blog->con->execute ("INSERT IGNORE INTO tmp_puzzle (`id`,`order`) VALUES ".implode(",", $puzzle_values).";"); '."\r".
			
			'  $params["from"] .= "JOIN tmp_puzzle ON ( tmp_puzzle.id = P.post_id )";'."\r".
			'  $params["order"] = "tmp_puzzle.order ASC";'."\r".
			
			'} else '."\r".
			'  $params["order"] = "post_dt desc";'."\r";
			
			$res = '<?php '.$res.' ?>'."\r";
			
			$res .= str_replace('$params[\'order\']', '//$params[\'order\']', parent::Entries($attr,$content));
			
			return $res;
		}
		
		return parent::Entries($attr,$content);
		
	}
	
	public static function PuzzlePart ( $attr ) {
		
		if( $attr['b'] == 'open' ) {
			
			$res = ''."\r".
			'$cat_id = $_ctx->categories->cat_id;'."\r".
			'if($core->url->type == "default")'."\r".
			'	$cat_id = "home";'."\r".
			
			'if( in_array($cat_id, $puzzle->puzzle_active) == true ) {'."\r".
			'  foreach ($puzzle_parts_cat as $puzzle_id=>$puzzle_part)'."\r".
			'    if( $_ctx->posts->post_id == $puzzle_part[2] ) {'."\r".
			'       $nbcol = $puzzle_part[0]; '."\r".
			'       $nblig = $puzzle_part[1]; '."\r".
			'       $right = $puzzle_part[3]; '."\r".
			'    }'."\r".
			
			'  $puzzle_format_cat = $puzzle->{"puzzle_format_".$cat_id};'."\r".
			'  $widthgutter = $puzzle_format_cat[1]; '."\r".
			'  $heightgutter = $puzzle_format_cat[3]; '."\r".
			'  $nbcolmax = $puzzle_format_cat[0]; '."\r".
			'  $heightline = $puzzle_format_cat[2]; '."\r".
			
			'  $width = $nbcol*100/$nbcolmax-$widthgutter; '."\r".
			'  $height = $nblig*$heightline+$heightgutter*($nblig-1); '."\r".
			
			'  $style = "class=\"gtypo col_".$nbcol." lig_".$nblig."\" style=\"width:".$width."%;height:".$height."px;margin:0 ".$widthgutter."% ".$heightgutter."px 0;overflow: hidden;float: ".($right? "right":"left").";\""; '."\r".
			
			'}'."\r".
			
			'echo "<div ".$style.">";'."\r";
			
			return '<?php '.$res.' ?>';
			
		}
		
		return '</div>'."\r";
		
	}
	
}