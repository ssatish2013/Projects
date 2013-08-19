window.PF = $.extend( true, window.PF || {}, {

	facebook: (function() {
		var perms = [
					'read_friendlists',
          'friends_birthday',
					'publish_stream'
				],
				publishStatus = function() {
					if ( loginStatus.authResponse ) {
						$.publish( '/facebook/loggedIn', [loginStatus] );
						$("#fbAccessToken").val(loginStatus.authResponse.accessToken);
					} else {
						$.publish( '/facebook/notLoggedIn', [loginStatus] );
					}
				},
				loginStatus,
                securePicSqaures = function( friends ) {
                    return $.map( friends, function( friend ) {
                        if ( friend.pic_square ) {
                            friend.pic_square = "https://" + window.location.host + "/facebook/securePic/uid/" + friend.uid;
                        }
                        return friend;
                    });
                }

		return {
			init : function() {
				if ( ! $('#fb-root').length ) {
					$('<div />', {
						id : 'fb-root'
					}).appendTo(document.body);
				}

				// Fixes security warnings
				FB._https = true;
				FB.init({
					appId: PF.page.FB.appId,
					status: true,
					cookie: true,
					xfbml: true,
					oauth: true,
					channelUrl  : window.location.protocol + "//" + window.location.host + "/facebook/channel"
				});

			},
			getLoginStatus : (function() {

				return function( callback ) {
					
					if ( loginStatus && callback ) {
						callback( loginStatus );
					} else {
						/**/
						FB.getLoginStatus(function( response ) {
							loginStatus = response;
							publishStatus();




							if ( callback ) {
								callback( loginStatus );
							}
						});
						/**/
					}

				}

		  })(),
			login : function( callback ) {

					FB.login(function( response ) { 
						loginStatus = response;
						publishStatus();
						callback( loginStatus );
					}, {
						scope: perms.join()
					});
	
			},
      inviteFriends: function( data, fn ) {

        var title = data.inviteTitle;
				var description = data.inviteDescription;
				FB.ui({
					method: 'send',
					display: 'popup',
					name: title,
					link: data.contributeUrl,
					picture: data.designSrc,
					'description': description
				},( fn || $.noop ));

      },
      share: function( data, fn ) {

        var title = data.shareTitle;
				var description = data.shareDescription;
				
				FB.ui({
					method: 'feed',
					display: 'popup',
					name: title,
					link: data.contributeUrl,
					picture: data.designSrc,
					'description': description
				},( fn || $.noop ));

      },
			
			resend: function ( data, fn ) {
				var title = 'A ' + data.partnerName + ' Gift Card';
				var description = data.senderName+' has created a '+data.partnerName+' Gift Card for '+data.recipientName;
				
				FB.ui({
					to: data.to,
					method: 'feed',
					display: 'popup',
					name: title,
					link: data.contributeUrl,
					picture: data.designSrc,
					'description': description
				},( fn || $.noop ));
			},

      getBirthdays : function( amountOfFriends ) {
				// ie8 seems to ask you to login when you getLoginStatus....  temp workaround
				// @todo revisit
        var amount = amountOfFriends;
        PF.facebook.getLoginStatus( function( status ) {
					var fql, query;
					if ( status.authResponse) {

            fql = "SELECT   uid, first_name, last_name, birthday_date, sex, pic_square, locale " +
                  "FROM     user " +
                  "WHERE    uid " +
                  "IN (SELECT uid2 FROM friend WHERE uid1 = me()) AND strlen(birthday_date) != 0 ORDER BY birthday_date";

						query = FB.Data.query( fql ).wait( function( friendsData ) {

              //setting up whitelist/blacklist
              whitelist = false;
              if(window.PF.page.geoWhitelist) {
                whitelist = new RegExp("^.._("+window.PF.page.geoWhitelist+")$");
              }
              blacklist = false;
              if(window.PF.page.geoBlacklist) {
                blacklist = new RegExp("^.._("+window.PF.page.geoBlacklist+")$");
              }

              //grabbing only the ok entries from the list
              friendsData = $.grep(friendsData, function(f) {

								//if whitelist is set, use that
								if(whitelist) {
									if(f.locale.match(whitelist)) {
										return true;
									}
									return false;
								}
								//otherwise use a blacklist
								if(blacklist) { 
									if(f.locale.match(blacklist)) {
										return false;
									}
									return true;
								}
								return true;
							});

              var friends = {}, 
                  ordered = [], 
                  amountOfFriends = amount || 9, 
                  secondsInAYear = 31556926,
                  secondsInADay = 86400, 
                  date = new Date(), 
                  year = date.getFullYear(), 
                  unix = Math.floor( ( date.getTime() / 1000 ) % secondsInAYear );


    
              $.each( friendsData, function( i, friend ) {
                var parts	= friend.birthday_date.split('/'),
					month	= parseInt( parts[0], 10) - 1,
					day		= parts[1],
					time;
    
                // Create friend's birthday object
                friend.birthday = new Date( year, month, day );
                friend.fan = false;
                
                // Determine how many days away it is
                time = Math.floor(((( friend.birthday.getTime() / 1000 ) - unix ) % secondsInAYear ) / secondsInADay) ;
    
                // Set the amount of days away as the index
                if ( ! friends[ time ] ) {
                  friends[ time ] = [];
                }
                friends[ time ].push( friend );
    
              });



              // Collect 9 friends with upcoming birthdays
              for ( var i = 0, q = 0; i < 367 && ( q < amountOfFriends || q < friends.length ); i++ ) {
      
                friends[i] && $.each( friends[i], function( i, friend ) {
      
                  
				/** /
                  $.each( fanIds.value, function( i, fan ) {
                    if ( friend.uid == fan.uid ) {
                      friend.fan = true;
                      $('#fansExist').show();
                      return false;
                    }
                  });
                  /**/
      
                  ordered.push( friend );
                  if ( q++ && q <= amountOfFriends ) {
                    return false;
                  }
                });
                
              }
              

              $.each(ordered, function( i, v ) {
				v.formatted_birthday = $.datepicker
					.setDefaults($.datepicker.regional[PF.langs.i18n])
					.formatDate("MM d", v.birthday);
              });

              $.publish("/facebook/friendsBirthdays", [ ( securePicSqaures( ordered ))]);

						});

					}
				});
      },
			getFriends : (function () {

                var friends;

                return function( callback ) {
                    if ( friends ) {
                        $.publish('/facebook/friends', [friends] );
                        callback( friends );
                    } else {
                        PF.facebook.getLoginStatus( function( status ) {
                            var fql, query;
                            if ( status.authResponse ) {

                                fql = "SELECT uid, name, pic_square, locale FROM user WHERE " +
                                            "uid IN (SELECT uid2 FROM friend WHERE uid1 = me())";

                                query = FB.Data.query( fql ).wait( function( result ) {
                                    friends = result;

																		//setting up whitelist/blacklist
																		whitelist = false;
																		if(window.PF.page.geoWhitelist) { 
																			whitelist = new RegExp("^.._("+window.PF.page.geoWhitelist+")$");
																		}
																		blacklist = false;
																		if(window.PF.page.geoBlacklist) { 
																			blacklist = new RegExp("^.._("+window.PF.page.geoBlacklist+")$");
																		}

																		//grabbing only the ok entries from the list
																		okFriends = $.grep(friends, function(f) { 
																			if(whitelist) { 
																				if(f.locale.match(whitelist)) { 
																					return true;
																				}
																				return false;
																			}
																			if(blacklist) { 
																				if(f.locale.match(blacklist)) { 
																					return false;
																				}
																				return true;
																			}
																			return true;
																		});

                                    $.publish('/facebook/friends', [okFriends] );
                                    callback( securePicSqaures( okFriends ));
                                });

                            } else {
                                PF.facebook.login(function(status) {
																		if(status.authResponse){
																			PF.facebook.getFriends( callback );	
																		}
                                });
                            }
                        });
                    }
                    
                }

		 	})()
		}

	})()

});
