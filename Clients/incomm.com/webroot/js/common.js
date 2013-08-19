$(document).ready(function() {
	
	// Set-up some defaults
	window.PF.tiptipDefaults = {
		maxWidth: "250px",
		defaultPosition: "right",
		delay: 0,
		edgeOffset: 8
	};
	
	$('#giftingoption').mdialog({
		url: '/gift/giftingoption',
		showheader: false,
		showfooter: false,
		centre: true,
		width: '820px',
		height:'230px'
	});

	$('#giftingoption, #cart #messages .empty > a').click(function(event){
		event.preventDefault();
		$('#giftingoption').mdialog('show');
		return false;
	});

	$('#openterm').mdialog({
		url: '/help/terms',
		showfooter: false,
		ioverflowy: 'scroll',
		centre: true,
		width: '700px',
		height:'500px',
		showcancelbtn : false,
		showokbtn : false
	});

	$("#openterm, #blockterm").filter(function() {
		return ($(this).closest("#billing").length == 0);
	}).add("#billingterm").click(function() {
		$("#openterm").mdialog("show");
		return false;
	});

	$('#openhelp').mdialog({
		url: '/gift/help',
		ioverflowy: 'auto',
		centre: true,
		width: '597px',
		height:'550px',
		showfooter: false,
		showcancelbtn : false,
		showokbtn : false,
	});

	$('#openhelp').click(function(){
		$('#openhelp').mdialog('show');
		return false;
	});

	$('#openfaq').mdialog({
		url: '/help/faq',
		ioverflowy: 'auto',
		centre: true,
		width: '597px',
		height:'550px',
		showfooter: false,
		showcancelbtn : false,
		showokbtn : false
	});

	$('#openfaq').click(function(){
		$('#openfaq').mdialog('show');
		return false;
	});

	$('#openprivacy').mdialog({
		url: '/help/privacy',
		showfooter: false,
		ioverflowy: 'auto',
		top: '0px',
		width: '780px',
		centre: true,
		height:'550px',
		showcancelbtn : false,
		showokbtn : false
	});

	$('#openprivacy').click(function(){
		$('#openprivacy').mdialog('show');
		return false;
	});

	$('#opencontactform').mdialog ({
		url: '/help/contact',
		showokbtn : true,
		showcancelbtn: false,
		ioverflowy:'auto',
		top:'0px',
		width: '500px',
		centre: true,
        	height: '490px',
		okclick: function() {
			if($('.dialogcontent > iframe[src="/help/contact"]')[0].contentWindow.iframevalidate() == true) {
				$('.dialogcontent > iframe').contents().find("form#contactUsForm").submit();
			}
		}
	});

	$('#opencontactform').click (function () {
		$('#opencontactform').mdialog ('show');
		return false;
	});
	
	// Read data-time attribute from <time> tags, convert unix timestamp
	// to m/d/yy format and display formatted time in the tag
	$("time").text(function() {
		var date = new Date(($(this).data("time") + 1) * 1000);
		return $.datepicker.formatDate("m/d/yy", date);
	});
	
	// helper method to lookup if a element exists in an array
	var hasDataTargetRule = function(rule, ruleString){
		var ruleArray = ('undefined' !== typeof(ruleString) && ruleString.length > 0) ? ruleString.split(" ") : [];

		if(ruleArray.length === 0){
			return false;
		} else {
			return (-1 !== $.inArray(rule, ruleArray));
		}
	};

	var processRules = function(text, ruleString){
		// enforce rules: currency
		if(hasDataTargetRule('currency', ruleString)){
			var patternCurrency = /^([0-9]*[\.]?[0-9]{0,2}).*$/;
			text = text.replace(patternCurrency,'$1');
		}

		// enforce rules: integer
		if(hasDataTargetRule('integer', ruleString)){
			var patternInteger = /^([0-9]+).*$/;
			text = text.replace(patternInteger,'$1');
		}

		// enforce rules: nospaces
		if(hasDataTargetRule('nospaces', ruleString)){
			text = $.trim(text);
		}

		return text;
	};

	// handle any text input that has a data-target attribute. On any entry into this field, mirror the value in the element specified by data-target.
	// data-target-rules will specify any behaviour or rules
	var targetOriginalValue = '';
	$('input[type="text"][data-target], textarea[data-target]')
		.on('focus', function(event){
			targetOriginalValue = $($(this).attr("data-target")).html();
		})
		.on('keyup blur', function(event){
			// if the following key presses are detected, exit
			if(event.ctrlKey && event.keyCode === 65) {return;}

			switch(event.keyCode){
				case 9:  // tab
				case 17: // ctrl
				case 37: // left-arrow
				case 39: // right-arrow
				case 91: // Windows
					return;
				default:
					break;
			}

			// init variables
			var dataTarget = $($(this).attr('data-target'));
			var dataTargetRulesString = $(this).attr('data-target-rules');
			var targetDataFormat = dataTarget.attr('data-format');
			var currency = $(this).attr("data-currency");
			var val = $(this).val();

			// if data-target doesn't exist (is invalid)
			if(0 === dataTarget.length) { return; }

			val = processRules(val, dataTargetRulesString);

			dataTarget.text(val);
		})
		.on('blur', function(event){
			var dataTarget = $($(this).attr('data-target'));
			var dataTargetRulesString = $(this).attr('data-target-rules');
			var val = $.trim($(this).val());
			val = processRules(val, dataTargetRulesString);

			if(0 === val.length && hasDataTargetRule('restoreoldvalue', dataTargetRulesString)){
				val = targetOriginalValue;

				// (custom case) if this is the "choose amount" element, check if the original value is a fixed amount and reselect that button
				if($(this).attr('id') === 'amountCustomText'){
					var radioSelectionId = $('input[name="messageAmount"]:checked').attr('id'); // record the currently selected radio button
					// check each denomination to see if it matches the original target value
					$('input[name="messageAmount"]').each(function(){
						if(null === targetOriginalValue || targetOriginalValue == $(this).val()){
							radioSelectionId = $(this).attr('id');
							return false; // match found
						}
					});

					$('#' + radioSelectionId).prop("checked",true).change();
					if('amountCustom' !== radioSelectionId){
						return; // fixed price radio button selected. do not set the custom field
					}
				}
			}

			dataTarget.text(val);
			$(this).val(val);
		});


	$('select[data-target], input[type="radio"][data-target]').on ('change', function () {
		if ($(this).attr ("data-target").length > 0) {
			var currency = $(this).attr("data-currency");
			var val = $(this).val ();
			if (val.length > 0) {
				if (typeof $($(this).attr ("data-target")).attr ("data-format") != 'undefined') {
					if(currency != "JPY") {
						val = (!isNaN(val)) ? parseFloat (val).toFixed (2) : "0.00";
					} else {
						val = (!isNaN(val)) ? parseInt(val, 10) : "0";
					}
				}
				$($(this).attr ("data-target")).text (val);
			} else if (typeof $($(this).attr ("data-target")).attr ("data-default") != 'undefined') {
				$($(this).attr ("data-target")).text ($($(this).attr ("data-target")).attr ("data-default"));
			}
		}
		$('#amountCustomText').removeClass("required");
	});

	// open links in new window if rel="external", used for validation compliance as target="_blank" has been deprecated
	$('a[rel="external"]').each (function (i, obj) {
		$(this).attr ("target", "_blank");
	});

	// tooltip for all elements with title attribute
	$("*[title]").tipTip (window.PF.tiptipDefaults);
	// Position fix for the tooltips that are covered by order summary
	// box in billing form area on checkout page
	$("#billingform #address1, #billingform #address2").tipTip($.extend(
		{}, 
		window.PF.tiptipDefaults, 
		{ defaultPosition: "left" }
	));

	// Get and display upcoming FB friends birthdays
	setTimeout(function() {
		var container = $("#adcontainer #birthdays");
		if (container.length < 1) {
			return;
		}
		$.subscribe("/facebook/friendsBirthdays", function(data) {
			if (data.length == 0) {
				return;
			}
			var firstUl = container.find("ul:first")
				, firstLi = firstUl.find("li:first").detach();
			$.each(data, function(i, e) {
				var li = firstLi.clone();
				li.find("img").attr("src", e.pic_square);
				li.find("span.birthdate").text(e.formatted_birthday);
				li.find("span.name").text(e.first_name + " " + e.last_name);
				li.find("a:first").attr(
					"href",
					"/_/gift/products?mode=1&search=" + e.first_name + "%20" + e.last_name
				);
				li.appendTo(firstUl);
			});
			container.show();
			container.parent().show();
			window.PF.xdm.resize(true, {extraHeight: 35});
		});
		window.PF.facebook.getBirthdays(6);
	}, 1200);

	$("#content div ul li fieldset input[type='radio']:first-child, #content div ul li fieldset + label + span input[type='radio']:first-child, #status fieldset input[type='radio']:first-child").addClass("checked");

	$("#content div ul li fieldset input[type='radio'], #content div ul li fieldset + label + span input[type='radio'], #status fieldset input[type='radio']").on('click', function() {
		$("#content div ul li fieldset input[type='radio'], #content div ul li fieldset + label + span input[type='radio'], #status fieldset input[type='radio']").removeClass("checked");
		var id = $(this).attr("id");
		$("#content div ul li fieldset input#" + id + ", #content div ul li fieldset + label + span input#" + id + ", #status fieldset input#" + id).addClass("checked");
	});

	if ($.browser.msie && ( parseInt($.browser.version, 10)>=8 ) ) {
		$('[placeholder]').focus(function() {
		  var input = $(this);
		  if (input.val() == input.attr('placeholder')) {
		    input.val('');
		    input.removeClass('placeholder');
		  }
		}).blur(function() {
		  var input = $(this);
		  if (input.val() == '' || input.val() == input.attr('placeholder')) {
		    input.addClass('placeholder');
		    input.val(input.attr('placeholder'));
		  }
		}).blur();

		$('[placeholder]').parents('form').submit(function() {
		  $(this).find('[placeholder]').each(function() {
		    var input = $(this);
		    if (input.val() == input.attr('placeholder')) {
		      input.val('');
		    }
		  });
		});

		$('select').change( function() {
			var ins = $(this);
			ins.blur();

			setTimeout( function() {
				console.log(ins.attr('value'));
				setFocusToNextInput(ins);
			}, 800);
		});

		// A function that sets focus to the next element

		var setFocusToNextInput = (function(i) {
			return function fixFocusToNextInput(i) {
				var fields = i.parents('form:eq(0)').find('li').not('.hidden').find('input, button, select, textarea').not('[type=hidden], [type=radio]');
				console.log(fields.length);
				var index = fields.index(i);
				console.log(index);
				if ( index > -1 && ( index + 1 ) < fields.length ) {
					fields.eq(index + 1).focus();
					console.log(fields.eq(index + 1).attr("name"));
				}
				return false;
			}
		})();
	}
});
