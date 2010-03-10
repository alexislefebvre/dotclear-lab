// init
jQuery(document).ready(function ($) {
/*if (isIE6) {
  jQuery('#page').append("<div class='crap-browser-warning'><?php printf(__("You're using a old and buggy browser. Switch to a <a%1$s>normal browser</a> or consider <a%2$s>upgrading your Internet Explorer</a> to the latest version","mystique"), ' href="http://www.mozilla.com/firefox/"', ' href="http://www.microsoft.com/windows/internet-explorer"'); ?></div>");
}*/
jQuery('#header div>ul').superfish({ autoArrows: true });

webshot("a.websnapr", "webshot");

$('.shareThis ul.bubble').css("width",''+($('ul.bubble li').length*32)+'px');

// layout controls
fontControl("#pageControls", "body", 10, 18);
//pageWidthControl("#pageControls", ".page-content", '100%', '940px', '1200px');
webshot("a.websnapr", "webshot");
jQuery(".post-tabs").minitabs({
  content: '.sections',
  nav: '.tabs',
  effect: 'top',
  speed: 333,
  cookies: false
});

jQuery(".sidebar-tabs").minitabs({
  content: '.sections',
  nav: '.box-tabs',
  effect: 'slide',
  speed: 150
});

jQuery("ul.menuList .cat-item").bubble({
  timeout: 6000
});
jQuery(".shareThis, .bubble-trigger").bubble({
  offset: 16,
  timeout: 0
});

jQuery("#pageControls").bubble({
  offset: 30
});
jQuery('ul.menuList li a').nudge({
  property: 'padding',
  direction: 'left',
  amount: 6,
  duration: 166
});
jQuery('a.nav-extra').nudge({
  property: 'top',
  direction: '',
  amount: -18,
  duration: 166
});

// fade effect
if (!isIE) {
  jQuery('.fadeThis').append('<span class="hover"></span>').each(function () {
	var jQueryspan = jQuery('> span.hover', this).css('opacity', 0);
	jQuery(this).hover(function () {
	  jQueryspan.stop().fadeTo(333, 1);
	},
	function () {
	  jQueryspan.stop().fadeTo(333, 0);
	});
  });
}
jQuery("#footer-blocks.withSlider").loopedSlider();
jQuery("#featured-content.withSlider").loopedSlider({
  autoStart: 10000,
  autoHeight: false
}); // scroll to top
jQuery("a#goTop").click(function () {
  jQuery('html').animate({
	scrollTop: 0
  },
  'slow');
});
jQuery('.clearField').clearField({
  blurClass: 'clearFieldBlurred',
  activeClass: 'clearFieldActive'
});

setup_comments();

if(redirectReadMore) setup_readmorelink();

jQuery('a.print').click(function() {
	jQuery('.post.single').printElement({printMode:'popup'});

	return false;
});

// set accessibility roles on some elements trough js (to not break the xhtml markup)
jQuery("#header div>ul").attr("role", "navigation");
jQuery("#primary-content").attr("role", "main");
jQuery("#sidebar").attr("role", "complementary");
jQuery("#searchform").attr("role", "search");


});
