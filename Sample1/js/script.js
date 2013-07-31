(function() { 
		var Item = Backbone.Model.extend({ 
			defaults: function() { 
				return { 
					first	:	'',
					last	:	'',
					user	:	'',
					email	:	'',
					sex		:	'',
					age		:	'',
					ret		:	''
				}
			}
		});
		
		var ItemList = Backbone.Collection.extend({ 
			model	:	Item
		});
		
		var ItemView = Backbone.View.extend({ 
			model: new Item(),
			tagName: "li",
			initialize: function() { 
				this.template = _.template($("#result-template").html());
			},
			render: function() { 
					this.$el.append(this.template(this.model.toJSON()));
					return this;
			}
		});
		
		var items = new ItemList();
		
		var ItemsView = Backbone.View.extend({ 
			model: items,
			el: 'ul#items',
			initialize: function() {
				this.model.on('add', this.render, this);
			},
			render: function() { 
				var self = this;
				self.$el.html('');
				_.each(this.model.toArray(), function(item, i) { 
					self.$el.append((new ItemView({model: item})).render().$el);
				});
				return this;
			}
		});
		
		$(document).ready( function() { 
		$("form#form1").on( "submit", function() { 
			var formValues = getFormValues();
			
			$.ajax({ 
				type		:	"GET",
				url			:	"xml/error.xml",
				dataType	:	"xml",
				success		: function(xml) {
					var error = validateForm(xml, formValues);
					var node = "";
					var numErrors = 0;
					
					$.each(error, function(key1, value1) { 
						$.each(value1, function(key2, value2) { 
							if(value2 != "") { 
								node = $(xml).find("errors > " + key1.toString() + " > " + value2.toString()).text();
								$("input[name=" + key2.toString() + "]").parents(":eq(1)").addClass("error");
								$("input[name=" + key2.toString() + "]").parents(":eq(2)").find("div.error > span").remove();
								$("input[name=" + key2.toString() + "]").parents(":eq(1)").append("<span>x " + node + "</span>");
								
								numErrors++;
							} else { 
								$("input[name=" + key2.toString() + "]").parents(":eq(2)").find("div.error > span").remove();
								$("input[name=" + key2.toString() + "]").parents(":eq(1)").removeClass("error");
							}
						});
					});
					
					if(numErrors == 0) { 
						$("div.result").slideDown();
						getResult(formValues);
					}
				}
			});
			
			return false;
		});
		
		var getResult = function(formValues) { 
			var item1 = new Item({
				first: formValues['first'],
				last: formValues['last'],
				user: formValues['user'],
				email: formValues['email'],
				sex: formValues['sex'],
				age: formValues['age'],
				ret: formValues['ret']
			});
			
			items.add(item1);
			console.log(items);
			var appView = new ItemsView();
		}
		
		var validateForm = function(xml, fields) { 
			var error = {};
			var xmlDoc = $(xml).find("errors").children();
			
			$.each(xmlDoc, function(key1, value1) { 
				error[value1.nodeName.toString()] = {};
			});
			
			$.each(fields, function(key, value) { 
				switch(key) { 
					case 'first' :
					case 'last'  : 
									if(value.length > 0) { 
										if(!$.isNumeric(value)) { 
											var min = $(xml).find("errors > length > " + key.toString()).attr("min");
											var max = $(xml).find("errors > length > " + key.toString()).attr("max");
											
											var minCond = ( (typeof min !== 'undefined') && (min !== false) ) ? (value.length >= min) : true;
											var	maxCond = ( (typeof max !== 'undefined') && (max !== false) ) ? (value.length <= max) : true;
											
											if( minCond && maxCond ){ 
												var regex = /^[a-z]+$/ig;
												error["error"][key]  = (!regex.test(value)) ? key : "";
											} else { 
												error["length"][key] = key;
												$(xml).find("min").text(min);
												$(xml).find("max").text(max);
											}
										} else { 
											error["numeric"][key] = key;
										}
									} else { 
										error["empty"][key] = key;
									}
									break;
					case 'user'	 :	
					case 'sex'	 : 
					case 'age'	 : 
					case 'ret'	 : 
									error["empty"][key] = (value.length == 0) ? key : "";
									break;
					case 'email' :  
									if(value.length > 0) { 
										var regex = /^[a-z0-9.-_]+[@]{1}[a-z]+\.{1}[a-z]{2,4}$/ig;
										error["error"][key] = (!regex.test(value)) ? key : "";
									} else { 
										error["empty"][key] = key;
									}
									break;
					default		 :	error["general"][key] = key;
				}
			});
			
			return error;
		}
		
		var getFormValues = function() { 
			var formValues = {
				'first' : $.trim($("input#first").val()),
				'last'  : $.trim($("input#last").val()),
				'user'  : $.trim($("select#user > option:selected").val()),
				'email' : $.trim($("input#email").val()),
				'sex'   : $.trim($("input[name=sex]:checked").val()),
				'age'   : $.trim($("select#age > option:checked").val()),
				'ret'   : $.trim($("select#ret > option:selected").val())
			};
			
			return formValues;
		}
	});
})($);