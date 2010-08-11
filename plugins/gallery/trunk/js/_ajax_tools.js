
var queuedManager = $.manageAjax.create('queued', { 
	queue: true,  
	cacheResponse: false,
	maxRequests: dotclear.maxajaxrequests
}); 



function requestDisplay(id) {
	this.id=id;
	this.component=$('#'+id);
	this.currentLine=0;
}

requestDisplay.prototype = {
	waitingImage: '<img src="index.php?pf=gallery/progress.gif"/>',

	addLine: function(desc)  {
		this.currentLine++;
		var lineId=this.id+"_"+this.currentLine;
		this.component.append('<tr id="'+lineId+'"><td class="desc">'+
			desc+'</td><td class="res processing">'+this.waitingImage+	'</td></tr>');
		return this.currentLine;
	},

	setResult: function(data,id) {
		var res="#"+this.id+"_"+id+" .res";
		$(res).removeClass("processing");
		if (data instanceof String) {
			$(res).html('<img src="images/check-off.png" alt="KO" /> '+data);
		} else if ($(data).find('rsp').attr('status') == 'ok') {
			$(res).html('<img src="images/check-on.png" alt="OK" />');
		} else {
			$(res).html('<img src="images/check-off.png" alt="KO" /> '+$(data).find('message').text());
		}
}
}