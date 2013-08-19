window.PF = $.extend( true, window.PF || {}, {
	formHandlers: function(){
		$('form.ajax').submit(function(){
			$(this).ajaxSubmit({
				type: "post",
        success: function(data) {
					console.log(data);
					PF.template.render("adminMigration", {res:data}, function( generatedHtml ) {
						$("#"+data.type+"Container").html(generatedHtml);
					});
        }
			});
			return false;
		});
	},
	buttonHandlers: function(){
		$('td input[type=button]').live('click', function(){
			var $this = $(this);
			if($this.val()=="DEL"){
				if(!confirm("Are you sure you want to delete the destination record?")){
					return;
				}
			}
			$this.attr('disabled','disabled');
			$.ajax({
				dataType: 'json',
				url: '/admin/doMigration',
				type: 'POST',
				data:$this.data(),
				success: function(data){
					if(data.success){
						$this.parents('tr').fadeOut('slow',function(){});
					} else {
						$this.parents('tr').css('background-color','#FFCCCC');
					}
				}
			});
			
		});
	}
});