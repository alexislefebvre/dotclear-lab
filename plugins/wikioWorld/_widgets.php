<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of wikioWorld, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# Last news from wikio
$core->addBehavior('initWidgets',array('wikioWorldWidget','adminWikioNews'));
# Universal RSS subscription for your blog 
$core->addBehavior('initWidgets',array('wikioWorldWidget','adminWikioBlogRSS'));
# RSS subscription to wikio for your blog 
$core->addBehavior('initWidgets',array('wikioWorldWidget','adminWikioBlogAddWikio'));
# Backlinks to your blog
$core->addBehavior('initWidgets',array('wikioWorldWidget','adminWikioBlogBacklinks'));
# Neighbours of your blog
$core->addBehavior('initWidgets',array('wikioWorldWidget','adminWikioBlogNeighbours'));
# Top rank blog
$core->addBehavior('initWidgets',array('wikioWorldWidget','adminWikioBlogTopRank'));
# Universal sharing system for an entry
$core->addBehavior('initWidgets',array('wikioWorldWidget','adminWikioEntryShare'));
# Wikio vote for an entry (entry must be know by wikio)
$core->addBehavior('initWidgets',array('wikioWorldWidget','adminWikioEntryVote'));

class wikioWorldWidget
{
	public static function adminWikioNews($w)
	{
		$w->create('wwnews',
			__('Wikio : Wikio news'),array('wikioWorldWidget','publicWikioNews')
		);
		$w->wwnews->setting('title',
			__('Title:'),__('News Wikio'),'text'
		);
		$w->wwnews->setting('count',
			__('Number of news to show:'),5,'combo',
			array(1=>1,2=>2,3=>3,4=>4,5=>5,10=>10,15=>15,20=>20)
		);
		$w->wwnews->setting('fcolor',
			__('Text color: (in hexadecimal without #)'),'','text'
		);
		$w->wwnews->setting('comments',
			__('Number of comments to show:'),0,'combo',
			array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5)
		);
		$w->wwnews->setting('bgcomment',
			__('Background color of comments: (in hexadecimal without #)'),
			'','text'
		);
		$w->wwnews->setting('summary',
			__('Show summary'),1,'check'
		);
		$w->wwnews->setting('theme',
			__('Show related themes'),1,'check'
		);
		$w->wwnews->setting('podcast',
			__('Show podcast'),1,'check'
		);
		$w->wwnews->setting('size',
			__('Size of text:'),1,'combo',
			array(__('small')=>0,__('medium')=>1,__('large')=>2,__('larger')=>3)
		);
		$w->wwnews->setting('target',
			__('Open link in new window'),1,'check'
		);
		$w->wwnews->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}
	
	public static function adminWikioBlogRSS($w)
	{
		$w->create('wwblogrss',
			__('Wikio : Blog subscription'),array('wikioWorldWidget','publicWikioBlogRSS')
		);
		$w->wwblogrss->setting('title',
			__('Title:'),__('Feeds'),'text'
		);
		$w->wwblogrss->setting('button',
			__('Style:'),'','combo',array(
				__('interactive')=>'',
				__('plain')=>'plain',
				__('rounded')=>'rounded',
				__('rounded open')=>'rounded-open',
				__('plain blue') => 'plain-blue',
				__('rounded blue') => 'rounded-blue',
				__('rounded open blue') => 'rounded-open-blue'
			)
		);
		$w->wwblogrss->setting('srvwikio',
			sprintf(__('Add %s service for interactive button only'),'Wikio'),1,'check'
		);
		$w->wwblogrss->setting('srvnetvibes',
			sprintf(__('Add %s service for interactive button only'),'Netvibes'),1,'check'
		);
		$w->wwblogrss->setting('srvgoogle',
			sprintf(__('Add %s service for interactive button only'),'Google'),1,'check'
		);
		$w->wwblogrss->setting('srvyahoo',
			sprintf(__('Add %s service for interactive button only'),'Yahoo'),1,'check'
		);
		$w->wwblogrss->setting('srvbloglines',
			sprintf(__('Add %s service for interactive button only'),'Bloglines'),1,'check'
		);
		$w->wwblogrss->setting('srvaol',
			sprintf(__('Add %s service for interactive button only'),'AOL'),1,'check'
		);
		$w->wwblogrss->setting('srvmsn',
			sprintf(__('Add %s service for interactive button only'),'MSN'),1,'check'
		);
		$w->wwblogrss->setting('srvnewsgator',
			sprintf(__('Add %s service for interactive button only'),'Newsgator'),1,'check'
		);
		$w->wwblogrss->setting('srvpageflakes',
			sprintf(__('Add %s service for interactive button only'),'Pageflakes'),1,'check'
		);
		$w->wwblogrss->setting('srvlive',
			sprintf(__('Add %s service for interactive button only'),'Windows Live'),1,'check'
		);
		$w->wwblogrss->setting('srvwebwag',
			sprintf(__('Add %s service for interactive button only'),'WebWag.this'),1,'check'
		);
		$w->wwblogrss->setting('srvrss',
			sprintf(__('Add %s service for interactive button only'),'RSS'),1,'check'
		);
		$w->wwblogrss->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}
	
	public static function adminWikioBlogAddWikio($w)
	{
		$w->create('wwblogaddwikio',
			__('Wikio : Blog add to wikio'),array('wikioWorldWidget','publicWikioBlogAddWikio')
		);
		$w->wwblogaddwikio->setting('title',
			__('Title:'),__('Add to Wikio'),'text'
		);
		$w->wwblogaddwikio->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}
	
	public static function adminWikioBlogBacklinks($w)
	{
		$w->create('wwblogbacklinks',
			__('Wikio : Blog backlinks'),array('wikioWorldWidget','publicWikioBlogBacklinks')
		);
		$w->wwblogbacklinks->setting('title',
			__('Title:'),__('Backlinks'),'text'
		);
		$w->wwblogbacklinks->setting('style',
			__('Style:'),'light','combo',array(
				__('box')=>'light',
				__('raw')=>'raw'
			)
		);
		$w->wwblogbacklinks->setting('width',
			__('Width: (for box style)'),'200','text'
		);
		$w->wwblogbacklinks->setting('content',
			__('Show summary'),1,'check'
		);
		$w->wwblogbacklinks->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}
	
	public static function adminWikioBlogNeighbours($w)
	{
		$w->create('wwblogneighbours',
			__('Wikio : Blog neighbours'),array('wikioWorldWidget','publicWikioBlogNeighbours')
		);
		$w->wwblogneighbours->setting('title',
			__('Title:'),__('Neighbours'),'text'
		);
		$w->wwblogneighbours->setting('style',
			__('Style:'),'light','combo',array(
				__('box')=>'light',
				__('raw')=>'raw'
			)
		);
		$w->wwblogneighbours->setting('width',
			__('Width: (for box style)'),'200','text'
		);
		$w->wwblogneighbours->setting('dir',
			__('Direction:'),'both','combo',array(
				__('blog that you linked to')=>'out',
				__('blog that linked to you')=>'in',
				__('both')=>'both'
			)
		);
		$w->wwblogneighbours->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}
	
	public static function adminWikioBlogTopRank($w)
	{
		$w->create('wwblogtoprank',
			__('Wikio : Blog top rank'),array('wikioWorldWidget','publicWikioBlogTopRank')
		);
		$w->wwblogtoprank->setting('title',
			__('Title:'),__('Top rank on Wikio'),'text'
		);
		$w->wwblogtoprank->setting('cat',
			__('Category:'),'high-tech','combo',wikioWorld::topCatCombo()
		);
		$w->wwblogtoprank->setting('homeonly',
			__('Home page only'),1,'check'
		);
	}
	
	public static function adminWikioEntryShare($w)
	{
		$w->create('wwentryshare',
			__('Wikio : Blog entry share'),array('wikioWorldWidget','publicWikioEntryShare')
		);
		$w->wwentryshare->setting('title',
			__('Title:'),__('Share'),'text'
		);
		$w->wwentryshare->setting('button',
			__('Style:'),'','combo',array(
				__('interactive')=>'',
				__('plain')=>'plain',
				__('rounded')=>'rounded',
				__('rounded open')=>'rounded-open',
				__('plain blue') => 'plain-blue',
				__('rounded blue') => 'rounded-blue',
				__('rounded open blue') => 'rounded-open-blue'
			)
		);
		$w->wwentryshare->setting('srvwikio',
			sprintf(__('Add %s service for interactive button only'),'Wikio-share'),1,'check'
		);
		$w->wwentryshare->setting('srvdigg',
			sprintf(__('Add %s service for interactive button only'),'Digg'),1,'check'
		);
		$w->wwentryshare->setting('srvdelicious',
			sprintf(__('Add %s service for interactive button only'),'Delicious'),1,'check'
		);
		$w->wwentryshare->setting('srvfacebook',
			sprintf(__('Add %s service for interactive button only'),'Facebook'),1,'check'
		);
		$w->wwentryshare->setting('srvtwitter',
			sprintf(__('Add %s service for interactive button only'),'Twitter'),1,'check'
		);
		$w->wwentryshare->setting('srvlive',
			sprintf(__('Add %s service for interactive button only'),'Live-share'),1,'check'
		);
		$w->wwentryshare->setting('srvmyspace',
			sprintf(__('Add %s service for interactive button only'),'MySpace'),1,'check'
		);
		$w->wwentryshare->setting('srvyahoobookmarks',
			sprintf(__('Add %s service for interactive button only'),'Yahoo-bookmarks'),1,'check'
		);
		$w->wwentryshare->setting('srvgooglebookmarks',
			sprintf(__('Add %s service for interactive button only'),'Google-bookmarks'),1,'check'
		);
		$w->wwentryshare->setting('srvblogmarks',
			sprintf(__('Add %s service for interactive button only'),'Blogmarks'),1,'check'
		);
		$w->wwentryshare->setting('srvtechnorati',
			sprintf(__('Add %s service for interactive button only'),'Technorati'),1,'check'
		);
		$w->wwentryshare->setting('srvmisterwong',
			sprintf(__('Add %s service for interactive button only'),'MisterWwong'),1,'check'
		);
		$w->wwentryshare->setting('srvnewsvine',
			sprintf(__('Add %s service for interactive button only'),'NewsVine'),1,'check'
		);
		$w->wwentryshare->setting('srvreddit',
			sprintf(__('Add %s service for interactive button only'),'Reddit'),1,'check'
		);
		$w->wwentryshare->setting('srvviadeo',
			sprintf(__('Add %s service for interactive button only'),'Viadeo'),1,'check'
		);
		$w->wwentryshare->setting('srvnetvibes',
			sprintf(__('Add %s service for interactive button only'),'Netvibes-share'),1,'check'
		);
		$w->wwentryshare->setting('srvidentica',
			sprintf(__('Add %s service for interactive button only'),'Identica'),1,'check'
		);
	}
	
	public static function adminWikioEntryVote($w)
	{
		$w->create('wwentryvote',
			__('Wikio : Blog entry vote'),array('wikioWorldWidget','publicWikioEntryVote')
		);
		$w->wwentryvote->setting('title',
			__('Title:'),__('Vote'),'text'
		);
		$w->wwentryvote->setting('style',
			__('Style:'),0,'combo',array(
				__('normal')=>'normal',
				__('compact')=>'compact'
			)
		);
	}
	
	public static function publicWikioNews($w)
	{
		global $core;
		
		$core->blog->settings->addNamespace('wikioWorld');
		
		if ($w->homeonly && $core->url->type != 'default') { return; }
		if (!$core->blog->settings->wikioWorld->wikioWorld_active) { return; }

		$u = '';
		if ($w->fcolor) $u .= '&fcolor='.$w->fcolor;
		if ($w->bgcomment) $u .= '&bgcomment='.$w->bgcomment;
		if (!$w->target) $u .= '&target=1';
		if ($w->summary) $u .= '&s=1';
		if ($w->theme) $u .= '&t=1';
		if ($w->podcast) $u .= '&p=1';
		if ($w->comments) $u .= 'c=1&m='.abs((integer) $w->comments);
		
		$res = 
		'<script type="text/javascript" src="http://external.wikio.fr/index.html?'.
		'id=3992'.
		'&lang='.$core->blog->settings->system->lang.
		'&count='.abs((integer) $w->count).
		'&size='.abs((integer) $w->size).
		$u.'"></script>'.
		'<noscript><a href="http://www.wikio.fr">www.wikio.fr</a></noscript>';
		
		return wikioWorld::widgetBox('news',$w->title,$res);
	}
	
	public static function publicWikioBlogRSS($w)
	{
		global $core;
		
		$core->blog->settings->addNamespace('wikioWorld');
		
		if ($w->homeonly && $core->url->type != 'default') { return; }
		if (!$core->blog->settings->wikioWorld->wikioWorld_active) { return; }
		
		$url = wikioWorld::cleanURL($core->blog->url.$core->url->getBase("feed").'/atom');
		
		# Interactive
		if ('' == $w->button)
		{
			$srv = array();
			if ($w->srvwikio) $srv[] = 'wikio';
			if ($w->srvnetvibes) $srv[] = 'netvibes';
			if ($w->srvgoogle) $srv[] = 'google';
			if ($w->srvyahoo) $srv[] = 'yahoo';
			if ($w->srvbloglines) $srv[] = 'bloglines';
			if ($w->srvaol) $srv[] = 'aol';
			if ($w->srvmsn) $srv[] = 'msn';
			if ($w->srvnewsgator) $srv[] = 'newsgator';
			if ($w->srvpageflakes) $srv[] = 'pageflakes';
			if ($w->srvlive) $srv[] = 'live';
			if ($w->srvwebwag) $srv[] = 'webwag';
			if ($w->srvrss) $srv[] = 'rss';
			
			if (empty($srv)) { return; }
			
			$res = 
			'<a target="_blank" href="http://www.wikio.fr/subscribethis?'.
			'url='.$url.'" class="wikio-popup-button">Wikio</a>'.
			'<script type="text/javascript" src="http://www.wikio.fr/wikiothispopupv2?'.
			'services='.implode('+',$srv).'&widgets=&url='.$url.'"></script>';
		}
		# classic
		else
		{
			$res = 
			'<a target="_blank" href="http://www.wikio.fr/subscribethis?'.'url='.$url.'">'.
			'<img src="http://www.wikio.fr/shared/images/wikiothis/buttons/wikio_btn_abo-univ_'.
			$w->button.'_'.$core->blog->settings->system->lang.
			'.gif" style="border: none;" alt="http://www.wikio.fr"/></a>';
		}
		
		return wikioWorld::widgetBox('blogrss',$w->title,$res);
	}
	
	public static function publicWikioBlogAddWikio($w)
	{
		global $core;
		
		$core->blog->settings->addNamespace('wikioWorld');
		
		if ($w->homeonly && $core->url->type != 'default') { return; }
		if (!$core->blog->settings->wikioWorld->wikioWorld_active) { return; }
		
		$res = 
		'<a href="http://www.wikio.fr/subscribe?url='.
		wikioWorld::cleanURL($core->blog->url.$core->url->getBase("feed").'/atom')
		.'">'.
		'<img src="http://www.wikio.fr/shared/images/add-rss.gif" '.
		'style="border: none;" alt="http://www.wikio.fr"/></a>';
		
		return wikioWorld::widgetBox('addwikio',$w->title,$res);
	}
	
	public static function publicWikioBlogBacklinks($w)
	{
		global $core;
		
		$core->blog->settings->addNamespace('wikioWorld');
		
		if ($w->homeonly && $core->url->type != 'default') { return; }
		if (!$core->blog->settings->wikioWorld->wikioWorld_active) { return; }
		
		$s = $w->style;
		$u = wikioWorld::cleanURL($core->blog->url);
		$t = urlencode($core->blog->name);
		$c = $core->blog->settings->system->lang;
		$l = abs((integer) $w->width);
		if ($l == 0) $l = 200;
		$r = abs((integer) $w->content);

		$res = 
		'<a href="http://www.wikio.fr" class="wikio-bl-source">Widget Backlinks par Wikio!</a>'.
		'<script type="text/javascript" src="http://widgets.wikio.fr/js/source/backlinks?'.
		'style='.$s.'&country='.$c.'&width='.$l.'&content='.$r.'&url='.$u.'&title='.$t.'" charset="utf-8"></script>';
		
		return wikioWorld::widgetBox('backlinks',$w->title,$res);
	}
	
	public static function publicWikioBlogNeighbours($w)
	{
		global $core;
		
		$core->blog->settings->addNamespace('wikioWorld');
		
		if ($w->homeonly && $core->url->type != 'default') { return; }
		if (!$core->blog->settings->wikioWorld->wikioWorld_active) { return; }
	
		$s = $w->style;
		$u = wikioWorld::cleanURL($core->blog->url);
		$t = urlencode($core->blog->name);
		$c = $core->blog->settings->system->lang;
		$l = abs((integer) $w->width);
		if ($l == 0) $l = 200;
		$d = $w->dir;

		$res = 
		'<a href="http://www.wikio.fr" class="wikio-neighbours">Widget blogroll par Wikio!</a>'.
		'<script type="text/javascript" src="http://widgets.wikio.fr/js/source/neighbours?'.
		'style='.$s.'&country='.$c.'&width='.$l.'&dir='.$d.'&url='.$u.'&title='.$t.'" charset="utf-8"></script>';
		
		return wikioWorld::widgetBox('neighbours',$w->title,$res);
	}
	
	public static function publicWikioBlogTopRank($w)
	{
		global $core;
		
		$core->blog->settings->addNamespace('wikioWorld');
		
		if ($w->homeonly && $core->url->type != 'default') { return; }
		if (!$core->blog->settings->wikioWorld->wikioWorld_active) { return; }
		
		if ('' == $w->cat) { return; }
		$cat = wikioWorld::cleanURL($w->cat);
		
		$res = 
		'<a href="http://www.wikio.fr/blogs/top/'.$cat.'">'.
		'<img src="http://external.wikio.fr/blogs/top/getrank?url='.
		wikioWorld::cleanURL($core->blog->url).'&cat='.$cat.
		'" border=0 alt="Wikio - Top des blogs"/></a>';
		
		return wikioWorld::widgetBox('addwikio',$w->title,$res);
	}
	
	public static function publicWikioEntryShare($w)
	{
		global $core, $_ctx;
		
		$core->blog->settings->addNamespace('wikioWorld');
		
		if ('post.html' != $_ctx->current_tpl) { return; }
		if (!$core->blog->settings->wikioWorld->wikioWorld_active) { return; }
		
		$url = wikioWorld::cleanURL($_ctx->posts->getURL());
		$title = urlencode($core->blog->name.' - '.$_ctx->posts->post_title);
		
		# Interactive
		if ('' == $w->button)
		{
			$srv = array();
			if ($w->srvwikio) $srv[] = 'wikio-share';
			if ($w->srvdigg) $srv[] = 'digg';
			if ($w->srvdelicious) $srv[] = 'delicious';
			if ($w->srvfacebook) $srv[] = 'facebook';
			if ($w->srvtwitter) $srv[] = 'twitter';
			if ($w->srvlive) $srv[] = 'live-share';
			if ($w->srvmyspace) $srv[] = 'myspace';
			if ($w->srvyahoobookmarks) $srv[] = 'yahoobookmarks';
			if ($w->srvgooglebookmarks) $srv[] = 'googlebookmarks';
			if ($w->srvblogmarks) $srv[] = 'blogmarks';
			if ($w->srvtechnorati) $srv[] = 'technorati';
			if ($w->srvmisterwong) $srv[] = 'misterwong';
			if ($w->srvnewsvine) $srv[] = 'newsvine';
			if ($w->srvreddit) $srv[] = 'reddit';
			if ($w->srvviadeo) $srv[] = 'viadeo';
			if ($w->srvnetvibes) $srv[] = 'netvibes-share';
			if ($w->srvidentica) $srv[] = 'identica';
			
			if (empty($srv)) { return; }
			
			$res = 
			'<a href="http://www.wikio.fr/sharethis?'.
			'url='.$url.'&title='.$title.'" class="wikio-share-popup-button">Wikio</a>'.
			'<script type="text/javascript" src="http://www.wikio.fr/sharethispopupv2?'.
			'services='.implode('+',$srv).'&url='.$url.'&title='.$title.'"></script>';
		}
		# classic
		else
		{
			$ext = in_array($w->button,array('rounded','plain')) ? '.gif' : '.png';
			$res = 
			'<a target="_blank" href="http://www.wikio.fr/sharethis?'.
			'url='.$url.'&title='.$title.'">'.
			'<img src="http://www.wikio.fr/shared/images/wikiothis/buttons/wikio_btn_partager_'.
			$w->button.'_'.$core->blog->settings->system->lang.$ext.
			'" style="border: none;" alt="http://www.wikio.fr"/></a>';
		}
		
		return wikioWorld::widgetBox('entryshare',$w->title,$res);
	}
	
	public static function publicWikioEntryVote($w)
	{
		global $core, $_ctx;
		
		$core->blog->settings->addNamespace('wikioWorld');
		
		if ('post.html' != $_ctx->current_tpl) { return; }
		if (!$core->blog->settings->wikioWorld->wikioWorld_active) { return; }
		
		$res = wikioWorld::buttonEntryVote($_ctx->posts->getURL(),$w->style);
		
		if (!$res) { return; }
		
		return wikioWorld::widgetBox('entryvote',$w->title,$res);
	}
}
?>