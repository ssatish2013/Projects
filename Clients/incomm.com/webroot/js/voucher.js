
var Voucher = function() {
	var _loadPan = function() {
		var guid = $("#barcode").data("guid");
		if (guid == "") {
			return;
		}
		$.ajax({
			method: "get",
			url: "/claim/code/guid/" + guid + "/page/voucher",
			dataType: "json"
		}).done(function(data) {
			var div = $("#barcode")
				, divDiv = div.find("div");
			if (data.exception.has) {
				_handleLoadPanException(data.exception);
			} else {
				if (/^<img[^>]+>$/.test(data.pinDisplay)) {
					$(data.pinDisplay).one('load', function() {
						divDiv.html(data.pinDisplay);
						_centreIt(divDiv, div.height(), this.height);
						window.print();
					}).each(function() {
						if (this.complete) {
							$(this).load();
						}
					});
				}
				else if (data.pinDisplay) {
					$("#barcode div").html(data.pinDisplay);
					_centreIt(divDiv, div.height(), divDiv.height());
					window.print();
				}
				else {
					$("#barcode div").html(data.pan + "<br /><br />" + data.pin);
					_centreIt(divDiv, div.height(), divDiv.height());
					window.print();
				}
			}
		}).fail(function() {
			_handleLoadPanException({has: true, type: "fail", message: ""});
		});
	};

	var _handleLoadPanException = function(exception) {
		if (exception.type == "fail") {
			exception.message = "Failed to load PIN number and gift code."
		}
		$("#barcode div").html(exception.message);
	};

	var _centreIt = function(targetElement, parentHeight, childHeight) {
		var position = {top: 0, left: 0};
		childHeight = ($.browser.msie) ? targetElement.height() : childHeight;
		position.top = (parentHeight - childHeight) / 2;
		targetElement.css({"top": ((position.top < 0) ? 0 : position.top) + "px"});
	};

	var _isVoucherPage = function () {
		return ($("#voucherprint").length > 0);
	};

	// Public static methods
	return {
		init: function() {
			if (!_isVoucherPage()) {
				return;
			}
			_loadPan();
		}

		, destruct: function() {
			if (!_isVoucherPage()) {
				return;
			}
		}

		, is: function() {
			return _isVoucherPage();
		}
	};
}();

~function() {
	Voucher.init();
	$(window).on("beforeunload", function() {
		Voucher.destruct();
	});
}();
