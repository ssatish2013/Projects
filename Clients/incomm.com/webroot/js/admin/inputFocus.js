window.PF = $.extend( true, window.PF || {}, {

	inputFocus: function() {
		$(document.body)
			.delegate('input[type=text], input[type=password], textarea, select', 'focus blur', function( e ) {
				$(this).closest("label, li")[( e.type == "focusin" ? "addClass" : "removeClass" )]('focus');
			});
	}

});
