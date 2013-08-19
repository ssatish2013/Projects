(function( doc ) {
	
	var frame = document.getElementById("pfapp"),
		paramParts = [],
		urlParts = [],
		methods = {
			resize : function( height, scrollUp ) {
				frame.style.height = height + "px";
				if ( scrollUp == "true" ) {
					window.scrollTo(0,0);
				}
			}
		},
		hash, hashParts, paramPart;

	// Construct base
	urlParts.push( "https://" + PF.partner +  ".giftingapp.com/" );

	// Add hash, if any
	hash = location.hash.replace("#", "");
	if ( hash ) {
		urlParts.push( hash.split("?").shift().replace(/^\//, "") );	
		if ( hash.indexOf("?") > -1 ) {
			paramParts.push( hash.split("?").pop() );
		}
	}

	// Add params
	if ( PF.params || paramParts.length ) {
		urlParts.push( "?" );
		for ( paramPart in PF.params ) {
			if ( PF.params.hasOwnProperty( paramPart )) {
				paramParts.push( [ paramPart, PF.params[paramPart] ].join("=") );
			}
		}
		urlParts.push( paramParts.join("&") );
	}

	frame.src = urlParts.join("");

	function message ( e ) {
			var parts = e.data.split(":"),
					method = parts.shift(),
					args;
					
			try{
				if(parts && parts.pop)
					args = parts.pop().split("|");
			} catch (ex){}
			
			if ( methods[ method ] ) {
					methods[ method ].apply( methods, args );
			}
	}

	if ( !! window.postMessage ) {
			if ( !! window.attachEvent ) {
					window.attachEvent( 'onmessage', message );
			} else {
					window.addEventListener("message", message, false);
			}
	} else {
			// IE7 :(
	}

}( document ));
