var recordTwilioClient = null;
var recordTwilioClientID = "";
var defaultTimeOffset = null;
var newTimeOffset = null;

(function($) {
	$("#timeHour").keyup( function() {
		var dd = $("#date_dd").val();
		var mm = $("#date_mm").val();
		var yyyy = $("#date_yyyy").val();

		var hour = $(this).val();

		if(!isNaN(hour)) {
			if(parseInt(hour, 10) >= 12) {
				$("select[name='timeShift'] > option[value='PM']").attr("selected", true);
			} else {
				$("select[name='timeShift'] > option[value='AM']").attr("selected", true);
			}

			$("#timeHour").rules("add", {
				"min" : false,
				"max" : false
			});

			if((parseInt($(this).val(), 10) < 0) || (parseInt($(this).val(), 10) > 23)) {
				$("#timeHour").rules("add", {
					"min" : function() { return "00"; },
					"max" : function() { return "23"; }
				});
			} else {
				checkTime();
			}
		} else {
			$("#timeHour").rules("add", {
				"number" : true
			});
		}
	});

	$("select[name='timeMin']").change( function() {
			if( $("#isToday").val() == "1" ) {
				var timeHour   = ( !isNaN($("#timeHour").val()) ) ? $("#timeHour").val() : "24";
				var nowHour    = new Date().getHours();
				var nowMinute  = new Date().getMinutes();
				var newMinute;

				if(nowMinute%15 !== 0) {
					newMinute = ( nowMinute - (nowMinute%15) ) + 15;
				} else {
					newMinute = nowMinute;
				}

				if(timeHour == nowHour) {
					var selectedMin = parseInt($("select[name='timeMin'] > option:selected").val(), 10);
					if( selectedMin < newMinute ) {
						if(newMinute != 60) {
							$("select[name='timeMin'] > option[value='" + newMinute + "']").attr("selected", true);
						} else {
							newMinute = "00";
							if(timeHour == 24) { timeHour = 0; }
							newHour   = parseInt(timeHour, 10) + 1;

							$("select[name='timeMin'] > option[value='" + newMinute + "']").attr("selected", true);
							$("#timeHour").focus();
							setTimeout(function() { $("#timeHour").blur(); }, 30);
							$("#timeHour").rules("add", {
								min : function() { return newHour; },
								messages: {
									min: $("#pastTimeError").val()
								}
							});
						}
					}
				} else {
					// Nothing to do here really :-P
				}
			} else {
				$("#timeHour").focus();
				$("#timeHour").rules("add", {
					"min" : false,
					"max" : false
				});
			}
	});

	$("select[name='timeShift']").change( function() {
		if( $("#timeHour").val() !== "" ) {
		var shift = $("select[name='timeShift'] > option:selected").val();
		var value = (!isNaN($("#timeHour").val()))?parseInt($("#timeHour").val(), 10):"00";

		if(shift == "AM") {
			if( (value > 12) && (value < 24) ) {
				value -= 12;
			}

			if(parseInt(value, 10) == 12) {
				value = "00";
			}
		} else {
			if( (value < 12) && (value > 0) ) {
				value += 12;
			}

			if(parseInt(value, 10) === 0) {
				value = 12;
			}
		}

		$("#timeHour").focus();
		setTimeout(function() {$("#timeHour").blur(); }, 30);
		$("#timeHour").rules("add", {
			"min" : false,
			"max" : false
		});

		$("#timeHour").val(value);
		checkTime();
		}
	});

	$("select[name='timeZone']").change( function() {
		$("select[name=timeZone]").rules("remove", "conditional");
		$("#timeHour").focus();

		if( ($("#timeHour").val() !== "") && (!isNaN($("#timeHour").val())) && ($(this).val() !== "") ) {
			var zone    = $("select[name='timeZone'] > option:selected").val();
			var newOffset = parseInt($("select[name='timeZone'] > option:selected").attr("data-offset"), 10) * -1;
			var oldOffset = parseInt($("select[name='timeZone'] > option[value='" + $("#currentTimeZone").val() + "']").attr("data-offset"), 10) * -1;
			var time    = isNaN($("#timeHour").val()) ? 0 : parseInt($("#timeHour").val(), 10);
			var diff;

			if( $("#timeZoneOffset").val() === "") {
				$("#timeZoneOffset").val(newOffset);
			} else {
				oldOffset = parseInt($("#timeZoneOffset").val(), 10);
			}

			diff = oldOffset - newOffset;
			diff = isNaN(diff) ? 0 : diff;

			time += diff;
			if(time >= 24) {
				time -= 24;
				$("select[name='timeShift'] > option[value='AM']").attr("selected", "selected");
			}
			if(time < 0) {
				time = 24 + time;
				$("select[name='timeShift'] > option[value='PM']").attr("selected", "selected");
			}
			if(time < 10) {
				time = "0" + time;
			}
			$("#timeHour").val(time);

			setNewOffset(newOffset);

			var nowHour = new Date().getHours();
			nowHour += parseInt(defaultTimeOffset, 10) + parseInt(newTimeOffset * -1, 10);

			$("#timeHour").rules("add", {
				min : function() { return nowHour; },
				messages: {
					min: $("#pastTimeError").val()
				}
			});

			$("#timeZoneOffset").val(newOffset);

			if( $("#isToday").val() == "1" ) {
				checkTime();
			} else {
				$("select[name=timeZone]").focus();
				$("select[name=timeZone]").rules("add", "conditional");

				$("#timeHour").focus();
				$("#timeHour").rules("add", {
					"min" : false,
					"max" : false
				});
			}
		}
	});

})($);


