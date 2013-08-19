window.PF = $.extend( true, window.PF || {}, {




	covers: (function() {



        var container = $('body > section'),
            methods = {

                page: (function( cardContainer ) {

                    var offset = cardContainer.offset(),
                        containerWrap = cardContainer.parent(),
                        positions = [],
                        roundCheckbox = $('input[name=round]'),
                        borderCheckbox = $('input[name=border]'),
                        lastAlt,
                        checked;

                    roundCheckbox.change(function() {
                        if ( roundCheckbox.is(":checked") ) {
                            borderCheckbox.removeAttr("disabled");
                        } else {
                            borderCheckbox.attr("disabled", true).removeAttr("checked");
                        }
                    });



                    cardContainer
                        .sortable({
                            handle: '.card',
                            containment: cardContainer.parent(),
                            tolerance : 'pointer'
                        })
                        .disableSelection()
                        .bind( 'sortstart sortstop', function(){
                            $(this).toggleClass('sorting');

                        })
                        .bind( 'sortstart', function() {
                            $('.mustardized').mustard('hide');
                            $(this).find('.cardDetails, .delete').css({
                                visibility : 'hidden'
                            });
                        })
                        .bind( 'sortstop', function() {
                            $(this).find('.cardDetails, .delete').css({
                                opacity: 0,
                                visibility: 'visible'
                            }).animate({
                                opacity: 1
                            });

                            // Save order
                            $.ajax({
                                type: 'post',
                                data: {
                                    action: 'saveOrder',
                                    ids: cardContainer.find('.cardWrap').map( function(){ return $(this).data('id'); }).get().join()
                                }
                            });
                        });

                    cardContainer
                        .delegate('.edit', 'click', function( e ) {
                            $(this).toggleClass('edit save').parent().addClass('active').find('.altText').removeAttr('disabled').focus();
                            return false;
                        })
                        .delegate(".btnEditDesign", "click", function() {

							var template = $("#editDesignTemplate").html();

							var data = {
									designId:$(this).closest('.cardWrap').data('id'),
									categories: $(this).closest('.cardWrap').data('catids'),
									groups: $(this).closest('.cardWrap').data('groupids'),
									};


								$.modal({
									content : _.template( template,data)
								});


						})
						.delegate("select", "change", function() {
							var $this = $(this),
								wrap  = $this.closest(".categoryWrap"),
								ids	  = [];

							wrap.find("select").each( function( i, v ) {
								ids.push( $(v).val() );
							}).data("ids", ids);

							$.ajax({
								type : "post",
								data : {
									action : "saveDesignCategories",
									ids : ids.join("|"),
									designId : $this.closest(".cardWrap").data("id")
								}
							});


						})
                        .delegate('.altText', 'focus', function() {
                            lastAlt = $(this).val();
                        })
                        .delegate('.save', 'click', function( e ) {

                            var $this = $(this).siblings('.altText').attr('disabled', true),
                                    alt		= $.trim( $this.val());

                            $this.parent().removeClass('active');
                            $this.siblings('.save').toggleClass('save edit');

                            if ( lastAlt != alt ) {
                                // Save new alt text
                                $.ajax({
                                    type: 'post',
                                    data: {
                                        action : 'saveAlt',
                                        id : $this.closest('.cardWrap').data('id'),
                                        alt : alt
                                    }
                                });
                            }

                        })
                        .delegate('.altText', 'keyup', function( e ) {
                            if ( e.which == 13 ) {
                                $(this).parent().find('.edit.editing').click();
                            }
                        })
                        .delegate('.delete', 'click', function() {


                            var $this	= $(this),
                                card	= $this.closest('.card'),
                                lastOne = $('.cardWrap').length == 1;

                            $('.mustardized').mustard('hide');

                            card.mustard({
                                content : lastOne ? "You can not delete your only card design" : $('#deleteTooltip').html().replace('<id>', $(this).closest('.cardWrap').data('id') ),
                                css :{
                                    inner : {
                                        'max-width' : '225px'
                                    },
                                    theme : 'info'
                                },
                                show : {
                                    event : false
                                },
                                hide : {
                                    event : false
                                }
                            });

                            if ( lastOne ) {
                                setTimeout(function() {
                                }, 5000);
                            }
                        })
                        .delegate('input:checkbox', 'change', function(){

                            var $this = $(this),
                                card	= $this.closest('.cardWrap').find('.card'),
                                checked = $this.is(":checked"),
                                last;

                            $('.mustardized').mustard('hide');

                            // Make sure they're not disabling the only available cover
                            if ( ( last = $this.closest('#cardContainer').find('input:checked')).length == 1 ) {
                                last.attr('disabled', true);
                            } else {
                                last.removeAttr('disabled');
                            }

                            if ( checked ) {
                                card.animate({
                                    opacity: 1
                                }, function() {
                                    $(this).closest('.cardWrap').removeClass('inactive');
                                });
                            } else {
                                card.animate({
                                    opacity: 0.25
                                }, function() {
                                    $(this).closest('.cardWrap').addClass('inactive');
                                });
                            }

                            // Save new alt text
                            $.ajax({
                                type: 'post',
                                data: {
                                    action : 'saveStatus',
                                    id : $this.closest('.cardWrap').data('id'),
                                    status : checked | 0
                                }
                            });

                        })
                        .delegate('.iPhoneCheckContainer', 'click', function() {
                            var $this = $(this);
                            if ( $this.has('input[disabled]').length ) {
                                $this.mustard({
                                    content : "You may not disable your last enabled cover.",
                                    css :{
                                        inner : {
                                            'max-width' : '225px'
                                        },
                                        theme : 'info'
                                    },
                                    show : {
                                        event : false
                                    },
                                    hide : {
                                        event : false
                                    }
                                });
                            }

                            setTimeout(function() {
                                $this.mustard('hide');
                            }, 5000);


                        });


                    $('body')
                        .delegate('.mustard-tooltip span', 'click', function() {
                            $(this).closest('.mustard-tooltip').fadeOut();

                        })
                        .delegate('.mustard-tooltip input.deleteSubmit', 'click', function() {
                            var $this = $(this),
                                    id = $(this).siblings('input[type=hidden]').val(),
                                    cardWrap = $('.cardWrap[data-id=' + id + ']').addClass('sorting');

                            $.ajax({
                                type : 'post',
                                data : {
                                    action : 'delete',
                                    id : id
                                },
                                success: function() {
                                    cardWrap.height( cardWrap.height() );
                                    $this.closest('.mustard-tooltip').fadeOut(function() {
                                        cardWrap.find('.cardDetails').css('visibility', 'hidden');
                                        cardWrap.find('.card').hide('explode', { pieces : 24 }, function() {
                                            cardWrap.hide('normal', function() {
                                                cardWrap.remove();
                                            });
                                        });
                                    });
                                }
                            });
                        });

                    cardContainer.find('input:checkbox').iphoneStyle();

                    $('#newDesign form').ajaxForm({
                        dataType: 'json',
                        iframe: true,
                        beforeSubmit: function( arr, form ) {
                            var data	= {},
								errors	= [];

                            $.each( arr, function( i, v ) {
                                data[ v.name ] = $.trim( v.value );
                            });

                            // Validate alt text
                            if ( ! data.alt ) {
                                errors.push({
                                    elem : form.find('input[name=alt]'),
                                    error: 'Please enter the alt text for this design.'
                                });
                            }

                            if ( ! data.newCard ) {
                                errors.push({
                                    elem: form.find('input[name=newCard]'),
                                    error: 'Please choose an image file.',
                                    pos: 'top'
                                });
                            } else if ( ! /^(png|gif|jpeg|jpg)$/i.test( data.newCard.split('.').pop() ) ) {
                                errors.push({
                                    elem: form.find('input[name=newCard]'),
                                    error: 'Invalid image type. We support png, gif and jpegs only.',
                                    pos: 'top'
                                });
                            }

                            if ( errors.length ) {

                                $.each( errors, function( i, v ) {

                                    v.elem.mustard({
                                        content: v.error,
                                        css : {
                                            theme : 'error'
                                        },
                                        show: {
                                            event: false
                                        },
                                        hide: {
                                            event: 'focus change'
                                        },
                                        position: v.pos || 'right'
                                    });

                                });

                                return false;
                            }
                        },
                        success: function( json ) {
                            var newCard;

                            if ( json.valid ) {
                                cardContainer.height('auto');

                                $.publish("/admin/ajaxStatus", ['success', "Cover design added successfully!"]);

                                // Build template
                                newCard = $(_.template( $('#newCardTemplate').html(), $.extend( json, { alt : $('#alt').val() } )));

                                $('<img />').bind('load', function() {

                                    // Hide the card initially
                                    newCard.css('opacity', 0);

                                    // Show card
                                    newCard.appendTo( cardContainer );
                                    $('ol a[href=#cardContainer]').click();
                                    newCard.find('input:checkbox').iphoneStyle();
									newCard.find(".categoryWrap").append( PF.covers.generateSelects ).find("select").each(function() {

										// select correct options
										$(this).find("option[value]").each(function() {
											var $option = $(this);
											if (  ~ _.indexOf( json.design.categories, $option.val() )) {
												$option.attr("selected", true).siblings("[selected]").removeAttr("selected");
												return false;
											}
										});

									});
                                    newCard.animate({
                                        opacity: 1
                                    });

                                    // Reset form
                                    $('#alt').val('');
                                    //$('input[name=round]').attr('checked', true);
                                    $('#newCard').replaceWith( $('<input />', {
                                        type: 'file',
                                        id : 'newCard',
                                        name: 'newCard'
                                    }));

                                    lockContainerHeight();

                                }).attr( 'src', json.design.smallSrc );

                            } else {
                                $('form').find('li').eq(0).mustard({
                                    content: json.message,
                                    css: {
                                        theme: 'error'
                                    },
                                    position: 'top',
                                    show: {
                                        event: false
                                    }
                                });
                            }
                        }
                    });

                    function lockContainerHeight() {
						cardContainer.css("height", "auto");
						setTimeout(function(){
							cardContainer.height( cardContainer.height() );
						}, 50);
                    }
                    setTimeout(lockContainerHeight, 100);

                    if (( checked = $('#cardContainer').find('input:checked')).length == 1 ) {
                        checked.attr('disabled', true);
                    }

                })( PF.admin.section.find("#cardContainer") ),


				generateSelects : (function() {

					var categories	= $("#categories"),
						tmpl		= $("#selectsUpdate").html();

					return function() {

						var structure = [],
							selects;

						// Build structure
						categories.find("> ul > li").each(function() {
							var $li = $(this),
								obj = {
									id : $li.data("id"),
									name : $.trim( $li.find("> div.name").text()),
									children : []
								};

								$li.find(".child").each(function() {
									var $this = $(this);
									obj.children.push({
										id : $this.data("id"),
										name : $.trim( $this.find(".name").text())
									});
								});

							structure.push( obj );
						});

						// Build selects
						selects = $( _.template( tmpl, { structure : structure }) );

						return selects;

					}

				}()),

				updateSelects : function() {

					var selects = PF.covers.generateSelects(),
						wraps	= $(".categoryWrap"),
						uploadWrap = $(".newDesignCatWrap");

					wraps.each(function() {
						var $wrap	= $(this),
							ids		= $wrap.data("ids");

						$wrap.children().replaceWith( selects.clone() );

						$wrap.find("select").each(function(){
							$(this).find("option[value]").each(function() {
								var $option = $(this);
								if (  ~ _.indexOf( ids, $option.val() )) {
									$option.attr("selected", true).siblings("[selected]").removeAttr("selected");
									return false;
								}
							});
						});
					});

					uploadWrap.html( selects.clone() );


				},

				categories: function() {

					$("#categories")
						// On parent sort stop
						.delegate("ul", "sortstop", function( e, ui ) {
							var data	= {
								action : "saveCategoryParentOrder",
								order  : $.map( $.makeArray( ui.item.closest("ul").find("> li")), function( elem ) {
									return $(elem).data("id");
								})
							};

							$.ajax({
								data: data,
								dataType: "json",
								success: function () {
									PF.covers.updateSelects();
								}
							});


						})
						.delegate(".name[contenteditable]", "blur", function() {
							var $this	= $(this),
								name	= $.trim( $this.text() ),
								id		= $this.closest("li").data("id");

							$.ajax({
								data : {
									action: "saveCategoryName",
									id : id,
									name: name
								},
								success: function() {
									PF.covers.updateSelects();
								}
							});
						})
						.delegate(".newChild, #newParent", "click", (function() {

							var template = $("#newCategoryTemplate").html();

							return function() {
								var parentId = $(this).closest("li").data("id"),
								modal = $.modal({
									content : _.template( template, {
										parentId : parentId
									})
								});
								setTimeout(function() {
									modal.find("input").filter("[type=text]:visible").focus();
								}, 50);
							}

						}()))
						.delegate(".deleteCategory", "click", (function() {

							var catDeleteTmpl = $("#deleteCategoryTooltip").html();

							return function() {
								var $this = $(this);

								$this.mustard({
									content : _.template(catDeleteTmpl, {
										id : $this.closest("li").data("id"),
										isParent : $this.closest("li").hasClass("parent")
									}),
									css :{
										inner : {
											'max-width' : '225px'
										},
										theme : 'info'
									},
									show : {
										event : false
									},
									hide : {
										event : false
									}
								});
							}

						}()))
						.find("ul")
							.sortable({
								handle: ".sortHook",
								containment : PF.admin.section
							})
							// On child sort stop
							.delegate("ul", "sortstop", function( e, ui ) {
								var item	= ui.item,
									parent	= item.parent(),
									data	= {
										action : "saveCategoryChildrenOrder",
										parent		: parent.closest("li").data("id"),
										children	: $.map( $.makeArray( parent.find("li")), function( elem ) {
											return $(elem).data("id");
										})
									};

								$.ajax({
									data: data,
									dataType: "json",
									success: function() {
										PF.covers.updateSelects();
									}
								});
								e.stopPropagation();
							})
							.find("ul")
								.sortable({
									connectWith : "#categories .parent ul",
									containment : PF.admin.section,
									handle: ".sortHook"
								})

					$(document.body)
						.delegate("#editDesignDetailsForm", "submit", function() {

							$.modal("close");

							$.ajax({
								type: "post",
								data : $(this).serialize(),
								success: function( data ) {
									//update local data storage
									if (data.valid){
										var card = $("li[data-id="+data.designid+"]");
										card.data('catids', data.catids);
										card.data('groupids', data.groupids);
									}
								}
							});

							return false;
						})
						.delegate("#newCategoryForm", "submit", function() {

							var $this = $(this),
								name = $.trim( $this.find("input[name=categoryName]").val() ),
								parent = $this.find("input[name=parentId]").val();

							$.modal("close");

							$.ajax({
								type: "post",
								data : {
									action : "saveNewCategory",
									name : name,
									parentId : parent
								},
								success: function( category ) {
									var parent = $("li[data-id=" + category.parentId + "]"),
										jq;

									if ( parent.length ) {
										jq = $( _.template( $("#categoryTemplate").html(), category ) ).hide();
										parent.find("ul").append( jq );
										jq.fadeIn();
									} else {
										jq = $( _.template( $("#parentCategoryTemplate").html(), category )).hide();
										$("#categories > ul").append( jq );
										jq.fadeIn();
									}

									PF.covers.updateSelects();

								}
							});

							return false;
						})
						.delegate(".deleteCategoryForm", "submit", function() {

							var id = $(this).find("input[name=id]").val(),
								isParent = $(this).find("input[name=isParent]").val();


							$.ajax({
								type: "post",
								data: {
									action : "deleteCategory",
									id : id,
									isParent : isParent == "true"
								},
								dataType: "json",
								success: function( json ) {
									var li = $("#categories li[data-id=" + id + "]");
									li.find(".deleteCategory").mustard("hide");
									li.hide('explode', { pieces : 6 }, function() {
										li.remove();
										PF.covers.updateSelects();
									});
								}
							});

							return false;
						});
				},

                init: function() {

                    // Style iPhone Checkboxes
                    PF.admin.section.find('#cardContainer input:checkbox').iphoneStyle();

                    // Setup AJAX events
                    $.ajaxSetup({
                        type: 'post',
                        dataType: 'json',
                        statusCode: {
                            404 : function( xhr ) {
                                var res = $.parseJSON( xhr.responseText );
                                if ( res.message ) {
                                    $.publish( "/admin/ajaxStatus", ['error', res.message] );
                                }
                            },
                            200 : function( res ) {
								if ( res ) {
									$.publish( "/admin/ajaxStatus", [res.className || 'success', res.message] );
								}
                            }
                        }
                    });

                    $(document).ajaxStart(function() {
                        $.publish( "/admin/ajaxStatus", ['loading', 'Working&hellip;'] );
                    });

					PF.covers.categories();

                }

            };


		return methods;

	})()

});
