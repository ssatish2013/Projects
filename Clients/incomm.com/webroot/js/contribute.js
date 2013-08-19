$(document).ready(function(){

	var unsubscribeInviteReminderDialog = $('#unsubscribeInviteReminderDialog');
	// alert the user they have been unsubscribed (if this element exists on the page)
	if(unsubscribeInviteReminderDialog.length) {

		unsubscribeInviteReminderDialog.mdialog({
			title: 'Stop Reminders',
			ioverflowy: 'auto',
			centre: true,
			width: '550px',
			height:'150px',
			showfooter: false
		}).mdialog('show');

	}

});