(function(){

	$('#deliveryMethodNone').bind('click', function(){ setDeliveryMethod('none'); });
	$('#deliveryMethodFacebook').bind('click', function(){ setDeliveryMethod('facebook'); });
	$('#deliveryMethodTwitter').bind('click', function(){ setDeliveryMethod('twitter'); });
	$('#deliveryMethodMobile').bind('click', function(){ setDeliveryMethod('mobile'); });
	$('#deliveryMethodEmail').bind('click', function(){ setDeliveryMethod('email'); });
	$('#deliveryMethodPhysical').bind('click', function(){ setDeliveryMethod('physical'); });

	var setDeliveryMethod = function(deliveryMethod){

		var lblFacebook = $('#deliveryFacebook');
		var lblTwitter = $('#deliveryTwitter');
		var lblMobile = $('#deliveryMobile');
		var lblPhysical = $('#deliveryPhysical');
		var lblEmail = $('#deliveryEmail');

		var btnNone = $('#deliveryMethodNone');
		var btnFacebook = $('#deliveryMethodFacebook');
		var btnTwitter = $('#deliveryMethodTwitter');
		var btnMobile = $('#deliveryMethodMobile');
		var btnPhysical = $('#deliveryMethodPhysical');
		var btnEmail = $('#deliveryMethodEmail');

		var facebook = PF.deliveryMethod['social'];
		var twitter = PF.deliveryMethod['twitter'];
		var mobile = PF.deliveryMethod['mobile'];
		var physical = PF.deliveryMethod['physical'];
		var email = PF.deliveryMethod['email'];

		lblFacebook.css('display', 'none');
		lblTwitter.css('display', 'none');
		lblMobile.css('display', 'none');
		lblEmail.css('display', 'none');
		lblPhysical.css('display', 'none');

		btnNone.removeAttr('checked');
		btnFacebook.removeAttr('checked');
		btnTwitter.removeAttr('checked');
		btnMobile.removeAttr('checked');
		btnEmail.removeAttr('checked');
		btnPhysical.removeAttr('checked');

		switch(deliveryMethod){
			case 'facebook':
				//don't run facebook method setup if this is not the create page
				if (btnFacebook.length==0){
					return;
				}
				lblFacebook.css('display', 'block');
				btnFacebook.attr('checked', 'checked');
				facebook.setup();
				twitter.teardown();
				mobile.teardown();
				email.teardown();
				physical.teardown();
				break;
			case 'twitter':
				lblTwitter.css('display', 'block');
				btnTwitter.attr('checked', 'checked');
				twitter.setup();
				facebook.teardown();
				mobile.teardown();
				email.teardown();
				physical.teardown();
				break;
			case 'mobile':
				lblMobile.css('display', 'block');
				btnMobile.attr('checked', 'checked');
				mobile.setup();
				facebook.teardown();
				twitter.teardown();
				email.teardown();
				physical.teardown();
				break;
			case 'email':
				lblEmail.css('display', 'block');
				btnEmail.attr('checked', 'checked');
				email.setup();
				facebook.teardown();
				twitter.teardown();
				mobile.teardown();
				physical.teardown();
				break;
			case 'physical':
				lblPhysical.css('display', 'block');
				btnPhysical.attr('checked', 'checked');
				physical.setup();
				facebook.teardown();
				twitter.teardown();
				mobile.teardown();
				email.teardown();
				break;
			default:
				btnNone.attr('checked', 'checked');
				facebook.teardown();
				twitter.teardown();
				mobile.teardown();
				email.teardown();
				physical.teardown();
				break;
		}
	};


	window.PF = $.extend( true, window.PF || {}, {
		page : {
			elements : (function() {
				var elems = {
					recipName : $('#recipientFacebook'),
					facebookUID : $('#facebookUID'),
					amountSection : $('.amount'),
					deliveryDate : $(".date")
				};

				elems.recipNameLabel = elems.recipName.siblings('label');
				elems.recipParent = elems.recipName.closest('li');

				return elems;
			})(),
			//custom validaton callback
			validation: {
				fbrequired: function(){
					return ( $('#deliveryMethodFacebook').is(':checked') && $('#facebookUID').val().length > 0 );
					// return $("select[name*='giftDeliveryMethod']").first().val()=='social' && $('#facebookUID').val().length>0;
				},
				twitterRequired: function() {
					return ( $('#deliveryMethodTwitter').is(':checked') && $('#recipientTwitter').val().length > 0 );

					// return ($("select[name*='giftDeliveryMethod']").first().val() == "twitter"
					// && $("#recipientTwitter").val().length > 0);
				},
				validMobileNumber: function() {
					var numbers = $("#recipientPhoneNumber").val();

					return ($('#deliveryMethodMobile').is(':checked') && (/^([0-9]{1,2}\-?)?[0-9]{3}\-?[0-9]{3}\-?[0-9]{4}$/.test(numbers)));

					// return ($("select[name*='giftDeliveryMethod']").first().val() == "mobile"
					// && (/^([0-9]{1,2}\-?)?[0-9]{3}\-?[0-9]{3}\-?[0-9]{4}$/.test(numbers)));
				},
				validTimeZone: function() {
					var timezone = $("select[name=timeZone] > option:selected").val();
					return ( (timezone !== "") ? true : false );
				}
			}
		}
	});

	window.PF = $.extend( true, window.PF || {}, {
		deliveryMethod : (function() {
			var recipName = PF.page.elements.recipName;
			var recipNameLabel = PF.page.elements.recipNameLabel;
			var deliveryDate = PF.page.elements.deliveryDate;

			var _reloadTooltip = function(element) {
				element.tipTip({
					maxWidth: "250px",
					defaultPosition: "right",
					delay: 0,
					edgeOffset: 8
				});
			};

			return {

				social : {
					setup : function() {
						// Initialize friend selector
						PF.facebook.getFriends( PF.friendSelector.initSelector );
						$.subscribe("/facebook/loggedIn", function() {
							$('#deliveryMethodFacebook').data('loggedIn', true);
						});

						$(".facebookName").slideDown().removeClass("hidden");
					},

					teardown : function() {
						if ( recipName.data('autocomplete') ) {
							recipName.autocomplete("destroy").removeClass('selected');
						}

						$('#recipDiv, #recipPic').hide();
						$(".facebookName").slideUp().addClass("hidden");
					}
				},

				email : {
					setup : function() { },

					teardown : function() { }
				},

				physical: {
					setup: function() {
						var isCountryUs = ($("#recipientCountry").val() == "US");
						var methodCon = $("ul.deliveryMethodsSmall, section#deliveryMethod");

						// Change delivery date field label and tooltip text ship date
						deliveryDate.find("label.ship").show();
						deliveryDate.find("label.delivery").hide();
						$(".shippingInfo").removeClass("hidden");
						$(".shippingInfo").slideDown();
						// hide audio/video
						$(".createMultimedia").slideUp ();
						// hide delivery time
						$(".time").slideUp();
					},

					teardown: function() {
						// Change ship date field label and tooltip to delivery date
						deliveryDate.find("label.delivery").show();
						deliveryDate.find("label.ship").hide();
						$(".shippingInfo").addClass("hidden");
						// Hide shipping address fields
						$(".shippingInfo").slideUp();
						// show audio/video
						$(".createMultimedia").slideDown ();
						// hide delivery time
						$(".time").slideDown();
					}
				},

				twitter : {
					setup : function() {
						if(!$("#deliveryMethodTwitter").data('loggedIn')){
							var tw = window.open("/twitter/auth/","_blank","height=300,width=500,menubar=no,toolbar=no,personalbar=no,status=no,dialog=yes");
							tw.focus();
						}
						$(".twitterSection").slideDown().removeClass("hidden");
						if($("#deliveryMethodTwitter").data('loggedIn')) {
							var cache = [];
							$.ajax( "/twitter/followers", {
								"type":"POST",
								"data":{
										"token":$("#twitterToken").val(),
										"secret":$("#twitterSecret").val()
								},
								"dataType":"json",
								"success":function( data, status, xhr ) {
											$.map( data.users, function( item ) {
												cache.push({
													label: item.screen_name,
													value: item.screen_name
												});
											});



											$( "#recipientTwitter" ).autocomplete({
												autoFocus: true,
												minLength: 0,
												source: cache,
												open: function(event, ui) {
													var msg;
													if (data.omitted && data.omitted>0) {
														var pl = data.omitted > 1 ? "s" : "";
														msg = data.omitted+" result"+pl+" omitted";
													}

													if (!data.users || data.users.length === 0){
														msg = "No follower found";
													}

													if (msg){
														$('ul.ui-autocomplete').append("<li class='ui-autocomplete-close' style='font-style:italic;font-size:11px;color:red;text-align:right'>"+msg+"</li>");
														$('.ui-autocomplete-close').click(function(){
															$( "#recipientTwitter" ).autocomplete('close');
														});
													}
												},
												close: function(event, ui) {
													var $this = $(this);
													if ($this.data("ac") === undefined || $this.data("ac") != $this.val()) {
														$this.attr("data-validate-required-conditional", "true");
														$(this).rules("add", "conditional");
													}
													$(this).focus();
												},
												select: function(event, ui) {
													if (("item" in ui) && ("value" in ui.item)) {
														$(this).data("ac", ui.item.value);
														$(this).attr("data-validate-required-conditional", "false");
														$(this).rules("remove", "conditional");
														$(this).blur();
													}
												}
											});

											$( "#recipientTwitter" ).bind('change keyup', function() {
												$(this).autocomplete({
													"search" : function(event, ui) {
														$(this).attr("data-validate-required-conditional", "true");
														$(this).rules("add", "conditional");
													}
												});

												var matches = 0;

												$.map( data.users, function( item ) {
													var val = $( "#recipientTwitter" ).val();
													var result = item.screen_name;

													if(val == result) {
														matches++;
													}
												});

												if(matches == 1) {
													$(this).attr("data-validate-required-conditional", "false");
													$(this).rules("remove", "conditional");
												} else {
													$(this).attr("data-validate-required-conditional", "true");
													errorMsg("recipientTwitter");
												}
											});
								}
							});
						} else {
							$( "#recipientTwitter" ).change(function() {
								$(this).attr("data-validate-required-conditional", "true");
								errorMsg("recipientTwitter");
							});
						}
					},

					teardown : function() {
						$(".twitterSection").slideUp().addClass("hidden");
					}
				},

				mobile: {
					setup: function() {
						$(".mobileNumber").slideDown().removeClass("hidden");
					},

					teardown: function() {
						$(".mobileNumber").slideUp().addClass("hidden");
						$('#recipientPhoneNumber').val('');
					}
				}

			};


		})(),
		friendSelector: {
			initSelector : function( friends ) {
				// Prepare friends array for jQuery autocomplete
				var source = $.map( friends, function( friend ) {
					return {
						label : friend.name,
						uid   : friend.uid,
						pic   : friend.pic_square
					};
				}),

				recipName = PF.page.elements.recipName,
				recipParent = PF.page.elements.recipParent,
				recipPic = PF.page.elements.recipPic || (function() {
					return PF.page.elements.recipPic = $('<img />', {
						id : "recipPic"
					}).appendTo( recipName.closest('li') );
				})(),
				facebookUID = PF.page.elements.facebookUID,
				selected;

				// Initialize autocomplete
				recipName
					.bind('change', function( e, ui ) {
						// if the textbox has changed and is no longer the selected FB user, clear the selection
						if(selected.value !== $(this).val()){
							selected = undefined;
							facebookUID.val('');
							recipPic.hide();
							recipName.removeClass('selected');
						}
					})
					.autocomplete({
						source : source,
						max : 5,
						delay : 60,
						create : function( e, ui ) {
							$.publish("/facebook/autocompleteCreated");
						},
						open : function() {
							// IE display issue fix
							recipName.autocomplete("widget").width( recipName.width() - 2 );
						},
						select : function( e, ui ) {
							$(this).val(ui.item.label);		// update the input field w/the name when the FB item is selected
							selected = ui.item;				// save the currently selected item
							facebookUID.val( ui.item.uid ); // set FB UID input

							// Set and position Facebook pic
							recipPic
								.attr('src', ui.item.pic)
								.css('margin-top', '-24px')
								.css('margin-left', '6px')
								.show();

							recipName.addClass('selected');

							if (!e.keyCode || e.keyCode != 9) {
								// If the user types tab, this would skip an additional form-field, we don't want that, hence the condition above
								recipParent.nextAll().add(":parent + ul > li").filter(function() {
									return $(this).css("display") != "none";
								}).find('input:text').eq(0).focus();
							}
						}
					})
					.data("autocomplete")._renderItem = function(ul, item) {
						if (ul.children().length <= 5) {
							return $("<li><a><div><img src=\"" + item.pic + "\" /></div>" + item.label + "</a></li>")
								.data("item.autocomplete", item)
								.on("mousedown", function(e) {
									e.preventDefault();
								})
								.appendTo(ul);
						} else {
							return false;
						}
					};
			}

		},
		presetFacebookFriend : function() {
			var search = $("input[name=search]");
			if (search.length) {
				setTimeout(function() {
					$.subscribe("/facebook/autocompleteCreated", function() {
						$("#recipientFacebook")
							.val(search.val())
							.autocomplete('search')
							.autocomplete("widget")
							.find('li:first a')
							.trigger("mouseenter")
							.trigger("click");
					});
					$('#deliveryMethodFacebook').attr('selected', true).change();
				}, 10);
			}
		},
		create : function(){
			var timezone    = jstz.determine();
			var currentTz   = $.trim(timezone.name());
			$("select#timeZone option[value='" + currentTz  + "']").attr("selected", true);
			$("#currentTimeZone").val(currentTz);

			defaultTimeOffset = parseInt($("select[name='timeZone'] > option[value='" + $("#currentTimeZone").val() + "']").attr("data-offset"), 10) * -1;

			var deliveryMethod = $('#deliveryMethod');
			// Preload ajax loader image
			$.publish('/preload/add', ['//gc-pf.s3.amazonaws.com/common/ajax_loader_large.gif']);
			if(parseInt($("input#isPhysicalOnly").val(), 10) == 1) {
				crud = PF.deliveryMethod["physical"];
				crud.setup();
			}

			//from saved method.
			if (window.PF.page.gift){

				if ( window.PF.page.gift.facebookDelivery){
					setDeliveryMethod('facebook');
				}
				else if (window.PF.page.gift.twitterDelivery){
					//check if previous token exists
					if ($("#twitterToken").val() &&	$("#twitterSecret").val()){
						$("#deliveryMethodTwitter").data('loggedIn',true);
					}
					setDeliveryMethod('twitter');
				}
				else if (window.PF.page.gift.recipientPhoneNumber){
					setDeliveryMethod('mobile');
				}
				else if (window.PF.page.gift.physicalDelivery){
					setDeliveryMethod('physical');
				}

			}
		},
		amountselector: function() {

				var defaultAmount = $('#amountCustomText').attr('data-default-amount');
				defaultAmount = (defaultAmount !== '') ? defaultAmount : '0.00';

				$('label[for="amountCustom"]').attr ('for', null);
				$('#amountCustomText').on ('keyup', function () {
					$("div ul li fieldset input[type=radio]").removeClass("checked");

					if ($(this).val()) {
						$('#amountCustom').prop ("checked", true)
										.addClass("checked");
					} else {
						$('input[name="messageAmount"]').each(function(){
							if(null === defaultAmount || defaultAmount == $(this).val()){
								$(this).prop("checked",true);
								return false;
							}
						});
					}

					var val = $(this).val();
					var currency = $(this).attr("data-currency");

					if(!isNaN(val)) {
						if( (currency == "JPY") && (parseFloat(val)%1 !== 0) ) {
							$(this).rules("add", {
								conditional: true,
								messages: {
									conditional: $("#customAmtDenominationError").val()
								}
							});
						} else {
							$(this).rules("remove", "conditional");
						}
					} else {
						$(this).rules("add", {
							conditional: true,
							messages: {
								conditional: $("#customAmtInputError").val()
							}
						});
						val = "";
					}

					var min1 = parseInt($(this).attr("data-validate-min"), 10);
					var max1 = parseInt($(this).attr("data-validate-max"), 10);

					if(val === "") {
						$('#amountCustomText').addClass("required");
					}
				});
				$('input[name="messageAmount"]').change(function(){
					if (!$('#amountCustom').is(":checked")){
						$('#amountCustomText').val('');
						$('span.error[for=amountCustomText]').hide();
						$('#amountCustomText').removeClass("error");
					} else {
						$('span.error[for=amountCustomText]').show();
						$('#amountCustomText').addClass("error");
					}
				});
		},
		formsubmit: function() {
			$('#createsubmit').click( function(event) {
				event.preventDefault();
				var deliverytime = $("#deliveryTimeStatus").val();
				var timeHour = $("#timeHour").val();

				// The below condition applies to SINGLE or GROUP gifting only
				if(parseInt($('input[name=giftGiftingMode]').val(), 10) <= 2) {
					// If the delivery time widget is turned ON and hour input is not empty, do the following
					if( (parseInt(deliverytime, 10) === 1) && (timeHour !== "") ) {
						$("select[name=timeZone]").focus();
						$("select[name=timeZone]").rules("add", "conditional");
					} else {
						if(parseInt(deliverytime, 10) == 1) {
							$("select[name=timeZone]").focus();
							$("select[name=timeZone]").rules("remove", "conditional");
						}
					}
				}

				$("select[name=timeZone]").blur();
				$('input[name="messageAmount"]:checked').each(function(idx,item) {
					$('#giftProductId').val($(item).data('pid'));
				});
				$('#createform').submit();
			});
		},
		datepicker: function() {
			// insufficient length of time to contribute dialogue, past date dialogue
			$('#datePast').mdialog ({
				overflow: 'auto',
				top: '150px',
				width: '350px',
				height: '175px',
				showcancelbtn: false
			});
			$('#dateFuture').mdialog ({
				overflow: 'auto',
				top: '150px',
				width: '350px',
				height: '175px',
				showcancelbtn: false
			});
			$('#dateAlert').mdialog ({
				overflow: 'auto',
				top: '150px',
				width: '350px',
				height: '175px',
				showcancelbtn: false
			});

			$("#datepicker").datepicker({
				showButtonPanel: true,
				//changeMonth: true,
				//changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true,
				dateFormat: "yy-mm-dd",
				currentText: "Select Today",
				dayNamesMin: ['S','M','T','W','T','F','S'],
				onSelect: function(dateText, inst) {
					var d = $.datepicker.parseDate ('yy-mm-dd', dateText);
					if (d) {
						$("#date_yyyy").val (d.getFullYear ());
						$("#date_mm").val (d.getMonth () + 1);
						$("#date_dd").val (d.getDate ());
						showDate(d);
						$(".overlaylit").css('visibility','hidden');
					}
				}
			}).ready(function() {
				if ($("#date_mm").length && $("#date_dd").length && $("#date_yyyy").length) {
					var d = null;
					if ($("#date_mm").val() && $("#date_dd").val() && $("#date_yyyy").val()){
						d = $.datepicker.parseDate ('mm/dd/yy', $("#date_mm").val() + '/' + $("#date_dd").val() + '/' + $("#date_yyyy").val().substr (2, 2));
						$("#datepicker").datepicker ("setDate", d);
					}
					else{//default delivery date to local date
						d = new Date();
						if ("1" == $('input[name=giftGiftingMode]').val()){
							//groupmode default delivery date is tomorrow.
							d.setDate(d.getDate()+1);
						}
						$("#date_yyyy").val (d.getFullYear());
						$("#date_mm").val (d.getMonth() + 1);
						$("#date_dd").val (d.getDate());
						//suppress date alert dialog
						showDate(d,true);
					}
				}
				$(".overlaylit").css('visibility','hidden');
			});

			$('.calendardiv').delegate('.calendarheader img','click',function(){
				$(".overlaylit").css('visibility','hidden');
			});

			$('.calendardiv').delegate('button.ui-datepicker-current','click',function(){
				var d = new Date();
				showDate(d);
				$(".overlaylit").css('visibility','hidden');
			});

			$('#content').delegate('.calendar','click', function(e){
				$(".calendardiv").offset({top:e.pageY-190,left:e.pageX+38});
				$(".calendartri").offset({top:e.pageY-20,left:e.pageX});
				$(".overlaylit").css('visibility','visible');
				return false;
			});
			// capture change event on date text inputs
			$("#date_yyyy, #date_mm, #date_dd").on ('blur', function () {
				try {
					var d = $.datepicker.parseDate ('mm/dd/yy', $("#date_mm").val () + '/' + $("#date_dd").val () + '/' + $("#date_yyyy").val ().substr (2, 2));
					showDate(d);
					$("#datepicker").datepicker ("setDate", d);
				} catch (err) {
					var today = new Date();
					showDate(today);
					$("#datepicker").datepicker("setDate", today);
				}
			});
		},
		video: function () {
			$('#videoForm').mdialog({
				overflow:'auto',
				top:'150px',
				width: '500px',
				height:'200px',
				okclick: function () {
					$("#giftVideoLink").val ($(".videoFormURL:visible").val ());
					$("#videoView").mdialog ('show');
					return true;
				},
				show: function (mdialog) {
					$(".videoFormURL").val ($("#giftVideoLink").val ());
					return mdialog;
				}
			});
			$('#videoView').mdialog ({
				showokbtn: false,
				showcancelbtn: false,
				showfooter: false,
				overflow:'auto',
				top:'150px',
				width: '540px',
				height:'410px',
				show: function (mdialog) {
					var videoLink = $("#giftVideoLink").val ();
					if (videoLink.indexOf ("http") === 0) {
						videoLink = videoLink.match(/.*(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?]*).*/);
						if (videoLink && videoLink[1].length == 11){
							videoLink = videoLink[1];
						} else {
							videoLink = "";
						}
					} else if (videoLink.length != 11) {
						videoLink = "";
					}
					if (videoLink.length > 0) {
						mdialog.find (".thankyouform").html (
							"<iframe width=\"480\" height=\"360\" style=\"width: 480px; height: 360px;\" src=\"https://www.youtube.com/embed/" + videoLink + "?rel=0\" frameborder=\"0\" allowfullscreen></iframe>"
						);
					} else {
						mdialog.find (".thankyouform").html (
							"<p>" + mdialog.find (".thankyouform").attr ("data-default") + "</p>"
						);
					}
					return mdialog;
				},
				hide: function (mdialog) {
					mdialog.find (".thankyouform").html ("");
					return mdialog;
				}
			});
			$('#videoUpload').click (function(){
				$('#videoForm').mdialog ('show');
			});
			$('#videoPlay').click (function(){
				$('#videoView').mdialog ('show');
			});
		},
		audio: function () {
			$('#audioForm').mdialog({
				overflow:'auto',
				top:'150px',
				width: '400px',
				height:'200px',
				okclick: function (obj) {
					if (typeof $(obj).attr ("data-recording") == 'undefined' || $(obj).attr ("data-recording") == 0) {
						if (typeof $(obj).attr ("data-recording") == 'undefined') {
							$.getJSON ("/record/dial/?client=" + recordTwilioClientID, function (val) {
								if (!val) {
									alert ("Could not request a call from the service.");
								}
							});
						} else {
							recordTwilioClient.sendDigits ("2");
						}
						$(".audioStatus").html ($(".audioStatus").attr ("data-text-start"));
						$(obj).attr ("value", $(".audioStatus").attr ("data-button-start"));
						$(obj).attr ("data-recording", 1);
					} else {
						$(".audioStatus").html ($(".audioStatus").attr ("data-text-stop"));
						$(obj).attr ("value", $(".audioStatus").attr ("data-button-stop"));
						$(obj).attr ("data-recording", 0);
						recordTwilioClient.sendDigits ("#");
					}
					return false;
				},
				cancelclick: function () {
					// assign recording result URL to form here
					if (recordTwilioClient !== null) {
						recordTwilioClient.disconnect ();
						$("#recordClient").val (recordTwilioClientID);
					}
					$("#audioView").mdialog ('show');
					return true;
				},
				show: (function (mdialog) {
					var twilioLoaded = 0;
					return function (mdialog) {
						$(".dialogfooter .btnOK[data-recording]").val ($(".audioStatus").attr ("data-button-default"));
						$(".dialogfooter .btnOK[data-recording]").attr ("data-recording", null);
						$(".audioStatus").html ($(".audioStatus").attr ("data-text-default"));
						if (!twilioLoaded) {
							var twilioScript = document.createElement('script');
							twilioScript.type = 'text/javascript';
							twilioScript.async = true;
							twilioScript.src = 'https://static.twilio.com/libs/twiliojs/1.0/twilio.js';
							var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(twilioScript, s);
							twilioLoaded = 1;
						}
						if (recordTwilioClient === null) {
							setTimeout (function () {
								$.getJSON (
									"/record/client",
									function (data) {
										recordTwilioClientID = data.client;
										Twilio.Device.setup (data.token);
										Twilio.Device.ready (function (device) {

										});

										Twilio.Device.offline (function (device) {
											$('.audioStatus').text ('Initializing recording session.');
										});

										Twilio.Device.error (function (error) {
											$('.audioStatus').text (error.message);
										});

										Twilio.Device.connect (function (conn) {
											recordTwilioClient = conn;
										});

										Twilio.Device.disconnect (function (conn) {
											$('.audioStatus').text ('Recording session ended.');
										});

										Twilio.Device.incoming (function (conn) {
											conn.accept ();
										});
									}
								);
							}, 850);
						}
						return mdialog;
					};
				})()
			});
			$("#audioView").mdialog ({
				showokbtn: false,
				showcancelbtn: false,
				showfooter: false,
				overflow:'auto',
				top:'150px',
				width: '400px',
				height:'100px',
				show: function (mdialog) {
					$.getJSON ("/record/playback/?client=" + $("#recordClient").val (), function (data) {
						if (data.recording !== null) {
							mdialog.find (".thankyouform").html ("<audio controls=\"controls\" style=\"width: 100%;\"><source src=\"" + data.recording + ".mp3\" type=\"audio/mp3\" /><source src=\"" + data.recording + "\" type=\"audio/wav\" /><embed type=\"application/x-shockwave-flash\" src=\"https://www.google.com/reader/ui/3523697345-audio-player.swf\" quality=\"best\" flashvars=\"audioUrl=" + data.recording + ".mp3\" width=\"100%\" height=\"27\"></embed></audio>");
						} else {
							mdialog.find (".thankyouform").html (mdialog.find (".thankyouform").attr ("data-default"));
						}
					});
					return mdialog;
				},
				hide: function (mdialog) {
					mdialog.find (".thankyouform").html ("");
					return mdialog;
				}
			});
			$('#audioUpload').click (function(){
				$('#audioForm').mdialog ('show');
			});
			$('#audioPlay').click (function(){
				$('#audioView').mdialog ('show');
			});
		}
	});

})();



