{include file='common/adminHeader.tpl'}
<ol class="tabs">
	<li><a href=#permissions>Permissions</a></li>
	<li><a href=#roles>Roles</a></li>
	<!-- <li class="hidden"><a href=#roles>Roles</a></li> -->
	<li class=active><a href=#user>User</a></li>
</ol>
<div id="tabContent">
	<!-- User Form -->
	<div id="user"> 
    <form id="userForm" class="validate">
      <h2>Find User</h2>
      <ul>
        <li>
          <label>User Email</label>
          <input name="email" type="text" title="User Email" data-validate=["required"] value=""/>
					&nbsp;
          <input id="getUserRoles" type="submit" value="find" />
        </li>
      </ul>
    </form>
		
    <form id="userRolesForm" class="hidden">
			<div id="userRoles"> 
			</div>

			<div class="buttons">
				<input name="id" type="hidden" value="0" />
      	<span class="clickable cancel">Cancel</span>
      	<input type="submit" value="Submit" />
			</div>
    </form>
	</div>
	<!-- Roles Table -->
  <div id="roles" class="hidden">
    <div id="rolesTableWrap">
      <h2>Role List</h2>
      <table>
        <thead>
          <tr>
            <th>Role</th>
            <th>Restricted</th>
						<th>Description</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {foreach $roles as $role}
            <tr 
							data-name="{$role->name}" 
							data-restricted-to-partner="{$role->restrictedToPartner}"
							data-description="{$role->description}"
							data-id="{$role->id}"
						>
              <td class="roleData">{$role->name} </td>
              <td class="roleData">{$role->restrictedToPartner}</td>
              <td class="roleData">{$role->description}</td>
              <td>
                <span data-role-id="{$role->id}" class="clickable">
								{if $role->status }
									Disable
								{else}
									Enable
								{/if}
								</span>
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
      <div class="buttons">
        <input type="submit" value="Add Role" id="addRole" />
      </div>
    </div>

	</div >
		<form id="newRoleForm" class="hidden validate">
			<h2>Add/Edit Role</h2>
			<ul>
				<li>
					<label>Role Name</label>
					<input name="name" type="text" title="Role Name" data-validate=["required"] />
				</li>
				<li>
					<label>Restricted to Partner</label>
					<select name="restrictedToPartner" title="Restricted to Partner">
						<option value="">None</option>
						{foreach $partners as $partner}
						<option value="{$partner['partner']}">{$partner['partner']}</option>
						{/foreach}	
					</select>
				</li>
				<li>
					<label>Description</label>
					<input name="description" type="text" title="Role Description" />
				</li>
			</ul>

			<div id="rolePermissions">
			</div>
			<ul>

        <li class="buttons">
					<input name="id" type="hidden" value="0" />
          <span class="clickable cancel">Cancel</span>
          <input type="submit" value="Submit" />
        </li>
			</ul>

		</form>

	<!-- Permissions Table -->
  <div class="hidden" id="permissions">
    <div id="permissionsTableWrap">
      <h2>Permission List</h2>
      <table>
        <thead>
          <tr>
            <th>Key</th>
						<th>Name</th>
						<th>Description</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {foreach $permissions as $permission}
            <tr
							data-key="{$permission->key}"
							data-name="{$permission->name}" 
							data-description="{$permission->description}"
							data-id="{$permission->id}"
						>
              <td class="permissionData">{$permission->key} </td>
              <td class="permissionData">{$permission->name}</td>
              <td class="permissionData">{$permission->description}</td>
              <td>
                <span data-permission-id="{$permission->id}" class="clickable disablePermission">
								{if $permission->status } 
									Disable
								{else }
									Enable
								{/if}
								</span>
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
      <div class="buttons">
        <input type="submit" value="Add Permission" id="addPermission" />
      </div>
    </div>
	</div>
	<form id="newPermissionForm" class="hidden validate">
		<h2>Add/Edit Permission</h2>
		<ul>
			<li>
				<label>Permission Key</label>
				<input name="key" type="text" title="Permission Key" data-validate=["required"] />
			</li>
			<li>
				<label>Permission Name</label>
				<input name="name" type="text" title="Permission Name" data-validate=["required"] />
			</li>
			<li>
				<label>Description</label>
				<input name="description" type="text" title="Permission Description" />
			</li>
        <li class="buttons">
				<input name="id" type="hidden" value="0" />
          <span class="clickable cancel">Cancel</span>
          <input type="submit" value="Submit" />
        </li>
		</ul>
	</form>
</div>
{include file='common/adminFooter.tpl'}
