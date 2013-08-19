
var Cart = function() {
	var _initButtonEvents = function() { 
		$("#cart div.remove > a.update").on("click", function(event) {
			var dialog = $("#dialogRemoveItem");
			dialog.mdialog({ 
				showheader: true
				, showfooter: true
				, centre: true
				, width: "620px"
				, height: "70px"
				, okclick: function() {
					var guid = $(event.currentTarget).data("guid")
						, form = dialog.find("form");
					form.find("input[name='messageGuid']").val(guid);
					form.submit();
				}
			}).mdialog("show");
			$(".overlay .dialogcontent").addClass("mdialog");
			return false;
		});
		$("#cart div.edit > a.update").on("click", function(event) { 
			var container = $("#editItemBlock");
			var guid = $(event.currentTarget).data("guid")
			  , form = container.find("form");
			form.find("input[name='messageGuid']").val(guid);
			form.submit();
			return false;
		});
		$("table td.terms a").on("click", function() { 
			$(this).mdialog({ 
				url: "/help/redemptionTerms?pid=" + $(this).attr ("data-pid"),
				showheader: true,
				showfooter: false,
				ioverflowy: "scroll",
				centre: true,
				width: "700px",
				height: "500px",
				showcancelbtn: false,
				showokbtn: false,
				onhide: function (mdialog) {
					mdialog.destroy ();
					return mdialog;
				}
			}).mdialog("show");
			return false;
		});
		
		$('#btncheckout').click(function(event){
			event.preventDefault();
			//check if the form passed valiation
			if (!$('#zerocheckoutform').validate().form()){
				return;
			}
			var c = $('#recaptcha_challenge_field').val(),
			r = $('#recaptcha_response_field').val();
			$.post('/cart/recaptcha',
					{	challenge: c,
						response: r
					},
					function(response){
						if (response && response.success){
							$('#zerocheckoutform').submit();
						}
						else{
							if (Recaptcha) Recaptcha.reload(); 
						}
					},'json');
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
				});
			});
		}
	};
	
	var _destroyButtonEvents = function() { 
		$("#cart div.edit > a.button").off();
		$("#cart div.remove > a.button").off();
	};
	
	var _isCartPage = function() {
		return ($("#cart").length > 0);
	};
	
	return {
		init: function() {
			if (!_isCartPage()) {
				return;
			}
			_initButtonEvents();
		}

		, destruct: function() {
			if (!_isCartPage()) {
				return;
			}
			_destroyButtonEvents();
		}

		, is: function() {
			return _isCartPage();
		}
	};
}();

~function() {
	Cart.init();
	$(window).on("beforeunload", function() {
		Cart.destruct();
	});
}();
