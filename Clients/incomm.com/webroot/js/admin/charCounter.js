window.PF = $.extend( true, window.PF || {}, {
	charCounter: { 
		
		init : (function() {
      
      function init( elems ) {
        var template = $('<div class="charCounter"><span class="current"></span>/<span class="total"></span></div>'),
            wrap = $('<div class="charCounterWrap" />'),
            body = $(document.body);

        (elems || $('textarea[maxlength]')).each(function() {

          var $textarea = $(this),
              maxlength = parseInt( $textarea.attr('maxlength'), 10 ),
              counter		= template.clone(),
              countWrap = wrap.clone(),
              current   = counter.find('.total').text( maxlength ).siblings('.current').text( $textarea.val().length );

          // Copy styles
          $.each(['float'], function( i, v ) {
            countWrap.css( v, $textarea.css( v ) );
          });

          // Append to document
          $textarea.wrap( countWrap );
          $textarea.after( counter );
          
          $textarea.bind('keyup.charCounter', function() {
            setTimeout(function() { current.text( $textarea.val().length ); }, 0);
          }).bind('focus', function() {
            counter.show();
          }).bind('blur', function() {
            counter.fadeOut();
          });

        });
      }

      return init;
    })()

  }
	
});
