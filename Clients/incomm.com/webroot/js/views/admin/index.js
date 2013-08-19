window.PF = $.extend( true, window.PF || {}, {

	preview: function() {
		$.publish('/preload/add', ['//gc-pf.s3.amazonaws.com/common/ajax_loader_large.gif']);
		$('.buttons').delegate('a', 'click', function() {
			$(this).parent().parent().addClass('submitted');
		});
	}


});
