window.PF = $.extend( true, window.PF || {}, {

	openAddMonitoring: function(){
		$('#newMonitor').click(function(){
			$('#newMonitor').fadeOut('fast', function(){
				$('#newMonitoringForm').slideDown();
			});
		});
	},

	monitoring: function() {

		PF.admin.section
			.delegate("td.value", "click", function() {

				var $this = $(this);

				$this.attr("contenteditable", true).data('monitoring', $.trim( $this.text() ))
					.focus().closest("tr").addClass("editing");

			})
			.delegate(".save", "click", function() {

				var $this	= $(this),
						tr		= $this.closest("tr").removeClass("editing"),

				name = tr.find(".key"),
				id = tr.find(".key").data('id'),
				enabled = tr.find(".enabled"),
				minimumPercent = tr.find(".minimumPercent"),
				maximumPercent = tr.find(".maximumPercent"),
				minimumHardLimit = tr.find(".minimumHardLimit"),
				maximumHardLimit = tr.find(".maximumHardLimit"),
				compareStartTime = tr.find(".compareStartTime"),
				compareEndTime = tr.find(".compareEndTime"),
				currentStartTime = tr.find(".currentStartTime"),
				currentEndTime = tr.find(".currentEndTime");

				$.publish("/admin/ajaxStatus", ["loading", "Saving " + name.text() ]);

				$.ajax({
					type: "post",
					data: {
						id : id,
						enabled : $.trim( enabled.text()),
						minimumPercent: $.trim( minimumPercent.text()),
						maximumPercent: $.trim( maximumPercent.text()),
						minimumHardLimit: $.trim( minimumHardLimit.text()),
						maximumHardLimit: $.trim( maximumHardLimit.text()),
						compareStartTime: $.trim( compareStartTime.text()),
						compareEndTime: $.trim( compareEndTime.text()),
						currentStartTime: $.trim( currentStartTime.text()),
						currentEndTime: $.trim( currentEndTime.text())
					},
					success: function() {
						$.publish("/admin/ajaxStatus", ["success", "Setting " + name.text() + " saved successfully" ]);
					}
				});
				
			});
	}


});
