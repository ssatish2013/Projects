window.PF = $.extend( true, window.PF || {}, {
	insetButtons: {
		init: function() {
			$('.insetButtons').delegate('label', 'click', function() {
				$(this).closest('li').addClass('checked').siblings('.checked').removeClass('checked');
			}).find('input:checked').trigger('change').trigger("click");
		}
	}
});
