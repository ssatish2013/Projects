window.PF = $.extend( true, window.PF || {}, {

	dashboard : (function() {

		var labels = (function() {
			var day = [
					"12am", "1am", "2am", "3am", "4am", "5am", "6am", "7am", "8am", "9am", "10am", "11am",
					"12pm", "1pm", "2pm", "3pm", "4pm", "5pm", "6pm", "7pm", "8pm", "9pm", "10pm", "11pm"
				],
				week = [ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ]

			return function( spread, until, data, from ) {

				var copy, temp, dayNum, ret, key;

				//day
				if ( from == until) {
					return day;

				//week
				} else if ( spread == "week" ) {
					dayNum = (new Date()).getDay();
					copy = week.slice(0);
					temp = copy.splice( dayNum, 7 );
					ret = temp.concat( copy );
					return ret;

				//other/month
				} else {
					until = $.datepicker.parseDate('yy-mm-dd', until);
					copy = until;
					temp = [];
					
					for ( key in data ) {

						$.each( data[key], function() {

							//setup the day
        			var date = $.datepicker.formatDate("m/d", copy);
							copy.setDate( copy.getDate() - 1 );

							temp.unshift(date);
						});

						//whoa! quite the list, let's only show every other day
						//so we don't get date overlap
						if(temp.length > 25) { 
							$.each(temp, function(i, v) { 
								if(i%2) { 
									temp[i] = "";
								}
							});
						}
						return temp;
					}


				}

			}
		}()),
		chart,
		graphData = null,
		filterOut,
		defaults = {
			width : 772,
			height: 386
		},
		getColors = (function(){

			var colors = [
				"#009900",
				"#009999",
				"#990000",
				"#000099",
				"#999900",
				"#990099",
				"#999999",
				
				"#FF99FF",
				"#FF9999",
				"#99FFFF",
				"#FFFF99",
				"#9999FF",
				"#99FF99",

				"#0099FF",
				"#9900FF",
				"#00FF99",
				"#FF9900",
				"#99FF00",
				"#FF0099"
				
			];

			return function( obj ) {

				var colorObj = {},
					i = 0,
					key;

				for ( var key in obj ) {
					colorObj[key] = colors[( i % colors.length )];
					i++;
				}

				return colorObj;

			};
		}()),
		generateLegend = function( colors ) {

			return _.template( $("#legendTemplate").html(), { colors : colors, filter: PF.dashboard.filterOut});
			
		},
		getRange = (function() {
				var secondsIn = {
					hour : 3600,
					day : 86400,
					week : 604800
				},
				ranges = {
					today : function( until ) {
						return {
							from	: $.datepicker.formatDate('yy-mm-dd', new Date()),
							until : $.datepicker.formatDate('yy-mm-dd', new Date())
						}
					},
					yesterday : function( until ) {
						fromDate = new Date();
						fromDate.setDate(fromDate.getDate() - 1);
						return {
							from	: $.datepicker.formatDate('yy-mm-dd', fromDate),
							until : $.datepicker.formatDate('yy-mm-dd', fromDate)
						}
					},
					week : function( until ) {
						fromDate = new Date();
						fromDate.setDate(fromDate.getDate() - 6);
						return {
							from	: $.datepicker.formatDate('yy-mm-dd', fromDate),
							until : $.datepicker.formatDate('yy-mm-dd', new Date())
						}	
					},
					month : function( until ) {
						fromDate = new Date();
						fromDate.setDate(fromDate.getDate() - 30);
						return {
							from	: $.datepicker.formatDate('yy-mm-dd', fromDate),
							until : $.datepicker.formatDate('yy-mm-dd', new Date())
						}
					},
					custom: function( until ) {
						from = $.datepicker.formatDate('yy-mm-dd', ($('#customFromDate').datepicker("getDate")));
						until = $.datepicker.formatDate('yy-mm-dd', ($('#customUntilDate').datepicker("getDate")));
						return {
							from: from,
							until: until
						}	
					}
				};

			return function() {
				// Get current timestamp in seconds
				var range = 'custom';
				if(PF.dashboard.elems.rangeInputs.filter(":checked").length) { 
					range = PF.dashboard.elems.rangeInputs.filter(":checked").val()
				}
				return ranges[ range ]( );

			}
		})();

		return {

			elems : {
				rangeInputs : $("input[name=range]")
			},

			generateCurrentGraph : (function() {

				var types = $(".tabs li"),
					graph = $("#graph"),
					screen = graph.find(".screen"),
					noData  = graph.find(".no-data");


				return function() {

					var spread	= PF.dashboard.elems.rangeInputs.filter(":checked");
					if(spread.length) { spread = spread.val(); }
					else { spread = 'custom'; }

					tzOffset = (new Date()).getTimezoneOffset();

					var data	= $.extend( getRange(), {
							action : types.filter(".active").data("graph"),
							spread : spread,
							tzOffset : tzOffset
						});
					screen.show();

					_generateCurrentGraph = function( res ) {
	
						$('#graph').find('.screen').hide();
						PF.dashboard.graphData = {};
						$.extend(true, PF.dashboard.graphData, res);
						var colors, canvas, chart;
	
						// Clear previous graph
						graph.children(":not(.screen, .no-data)").remove();
	
						if ( res ) {
	
							// Get colors for graph
							colors = getColors( res ),
	
							$.each(PF.dashboard.filterOut, function(name) { 
								delete res[name];
							});
	
	
							if(!$.isEmptyObject(res)) { 
								// This fixes a weird IE bug
								canvas = $.browser.msie ? (Raphael( graph[0], defaults.width, defaults.height )).canvas : graph[0];
	
								// Generate graph
								PF.dashboard.chart	= new Ico.LineGraph( canvas, res, {
									markers: 'circle',
									colours: colors,
									grid: true,
									labels: labels( spread, data.until, res, data.from )
								});						
							}
							
							// Generate legend
							graph.prepend( generateLegend( colors ));
	
	
							$('#graph circle').each(function(i,v) { 
								//setup our chart data
								chart = PF.dashboard.chart;
								total = chart.start_value === 0 ? chart.top_value : chart.top_value - chart.start_value;
								y_offset = (chart.graph_height + chart.y_padding_top) + chart.normalise(chart.start_value),
	
								value = Math.round(((y_offset-$(v).attr('cy'))/chart.graph_height)*total);
								if(value) { 
									$(v).mustard({
									'content': value,
									'css': {
										'theme': 'notice'
									},
									'hide': {
										'timeout': 0
									},
									'position': 'top'
									});
								}
							});
	
							noData.hide();
						} else {
							noData.show();
						}
					}

					if(!PF.dashboard.graphData) { 
						$.ajax({
							type : "post",
							dataType: "json",
							data : data,
							success: _generateCurrentGraph
						});
					}
					else {
						_generateCurrentGraph(PF.dashboard.graphData);
					}

				}

			}()),

			filter: function() { 
					$this = $(this);
					name = $this.data('seriesName');
					$this.css({'background-color': 'transparent'});
					if(PF.dashboard.filterOut[name]) { 
						delete PF.dashboard.filterOut[name];
					}
					else {
						PF.dashboard.filterOut[name] = 1;
					}
					PF.dashboard.generateCurrentGraph();
			},

			highlightSeries: function() { 
				$this = $(this);
				color = $this.siblings('b').data('color');

				//if the color is transparent, it's not displayed
				//so don't worry about it
				if(color == "transparent") { return; };

				$("svg path[stroke='"+color+"'], svg circle[fill='"+color+"']").appendTo('svg');
			},

			rangeControls : function() {
				$("#ranges").delegate("input[type=radio]", "change", function() { 
					PF.dashboard.graphData = null;
					PF.dashboard.generateCurrentGraph();
				});
			},

			download: function(e) { 
				var types = $(".tabs li"),
        spread  = PF.dashboard.elems.rangeInputs.filter(":checked");
        if(spread.length) { spread = spread.val(); }
        else { spread = 'custom'; }

				if(PF.dashboard.elems.rangeInputs.filter(":checked").length) { 
					spread = PF.dashboard.elems.rangeInputs.filter(":checked").val();
				}

				form = $('#downloadForm');
        $.each($.extend( getRange(), {
          action: types.filter(".active").data("graph"),
          spread : spread
        }), function(k, v) { 
					form.find('input[name="'+k+'"]').val(v);
				});
				form.submit();
				e.preventDefault();
			},

			showCustomRange: function(e) { 
				$('#ranges').fadeOut('fast', function() {
					$('#customRanges').fadeIn('fast');
				});
				e.preventDefault();
			},
			showStandardRanges: function(e) { 
				$('#customRanges').fadeOut('fast', function() {
					$('#ranges').fadeIn('fast');
				});
				e.preventDefault();
			},
			createCustomGraph: function(e) { 
				e.preventDefault();
				PF.dashboard.graphData = null;
				PF.dashboard.elems.rangeInputs.attr('checked', false);
				PF.dashboard.generateCurrentGraph();
			},
			init : function() {

				$(".tabs li:first-child").addClass('active');
				$("#legend .legendLabel").live("click", PF.dashboard.filter);
				$("#legend li label").live("click", PF.dashboard.highlightSeries);
				PF.dashboard.rangeControls();
				PF.dashboard.generateCurrentGraph();
				$.subscribe("/tabs/click", function() { 
					PF.dashboard.graphData = null;
					PF.dashboard.filterOut = {};
					PF.dashboard.generateCurrentGraph();
				});
				PF.dashboard.filterOut = {};

				//download button
				$('.downloadGraph').click(PF.dashboard.download);

				//setup date pickers
				$('#customRangeButton').click(PF.dashboard.showCustomRange);
				$('#standardRangesButton').click(PF.dashboard.showStandardRanges);
				$('#createCustomGraph').click(PF.dashboard.createCustomGraph);

        var today = $.datepicker.formatDate("mm/dd/yy", new Date());


				$.each(['From','Until'], function(i, time) { 
					$('#custom'+time+'Date').datepicker({
							constrainInput : false
					}).bind('change', function() {
						var $this = $(this),
						val   = $.trim( $this.val());

						if ( ! val || val == today ) {
							$(this).val("Now");
						}
					});
				});

			}
		}

	})()

});
