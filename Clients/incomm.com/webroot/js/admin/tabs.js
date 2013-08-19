window.PF = $.extend( true, window.PF || {}, {

	tabs: function() {

        $('.tabs').delegate('a', 'click', function( e ) {
            var $this = $(this);
            
            // Hide tooltips, if any
            if ( $.fn.mustard ) {
                $('.mustardized').mustard('hide');
            }

            // Change active tab
            $this.closest('li').addClass('active').siblings().removeClass('active');
            
            // Show appropriate content
            $( $this.attr('href') ).siblings().hide().end().fadeIn();

            // Don't navigate
            e.preventDefault();

					$.publish("/tabs/click");
        })
		// Hide other content initially
		.find("li").not(".active").each(function() {
			$( $(this).find("a").attr("href") ).hide();
		});
    }
});
