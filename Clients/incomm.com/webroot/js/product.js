window.PF = $.extend( true, window.PF || {}, {
	imageUploader : {
			_getCropDimensions : function( json, modal) {

				var	maxHeight		= $(window).height() - modal.find(".dialogcontent").first().height(),
					maxWidth		= 440,
					imageRatio		= json.width / json.height,
					spaceRatio		= maxWidth / maxHeight,
					dimensions;

				if ( imageRatio > spaceRatio ) {
					dimensions = [
						maxWidth,
						( json.height * maxWidth ) / json.width
					];
				} else {
					dimensions = [
						( json.width * maxHeight) / json.height,
						maxHeight
					];
				}

				return dimensions;

			},
			_showPreview: _.debounce( function( coords, dimensions, preview, json ) {

				if ( JcropAPI ) {
					coords = JcropAPI.tellSelect();
				}

				var rx = 150 / coords.w;
				var ry = 95 / coords.h;


				preview.css({
					width: Math.round(rx * json.width ) + 'px',
					height: Math.round(ry * json.height ) + 'px',
					marginLeft: '-' + Math.round(rx * coords.x) + 'px',
					marginTop: '-' + Math.round(ry * coords.y) + 'px'
				});
			}, 0),
			initCrop: function(modal){
				// Populate the crop image
				var json = PF.imageUploader.uploadedImage,
				dimensions = PF.imageUploader._getCropDimensions( json, modal),
				preview	= modal.find(".previewImage"),
				showPreview = function( coords ) {
					PF.imageUploader._showPreview( coords, dimensions, preview, json );
				}

				modal.find(".cropImage").first().load(function() {
					JcropAPI = $.Jcrop( this, {
						setSelect: [
							json.width * .5,
							json.height * .5,
							json.width * .5 + 1,
							json.height * .5 + 1
						],
						boxWidth : 404,
						boxHeight: dimensions[1],
						onChange: showPreview,
						onSelect: showPreview,
						trueSize: [ json.width, json.height ],
						aspectRatio : (30/19)
					});

					// Jcrop needs some time to
					// initialize before calling next
					setTimeout(function() {
						//next();

						setTimeout(function() {
							JcropAPI.animateTo([
								json.width * .25,
								json.height * .25,
								json.width * .75,
								json.height * .75
							]);
						}, 1000);

					}, 600);
				})
				.attr("src", json.url);

				var allCards = modal.find(".card");
				var cardsWrap = modal.find(".cards_wrap");

				allCards.click(function(){
					allCards.removeClass('selected');
					$(this).addClass('selected');
					cardsWrap.addClass('selected');
				});

				modal.find("div.arrow").click(function(){
					var cards = modal.find("div.card");
					var minLeft = (Math.ceil(cards.length / 2) - 1)*-340;
					var currentLeft = parseInt(modal.find("div.cards_wrap").css('left'),10);
					var newLeft = currentLeft + ($(this).hasClass("right")?-340:340);
					var nextLeftLeft = newLeft + 340;
					var nextRightLeft = newLeft - 340;
					modal.find("div.arrow").removeClass('no-pointer');
					if(nextLeftLeft > 0){
						modal.find("div.arrow.left").addClass('no-pointer');
					}
					if(nextRightLeft < minLeft){
						modal.find("div.arrow.right").addClass('no-pointer');
					}
					if(newLeft > 0 || newLeft < minLeft){
						$(this).addClass('no-pointer');
						return;
					}

					modal.find("div.cards_wrap").css('left',newLeft);
				})

				// Populate the preview image
				modal.find(".previewImage").attr("src", json.url);
			},
			initCropDialog: function(){
				$('.cropImageForm').mdialog ({
					showheader: true,
					showcancelbtn: false,
					centre: true,
					overflow:'auto',
					top:'250px',
					width: '700px',
					height:'500px',
					okclick: function(btn){
						var btn = $(btn),
						sceneId = null,
						form = btn.parent().parent().find('form').first(),
						data = $.extend( JcropAPI.tellSelect(), {
							image : PF.imageUploader.uploadedImage.url
						}),
						selectedScene=$(form).find('.card.selected');

						$('.cropImageForm').mdialog('showLoader');
						btn.hide();
						if( (parseInt(PF.page.forceLogoUpload) == 1) && (PF.page.customScenes.length == 1) ) { 
							sceneId = PF.page.customScenes[0]['id'];
						}
						if(selectedScene.length) { 
							sceneId = selectedScene.attr('did');
						}
						if( selectedScene.length || ((parseInt(PF.page.forceLogoUpload) == 1) && (PF.page.customScenes.length == 1)) ) {
							data = $.extend(data, {
								customSceneId: sceneId
							});
						}
						
						$.ajax({ 
							url : "/gift/customCrop",
							type : "POST",
							dataType : "json",
							data : data,
							success: function( json ) {
								if (json.valid) {
									PF.imageUploader.cropDone(json.design);
								}
							}
						});

					},
					show: function(m){
						PF.imageUploader.initCrop(m);
						return m;
					},
					hide: function(m){
						//destroy the dialog when it closes, to work around an issue which Jcrop been initialized multiple times.
						$('.cropImageForm').mdialog('destroy');
						m.remove();
						return m;
					}

				});
				$('.cropImageForm').mdialog('show');
			},
			cropDone: function(design){
				$('.cropImageForm').mdialog('hide');
				if(PF.productSelector.selectedDesign){
					PF.productSelector.selectedDesign.id = design.id;
				}
				//update preview image and local design store
				var designs = $('.designlist').data('designs'),
				source = $(PF.imageUploader.sourceDesign),
				idx = source.attr('idx');
				designs[idx].smallSrc = design.smallSrc;
				designs[idx].largeSrc = design.largeSrc;
				designs[idx].mediumSrc = design.mediumSrc;
				$('.designlist').data('designs',designs);
				source.find('img.design').first().attr('src',design.smallSrc);
				source.find('img.mail').first().css('background', 'transparent');
				$("#card:first img:first").attr('src',design.largeSrc);
				//$("#card:first img:first").css ("background", "url('" + design.largeSrc + "') center center no-repeat");
			},
			uploadedImage: {},
			sourceDesign: {}
	},
	initImageUploader : function(){
		if ($('.uploadImageForm').length==0) return;
		$('.uploadImageForm').mdialog ({
			showheader: true,
			showcancelbtn: false,
			centre: true,
			overflow:'auto',
			top:'250px',
			width: '500px',
			height:'150px',
			show: function(m){
				m.find('.btnOK').show();
				m.find('.loader').hide();
				m.find('form').show();
				return m;
			},
			okclick: function(btn){
				var btn = $(btn),
				form = btn.parent().parent().find('form').first();

				$('.uploadImageForm').mdialog('showLoader');
				form.hide();
				btn.hide();

				form.ajaxSubmit({
					url : "/gift/customUpload",
					type : "post",
					dataType : "json",
					iframe : "true",
					success: function( json ) {
						if (json.valid){
							PF.imageUploader.uploadedImage = json;
							PF.imageUploader.initCropDialog();
							$('.uploadImageForm').mdialog('hide');
						}
						else{
							$('.uploadImageForm').mdialog('hideLoader');
							form.show();
							btn.show();
						}
					}
				});
			}
		});

		$(document.body).delegate('img.custom','click', function(){
			$('.uploadImageForm').mdialog('show');
			// remember the design is customizing so later can be replaced
			PF.imageUploader.sourceDesign = $(this).parent();
		});
	},
	productSelector : {
		currentpage: 1,
		loadsaved: false,
		selectedDesign: null,

		loadCurrency: function(){
			$("#curselector").empty();
			  for (var key in PF.page.products) {
				var flag='//gca-common.s3.amazonaws.com/assets/'+ key.toLowerCase().substring(0,2) +'.png';
				if (key==PF.page.defaultCurrency){
					$("#curselector").append ($('<option class="'+ key.toLowerCase() +'" title="'+ flag +'" value="' + key + '" selected="selected">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + key + '</option>'));
				} else {
					$("#curselector").append ($('<option class="'+ key.toLowerCase() +'" title="'+ flag +'" value="' + key + '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + key + '</option>'));
				}
			  }
			  //reset category selector if currency changed
			  $("#curselector").change(function(){
				  PF.productSelector.loadCategory();
				 //update flag display
				 $("#curselector option:selected").each(function(){
						$("#curdisplay").attr("class",$(this).attr("class"));
				 });
			  });

			  //image dropdown list
			  var container = $("#curselector").parent();
			  //don't init if the selector is hidden.
			  if (!container.hasClass('hidden')){
				  container.removeClass();
				  container.css({width:'200px',height:'200px',position:'absolute'});
				  $("#curselector").msDropDown({mainCSS:'dd2'});
			  }

			  PF.productSelector.loadCategory();
		 },
		 isFilterCategory: function (cur,catid){
			//remove category which has no designs in it.
			var designs = PF.productSelector.filterDesigns(cur,catid);
			return designs.length==0;
		 },
		 loadCategory: function (){
			$("#catselector").empty();
			var options = '<option value="0">'+PF.page.lang.selectCategory+'</option>',
			j = PF.page.designCategories,
			cur = $("#curselector option:selected").val(),
			optioncount = 0;

			  for (var i = 0; i < j.length; i++) {
				if (PF.productSelector.isFilterCategory(cur,j[i].id)){
					continue;
				}
				options += '<option value="' + j[i].id + '">' + j[i].name + '</option>';
				optioncount ++;
				$.each(j[i].children, function(index,child){
						if (PF.productSelector.isFilterCategory(cur,child.id)){
							return;
						}
						options += '<option value="' + child.id + '">--' + child.name + '</option>';
						optioncount++;
				});
			  }
			  $("#catselector").html(options);
			  if (optioncount>0){
				  $("#catselector").parent().removeClass('hidden');
				  $('#btnBrowseAll').show();
			  }
			  else{
				  $("#catselector").parent().addClass('hidden');
				  $('#btnBrowseAll').hide();
			  }
			  $("#catselector").change(function(){
				  PF.productSelector.loadDesigns($(this).val());
				  $("#card:first img:first").attr("src", "//gca-common.s3.amazonaws.com/assets/blank.png");
			  });
			  PF.productSelector.loadDesigns($("#catselector option:first").val());
		 },
		 loadDesigns: function (catid){
			//clear current seletion and preview area
			PF.productSelector.selectedDesign = null;
			$("#card:first img:first").css ('background', 'url("//gca-common.s3.amazonaws.com/assets/card.default.png") no-repeat scroll center center transparent');

			$('#loading-designs').show();
			var cur = $("#curselector option:selected").val(),
			j = PF.productSelector.filterDesigns(cur,catid);
			$('#loading-designs').hide();
			$('.designlist').data('designs',j);
			PF.productSelector.navpage(0);
		 },
		 filterDesigns: function (cur,catid){
			//filter designs in page data store
			 var designs = []
				, hasPhysicalProducts = false
				, hasCustomizableProducts = false
				, isgroup = ("1" == $('#giftingmode').val());
			 for(var c in PF.page.products){
				 if (c == cur){
					 for(var p in PF.page.products[c]){
						var product =  PF.page.products[c][p];
						if  (catid==0 || product.cid == catid || product.ccid == catid){
							if (isgroup && product.singlefixed == "1"){
								//don't show product group only have one fixed amount option
								//however, groups with multiple fixed amount and no open amount,
								//or groups only have one open amount will be showing.
							}
							else{
								designs.push(product);
								if (product.isCustomizable == "1") {
									hasCustomizableProducts = true;
								}
								if (product.isPhysicalOnly == "1" || product.isPhysical == "1") {
									hasPhysicalProducts = true;
								}
							}
						}
					 }
					 break;
				 }
			 }
			 (hasCustomizableProducts)
				 ? $(".legend .custom").show()
				 : $(".legend .custom").hide();
			 (hasPhysicalProducts)
				 ? $(".legend .postal").show()
				 : $(".legend .postal").hide();
			 return designs;
		 },
		 renderDesigns: function (j, page){
			var designs = ''
				, pagesize = PF.page.productsPerPage
				, start = (page-1)*pagesize
				, end = start + pagesize
				, isgroup = ("1" == $('#giftingmode').val());
			$.each(j,function(index,design){
				if (index>=start && index < end){
					designs +=
						'<li idx="' + index + '">' +
							'<img src="' + design.smallSrc + '" class="design" />';
					if (design.isNew == "1") {
						designs += '<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="new" />';
					}
					if (design.isCustomizable == "1") {
						designs += '<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="custom" />';
					}
					if (design.isPhysicalOnly == "1" || design.isPhysical == "1") {
						designs += '<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="mail" />';
					}
					designs +=
							'<label>' + design.alt + '</label>' +
						'</li>';
					//preload the images so selected design can show faster on the preview area
					var preload = new Image();
					preload.src = design.largeSrc;
				}
			});
			$('.designlist').html(designs);
			$('.designlist').fadeIn();

			//load saved product design
			var did = $('#did').val();
			if (did && !PF.productSelector.loadsaved){
				PF.productSelector.loadsaved = true;
				$.map($('.designlist').data('designs'),function(item,idx){
					if (item.id==did){
						$($('.designlist li')[idx]).click();
					}
				});
			}

		 },
		 selectDesign: function (li, design) {
		 	$('.designlist li').removeClass('selected');
			$(li).addClass('selected');
			PF.productSelector.selectedDesign = design;
			//$("#card:first img:first").css ("background", "url('" + design.largeSrc + "') center center no-repeat");
			$("#card:first img:first").attr('src',design.largeSrc);
		 },
		 navpage: function (delta){
			 var totalpage = Math.ceil($('.designlist').data('designs').length / PF.page.productsPerPage),
			 pagetogo = PF.productSelector.currentpage + delta;
			 if ((pagetogo) > totalpage){
				pagetogo = totalpage;
			 }
			 if ((pagetogo) < 1) {
				pagetogo = 1;
			 }

			 PF.productSelector.currentpage = pagetogo;
			 PF.productSelector.renderDesigns($('.designlist').data('designs'),PF.productSelector.currentpage);
			 $("#btnPrev").show();
			 $("#btnNext").show();

			 if (PF.productSelector.currentpage<=1){
				$("#btnPrev").hide();
			 }

			 if (PF.productSelector.currentpage>=totalpage){
				$("#btnNext").hide();
			 }

			 $("#currentpage").html(" " + PF.productSelector.currentpage + " ");
			 $("#totalpage").html(" / " + totalpage + " ");
		 }
	},
	initProductSelector: function (){
			if ($('.designlist').length==0) return;
			$('.designlist').delegate('li','click', function(){
				var design = $('.designlist').data('designs')[parseInt($(this).attr('idx'),10)];
				PF.productSelector.selectDesign(this, design);
			});

			PF.productSelector.loadCurrency();

			$('#btnBrowseAll').click(function(){
				 $("#catselector").val(0).change();
			});

			$('#btnPrev').click(function(){
				PF.productSelector.navpage(-1);
			});

			$('#btnNext').click(function(){
				PF.productSelector.navpage(1);
			});

			$('#next').click(function(event) {
				event.preventDefault();
				var mode= $('#giftingmode').val()
					, messageGuid = $('#messageGuid').val()
					, search = ($("#search").length > 0)
						? "&search=" + escape($("#search").val())
						: "";

				if (PF.productSelector.selectedDesign) {
					if (messageGuid){
						location.href = '/gift/create/messageGuid/'+messageGuid+'?did=' + PF.productSelector.selectedDesign.id + '&gid=' + PF.productSelector.selectedDesign.productGroupId + search;
					}
					else{
						location.href = '/gift/create?did=' + PF.productSelector.selectedDesign.id + '&gid=' + PF.productSelector.selectedDesign.productGroupId + '&mode=' + mode + search;
					}
				} else {
					 $('#next-error').mdialog({
		                title: 'Oops',
		                overflow:'auto',
		                top:'150px',
		                width: '500px',
		                height:'100px',
		                okbtntext: 'Ok',
		                showcancelbtn: false,
		                okclick: function(){
		                    $('#next-error').mdialog('hide');
		                }
		            });
		            $('#next-error').mdialog('show');

				}
			});
	}
});
