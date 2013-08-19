window.PF = $.extend( true, window.PF || {}, {
	preload : (function(){

		var list = [],
				inited = false,

				preload = function( i, v ) {
					$('<img />', {
						src : this || v
					});
				},

				init = function() {
					inited = true;
					$.each( list, preload );
				},
				
				add = function( res ) {

					if ( $.isArray( res ) ) {
						inited ? $.each( res, preload ) : list.concat( res );
					} else {
						inited ? preload.call( res ) : list.push( res );
					}
				}

		// Pub sub!
		$.subscribe('/preload/add', add);

		return {
			init : init
		};

	})()
});
