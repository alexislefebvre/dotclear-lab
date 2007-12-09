$(function() {
	$('#edit-entry').onetabload(function() {
		$('h3.httpredirect').toggleWithLegend($('p.httpredirect'),{
			cookie: 'dcx_httpredirect',
			hide: $('#redirect_url').val() == ''
		});
	});
});
