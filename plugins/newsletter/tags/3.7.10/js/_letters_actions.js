$(document).ready(function(){

function processSendALetter(data) {
	var retrieve = retrieves[currentRetrieve-1];
	
	var letter=$(data).find('letter');
	var subscribers=$(data).find('subscriber');

	if ($(data).find('rsp').attr('status') == 'ok') {
		$("#"+retrieve.line_id).html('<img src="images/check-on.png" alt="OK" />&nbsp;'+dotclear.msg.subscribers_found.replace(/%s/,subscribers.length));
		
		var p_letter_id=$(letter).attr('letter_id');
		var p_letter_subject=$(letter).find('letter_subject').text();
		var p_letter_header=$(letter).find('letter_header').text();
		var p_letter_body=$(letter).find('letter_body').text();
		var p_letter_footer=$(letter).find('letter_footer').text();
		
		subscribers.each(function() {
			var p_sub_id=$(this).attr('id');
			var p_sub_email=$(this).attr('email');
			
			var action_id = addLine(processid,p_letter_id+" : "+dotclear.msg.subject+" : "+p_letter_subject,"=> "+p_sub_email,dotclear.msg.please_wait);

			actions.push ({line_id: action_id, params: {f: "sendLetterBySubscriber", 
				p_sub_id: p_sub_id, 
				p_sub_email: p_sub_email, 
				p_letter_id: p_letter_id, 
				p_letter_subject: p_letter_subject, 
				p_letter_header: p_letter_header,
				p_letter_footer: p_letter_footer,
				p_letter_body: p_letter_body
				}});
		});
	} else {
		$("#"+retrieve.line_id).html('<img src="images/check-off.png" alt="KO" /> '+$(data).find('message').text());
	}	
	doProcess();
}

function statsLetter(data) {
	var retrieve = retrieves[currentRetrieve-1];
	$("#"+retrieve.line_id).html('... not yet available ...&nbsp;');
	doProcess();
}
	
$("input#cancel").click(function() {
	cancel = true;
});

$(processid).empty();
$(requestid).empty();
actions=[];
currentAction=0;
currentRetrieve=0;
nbActions=0;
retrieves=[];

for (var i=0; i<letters.length; i++) {
	var action_id = addLine(requestid,dotclear.msg.search_subscribers_for_letter,'id='+letters[i], dotclear.msg.please_wait);
	retrieves.push({line_id: action_id, request: {f: 'prepareALetter', letterId: letters[i], subscribersId: subscribers.join(',')}, callback: processSendALetter});
	
//	var action2_id = addLine(requestid,"Statistiques sur la lettre d'informations",'id='+letters[i], dotclear.msg.please_wait);	
//	retrieves.push({line_id: action2_id, params: {f: "statsLetter", letterId: letters[i]}, callback: statsLetter});	
}
doProcess();



});
