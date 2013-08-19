window.PF = $.extend(true, window.PF || {}, {

	actionLogs : (function() {

		var wrap = PF.admin.section, methods = {

			init : function() {
				// setup events
				wrap.delegate("#actionLogSearchForm", "submit", function(e) {
					$.publish("/actionLogs/doSearch");
					e.preventDefault();
				}).delegate("#searchAction", "change", function(e) {
					$this = $(this);
					action = $this.val();

					if (action == 'custom') {
						$("#searchCustom").slideDown();
					} else {
						$("#searchCustom").slideUp();
					}
				});
				$(".loadActionLog").live("click", function() {
					$.publish("/actionLogs/loadLog", [ $(this) ]);
				});
				$(".loadActionLog.loaded").live("click", function(e) {
					row = $(this);
					row.next().find('div').slideUp(400, function() {
						row.next().slideUp().remove();
						row.removeClass('loaded');
					});
				});

				// handle events
				$.subscribe("/actionLogs/doSearch", function() {
					form = $("#actionLogSearchForm");
					form.find('.buttons').addClass('loading');
					form
							.ajaxSubmit({
								type : "post",
								dataType : "json",
								success : function(json) {
									form.find('.buttons')
											.removeClass('loading');
									$.publish("/actionLogs/doSearchResponse",
											[ json ]);
								}
							});
				});

				$.subscribe("/actionLogs/loadLog", function(row) {
					if (row.hasClass('loaded')) {
						return;
					}
					$.ajax({
						type : "post",
						dataType : "json",
						data : {
							action : 'loadActionLog',
							actionLogId : row.data('actionLogId')
						},
						success : function(json) {
							$.publish("/actionLogs/loadLogResponse", [ json,
									row ]);
						}
					});
				});

				// ajax events
				$.subscribe("/actionLogs/doSearchResponse", function(data) {
					PF.template.render("actionLogList", {
						actionLogs : data
					}, function(html) {
						$('#actionLogList').html(html);
					});
				});

				$.subscribe("/actionLogs/loadLogResponse", function(data, row) {
					PF.template.render("actionLogEntry", {
						actionLog : data,
						cols : row.children().length
					}, function(html) {
						row.after(html);
						row.next().find('div').slideDown();
						row.addClass('loaded');
					});
				});

			}
		};

		return methods;
	})()
});
