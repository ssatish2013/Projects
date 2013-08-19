window.PF = $.extend( true, window.PF || {}, {

	prefillText: function(){
		$("#loadDefaults").click(function(){
			var name = $("#articleType").val();
			$("#valueTextarea").val($("#" + name).data('value'));
		});
	},

	openAddHelp: function(){
		$('#newArticle').click(function(){
			$('#newArticle').fadeOut('fast', function(){
				$('#newArticleForm').slideDown();
			});
		});
	},

	helpArticle: function() {

		PF.admin.section.delegate("td.value", "click", function() {

			var $this = $(this);

			$this.attr("contenteditable", true).data('monitoring', $.trim( $this.text() )).focus().closest("tr").addClass("editing");
		}).delegate(".save", "click", function() {

			var $this	= $(this),
					tr		= $this.closest("tr").removeClass("editing"),
					name	= tr.find(".key"),
					id		= tr.find(".key").data('id'),
					value = tr.find(".value");

			$.publish("/admin/ajaxStatus", ["loading", "Saving " + name.text() ]);

			$.ajax({
				url: "/admin/editHelpText",
				type: "post",
				data: {
					id : id,
					value : $.trim( value.text())
				},
				success: function() {
					$.publish("/admin/ajaxStatus", ["success", "Setting " + name.text() + " saved successfully" ]);
				}
			});
		});
	}
});