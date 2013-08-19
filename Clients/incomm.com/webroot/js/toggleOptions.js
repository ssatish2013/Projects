$( function() {
	var mode = findURLparam('mode');
        var url = window.parent.location.href;
        var param = url.split("/");
        var checkout = param[3];

        if(checkout == "cart") {
            $("#accordion").accordion({ active: 3 });
        } else {
            if(mode == 1) {
                // Group Gifting mode
                $("#accordion").accordion({ active: 1 });
            } else if(mode == 2) {
                // Single Gifting mode
            	// the first node is expanded by default, no need to toggle it
                //$("#accordion").accordion({ active: 0 });
            } else if(mode == 3) {
                // Self Gifting mode
                $("#accordion").accordion({ active: 2 });
            }
        }

        function findURLparam( name ) {
            name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
            var regexS = "[\\?&]"+name+"=([^&#]*)";
            var regex = new RegExp( regexS );
            var results = regex.exec( window.parent.location.href );
            if( results == null ) {
                return "";
            } else {
                return results[1];
            }
        }
});
