window.PF = $.extend( true, window.PF || {}, {

	apiLogs: (function() {

		var wrap		= PF.admin.section,
			methods		= {

				init : function() {
          //setup events
					wrap
          .delegate("#apiLogSearchForm", "submit", function( e ) {
            $.publish("/apiLogs/doSearch");
            e.preventDefault();
          })
					.delegate("#searchAction", "change", function(e) { 
						$this = $(this);
						action = $this.val();

						if(action == 'custom') { 
							$("#searchCustom").slideDown();
						}
						else {
							$("#searchCustom").slideUp();
						}
					});
          $(".loadApiLog").live("click", function() {
            $.publish("/apiLogs/loadLog", [$(this)]);
          });
          $(".loadApiLog.loaded").live("click", function(e) {
						row = $(this);
						row.next().find('div').slideUp(400, function() { 
							row.next().slideUp().remove();
							row.removeClass('loaded');
						});
          });


					//handle events
					$.subscribe("/apiLogs/doSearch", function() { 
						form = $("#apiLogSearchForm");
						form.find('.buttons').addClass('loading');
            form.ajaxSubmit({
              type : "post",
              dataType: "json",
              success : function( json ) {
								form.find('.buttons').removeClass('loading');
                $.publish("/apiLogs/doSearchResponse", [json] );
              }
            });
					});

					$.subscribe("/apiLogs/loadLog", function(row) { 
						if(row.hasClass('loaded')) { return; }
          	$.ajax({
              type : "post",
              dataType: "json",
							data: {
								action: 'loadApiLog',
								apiLogId: row.data('apiLogId')
							},
              success : function( json ) {
                $.publish("/apiLogs/loadLogResponse", [json, row] );
              }
            });
					});


					//ajax events
					$.subscribe("/apiLogs/doSearchResponse", function(data) { 
            PF.template.render("apiLogList", { apiLogs: data}, function( html ) {
              $('#apiLogList').html(html);
            });
					});

					$.subscribe("/apiLogs/loadLogResponse", function(data, row) { 
            PF.template.render("apiLogEntry", { apiLog: data, cols: row.children().length}, function( html ) {
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
