dotclear.notEvilAdsExpander = function(line) {
	var td = line.firstChild;
	
	var img = document.createElement('img');
	img.src = (dotclear.img_plus_src ? dotclear.img_plus_src : 'images/plus.png');
	img.alt = (dotclear.img_plus_alt ? dotclear.img_plus_alt : 'plus');
	img.className = 'expand';
	$(img).css('cursor','pointer');
	img.line = line;
	img.onclick = function() { dotclear.previewAd(this,this.line); };
	
	td.insertBefore(img,td.firstChild);
};

dotclear.previewAd = function(img,line) {
	var adId = line.id.substr(1);
	
	var tr = document.getElementById('ae'+adId);
	
	document.write = function (str) {
		document.getElementById('nea_comp_'+adId).innerHTML += str;
	};

	if (!tr) {
		tr = document.createElement('tr');
		tr.id = 'ae'+adId;
		var td = document.createElement('td');
		td.colSpan = 5;
		td.className = 'expand';
		tr.appendChild(td);
		
		img.src = (dotclear.img_minus_src ? dotclear.img_minus_src : 'images/minus.png');
		img.alt = (dotclear.img_minus_alt ? dotclear.img_minus_alt : 'minus');
		
		$.get(dotclear.nea_xmlresponsefile,{xd_check: dotclear.nonce,notEvilAdsGetContent: adId},function(data) {
			if (data)
				$(td).append('<div id="nea_comp_'+adId+'"></div>'+data);
		});
		$(line).toggleClass('expand');
		line.parentNode.insertBefore(tr,line.nextSibling);
	}
	else if (tr.style.display == 'none')
	{
		$(tr).toggle();
		document.getElementById('nea_comp_'+adId).innerHTML = '';
		var trdata = tr.innerHTML;
		$(tr).empty();
		$(tr).append(trdata);
		$(line).toggleClass('expand');
		img.src = (dotclear.img_minus_src ? dotclear.img_minus_src : 'images/minus.png');
		img.alt = (dotclear.img_minus_alt ? dotclear.img_minus_alt : 'minus');
	}
	else
	{
		$(tr).toggle();
		$(line).toggleClass('expand');
		img.src = (dotclear.img_plus_src ? dotclear.img_plus_src : 'images/plus.png');
		img.alt = (dotclear.img_plus_alt ? dotclear.img_plus_alt : 'plus');
	}
};

$(function() {
	if (!document.getElementById) { return; }
	
	dotclear.hideLockable();
	
	$(".checkboxes-helpers").each(function() {
		dotclear.checkboxesHelpers(this);
	});
	
	$('#nea-form-list tr.line').each(function() {
		dotclear.notEvilAdsExpander(this);
	});
	
});