var showDate = (function (d, noAlert) {
	var today = new Date ();
	var flashInlineDateErrorCount = 0;
	var $errorSpan;
	var $dateFields;
	var displayInlineDateError = function(message) {
		$errorSpan = $('#content li.date span.error');
		if($errorSpan.length)
			$errorSpan.text(message);
		else
			$errorSpan = $('<span class="error">'+message+'</span>').appendTo($('#content li.date'));
		$dateFields = $('#date_mm, #date_dd, #date_yyyy').addClass('error');

	};
	var removeInlineDateError = function() {
		if($errorSpan != null) $errorSpan.remove();
		if($dateFields != null) $dateFields.removeClass('error');
	};
	var flashInlineDateError = function(message) {
		displayInlineDateError(message);
		flashInlineDateErrorCount++;
		setTimeout(
			function() {
				if(flashInlineDateErrorCount == 1) {
					removeInlineDateError();
				}
				flashInlineDateErrorCount--;
			},
			4000
		);
	};
	var isValidDate = function(yyyy, mm, dd) {
		var checkdate = new Date(yyyy,mm-1,dd);
		return checkdate && (checkdate.getMonth()+1) == mm && checkdate.getDate() == Number(dd);
	};
	var setDateTo = function(d) {
		$('#date_yyyy').val(d.getFullYear());
		$('#date_mm').val(d.getMonth() + 1);
		$('#date_dd').val(d.getDate());
		$("#datepicker").datepicker("setDate", d);
	};
	return function showDate(d, noAlert) {
		today.setHours(0, 0, 0, 0);
		if (typeof noAlert == 'undefined') noAlert = false;
		if (typeof d == 'undefined') d = today;
		removeInlineDateError();
		if (!isValidDate($('#date_yyyy').val(), $('#date_mm').val(), $('#date_dd').val()) && !noAlert) {
			flashInlineDateError("Invalid delivery date. Setting to today.");
			setDateTo(today);
		} else if (today.getTime () > d.getTime () && !noAlert) {
			// if this date is in the past, notify them that we don't have a time machine. set the date to today.
			flashInlineDateError("You cannot specify a delivery date in the past. Setting to today.");
			setDateTo(today);
		} else if (today.getTime () + 60 * 60 * 24 * 21 * 1000 < d.getTime ()) {
			// if this date is too far in the future, notify them that they can't have a gift that far out
			flashInlineDateError("Delivery date is too far in the future. Setting to today.");
			setDateTo(today);
		} else if ($("input[name=\"giftEventTitle\"]").length > 0 && today.getTime () + (1000 * 60 * 60 * 24 * 3) > d.getTime () && !noAlert) {
			// if this is a group gifting event, notify them that it may not allocate enough time for people to contribute
			flashInlineDateError("Delivery Date is soon. There may not be enough time for people to contribute.");
		}
		setDateFlag();
		if( $("#isToday").val() == "1" ) {
			checkTime();
		} else {
			$("#timeHour").focus();
			$("#timeHour").rules("add", {
				"min" : false,
				"max" : false
			});
		}
	};
})();

