<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Remote LaTeX', a plugin for Dotclear              *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Remote LaTeX' (see COPYING.txt);       *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

# Doesn't work well at all - see ticket #243 of Dotclear's Trac
# $core->addBehavior('rsExtPostGetContent',array('publicRemoteLatex','rsExtPostGetContent'));
# $core->addBehavior('rsExtPostGetExcerpt',array('publicRemoteLatex','rsExtPostGetExcerpt'));
$core->addBehavior('coreBlogGetPosts',array('publicRemoteLatex','coreBlogGetPosts'));

class publicRemoteLatex
{
	public static function rsExtPostGetContent(&$rs)
	{
		$rs->post_content_xhtml = remoteLatex::parseContent($rs->post_content_xhtml);
	}
	
	public static function rsExtPostGetExcerpt(&$rs)
	{
		$rs->post_excerpt_xhtml = remoteLatex::parseContent($rs->post_excerpt_xhtml);
	}
	
	public static function coreBlogGetPosts(&$rs)
	{
		$rs->extend('rsExtPostRemoteLatex');
	}
}

class rsExtPostRemoteLatex
{
	public static function getContent(&$rs,$absolute_urls=false)
	{
		return remoteLatex::parseContent(rsExtPost::getContent($rs,$absolute_urls));
	}
	
	public static function getExcerpt(&$rs,$absolute_urls=false)
	{
		return remoteLatex::parseContent(rsExtPost::getExcerpt($rs,$absolute_urls));
	}
}
?>