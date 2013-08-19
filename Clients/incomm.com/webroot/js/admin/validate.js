window.PF = $.extend( true, window.PF || {}, {

	validate : {

		regex : {
			email : /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i
		},

		fn : {
			required : function( input ) {
				if ( ! $.trim( input.val())) {
					return "* " + input.attr('title') + " " + PF.langs.fieldIsRequired + ".";
				}
			},
			requiredcheckbox : function ( input ) {
				if ( ! input.is(":checked") ) {
					return "* " + input.attr("title") + " " + PF.langs.fieldIsRequired + ".";
				}
			},
			requiredradio : function( radios ) {
				var checked = radios.filter(':checked');
				if ( ! checked.length || ! checked.val() ) {
					return "* " + radios.eq(0).attr('title') + " " + PF.langs.fieldIsRequired + ".";
				}
			},
			creditCard : function ( input ) {
				// From: http://www.brainjar.com/js/validation/default2.asp
				var r = input.val().replace(/[^\d]/gi, '').split('').reverse().join('');
				
				// Run through each single digit to create a new string. Even digits
				// are multiplied by two, odd digits are left alone.

				var t = "";
				for (var i = 0; i < r.length; i++) {
					var c = parseInt(r.charAt(i), 10);
					if (i % 2 != 0)
						c *= 2;
					t = t + c;
				}

				// Finally, add up all the single digits in this string.

				var n = 0;
				for (i = 0; i < t.length; i++) {
					c = parseInt(t.charAt(i), 10);
					n = n + c;
				}

				if( n == 0 || n % 10 != 0 ){
					return PF.langs.invalidCreditCardNumber;
				}
			},
			cvv : function ( input ) {
				var numbers = input.val().replace(/[^\d]/gi, '');
				if( numbers.length <3 || numbers.length >4 ){
					return PF.langs.invalidCvvSecurityCode;
				}
			},
			phone : function( input ) {
				var numbers = input.val().replace(/[^\d]/gi, '');
				if ( ! /^[0-9]{10,11}$/.test( numbers )) {
					return PF.langs.invalidPhoneNumber;
				}
			},
			zip : function( input ) {
				if ( ! /[\w\d-]/.test( input.val() )) {
					return PF.langs.invalidZipPostalCode;
				}
			},
			email : function( input ) {
				if ( ! PF.validate.regex.email.test( input.val() )) {
					return input.val() + " " + PF.langs.emailAddressNotValid + "."	
				}
			},
			max : function( input, max ) {
				var num = parseInt( input.val(), 10 );
				if ( isNaN( num ) || num > parseInt( max, 10 )) {
					return input.attr("title") + " " + PF.langs.mustBeLessThan + " " + max;
				}
			},
			min : function( input, min) {
				var num = parseInt( input.val(), 10 );
				if ( isNaN( num ) || num < parseInt( min, 10 )) {
					return input.attr("title") + " " + PF.langs.mustBeGreaterThan + " " + min;
				}
			},
			maxLength : function( input, length ) {
				length = input.attr('maxlength') || length;
				if ( input.val().length > parseInt( length, 10 )) {
					return input.attr('title') + " " + PF.langs.cannotBeLongerThan + " " + length + " " + PF.langs.characters + ".";
				}
			},
			matches : (function() {
				var cache = {};	
				return function( input, id ) {
					var elem = cache[id] || ( cache[id] = $('#' + id) );
					if ( input.val().toLowerCase() !== elem.val().toLowerCase() ) {
						return input.val() + " " + PF.langs.doesNotMatch + " " + elem.attr('title').toLowerCase() + ".";
					}
				}
			})()
		},

		input : function( input, specialType ) {

			var name = input.eq(0).attr('name'), fns, message;

			if (( fns = input.data('validate') || ( PF.validate.elems && PF.validate.elems[ name ] ))) {

				$.each( fns, function( i, v ) {
					var parts, method;
					if ( $.isFunction( v ) ) {
						message = v( input );
					} else {
						parts = v.split(":");
						method = parts.shift();
						parts.unshift( input );
						message = ( PF.validate.fn[method + specialType] || PF.validate.fn[method] || $.noop ).apply(this, parts);
					}

					if ( message ) {
						return false;
					}
				});

				input.data( 'PF', $.extend( input.data('PF') || {}, {
					validate : {
						validated : true
					}
				}));

				PF.validate.display( (function() {
					var checked = input.filter(":checked");
					return checked.length ? checked : input.eq(0);
				})(), message );
			}
			return ! message;
		},

		form : function( form ) {
			var valid = true, radios  = {}, first;
				
			// Gather radio groups
			form.find('input[type=radio]').each(function( i, v ) {
				var $this = $(v),
					name = $this.attr('name');
				if ( ! radios[name] ) {
					radios[name] = [];
				}
				radios[name].push( v );
			});
			
			// Validate radios
			$.each( radios, function( key, value ) {
				var $value = $(value);
				if ( ! PF.validate.input( $value, $value.eq(0).attr('type') )) {
					valid = false;	
				}
			});
			
			// Validiate texts
			form.find('input, textarea').not('[type=radio], [type=radio], [type=submit], :hidden').add('input[type=hidden]', form).each(function( i, v ) {
				if ( ! PF.validate.input( $(v) , $(v).attr("type"))) {
					valid = false;	
				}
			});

			// Scroll to first
			if ( ( first = $('em.error') ).length ) {
				$("html, body").animate({
					scrollTop : first.offset().top - 24
				});
			}
			
			return valid;
		},

		defaults : {

			handle: function( input, message, options ) {

				if ( ! message ) {
					if ( input.attr("type") == "radio" ) {
						$("input[name=" + input.attr("name") + ']').mustard("hide");
					} else if ( input.hasClass("mustardized")) {
						input.mustard("hide");
					} 
					return;
				}

				var settings = {
					content: message,
					show: {
						event: false
					},
					hide: {
						event: false
					},
					css: {
						theme: 'error'
					},
					position: 'right'
				},  $this = $(this)
				,   hide = function(){
					$this.mustard('hide')
				};

				if ($(document.body).hasClass("branded")){
					settings.position = 'left';
				}

				// Extend options
				if ( options ) {
					$.extend( settings, options );
				}

				input.mustard( settings );

			}
		},

		handle: {},

		display : function( input, message ) {
			message !== false && ( PF.validate.handle[ input.attr('name') ] || PF.validate.defaults.handle )( input, message );
		},

		// Check whether or not the given element is required
		// If the element has attribute validate="['required']" or ["required"] defined in PF.validate.elems
		// This element is considered as required
		isRequired: function(input) {
			var name = input.eq(0).attr('name')
				, fns = input.data('validate') || (PF.validate.elems && PF.validate.elems[name]);
			if (fns !== undefined) {
				return ($.inArray("required", fns) != -1);
			}
		},

		init: function() {

			$(document.body).delegate('form.validate input, form.validate textarea', 'change', function() {
				var $this = $(this),
					data	= $this.data('PF');
				if( $this.data('skipchangevalidate') ){
					return;
				}
				if ( $this.attr("type") == "radio" ) {
					PF.validate.input( $('input[name=' + $this.attr("name") + ']') );
				} else if ( $.trim( $this.val() )) {
					PF.validate.input( $this );	
				}
			}).delegate('form.validate', 'submit', function( e ) {

				
				var $this = $(this),
								firstError;

				if ( PF.validate.form( $(this) )) {
					$this.addClass('submitted');
					return true;
				} else {


					setTimeout(function() {
				
						firstError = $('.mustardized.mustard-visible').eq(0);
						if ( firstError.length ) {
							$('html, body').animate({
							scrollTop: firstError.offset().top - 40
							}, 500);
						}
					}, 0);
					e.stopImmediatePropagation();
					return false;
				}
			});
		}
	}
});
