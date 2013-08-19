window.PF = $.extend( true, window.PF || {}, {

	// Rename your property
	admin: (function() {
		var section = $('body > section'),
            methods = {
/** /
                history : function() {

                    function loadSection( href ) {
                        return $.ajax({
                            url : href
                        });
                    }

                    function loadJS( href ) {
                        var last = href.split('/').pop();
                            return window.PF[last] || $.getScript('/js/views/' + href + '.js', function() {
                                window.PF[last] && (window.PF[last] || window.PF[last].init)();	
                            });
                    }

                    // History navigation
                    $('nav').delegate('a', 'click', function() {

                        // Grab href
                        var $this = $(this),
                                href = $this.attr('href');

                        $.when( loadSection( href ), loadJS( href ) )
                            .done( function( results ) {
                                section.html( results.shift() );
                            });


                        $this
                            .addClass('active')	
                            .siblings('.active')
                            .removeClass('active');

                        window.history.pushState({
                            path: "" + window.location
                        }, '', href );

                        return false;
                    });



                }
/**/
            },
            ajaxStatus = (function() {
				var ajaxStatus,
                    timeout;

				return function( className, contents, cb ) {
                    
                    // Grab ajaxStatus
					if ( ! ajaxStatus ) { 
                        ajaxStatus = $('#ajaxStatus');
                    }

                    // Apply class
					ajaxStatus.attr('class', className);

                    // Set contents
					if ( contents ) {
						ajaxStatus.html( contents );
					}

                    // Show the status
					ajaxStatus.show();

                    // Fire callback
					(cb || $.noop).call( ajaxStatus );

                    // Debounce fadeOut
					clearTimeout( timeout );
					timeout = setTimeout(function() {
						ajaxStatus.fadeOut();
					}, 10000);

				}
			})()


		// Return object literal of public functions
		// and variables - if you return an init property
		// on the literal, that will be called on dom ready
		return {
			section : section,
            ajaxStatus : ajaxStatus,
			init : function() {

				if ( Modernizr.history ) {
				//	methods.history();
				}

		  }
		};

	})()

});