var checkTime = (function () {
	return function checkTime() {
		var now_mm     = new Date().getMonth()+1;
		var now_dd     = new Date().getDate();
		var now_yyyy   = new Date().getFullYear();
		var date_mm   = ( isNaN($("#date_mm").val()) ) ? now_mm : $("#date_mm").val();
		var date_dd   = ( isNaN($("#date_dd").val()) ) ? now_dd : $("#date_dd").val();
		var date_yyyy = ( isNaN($("#date_yyyy").val()) ) ? now_yyyy : $("#date_yyyy").val();

		if( ( now_mm == date_mm )&&( now_dd == date_dd )&&( now_yyyy == date_yyyy ) ) {
			var nowHour   = new Date().getHours();
			var nowMinute = new Date().getMinutes();

			var currentTz   = $("#currentTimeZone").val();
			var selectedTz	= $("select#timeZone option:selected").attr("value");

			if(nowMinute > 45) {
				nowHour += 1;
			}

			if(parseInt(nowHour, 10) === 0) {
				nowHour = 24;
			}

			if( (newTimeOffset !== null) && (selectedTz !== currentTz) ) {
				nowHour += parseInt(defaultTimeOffset, 10) + parseInt(newTimeOffset * -1, 10);
			}

			var timeHour   = ( !isNaN($("#timeHour").val()) ) ? $("#timeHour").val() : "24";
			var timeMinute = parseInt($("select[name='timeMin'] > option:selected").val(), 10);

			if(timeHour < nowHour) {
				$("#timeHour").focus();
				$("#timeHour").attr("data-validate-min", nowHour);
				$("#timeHour").rules("add", {
					min : function() { return nowHour; },
					messages: {
						min: $("#pastTimeError").val()
					}
				});
			} else {
				if( (timeMinute < nowMinute) && (timeHour == nowHour) ) {
					var newMinute;
					if(nowMinute%15 !== 0) {
						newMinute = ( nowMinute - (nowMinute%15) ) + 15;
					} else {
						newMinute = nowMinute;
					}

					$("select[name='timeMin'] > option[value='" + newMinute + "']").attr("selected", "selected");

					if( newMinute == 60 ) {
						newMinute = "00";

						if(timeHour == 24) { timeHour = 0; }
						newHour   = parseInt(timeHour, 10) + 1;

						$("select[name='timeMin'] > option[value='" + newMinute + "']").attr("selected", true);
						$("#timeHour").focus();
						setTimeout(function() { $("#timeHour").blur(); }, 30);
						$("#timeHour").rules("add", {
							min : function() { return newHour; },
							messages: {
								min: $("#pastTimeError").val()
							}
						});
					} else {
						$("#timeHour").rules("add", {
											"min" : false,
											"max" : false
						});
					}
				}

				$("#timeHour").rules("add", {
									"min" : false,
									"max" : false
				});
			}
		} else {
			$("#timeHour").focus();

			$("#timeHour").rules("add", {
                                "min" : false,
                                "max" : false
                        });

			if( now_yyyy > date_yyyy ) {
				$(".time").hide();
			} else {
				if(now_mm > date_mm) {
					$(".time").hide();
				} else {
					if(now_mm == date_mm) {
						if(now_dd > date_dd) {
							$(".time").hide();
						} else if (($("select[name*='giftDeliveryMethod']").first().val() != "physical")) {
							$(".time").show();
						}
					}
				}
			}
		}
	};
})();

