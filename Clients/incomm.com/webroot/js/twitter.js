window.PF = $.extend( true, window.PF || {}, {

	twitter: (function() {
		return {
			callback: function ( token, secret ){
				$("#twitterToken").val(token);
				$("#twitterSecret").val(secret);
				$("#deliveryMethodTwitter").data('loggedIn',true);
				//run setup again
				PF.deliveryMethod.twitter.setup();
			},
			callbackCancel: function(){

			},
			invite: function( data, fn ) {
				var title = data.twitterTitle;
				var description = data.twitterDescription;
				var contentString = 'https://twitter.com/share?text=' + encodeURIComponent(description) + '&url=' + data.contributeUrl;
				window.open(contentString, 'twitter', 'width=600,height=400,location=no,left=100,top=100');
			}
		};
	})()
});
