window.PF = $.extend( true, window.PF || {}, {
	selectHandler: function(){
		$('select').change(function(){
			var $this=$(this),form=$('form');
			form.removeClass('requestActiveCode returnActiveCode').addClass($this.val());
		});
	},
	formHandler: function(){
		var templates = $("#template").html(),$console = $("#console");
		$('form').submit(function(){
			var $this = $(this);
			$this.ajaxSubmit({
				type: "post",
				dataType: "json",
        success: function(data) {
					$("#txnId").val(data.newTxnId);
					$("#dateTime").val(data.newDateTime);
					var html = $( _.template( templates, { theData : data }) );
					$console.find("div").addClass('old');
					$console.prepend(html);
					
				}
			});
			return false;
		});
	}
}); 