var errorMsg = (function (elem) {
	return function errorMsg(elem) {
		if($("#" + elem).val() !== "") {
			$("#" + elem).rules("add", {
				conditional: true,
				messages: {
					conditional: $("#twitterInvalid").val()
				}
			});
		} else {
			$("#" + elem).rules("add", {
				conditional: true,
				messages: {
					conditional: $("#twitterRequired").val()
				}
			});
		}
	};
})();

var setNewOffset = (function (o) {
	return function setNewOffset(o) {
		newTimeOffset = o;
	};
})();

var getProductId = (function (){
	return function getProductId(){
		var pid = '';
		$('input[name="messageAmount"]:checked').each(function(idx,item){
			pid = $(item).data('pid');
		});
		if (pid === ''){
			pid = $('#amountCustomText').data('pid');
		}
		return pid;
	};
})();

var setDateFlag = (function () {
	return function setDateFlag() {
		var dd   = $("#date_dd").val();
		var mm   = $("#date_mm").val();
		var yyyy = $("#date_yyyy").val();

		var ddNow   = new Date().getDate();
		var mmNow   = new Date().getMonth() + 1;
		var yyyyNow = new Date().getFullYear();

		if( (dd == ddNow) && (mm == mmNow) && (yyyy == yyyyNow) ) {
			$("#isToday").val("1");
		} else {
			$("#isToday").val("0");
		}
	};
})();
