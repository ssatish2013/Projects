
var SecurePay = function() {

	var _sourceHost
		, _geoCountry
		, _msgTarget = "parent"
		, _body = $(document.body)
		, _preloads = {arrows: "//gc-fgs.s3.amazonaws.com/common/lang_ajax.gif"};

	var _isSecurePayPage = function() {
		return ($("#billing #billingform").length > 0);
	};

	var _destroyEventListeners = function() {
		$("#paymentForm")
			.add("#paymentForm input:text")
			.add("#billing #openterm")
			.off();
		$(window).off("message");
	};

	var _preloadImages = function() {
		$.each(_preloads, function(index, value) {
			$(document.createElement("img"))[0].src = value;
		});
	};

	var _getQsParam = function(name) {
		name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
		var regexS = "[\\?&]" + name + "=([^&#]*)"
			, regex = new RegExp(regexS)
			, results = regex.exec(window.location.search);
		if (results == null) {
			return "";
		} else {
			return decodeURIComponent(results[1].replace(/\+/g, " "));
		}
	};

	var _addGoBackAndCancelButtons = function() {
		var goBack, cancel;
		if (_sourceHost) {
			goBack = $(document.createElement("a"))
				.prop({
					"href": "//" + _sourceHost + "/cart",
					"target": "_parent"
				})
				.html("<span>Go Back</span>");
			cancel = $(document.createElement("a"))
				.prop({
					"href": "//" + _sourceHost + "/gift/products",
					"target": "_parent"
				})
				.html("<span>Cancel</span>");
			$("#navigation div input").before(goBack).before(cancel);
		}
	};

	var _postResizeMessage = function() {
		Messaging.post(JSON.stringify({
			method: "resizeSecurePayIframe"
			, data: {height: _body.outerHeight()}
		}), _msgTarget);
	};

	var _listenCrossDomainMessages = function() {
		Messaging.handleErrors();
		Messaging.receiveAndApply(_sourceHost, SecurePay);
	};

	var _listenFormSubmitButtonEvent = function() {
		var loader = $(document.createElement("img"))
				.prop("src", _preloads.arrows)
				.css("margin", "10px 0 0 315px")
			, submit = $("#btncheckout")
			, fakeSubmit = $(document.createElement("input"))
				.attr({type: "hidden", name: "btncheckout"})
				.val(submit.val());
		$("#paymentForm").on("submit", function() {
			if (!$(this).valid()) {
				_postResizeMessage();
				return false;
			}
			submit.prop("disabled", true).before(loader);
			submit.before(fakeSubmit);
			_postResizeMessage();
			return true;
		}).find("input:text").on("focusout", function() {
			_postResizeMessage();
		});
	};

	var _listenTermOfUseLinkEvent = function() {
		$("#billing #openterm").on("click", function() {
			Messaging.post(JSON.stringify({
				method: "openTermOfUseDialog"
				, data: {}
			}), _msgTarget);
			return false;
		});
	};

	var _ieCheckboxFix = function () {
		if ($.browser.msie && +($.browser.version) == 8) {
			$("input[type=checkbox] + label").on("click", function() {
				var label = $(this)
					, input = label.siblings("input")
					, img = label.find("img")
					, cls = ["checkbox_off", "checkbox_on"];
				input.trigger("click");
				img.removeClass().addClass(cls[+(input.is(":checked"))]);
			});
		}
	};
	
	var _validateHiddenAgreeTermsCheckbox = function() {
		//prevent validation plugin ignore the hidden checkbox
		var settings = $.extend( true, $('form.validate').validate().settings || {}, {
			errorElement: 'span',
			ignore: ':hidden[id!=agreeterm]',
			invalidHandler: function(e, validator){
				var errors = validator.numberOfInvalids();
				if (errors) { }
			},
			errorPlacement: function(error, element){
				$(element).addClass('error');
				if($(element).data('validateErrorTarget')){
					error.appendTo($($(element).data('validateErrorTarget')));
				} else {
					error.appendTo(element.parent());
				}
				Messaging.post(JSON.stringify({
					method: "resizeSecurePayIframe"
					, data: {height: _body.outerHeight()}
				}), _msgTarget);
			}
		});
		$('form.validate').validate(settings);
	};
	
	var _setPostalZipLabel = function(label) {
		$('#billingzip').attr('placeholder', label)
			.attr('data-validate-required-message', label+' is required')
			.tipTip($.extend({}, window.PF.tiptipDefaults, {content:label}));
	};
	
	var _updateRegionBox = function() {
		var selected = $('#country option:selected').val();
        $('.regionBox').hide();
        if (selected == 'CA') {
        	$('#provinces-box').show();
        	_setPostalZipLabel('Postal Code');
        }
        else if (selected == 'US') {
        	$('#states-box').show();
        	_setPostalZipLabel('Zip');
        }
        else {
        	$('#region-box').show();
        	_setPostalZipLabel('Postal Code');
        }
	};
	
	var _undoRegionBoxesHiddenClass = function() {
		$('div.selectstate.regionBox.hidden').removeClass('hidden');
	};
	
	return {
		init: function() {
			if (!_isSecurePayPage()) {
				return;
			}
			Messaging.debug(true);
			_preloadImages();
			_sourceHost = _getQsParam("tx_httphost");
			_geoCountry = _getQsParam("tx_geocountry");
			$("select#country").val(_geoCountry);
			_addGoBackAndCancelButtons();
			_listenTermOfUseLinkEvent();
			_listenCrossDomainMessages();
			_listenFormSubmitButtonEvent();
			_ieCheckboxFix();
			_validateHiddenAgreeTermsCheckbox();
			_updateRegionBox();
			_undoRegionBoxesHiddenClass(); // TODO: Have the hidden class removed from the securepay page and remove this function
			$('select#country').change(_updateRegionBox); // TODO: Have this code removed from the securepay page
		}

		, destruct: function() {
			if (!_isSecurePayPage()) {
				return;
			}
			_destroyEventListeners();
		}

		, replySecurePayHeight: function(event, data) {
			Messaging.reply(JSON.stringify({
				method: "resizeSecurePayIframe"
				, data: {height: _body.outerHeight()}
			}), event);
		}

		, fillTestForm: function(event, data) {
			$("#firstName").val(data.firstName);
			$("#lastName").val(data.lastName);
			$("#phoneNumber").val(data.phoneNumber);
			$("#email").val(data.email);
			$("#confirmEmail").val(data.confirmEmail);
			$("#address1").val(data.address1);
			$("#address2").val(data.address2);
			$("#billingcity").val(data.billingCity);
			$("#transactionStateList option[value=" + data.transactionStateList + "]")
				.prop("selected", true);
			$("#billingzip").val(data.billingZip);
			$("#country option[value=" + data.country + "]")
				.prop("selected", true);
			$('#country').change();
			if (data.agreeTerm) {
				$("#agreeterm").prop("checked", true);
				if ($.browser.msie && +($.browser.version) == 8) {
					$("#agreeterm")
						.siblings("label")
						.find("img")
						.removeClass()
						.addClass("checkbox_on");
				}
			}
			$("#cardNumber").val(data.cardNumber);
			$("#cardName").val(data.cardName);
			$("#expireMonth option[value=" + data.expireMonth + "]")
				.prop("selected", true);
			$("#expireYear option[value=" + data.expireYear + "]")
				.prop("selected", true);
			$("#securityCode").val(data.securityCode);
		}
	};

}();

~function() {
	SecurePay.init();
	$(window).on("beforeunload", function() {
		SecurePay.destruct();
	});
}();
