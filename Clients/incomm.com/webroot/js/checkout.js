
var Checkout = function() {

	var _payments
		, _floatingSum
		, _iframe
		, _loader
		, _paypalBtn
		, _paypalNav
		, _creditCardBtn
		, _securePayHost
		, _msgTarget = "securepay"
		, _preloads = {arrows: "//gc-fgs.s3.amazonaws.com/common/lang_ajax.gif"};

	var _isCheckoutPage = function() {
		return $("body.checkout-page").length > 0;
	};
	
	var _hasCardPaymentOption = function () {
		return $('#paymentmethod ul.invisible #paymentmethodCard').length > 1;
	};

	var _destroyEventListeners = function() {
		$("input[name=paymentmethod]")
			.add("button#test-fill1")
			.off();
		$(window).off("message");
	};

	var _preloadImages = function() {
		$.each(_preloads, function(index, value) {
			$(document.createElement("img"))[0].src = value;
		});
	};

	var _initDomSelectors = function() {
		_payments = $("#payments > div");
		_floatingSum = $("#floatingsummary");
		_iframe = $("#securepayform iframe");
		_loader = $("#securepayloader");
		_paypalBtn = $("#paymentmethodPaypal");
		_paypalNav = $("#navigation.paypal");
		_creditCardBtn = $("#paymentmethodCard");
	};

	var _listenButtonEvents = function() {
		var loader = $(document.createElement("img"))
				.prop("src", _preloads.arrows)
				.css("margin", "10px 0 0 315px");
		$("input[name=paymentmethod]").on("click", function() {
			if (_creditCardBtn.prop("checked")) {
				_iframe.show();
				_paypalNav.hide();
				_floatingSum.find("#ordersum").removeClass("compress");
			} else {
				_iframe.hide();
				_paypalNav.show();
				_floatingSum.find("#ordersum").addClass("compress");
			}
			_adjustPaymentsContainerHeight();
			PF.xdm.resize();
		});
		$("#btncheckout").on("click", function(event) {
			if (_paypalBtn.prop('checked')) {
				event.preventDefault();
				$(this).prop("disabled", true).before(loader);
				$("#expressForm").submit();
			}
		});
		$("button#test-fill1").on("click", function() {
			Messaging.post(JSON.stringify({
				method: "fillTestForm"
				, data: testFillData
			}), _msgTarget);
		});
	};

	var _listenCrossDomainMessages = function() {
		Messaging.handleErrors();
		Messaging.receiveAndApply(_securePayHost, Checkout);
	};

	var _loadCreditCardForm = function() {
		if (_iframe.length == 0) {
			return;
		}
		_iframe.prop("src", _loader.data("src")).load(function() {
			Messaging.post(JSON.stringify({
				method: "replySecurePayHeight"
				, data: {}
			}), _msgTarget);
			_loader.hide();
			_adjustPaymentsContainerHeight();
			if (_paypalBtn.is(":checked")) {
				_paypalBtn.trigger("click");
			} else {
				_creditCardBtn.trigger("click");
			}
		});
	};

	var _adjustPaymentsContainerHeight = function() {
		var newHeight = "auto"
			, loaderShowed = (_loader.css("display") != "none")
			, creditCardBtnChecked = _creditCardBtn.is(":checked")
			, paymentsHeight = _payments.height()
			, floatingSumHeight = _floatingSum.height() + 35;
		// If iframe is loading (loader is displaying)
		// 
		// Non-securepay option could be checked while iframe is being loaded
		if (loaderShowed) {
			// Then use the bigger height between the current payments
			// container height and flaoting summary container height
			// 
			// height "auto" equals to the default height of payments
			// conainer
			newHeight = (paymentsHeight > floatingSumHeight)
				? "auto" : floatingSumHeight;
		}
		// Otherwise (if iframe is loaded)
		else {
			// If non-securepay option is checked then use floating sumarry
			// container height; otherwise use "auto" (payments container
			// height)
			newHeight = creditCardBtnChecked ? "auto": floatingSumHeight;
		}
		// If non-securepay option is checked and payments contains is higher than
		// floating summary box.
		if (!creditCardBtnChecked && paymentsHeight > floatingSumHeight) {
			newHeight = "auto";
		}
		_payments.css("height", newHeight);
	};
	
	return {
		init: function() {
			if (!_isCheckoutPage()) {
				return;
			}
			Messaging.debug($("#test-fill1").length > 0);
			_preloadImages();
			_initDomSelectors();
			_adjustPaymentsContainerHeight();
			if(_hasCardPaymentOption()) {
				_securePayHost = Messaging.extractHttpHost(_loader.data("src"));
			}
			if (!_securePayHost && Messaging.debug()) {
				_securePayHost = document.domain;
			}
			_loadCreditCardForm();
			_listenButtonEvents();
			_listenCrossDomainMessages();
		}

		, destruct: function() {
			if (!_isCheckoutPage()) {
				return;
			}
			_destroyEventListeners();
		}

		, is: function() {
			return _isCheckoutPage();
		}

		, resizeSecurePayIframe: function(event, data) {
			if (("height" in data) && +(data.height) > 0) {
				_iframe.height(+(data.height) - 16);
			}
		}

		, openTermOfUseDialog: function(event, data) {
			$("#openterm").mdialog("show");
			return false;
		}
	};

}();

~function() {
	Checkout.init();
	$(window).on("beforeunload", function() {
		Checkout.destruct();
	});
}();
