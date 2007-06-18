function progressUpdate(funcClass, funcMethod, pos, start, stop, baseInc) {
	params = {
			f: 'getProgress',
			funcClass: funcClass,
			funcMethod: funcMethod,
			pos: pos,
			start: start,
			stop: stop,
			baseInc: baseInc, 
			total_elapsed: 0
		};
		$.get('services.php', params, function (data) { update(data); });		
}

function update(data) {
	if ($(data).find('rsp').attr('status') != 'ok') { return; }

	if ($(data).find('error').length != 0) { 
		alert('error: %s', $(data).find('error').text());
		return;
	}

	if ($(data).find('return').length != 0) { 
		$('#return').show();
		$('#next').hide();		
		return;
	}

	percent = $(data).find('percent').text();
	$('#percent').empty();
	$('#percent').append(percent);

	eta = $(data).find('eta').text();
	$('#eta').empty();
	$('#eta').append('' + eta + ' s');
	
	params = {
			f: 'getProgress'
			};
	params.pos = $(data).find('pos').text();
	params.total_elapsed = $(data).find('total_elapsed').text();
	params.start = $(data).find('start').text();
	params.stop = $(data).find('stop').text();
	params.baseInc = $(data).find('baseinc').text();	
	params.funcClass = $(data).find('funcClass').text();	
	params.funcMethod = $(data).find('funcMethod').text();	

	$.get('services.php', params, function (data) { update(data); });	
}


