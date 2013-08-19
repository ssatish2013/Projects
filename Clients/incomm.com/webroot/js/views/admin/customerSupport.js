
var resultsDiv = $("#csResults"), wrap = PF.admin.section, buttons = PF.admin.section
		.find(".buttons"), reloadGift = function() {
	$.ajax({
		url : "/admin/customerSupport",
		type : "post",
		dataType : "json",
		beforeSubmit : function() {
			buttons.addClass("loading");
		},
		data : {
			action : "loadGift",
			giftId : $('#giftId').val()
		},
		success : ajaxSuccess
	});
}, vipStatusChange = function() {
	$this = $(this);
	email = $this.data('email');
	emailStatus = $this.val();
	$this.closest('.buttons').addClass('loading');
	$.ajax({
		url : "/admin/customerSupport",
		type : "post",
		dataType : "json",
		data : {
			action : 'changeEmailStatus',
			email : $this.data('email'),
			emailStatus : $this.val()
		},
		success : function() {
			$.publish("/admin/ajaxStatus", [ 'success',
					'Changed VIP Status!' ]);
			$this.closest('.buttons').removeClass(
					'loading');
		}
	});

}, ajaxSuccess = function(json) {
	try {
		if (json.gift) {
			PF.template.render("customerSupportGift",
					json, showGift);

			// set any vip statuses
			if (json.recipientVipStatus) {
				$('#recipientVipStatus').val(
						json.recipientVipStatus);
			}

			$.each(json.messages, function(i, msg) {
				if (msg.emailVipStatus) {
					$('#' + msg.id + 'VipStatus').val(
							msg.emailVipStatus);
				}
			})

			// attach events to all vip status selects
			wrap.delegate('.emailVipStatus', 'change',
					vipStatusChange);
		} else if ($.isArray(json)) {
			if (json.length) {
				PF.template.render(
						"customerSupportGiftList",
						json, showGift);
			} else {
				PF.template.render(
						"customerSupportNoResults",
						json, showGift);
			}
		}

		// if there are no actions available, show the
		// user
		actionsHeader = $("#actionsHeader");
		if (!actionsHeader.siblings().length) {
			actionsHeader
					.after('<li>There are no actions available for this gift</li>');
		}
	} catch (err) {
		PF.template.render(
				"customerSupportErrorResults", json,
				showGift);
		try {
			console.dir(err);
		} catch (e) { /*
						 * in case the browser doesn't
						 * support console
						 */
		}
	}
}, showGift = function(html) {
	resultsDiv.html(html);
	buttons.removeClass("loading");
	$('#csOverlay').hide();
};


// Load single gifts
wrap.delegate(".loadGift", "click", function() {

var $this = $(this);
$('#customerSupportForm .buttons')
.addClass("loading");
$('#csOverlay').show();
$.ajax({
	url : "/admin/customerSupport",
type : "post",
dataType : "json",
data : {
	action : "loadGift",
giftId : $this.data("giftId")
		},
		success : ajaxSuccess
	});

});














// Preload templates
$.publish("/template/preload",
[ [ "customerSupportGift" ] ]);

// Main search form submit
wrap.delegate("form", "submit", function() {

var $form = $(this);

$form.ajaxSubmit({
	cache : false,
	dataType : "json",
beforeSubmit : function() {
	buttons.addClass("loading");
$('#csOverlay').show();
		},
		success : ajaxSuccess
	});

	return false;
});

// setup action buttons
wrap
		.delegate(
				".actionButton",
"click",
function() {
	$this = $(this);
	$this
			.closest('.buttons')
			.addClass('loading');
	method = $this.attr('id');
	$
			.ajax(
					'/admin/customerSupport',
					{
						// data
						// passed
						// in
						dataType : "json",
						data : {
							giftId : $(
									'#giftId')
									.val(),
							resendEmail : $(
									'#resendEmail')
									.val(),
							action : method
						},

						// success
						success : function(
								json) {
							// success!
							// reload
							// the
							// page...
							// this
							// is
							// just
							// a
							// quick
							// fix
							// for
							// now.
							message = "Success!";
							messageStatus = 'error';
							if (json.message) {
								message = json.message;
							}
							if (json.status) {
								messageStatus = json.status;
							}
							$
									.publish(
											"/admin/ajaxStatus",
											[
													messageStatus,
													message ]);
							reloadGift();
							// $("#customerSupportForm").submit();
						},

						// error
						error : function() {
							// probbaly
							// need
							// some
							// better
							// error
							// handling
							alert('error');
						},

						// misc
						// vars
						type : "post",
						cache : false
					});
});

wrap.delegate(".refundmsgbtn", "click", function() {
	var container = $(this).closest('.buttons'),
	messageId = container.data('messageid');
	container.addClass('loading');


	$.ajax(
			'/admin/customerSupport',
			{
				dataType : "json",
				data : {
					messageId : messageId,
					action : 'refundMessage'
				},

				// success
				success : function(json) {
					message = "Success!";
					messageStatus = 'error';
					if (json.message) {
						message = json.message;
					}
					if (json.status) {
						messageStatus = json.status;
					}
					$.publish(
							"/admin/ajaxStatus",
							[messageStatus,
							message]);
					reloadGift();
				},
				// error
				error : function() {
					alert('error');
				},
				type : "post",
				cache : false
			});

});
