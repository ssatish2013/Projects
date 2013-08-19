window.PF = $.extend( true, window.PF || {}, {

	// Rename your property
	language: function() {
        var status  = $("section .status"),
            link    = $("#langEditLink");

        $('button').click(function() {
           $.ajax({
                type: "post",
                dataType: "json",
                data: {
                    action : 'toggle'
                },
                success: function( json ) {

                    if ( json.status ) {
                        status.text("ON").removeClass("off").addClass("on");
                        link.show();
                    } else {
                        status.text("OFF").removeClass("on").addClass("off");
                        link.hide();
                    }
                    $('section button').text("Turn " + (json.status ? "OFF" : "ON"));
                }
           });
        });
    }

});
