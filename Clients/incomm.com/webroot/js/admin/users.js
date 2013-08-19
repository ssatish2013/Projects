window.PF = $.extend( true, window.PF || {}, {

	users: (function() {

		var wrap		= PF.admin.section,
			userButtons = wrap.find("#users .buttons"),
			newUserForm	= wrap.find("#newUserForm"),
			table		= PF.admin.section.find("#usersTableWrap"),
			createableRoles = [],
			methods		= {

				clearNewUserForm: function() {
					newUserForm.find("input[type=text], input[type=password]").val("");
					$('#userId').val("");
          PF.template.render( "userRoles", {roles: createableRoles, userRoles: [], found: 1, userId: "", email: "New User"}, function(output) {
                $('#userRoles').html(output);
            });
				},
				init : function() {

					$.each(PF.page.roles, function(i, role) { 
						if(PF.page.canCreate[role.name]) { 
							createableRoles.push(role);
						}
					});
					// Bind to adding a user
					wrap
						.delegate("#addUser", "click", function( e ) {
							$.publish("/users/addUserClick");
							e.preventDefault();
						})
						.delegate("#newUserForm", "submit", function( e ) {
							$.publish("/users/addUserSubmit", [ $(this)] );
							e.preventDefault();
						})
						.delegate("#newUserForm .cancel", "click", function( e ) {
							$.publish("/users/addUserCancel");
							e.preventDefault();
						})
						.delegate("#usersTableWrap .userData", "click", function(e) { 
							$.publish("/users/clickUser", [$(this).closest('tr')]);
						});


					//editing a user
					$.subscribe("/users/clickUser", function(row) { 
						data = row.data();
						$('#firstName').val(data.userFirstName);
						$('#lastName').val(data.userLastName);
						$('#email').val(data.userEmail);
						$('#userId').val(data.userId);

						//grab user roles
						var userRoles = {};
						$.each(data.userRoleIds.toString().split(','), function(i, id) { 
							userRoles[id] = 1;
						});

						PF.template.render( "userRoles", {roles: createableRoles, userRoles: userRoles, found: 1, userId: data.userId, email: data.userFirstName+' '+data.userLastName+' - '+data.userId}, function(output) {
                $('#userRoles').html(output);
            });

						table.slideUp();
						newUserForm.slideDown();
					});

					$.subscribe("/users/addUserClick", function() {
						methods.clearNewUserForm();
						table.slideUp();
						newUserForm.slideDown();	
					});

					$.subscribe("/users/addUserCancel", function() {
						table.slideDown();
						newUserForm.slideUp();	
					});

					$.subscribe("/users/addUserSubmit", function( form ) {
						form.ajaxSubmit({
							type : "post",
							dataType: "json",
							data : {
								action : "newUser"
							},
							success : function( json ) {
								$.publish("/users/addUserResponse", [json] );
							}
						});
					});

					$.subscribe("/users/addUserResponse", function( user ) {
						table.slideDown(function() {
							PF.template.render("adminNewUser", { data : user }, function( html ) {
								var $html = $(html);
								var row = table.find('tr[data-user-id='+user.id+']');
								if(row.length) { 
									row.replaceWith($html);
								}
								else {
									table.find("tbody").append( $html );
								}
								$html.fadeIn();
							});
						});
						newUserForm.slideUp();	
					});
				}




			}

		return methods;
	})()
});
