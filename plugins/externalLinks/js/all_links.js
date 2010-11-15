$(function() {
    $('a[href^="http"]')
      .filter(function() {
	  return (this.hostname && this.hostname!=location.hostname);
	})
      .not(':has(img)')
      .each(function() {
	  if (external_one_link) {
	    $(this)
	      .html($(this).html()+'&nbsp;<img src="'+external_links_image+'" alt="" title="'+external_links_title+'"/>') 
	      .click(function() {
		  window.open($(this).attr('href'));
		  return false;
		});
	  } else {
	    $(this).append('&nbsp;')
	      .append($('<a href="'+$(this).attr('href')+'"><img src="'+external_links_image+'" alt="" title="'+external_links_title+'"/></a>').click(function() {
		    window.open($(this).attr('href'));
		    return false;
		  }));
	  }
	});	  
  });
