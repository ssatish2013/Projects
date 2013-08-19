window.PF = $.extend( true, window.PF || {}, {

	settings: function() {

		PF.admin.section
			.delegate("td.value", "click", function() {

				var $this = $(this);

				$this.attr("contenteditable", true).data('setting', $.trim( $this.text() ))
					.focus().closest("tr").addClass("editing");

			})
			.delegate(".save", "click", function() {

				var $this	= $(this),
					tr		= $this.closest("tr").removeClass("editing"),
					value	= tr.find(".value"),
					encrypted,
					category,
					key;

				if ( $.trim( value.text()) != value.data("setting") ) {
					key = tr.find(".key");
					category = tr.prevAll(".category:first");

					$.publish("/admin/ajaxStatus", ["loading", "Saving " + key.text() ]);

					$.ajax({
						type: "post",
						data: {
							key : $.trim( key.text()),
							value: $.trim( value.text()),
							category : $.trim( category.text() ),
							encrypted : 1 == value.data('encrypted')
						},
						success: function() {
							$.publish("/admin/ajaxStatus", ["success", "Setting " + key.text() + " saved successfully" ]);
						}
					});
				}
				
			});
	}


});
