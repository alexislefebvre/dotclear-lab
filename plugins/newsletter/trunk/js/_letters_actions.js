$(document).ready(function(){
	
function processSendALetter(data) {
	var retrieve = retrieves[currentRetrieve-1];
	var subscribers=$(data).find('subscriber');
	if ($(data).find('rsp').attr('status') == 'ok') {
		$("#"+retrieve.line_id).html('<img src="images/check-on.png" alt="OK" />&nbsp;'+dotclear.msg.subscribers_found.replace(/%s/,subscribers.length));
		subscribers.each(function() {
			var sub_id=$(this).attr('id');
			var sub_email=$(this).attr('email');
			var sub_letter_id=$(this).attr('letter_id');
			var sub_letter_title=$(this).attr('letter_title');
			var action_id = addLine(processid,dotclear.msg.subject+" : "+sub_letter_title,"=> "+sub_email,dotclear.msg.please_wait);
			actions.push ({line_id: action_id, params: {f: "sendLetter", letterId: sub_letter_id}});
		});
	} else {
		$("#"+retrieve.line_id).html('<img src="images/check-off.png" alt="KO" /> '+$(data).find('message').text());
	}	
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
retrieves=[]

for (var i=0; i<letters.length; i++) {
	var action_id = addLine(requestid,dotclear.msg.search_subscribers_for_letter,'id='+letters[i], dotclear.msg.please_wait);
	retrieves.push({line_id: action_id, request: {f: 'letterGetSubscribersUp', letterId: letters[i]}, callback: processSendALetter});
}		
doProcess();

});
