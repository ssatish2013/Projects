(window.PF || (window.PF = {})).xdm = (function() {

	var message = ( !! window.postMessage ) ?
	// Good browsers \o/
	function( message ) {
		window.top.postMessage( message, "*" );
	} :
	// Bad browsers /o\
	function() {};

	var socket,
		body = $(document.body),
		screen = $('#iframeScreen');

	function resize( scrollUp, FbResizeSettings ) {
		message( "resize:" + body.outerHeight() + "|" + scrollUp );

		FbResizeSettings = FbResizeSettings || {extraHeight: 32, autoResize: false};
		if ( window.FB ) {
			if (("autoResize" in FbResizeSettings) && FbResizeSettings.autoResize) {
				window.FB.Canvas.setAutoResize();
			} else {
				window.FB.Canvas.setSize({
					height: body.outerHeight()
						+ (("extraHeight" in FbResizeSettings)
							? FbResizeSettings.extraHeight
							: 0)
				});
			}
		}
	}
	
	function hideScreen( e ) {

	}

	function init () { 

		if ( window.FB ) {     
			window.FB.Canvas.scrollTo(0,0);
		}
		resize( true );

	}

	return {
		resize: resize,
		hideScreen : hideScreen,
		message: message,
		init : init // will fire automatically
	};

})();
