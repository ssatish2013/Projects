
var Claim = function() {
	// Private properties
	var _loaders = {arrows: "//gc-fgs.s3.amazonaws.com/common/lang_ajax.gif"}
		, _videoLink = ""
		, _audioLink = "";

	// Private methods
	var _preloadAjaxLoaders = function() {
		$.each(_loaders, function(index, value) {
			$("<img/>")[0].src = value;
		});
	};

	var _initDialogs = function() {
		$('#videoView').mdialog({
			showokbtn: false,
			showcancelbtn: false,
			showfooter: false,
			overflow: "auto",
			centre: true,
			width: "540px",
			height: "410px",
			show: function(mdialog) {
				if (_videoLink.indexOf("http") == 0) {
					_videoLink = _videoLink.match(/.*(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?]*).*/);
					_videoLink = (_videoLink && _videoLink[1].length == 11)
						? _videoLink = _videoLink[1]
						: _videoLink = "";
				} else if (_videoLink.length != 11) {
					_videoLink = "";
				}
				if (_videoLink.length > 0) {
					mdialog.find(".thankyouform").html(
						"<iframe width=\"480\" height=\"360\" style=\"width: 480px; height: 360px;\" src=\"https://www.youtube.com/embed/"
							+ _videoLink + "?rel=0\" frameborder=\"0\" allowfullscreen></iframe>"
					);
				} else {
					mdialog.find(".thankyouform").html("<p>No video attached.</p>");
				}
				return mdialog;
			},
			hide: function (mdialog) {
				mdialog.find("#videoView .thankyouform").html("");
				return mdialog;
			}
		});
		$("#audioView").mdialog({
			showokbtn: false,
			showcancelbtn: false,
			showfooter: false,
			overflow: "auto",
			centre: true,
			width: "400px",
			height: "110px",
			show: function(mdialog) {
				if (_audioLink.length > 0) {
					mdialog.find(".thankyouform").html(
						"<audio controls=\"controls\" style=\"width: 100%; height: 71px; margin-top: -20px;\"><source src=\"" + _audioLink
							+ ".mp3\" type=\"audio/mp3\" /><source src=\"" + _audioLink
							+ "\" type=\"audio/wav\" /><embed type=\"application/x-shockwave-flash\" src=\"https://www.google.com/reader/ui/3523697345-audio-player.swf\" quality=\"best\" flashvars=\"audioUrl="
							+ _audioLink + ".mp3\" width=\"100%\" height=\"27\"></embed></audio>"
					);
				} else {
					mdialog.find(".thankyouform").html("<p>No audio attached.</p>");
				}
				return mdialog;
			},
			hide: function (mdialog) {
				mdialog.find (".thankyouform").html("");
				return mdialog;
			}
		});
		$("#sendthankyouform").mdialog({
			overflow: "auto",
			centre: true,
			width: "500px",
			height: "300px",
			okclick: function() {
				$(".dialogcontent form.sendThankYouMessage").submit();
				return false;
			}
		});
		$("#sendthankyouresult").mdialog({
			showheader: false,
			showcancelbtn: false,
			overflow: "auto",
			centre: true,
			width: "500px",
			height: "150px"
		});
	};

	var _initButtonEvents = function() {
		$('#messages a.videoPlay').on("click", function() {
			_videoLink = $(this).data("videoLink");
			$('#videoView').mdialog('show');
			_videoLink = "";
			return false;
		});
		$('#messages a.audioPlay').on("click", function() {
			_audioLink = $(this).data("audioLink");
			$('#audioView').mdialog ('show');
			_audioLink = "";
			return false;
		});
		$("#openthankyouform").on("click", function() {
			$("#sendthankyouform").mdialog("show");
			return false;
		});
	};

	var _destroyEventListeners = function() {
		$("#openthankyouform").off();
		$("#sendSms").off();
		$("form.sendThankYouMessage").off();
	};

	var _listenSendSmsForm = function() {
		var loader = $("<img/>").attr("src", _loaders.arrows).css("margin", "10px 0 0 120px");

		$("#sendSms")
			.on("submit", function() {
				var $this = $(this);
				if (!$this.valid()) {
					return false;
				}
				$this.find("span.error").hide();
				$this.find("input:submit").attr("disabled", true).before(loader);
				$.ajax({
					type: "post",
					url: "/claim/sendSms",
					data: $this.serialize()
				}).done(function() {
					$this.html('Great, you should get your card on your mobile phone shortly.')
						.css({
							"border": "1px solid #7f9f60",
							"background": "#f4f7f2",
							"font-size": "14px",
							"padding": "10px",
							"height": "auto"
						})
						.delay(8000).slideUp()
						.prev("h2")
						.delay(8500).fadeOut();
				}).fail(function() {
					// @todo add failure handling code
				});
				return false;
			});
	};

	var _listenSendThankYouMessageForm = function() {
		var $form = $(".dialogcontent form.sendThankYouMessage")
			, guid = $form.find("input:hidden[name='giftGuid']")
			, message = $form.find("textarea[name='message']")
			, dialogFooter = $(".dialogfooter")
			, okButton = dialogFooter.find(".btnOK")
			, cancelButton = dialogFooter.find(".btnCancel")
			, loader = $("<img/>").attr("src", _loaders.arrows).css("margin", "8px 0 0 356px");
		// Form validation
		$form.validate({
			submitHandler: function(form) { },
			errorPlacement: function(error, element) {
				$(element).addClass('error');
				($(element).data('validateErrorTarget'))
					? error.appendTo($($(element).data('validateErrorTarget')))
					: error.appendTo(element.parent());
			}
		});
		message.rules("add", {required: true});
		// Listen to form submit event
		$form.on("submit", function() {
			if ($form.find("textarea.error").length > 0) {
				return false;
			}
			cancelButton.hide();
			okButton.attr("disabled", true).before(loader);
			$.ajax({
				type: "post",
				url: "/claim/sendThankYouMessage",
				data: $form.serialize()
			}).done(function() {
				$("#sendthankyouform").mdialog("hide");
				okButton.removeAttr("disabled").prev("img").hide();
				$("#sendthankyouresult").mdialog("show");
				$("#openthankyouform").hide();
			}).fail(function() {
				// @todo add failure handling code
			});
			return false;
		});
	};

	var _loadPan = function() {
		var guid = $("#barcode").data("guid");
		if (guid == "") {
			return;
		}
		$.ajax({
			method: "get",
			url: "/claim/code/guid/" + guid,
			dataType: "json"
		}).done(function(data) {
			var liFirst = $("#barcode li:first")
				, liFirstLabel = liFirst.find("label");
			if (data.exception.has) {
				_handleLoadPanException(data.exception);
			} else {
				if ((data.pan || data.pinDisplay) && !data.pinOnly) {
					if (/^<img[^>]+>$/.test(data.pinDisplay)) {
						$(data.pinDisplay).one('load', function() {
							liFirstLabel.html(data.pinDisplay);
							_centreIt(liFirstLabel, liFirst.height(), this.height);
						}).each(function() {
							if (this.complete) {
								$(this).load();
							}
						});
					} else if (data.pinDisplay) {
						liFirstLabel.html(data.pinDisplay);
						_centreIt(liFirstLabel, liFirst.height(), liFirstLabel.height());
					} else {
						liFirstLabel.html(data.pan);
						_centreIt(liFirstLabel, liFirst.height(), liFirstLabel.height());
					}
				} else {
					liFirstLabel.html("");
				}
				if (data.pin) {
					$("#barcode li:last span")
						.html((data.pinFormat) ? data.formattedPin : data.pin);
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
		$("#barcode li:first label").html("Error!");
		$("#barcode li:last span").html("");
		$("#recipient").hide();
		$("#messages ul")
			.append($("<li/>").html(exception.message))
			.find("li:not(:last)")
			.remove();
	};

	var _centreIt = function(targetElement, parentHeight, childHeight) {
		var position = {top: 0, left: 0};
		childHeight = ($.browser.msie) ? targetElement.height() : childHeight;
		position.top = (parentHeight - childHeight) / 2;
		targetElement.css({"padding-top": ((position.top < 0) ? 0 : position.top) + "px"});
	};

	var _isClaimPage = function() {
		return ($("#claim").length > 0 && $("#expired").length == 0);
	};

	// Public static methods
	return {
		init: function() {
			if (!_isClaimPage()) {
				return;
			}
			_preloadAjaxLoaders();
			_loadPan();
			_initDialogs();
			_initButtonEvents();
			_listenSendSmsForm();
			// Skip listening the form if thank you message has been sent already
			if ($("#sendthankyouform").length > 0) {
				_listenSendThankYouMessageForm();
			}
		}

		, destruct: function() {
			if (!_isClaimPage()) {
				return;
			}
			_destroyEventListeners();
		}

		, is: function() {
			return _isClaimPage();
		}
	};
}();

~function() {
	Claim.init();
	$(window).on("beforeunload", function() {
		Claim.destruct();
	});
}();
