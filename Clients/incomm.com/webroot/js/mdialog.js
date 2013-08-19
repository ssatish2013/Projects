(function( $ ){
	var methods = {
     init : function( options ) {
	 //defaults
	 var settings = $.extend({
		'url' : null,
		'top' : '100px',
		'width' : '600px',
		'height' : '300px',
		'centre' : true,
		'overflowx' : 'hidden',
		'overflowy' : 'hidden',
		'ioverflowx' : 'hidden',
		'ioverflowy' : 'hidden',
		'title' : typeof $(this).attr ("data-title") != 'undefined' ? $(this).attr ("data-title") : 'Dialog',
		'okbtntext' : typeof $(this).attr ("data-okbtntext") != 'undefined' ? $(this).attr ("data-okbtntext") : "OK",
		'cancelbtntext' : typeof $(this).attr ("data-cancelbtntext") != 'undefined' ? $(this).attr ("data-cancelbtntext") : 'Cancel',
		'showheader' : true,
		'showfooter' : true,
		'showcancelbtn' : true,
		'showokbtn' : true,
		'cancelclick' : function () { return true; }, //return false to prevent dialog close
		'okclick' : function () { return true; }, //return false to prevent dialog close
		'show' : function (mdialog) { return mdialog; }, // alter the mdialog DOM and return it, executed prior to insertion into document.body
		'hide' : function (mdialog) { return mdialog; } // alter the mdialog DOM and return it, executed prior to hiding
		}, options);
       return this.each(function(){
          var $this = $(this),
			data = $this.data('mdialog'),
			mdialog = $('<div class="overlay"><div><span class="btnClose"><a>CLOSE</a></span><div><div class="dialogheader"><a></a></div><div class="dialogcontent"></div><div class="dialogfooter" style="text-align:center"><a class="btnCancel" href="#"><span>Cancel</span></a><img class="loader hidden" src="/images/loading_small.gif"><input value="Ok" type="button" class="btnOK"></div></div></div></div>');
          	//apply settings
			mdialog.find('.dialogheader a').html(settings.title);
			mdialog.find('div:first').css('width',settings.width);
			mdialog.find('div:first div').css('width',settings.width);
			mdialog.find('.dialogcontent').css('height', parseInt (settings.height) + (settings.showheader ? 50 : 0) + (settings.showfooter ? 100 : 0) + 50 > parseInt ($(window).height ()) ? (parseInt ($(window).height ()) - (settings.showheader ? 50 : 0) - (settings.showfooter ? 100 : 0) - 50) + "px" : settings.height);
			mdialog.find('.dialogcontent').css('overflow-x',settings.overflowx);
			mdialog.find('.dialogcontent').css('overflow-y',settings.overflowy);
			mdialog.find('.btnOK').val(settings.okbtntext);
			mdialog.find('.btnCancel span').text (settings.cancelbtntext);
			if (!settings.showheader)
				mdialog.find('.dialogheader').css('display','none');
			if (!settings.showfooter)
				mdialog.find('.dialogfooter').css('display','none');
			if (!settings.showcancelbtn)
				mdialog.find('.btnCancel').css('display','none');
			if (!settings.showokbtn)
				mdialog.find('.btnOK').css('display','none');
			//setup content
			if (settings.url){
				//use a blank page when initializing, replace with real content when showing.
				var iframe = $('<iframe />',{src: '/blank.html'});
				iframe.css('overflow-x',settings.ioverflowx);
				iframe.css('overflow-y',settings.ioverflowy);
				mdialog.find('.dialogcontent').append(iframe);
			}
			else{
				mdialog.find('.dialogcontent').html($this.html());
			}

			mdialog.find('.btnClose').click(function(){
				mdialog = settings.hide (mdialog);
				mdialog.css('visibility','hidden');
			});
			//make clicking on overlay to close dialog
			mdialog.click(function(event){
				if ($(event.target).hasClass('overlay')){
					mdialog = settings.hide (mdialog);
					mdialog.css('visibility','hidden');
				}
			});
			mdialog.find('.btnCancel').click(function(){
				if (settings.cancelclick && settings.cancelclick(this))
					mdialog.css('visibility','hidden');
			});
			mdialog.find('.btnOK').click(function(){
				if (settings.okclick && settings.okclick(this))
					mdialog.css('visibility','hidden');
			});

			//append to body
			$('body').append(mdialog);
			// If the plugin hasn't been initialized yet
			if (!data) {
				/*
				 Do more setup stuff here
				*/
				//store the reference
				$(this).data('mdialog', {
				   source : $this,
				   mdialog : mdialog,
				   settings : settings
				});
			}
       });
     },
     destroy : function( ) {
       return this.each(function(){
         var $this = $(this),
             data = $this.data('mdialog');
         // Namespacing FTW
         $(window).unbind('.mdialog');
         data.mdialog.remove();
         $this.removeData('mdialog');
       })
    },
	show : function( ) {
		var mdialog = this.data('mdialog').mdialog
			, settings = this.data('mdialog').settings
			, viewport = $(window)
			, dialogContent = mdialog.find('.dialogcontent').parent('div')
			, scrollTop = viewport.scrollTop()
			, marginTop;
		//load content if using iframe
		if (settings.url){
			dialogContent.find('iframe').attr('src',settings.url);
		}
		// Adjust overlay height to cover whole document body
		mdialog.height($(document).height());
		// Adjust dialog margin-top to have it vertically centred if centre option
		// in settings is turned on
		if (settings.centre) {
			// (Window.height - dialog.height) / 2 - adjustedHeight
			mdialog.find('div:first').css('margin-top', (viewport.height() - dialogContent.height()) / 2 - 20);
			$(window).resize (function () {
				mdialog.height($(document).height());
				mdialog.find('.dialogcontent').css('height', parseInt (settings.height) + (settings.showheader ? 50 : 0) + (settings.showfooter ? 100 : 0) + 50 > parseInt ($(window).height ()) ? (parseInt ($(window).height ()) - (settings.showheader ? 50 : 0) - (settings.showfooter ? 100 : 0) - 50) + "px" : settings.height);
				mdialog.find('div:first').css('margin-top', (viewport.height() - dialogContent.height()) / 2 - 20);
			});
		} else {
			mdialog.find('div:first').css('margin-top', settings.top);
		}
		mdialog = settings.show (mdialog);
		mdialog.css('visibility','visible');
	},
	hide : function( ) {
    	var mdialog = this.data('mdialog').mdialog,
    	settings = this.data('mdialog').settings,
    	mdialog = settings.hide(mdialog);
    	mdialog.css('visibility','hidden');
    },
    showLoader : function (){
    	var mdialog = this.data('mdialog').mdialog;
    	mdialog.find(".loader").show();
    },
    hideLoader : function(){
    	var mdialog = this.data('mdialog').mdialog;
    	mdialog.find(".loader").hide();
    }
  };
  $.fn.mdialog = function( method ) {

    if ( methods[method] ) {
	  if (!this.data('mdialog'))
		methods.init.apply( this, arguments );
      return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return methods.init.apply( this, arguments );
    } else {
      $.error( 'Method ' +  method + ' does not exist on jQuery.mdialog' );
    }

  };
})( jQuery );
