window.PF = $.extend( true, window.PF || {}, {
	inviteGuest : (function() {
		return {
			splitEmail : function (input){
				if(input || input.length>0){
					var ret = [] , firstpass = input.split('\n');
					for(var line in firstpass){
						var emails = firstpass[line].split(',');
						for(var e in emails){
							var email = $.trim(emails[e]);
							if (PF.inviteGuest.validateEmail(email))
								ret.push(email);
						}
					}
					return ret;
				}
				else{
					return [];
				}
			},
			validateEmail : function (value){
				return /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(value)
			},
			storeEmail : function (emails){
				if (!PF.guestEmails){
					PF.guestEmails = {};
				}
				$.map(emails,function(e){
					PF.guestEmails[e] = e;
				});
			},
			renderList : function (){
				if (!PF.guestEmails) return;
				$('.guestlist').html('');
				var count=0;
				for(var e in PF.guestEmails){
					var row = $('<tr><td>'+PF.guestEmails[e]+'</td><td><a class="removeGuest" href="#" data-idx="'+e+'">Delete</a></td></tr>');
					$('.guestlist').append(row);
					count++;
				}
				$('.guestlistcount').html(count);
				PF.guestEmailsCount = count;
			}
		}
	})(),
	inviteGuestInit: function(){
		var test = function(){
			var emails = PF.inviteGuest.splitEmail($('.guestinput').val());
			$('.guestcount').html(emails.length);
		};
		$('.guestinput').keyup(test);
		$('.guestinput').click(test);
		$('.btnAddGuest').click(function(event){
			event.preventDefault();
			var emails = PF.inviteGuest.splitEmail($('.guestinput').val());
			PF.inviteGuest.storeEmail(emails);
			PF.inviteGuest.renderList();
			$('.guestinput').val('').click();
		});
		$('.guestlist').delegate('.removeGuest','click',function(event){
			event.preventDefault();
			delete PF.guestEmails[$(this).data('idx')];
			PF.inviteGuest.renderList();
		});

		$('.opensendform').click(function(event){
			event.preventDefault();
			if (PF.guestEmailsCount){
				//send emails
				var div = $('#invite'),
				temp = [];
				$.map(PF.guestEmails,function(e){
					temp.push(e);
				});

				$.post('/gift/success',
						{	giftGuid:div.data("gift-guid"),
							senderName:div.data("sender-name"),
							addresses:temp.join()
						},
						function(response){
							if (response && response.success){
								$('.sendinviteformresult').mdialog('show');
							}
						},'json');
			}
			else{
				$('.sendinviteformwarning').mdialog('show');
			}
		});

		$('#btninvitefacebook').click(function(event){
			 event.preventDefault();
			 var div = $('#invite');
			 $.publish( "/facebook/inviteFriends", [{
						'guid':div.data("gift-guid"),
						'senderName':div.data("sender-name"),
						'recipientName':div.data("gift-recipient"),
						'designSrc':div.data("design-src"),
						'partnerName':div.data("partner-name"),
						'contributeUrl':div.data("contribute-url"),
						'inviteTitle':div.data("invite-title"),
						'inviteDescription':div.data("invite-description"),
						'shareTitle':div.data("share-title"),
						'shareDescription':div.data("share-description"),
						'twitterTitle':div.data("twitter-title"),
						'twitterDescription':div.data("twitter-description")
						}, function( response ) {
							     if ( response && response.request_ids ) {
							    	 $('.sendinviteformresult').mdialog('show');
							     }
						}]);
		});

		$('#btnsharefacebook').click(function(event){
			 event.preventDefault();
			 var div = $('#invite');
			 $.publish( "/facebook/share", [ {
						'guid':div.data("gift-guid"),
						'senderName':div.data("sender-name"),
						'recipientName':div.data("gift-recipient"),
						'designSrc':div.data("design-src"),
						'partnerName':div.data("partner-name"),
						'contributeUrl':div.data("contribute-url"),
						'inviteTitle':div.data("invite-title"),
						'inviteDescription':div.data("invite-description"),
						'shareTitle':div.data("share-title"),
						'shareDescription':div.data("share-description"),
						'twitterTitle':div.data("twitter-title"),
						'twitterDescription':div.data("twitter-description")
						}, function( response ) {
								if ( response && response.request_ids ) {
									 $('.sendinviteformresult').mdialog('show');
								}
						}]);
		});

		$('#btninvitetwitter').click(function(event){
			 event.preventDefault();
			 var div = $('#invite');
			 $.publish( "/twitter/invite", [ {
						'guid':div.data("gift-guid"),
						'senderName':div.data("sender-name"),
						'recipientName':div.data("gift-recipient"),
						'designSrc':div.data("design-src"),
						'partnerName':div.data("partner-name"),
						'contributeUrl':div.data("contribute-url"),
						'inviteTitle':div.data("invite-title"),
						'inviteDescription':div.data("invite-description"),
						'shareTitle':div.data("share-title"),
						'shareDescription':div.data("share-description"),
						'twitterTitle':div.data("twitter-title"),
						'twitterDescription':div.data("twitter-description")
			 			}, function( response ) {
								if ( response && response.request_ids ) {
									$('.sendinviteformresult').mdialog('show');
								}
	 					}]);
		});

		$('#allowInvite').change(function(event){
			var giftguid = $('#invite').data('gift-guid'),
			checkbox = $(this),
			checked = checkbox.is(':checked');
			$.ajax({
				url:'/gift/allowGuestInvite',
				type: 'POST',
				dataType: 'json',
				data: {giftGuid:giftguid,value:checked?1:0}
			}).done(function(response){
						if ( response && response.success){
							//do nothing if success
						}
						else{
							//return to previous state if ajax call failed.
							checkbox.prop('checked',!checked);
						}
			}).fail(function(){
						//return to previous state if ajax call failed.
						checkbox.prop('checked',!checked);
			});
		});

		$('.sendinviteformresult').mdialog ({
			showheader: false,
			showcancelbtn: false,
			centre: true,
			overflow:'auto',
			top:'250px',
			width: '500px',
			height:'150px',
                        hide: (function(mdialog) {
                                document.location.href = $("a#backarrowlink").attr("href");
                        }),
                        okclick: function() {
                                document.location.href = $("a#backarrowlink").attr("href");
                        }
		});

		$('.sendinviteformwarning').mdialog ({
			showheader: false,
			showcancelbtn: false,
			centre: true,
			overflow:'auto',
			top:'250px',
			width: '500px',
			height:'150px'
		});

		$('#contributionLink input').bind('click', function(){
			$(this).select();
		});

		//IE8 fixes for the checkboxes
		if ($.browser.msie && parseInt($.browser.version, 10)==8){
			$('input[type="checkbox"] + label').each(function(idx,item){
				$(item).click(function(){
					var checkbox = $(this).parent().find('input[type="checkbox"]').first(),
					img =  $(this).find('img').first(),
					currentval = checkbox.prop('checked');
					//set the checkbox
					checkbox.prop('checked',!currentval);
					//switch background
					img.removeClass('checkbox_on checkbox_off');
					img.addClass(currentval?'checkbox_off':'checkbox_on');
					$('#allowInvite').change();
				});
			});
		}
	}
});
