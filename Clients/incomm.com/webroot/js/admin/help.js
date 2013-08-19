window.PF = $.extend( true, window.PF || {}, {

	help: (function() {

		return {

			//runs on load
			init: function() { 

				var admin	= $("header .user"),
					h1		= $("#main h1").eq(0),
					link	= $("<span />", {
						"data-article" : $(document.body).data('helpPage'),
						"class" : "helpLink",
						text : PF.langs.help
					});

				//attach a click event to all help links
				$('.helpLink').live('click', PF.help.getHelp);

				//preload the help dialog
				$.publish( "/template/preload", [[ "helpDialog" ]] );

				//attach dialog events
				$('#helpModalClose').live('click', function() { $.modal('close'); });

				$(document.body).delegate( "#modal", "modalClose", function() { 
					//on close, re-enable scrolling outside of the modal
					$("html").css("overflow", "scroll"); 
				});

				// Append link


				if (typeof $(document.body).data('helpPage') != 'undefined') {
					if ( admin.length ) {
						admin.append(" | ").append( link );
					} else if ( h1.length ) {
						h1.append( link );
					}
				}
				
			},

			getHelp: function() {
				$this = $(this);

				//create an ajax call to get the help content
				$.ajax({
					url: '/help',
					data: { 
						//use the data-article attribute on the help link itsel
						article: $this.data('article')
					},
					method: "post",
					dataType : "json",

					//on return...
					success: function(data) { 

						if(data.length == 0) { 
							data = {value: "There was an error retreiving the help for this page"};
						}
						//render the dialog from a template
						PF.template.render( "helpDialog", data, function(output) { 

							//create the modal, specifying a few extra attrs
							myModal = $.modal({
								content : output,
								width: 'auto'
							});

							//disable scrolling outside of the modal
							$("html").css("overflow", "hidden");
						});
					}
				});
			}
		}
	})()

});
