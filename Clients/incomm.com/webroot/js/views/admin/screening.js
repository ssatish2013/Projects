window.PF = $.extend( true, window.PF || {}, {

	onReady: function() {
		$('#clearButton').click(function() { 
			$('form input:radio').attr("checked", false);
		});
		$('#refreshButton').click(function() { 
			document.location.href = '/admin/screening';
		});
	}


});
