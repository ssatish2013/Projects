window.PF = $.extend( true, window.PF || {}, {

	permissions: (function() {

		var wrap		= PF.admin.section,
			methods		= {

				init : function() {

					$.subscribe("/tabs/click", function(elm) { 
						$.publish("/permissions/addRoleCancel");
						$.publish("/permissions/addPermissionCancel");
					});
					//Events!
					wrap
						//role stuff
						.delegate("#addRole", "click", function( e ) {
							$.publish("/permissions/addRoleClick");
							e.preventDefault();
						})
            .delegate("#newRoleForm", "submit", function( e ) {
              $.publish("/permissions/addRoleSubmit", [ $(this)] );
              e.preventDefault();
            })
						.delegate("#newRoleForm .cancel", "click", function( e ) {
							$.publish("/permissions/addRoleCancel");
							$.publish("/permissions/getRolePermissions", [0]);
						})
						.delegate("#rolesTableWrap td.roleData", "click", function( e ) {
							$.publish("/permissions/editRoleClick", [$(this).closest('tr').data()]);
							$.publish("/permissions/getRolePermissions", [$(this).closest('tr').data('id')]);
						})
						.delegate("#rolesTableWrap .clickable", "click", function( e ) {
							$.publish("/permissions/roleStatusUpdate", [$(this).data('roleId')]);
						})
						.delegate("#userForm", "submit", function( e ) {
							$.publish("/permissions/getUserRoles");
							e.preventDefault();
						})
						.delegate("#userRolesForm .clickable", "click", function( e ) {
							$('#userForm').slideDown();
							$('#userRolesForm').slideUp();
						})
						.delegate("#userRolesForm", "submit", function( e ) {
							$.publish("/permissions/editUserRoles");
							e.preventDefault();
						});


					//user events
					$.subscribe("/permissions/getUserRoles", function() {
						form = $('#userForm');
            form.ajaxSubmit({
              type : "post",
              dataType: "json",
              data : {
                action : "getUserRoles"
              },
              success : function( json ) {
                $.publish("/permissions/getUserRolesResponse", [json] );
              }
            });
					});

					$.subscribe("/permissions/getUserRolesResponse", function(data) { 
						PF.template.render( "userRoles", {roles: data.roles, userRoles: data.userRoles, found: data.found, email: data.email, userId: data.userId}, function(output) {
								$('#userRoles').html(output);
								$('#userForm').slideUp();
								$('#userRolesForm').slideDown();
						});
					});

					$.subscribe("/permissions/editUserRoles", function() { 
						form = $('#userRolesForm');
            form.ajaxSubmit({
              type : "post",
              dataType: "json",
              data : {
                action : "editUserRoles"
              },
              success : function( json ) {
								$('#userForm').slideDown();
								$('#userRolesForm').slideUp();
              }
            });
					});

					//role events
					$.subscribe("/permissions/addRoleClick", function() {
						form = $('#newRoleForm');
						form.find('input[type="text"]').val("");
						form.find('input[name="id"]').val(0);
						$('#rolesTableWrap').slideUp();
						form.slideDown();	
					});

					$.subscribe("/permissions/editRoleClick", function(data) {
						form = $('#newRoleForm');
						$.each(data, function(key) { 
							form.find('[name="'+key+'"]').val(data[key]);
						});
						$('#rolesTableWrap').slideUp();
						form.slideDown();	
					});

					$.subscribe("/permissions/addRoleCancel", function() {
						form = $('#newRoleForm');
						$('#rolesTableWrap').slideDown();
						form.slideUp();	
					});

					$.subscribe("/permissions/addRoleSubmit", function( form ) {

						//validation
						id = form.find('input[name="id"]').val();
						name = form.find('input[name="name"]').val();
						if(id == 0 && $('#rolesTableWrap tr[data-name="'+name+'"]').length) {
							$.publish( "/admin/ajaxStatus", ['error', 'There is already a role ' + name+'!'] );
							return;
						}
						if(!PF.validate.form(form)) { 
							return;
						}

						form.ajaxSubmit({
							type : "post",
							dataType: "json",
							data : {
								action : "newRole"
							},
							success : function( json ) {
								if(json === null) { 
									$.publish( "/admin/ajaxStatus", ['error', 'There was an error adding the role, make sure there are no roles with similar names']);
									return;
								}
								$.publish("/permissions/addRoleResponse", [json] );
							}
						});

					});

					$.subscribe("/permissions/addRoleResponse", function( role ) {
						if(role.restrictedToPartner === null) { role.restrictedToPartner = ''; }
						table = $('#rolesTableWrap');
						html = '<tr ' + 
              		'data-name="'+role.name+'" ' +  
              		'data-restricted-to-partner="'+role.restrictedToPartner+'" ' + 
              		'data-description="'+role.description+'" ' + 
              		'data-id="'+role.id+'" >' +
						      '<td class="roleData">' + role.name + '</td>' +
									'<td class="roleData">' + role.restrictedToPartner+ '</td>' +
									'<td class="roleData">' + role.description+ '</td>' +
									'<td><span data-role-id="'+role.id+'" class="clickable disableRole">Disable</span></td>' +
									'</tr>';
							table.slideDown(function() {
								//added a role
								if(role.isNew) { 
									table.find("tbody").append(html).fadeIn();
								}
								//edited role
								else {
									table.find('tr[data-id="'+role.id+'"]').replaceWith(html).fadeIn();
								}
							});
						$('#newRoleForm').slideUp();	
					});

					$.subscribe("/permissions/roleStatusUpdate", function( roleId ) {
						$.ajax({
							type : "post",
							dataType: "json",
							data : {
								action : "roleStatusUpdate",
								roleId: roleId 
							},
							success : function( json ) {
								if(json.status) { 
									$('span.clickable[data-role-id="'+roleId+'"]').html('Disable');
								}
								else {
									$('span.clickable[data-role-id="'+roleId+'"]').html('Enable');
								}
							}
						});
					});

					$.subscribe("/permissions/getRolePermissions", function( roleId ) {
						$('#rolePermissions').html('').addClass('loading');
						$.ajax({
							type : "post",
							dataType: "json",
							data : {
								action : "getRolePermissions",
								roleId: roleId 
							},
							success : function( data ) {
								PF.template.render( "rolePermissions", {permissions: data.permissions, rolePermissions: data.rolePermissions}, function(output) {
									$('#rolePermissions').html(output).removeClass('loading');
								});
							}
						});
					});


						wrap
						//permissions stuff
						.delegate("#addPermission", "click", function( e ) {
							$.publish("/permissions/addPermissionClick");
							e.preventDefault();
						})
            .delegate("#newPermissionForm", "submit", function( e ) {
              $.publish("/permissions/addPermissionSubmit", [ $(this)] );
              e.preventDefault();
            })
						.delegate("#newPermissionForm .cancel", "click", function( e ) {
							$.publish("/permissions/addPermissionCancel");
						})
						.delegate("#permissionsTableWrap td.permissionData", "click", function( e ) {
							$.publish("/permissions/editPermissionClick", [$(this).closest('tr').data()]);
						})
						.delegate("#permissionsTableWrap .clickable", "click", function( e ) {
							$.publish("/permissions/permissionStatusUpdate", [$(this).data('permissionId')]);
						});


					//permission events
					$.subscribe("/permissions/addPermissionClick", function() {
						form = $('#newPermissionForm');
						form.find('input[type="text"]').val("");
						form.find('input[name="id"]').val(0);
						$('#permissionsTableWrap').slideUp();
						form.slideDown();	
					});

					$.subscribe("/permissions/editPermissionClick", function(data) {
						form = $('#newPermissionForm');
						$.each(data, function(key) { 
							form.find('[name="'+key+'"]').val(data[key]);
						});
						$('#permissionsTableWrap').slideUp();
						form.slideDown();	
					});

					$.subscribe("/permissions/addPermissionCancel", function() {
						form = $('#newPermissionForm');
						$('#permissionsTableWrap').slideDown();
						form.slideUp();	
					});

					$.subscribe("/permissions/addPermissionSubmit", function( form ) {
						//validation
						id = form.find('input[name="id"]').val();
						key = form.find('input[name="key"]').val();
						if(id == 0 && $('#permissionsTableWrap tr[data-key="'+key+'"]').length) {
							$.publish( "/admin/ajaxStatus", ['error', 'There is already a permission ' + key+'!'] );
							return;
						}
						if(!PF.validate.form(form)) { 
							return;
						}
						form.ajaxSubmit({
							type : "post",
							dataType: "json",
							data : {
								action : "newPermission"
							},
							success : function( json ) {
								if(json === null) { 
									$.publish( "/admin/ajaxStatus", ['error', 'There was an error adding the permission, make sure there are no permissions with similar names']);
									return;
								}
								$.publish("/permissions/addPermissionResponse", [json] );
							}
						});

					});

					$.subscribe("/permissions/addPermissionResponse", function( permission ) {
						table = $('#permissionsTableWrap');
						html = '<tr ' + 
              		'data-key="'+permission.key+'" ' + 
              		'data-name="'+permission.name+'" ' +  
              		'data-description="'+permission.description+'" ' + 
              		'data-id="'+permission.id+'" >' +
						      '<td class="permissionData">' + permission.key+ '</td>' +
									'<td class="permissionData">' + permission.name+ '</td>' +
									'<td class="permissionData">' + permission.description+ '</td>' +
									'<td><span data-permission-id="'+permission.id+'" class="clickable disablePermission">Disable</span></td>' +
									'</tr>';
							table.slideDown(function() {
								//added a permission
								if(permission.isNew) { 
									table.find("tbody").append(html).fadeIn();
								}
								//edited permission
								else {
									table.find('tr[data-id="'+permission.id+'"]').replaceWith(html).fadeIn();
								}
							});
						$('#newPermissionForm').slideUp();	
					});

					$.subscribe("/permissions/permissionStatusUpdate", function( permissionId ) {
						$.ajax({
							type : "post",
							dataType: "json",
							data : {
								action : "permissionStatusUpdate",
								permissionId: permissionId 
							},
							success : function( json ) {
								if(json.status) { 
									$('span.clickable[data-permission-id="'+permissionId+'"]').html('Disable');
								}
								else {
									$('span.clickable[data-permission-id="'+permissionId+'"]').html('Enable');
								}
							}
						});
					});
				}


			}

		return methods;
	})()
});
