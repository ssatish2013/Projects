window.PF = $.extend( true, window.PF || {}, {

	template: (function() {

        var cache = {},
        methods = {
            get : function( templateName ) {
                return cache[ templateName ] || $.get("/js/templates/" + templateName + ".js?" + (+new Date), function( rawTemplate ) {
                    cache[ templateName ] = rawTemplate;
                }, 'text');
            },
            render : function( templateName, data, callback ) {
				if ( $.isFunction( data )) {
					callback = data;
					data = {};
				}
                return $.when( methods.get( templateName ), data ).done( function( template, data ) {
					var template = _.template( cache[ templateName ], data );

                    callback( template );
                });
            },
            init: function() {
                $.subscribe("/template/preload", function( templates ) {
                    setTimeout(function() {
                        $.each( templates, function( i, templateName ) {
                            methods.get( templateName );
                        });
                    }, 500);
                });
            }
        };

        return methods;

	})()

});
