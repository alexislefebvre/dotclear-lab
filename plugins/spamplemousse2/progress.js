function progressUpdate(funcClass, funcMethod, pos, start, stop, baseInc, nonce) {
	params = {
			f: 'postProgress',
			funcClass: funcClass,
			funcMethod: funcMethod,
			pos: pos,
			start: start,
			stop: stop,
			baseInc: baseInc, 
			total_elapsed: 0,
			xd_check: nonce
		};
	$.post('services.php', params, function (data) { update(data); });		
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
			f: 'postProgress'
			};
	params.pos = $(data).find('pos').text();
	params.total_elapsed = $(data).find('total_elapsed').text();
	params.start = $(data).find('start').text();
	params.stop = $(data).find('stop').text();
	params.baseInc = $(data).find('baseinc').text();	
	params.funcClass = $(data).find('funcClass').text();	
	params.funcMethod = $(data).find('funcMethod').text();	
	params.xd_check = $(data).find('nonce').text();

	$.post('services.php', params, function (data) { update(data); });	
}