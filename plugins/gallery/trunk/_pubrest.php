<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->pubrest->register('gallery','restGallery');
class restGallery {
	public static function getImages(&$core,$get,$post)
	{
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);

		$maxrequest=100;
		if (!empty($get['tag'])) {
			$params['tag']=$get['tag'];
		}
		if (!empty($get['galId'])) {
			$params['gal_id']=$get['galId'];
		}
		if (!empty($get['start'])) {
			$start=(integer)$get['start'];
		} else {
			$start=0;
		}
		if (!empty($get['limit']) && ($get['limit'] <= $maxrequest)) {
			$limit = (integer)$get['limit'];
		} else {
			$limit = $maxrequest;
		}
		$params['limit']=array($start,$limit);
		$rs = $core->gallery->getGalImageMedia($params);

		$rsp = array();
		while ($rs->fetch()) {
			$media = $core->gallery->readmedia($rs);
			$thumbs=array();
			$img=array(
				'id' =>$rs->post_id,
				'thumbs' =>$media->media_thumb,
				'media_url' =>$media->file_url,
				'url' =>$rs->getURL(),
				'title' =>$rs->post_title
				);

			$rsp[]=$img;
		}

		return $rsp;
	}

	public static function getAllImageTags(&$core,$get,$post)
	{
		$core->meta = new dcMeta($core);
		$core->gallery = new dcGallery($core);
		$params['limit']=100;
		$rs = $core->meta->getMeta('tag',null,null,null,'galitem');

		$rsp = array();
		while ($rs->fetch()) {
			$meta = array(
				'id' => $rs->meta_id,
				'count' => $rs->count
				);
			$rsp[]=$meta;
		}
		return $rsp;
	}
	public static function getCategories(&$core,$get,$post)
	{
		$params['post_type']='galitem';
		$rs = $core->blog->getCategories($params);
		$rsp = new xmlTag();
		while ($rs->fetch()) {
			$cat = array(
				'id' => $rs->cat_id,
				'title' => $rs->cat_title
				);
			$rsp[]=$cat;
		}
		return $rsp;
	}
	public static function getDates(&$core,$get,$post)
	{
		$params['post_type']='galitem';
		$params['type']='month';
		$rs = $core->blog->getDates($params);
		$rsp = array();
		while ($rs->fetch()) {
			$date = array(
				'dt' => $rs->dt,
				'count' => $rs->nb_post
				);
			$rsp[] = $date;
		}
		return $rsp;
	}
}


