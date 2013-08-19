(function( $ ) {

  $.extend( $, {

    modal: (function( method, options ) {

      var body        = $(document.body)
      ,   slice       = [].slice
      ,   defaults    = {
        width : 480
      },  options     = {}
      ,   screen      = $('<div id="screen" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1001;"></div>')
      ,   modal       = $('<div id="modal" style="position: absolute; top: 50%; left: 50%; z-index: 1002"><div id="modalBackground" style="border-radius: 8px; -moz-border-radius: 8px; -webkit-border-radius: 8px; opacity: 0.7; background-color: #525252; position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 3;"></div><div id="modalContent" style="overflow: hidden; background: #fff; position: relative; margin: 10px;  z-index: 400"></div></div>')
      ,   background  = modal.find("#modalBackground")
      ,   content     = modal.find("#modalContent")
	  ,   innerScreen	  = $('<div></div>', {
		  id : "modalScreen",
		  css : {
			position : "absolute",
			top : 0,
			left : 0,
			width : "100%",
			height : "100%",
			background : "#fff url(//gc-pf.s3.amazonaws.com/common/ajax_loader_large.gif) no-repeat scroll center",
			display: "none"
		}
	  })
      ,   methods     = {

        _applyOptions: function() {

          content
            .css({
              width : options.width
            })
            .html( options.content )

          this.updatePosition();

        },

		_beforeComplete : function() {

			var newContent	= modal.find(".newContent"),
				content     = modal.find("#modalContent")
				newWidth	= Math.max.apply( Math, $.map(newContent.children(), function( v, i ) {
					return $(v).outerWidth();
				})),
				newHeight	= newContent.height(),
				adjustments = {
					marginLeft : -(( newWidth + 20 ) / 2)
				};

				if ( window.top == window ) {
					adjustments.marginTop = -(( newHeight + 20 ) / 2)
				}

			// Readjust modal
			$.when(
				content.animate({
					height : newHeight,
					width : newWidth
				}),
				modal.animate( adjustments )
			).done(function() {

				innerScreen.fadeOut(function() {
					innerScreen.detach().children().remove();
				});
			});
		},

		updateContent: function( options ) {

			var newContent = $( '<div class="newContent">' + options.content + "</div>" ),
				height,
				transition,
				currentHeight,
				currentWidth,
				children;
			
			// Get the content
			content     = modal.find("#modalContent")

			// Grab children
			children = content.children();

			// Lock the dimensions
			currentHeight = content.height();
			currentWidth = content.width();
			content.css({
				"height"	: currentHeight,
				"width"		: currentWidth,
				"overflow"	: "hidden"
			});


			// Append the inner screen and fade it in
			content.append( innerScreen );

			if ( options.transitionText ) {
				height = innerScreen.height()
				transition = $("<div />", {
					id : "modalTransition",
					html : "<h1>" + options.transitionText + "</h1>",
					css : {
						lineHeight : height + "px",
						position: "absolute",
						top : "50%",
						left: "50%",
						marginTop : (-height / 2) + "px"
					}
				});
				innerScreen
					.append( transition )
					.css("visibility", "hidden")
					.css("display", "block");

				transition
					.css("width", "100%")
					.css("marginLeft", - ( transition.children("h1").outerWidth() / 2 ) + "px" )
					.css("width", "auto");

				innerScreen
					.css("display", "none")
					.css("visibility", "visible");
			}

			innerScreen.fadeIn( 500, function() {

				children.replaceWith( newContent );

				if ( options.beforeComplete ) {
					options.beforeComplete( methods._beforeComplete );
				} else {
					methods._beforeComplete();
				}
							  
			});

		},

        updatePosition: function() {

          var height, width;

          // Hide modal in DOM
          modal.css("visibility", "hidden").show();

          // Grab height and width
          width   = modal.width();
          height  = modal.height();
            
          modal
            .css({
              marginLeft : - ( width / 2 ),
              marginTop : - ( height / 2 )
            });

          if ( window.top == window ) {
            modal.css("position", "fixed");
          } else {
            modal.css({
              position : "absolute",
              top : "100px",
              marginTop : 0
            });
          }

          modal
            .css("visibility", "visible");
        },
	

        option: function( key, value ) {

          if ( $.isPlainObject( key ) ) {
            $.extend( options, key );
          } else {
            options[key] = value;
          }

          this._applyOptions();

        },

        show: function() {

          screen.show(0, $.proxy( options.onOpen || $.noop, modal ));
          modal.show();

        },
		cancel: function() {
			var e = $.Event("modalCancel");
			modal.trigger( e );
			if ( ! e.isDefaultPrevented() ){
				modal.hide();
				screen.hide();
				content.css({
					width : "auto",
					height : "auto"
				});
			}
		},

        close: function() {
			var e = $.Event("modalClose");
			modal.trigger( e );
			if ( ! e.isDefaultPrevented() ){
				modal.hide();
				screen.hide();
				content.css({
					width : "auto",
					height : "auto"
				});
			}
        },
        
        init: function( args ) {

          var methods = this;

          // Only one modal per page
          if ( ! modal.data("modal") ) {

            // Add to body
            body.append( modal ).append( screen );

            // Save state
            modal.data("modal", {
              state : "closed"
            });

            modal.delegate(".close, .cancel", "click", function() {
			//@todo maybe make close a callback with the ability to cancel default behavior
              methods.cancel();
              return false;
            });
          }

          // Extend options
          this.option( $.extend( defaults, args ));
          this.show();

		  return modal;
        }

      };

      return function( method, options ) {

        return ( methods[ method ] || ( methods.init )).call( methods, ( options || method )) || modal;

      }

    })()

  });

})( jQuery );
