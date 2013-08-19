{include file='common/adminHeader.tpl'}
<ol class="tabs">
	<li class=active><a href=#users>Users</a></li>
	<!-- <li class="hidden"><a href=#roles>Roles</a></li> -->
</ol>
<div id="tabContent">
	<div id=users>
		<div id="usersTableWrap">
			<h2>User List</h2>
			<table>
				<thead>
					<tr>
						<th>Name</th>
						<th>Email</th>
						<th>Roles</th>
					</tr>
				</thead>
				<tbody>
					{foreach $users as $user}
						<tr class="userRow"
							data-user-id="{$user->id}"
							data-user-first-name="{$user->firstName}"
							data-user-last-name="{$user->lastName}"
							data-user-email="{$user->email}"
							data-user-role-ids="{foreach from=$user->getRoles() item=role name=userRoles}{$role->id}{if !$smarty.foreach.userRoles.last},{/if}{/foreach}"
						>
							<td class="userData">{$user->firstName} {$user->lastName}</td>
							<td class="userData">{$user->email}</td>
							<td class="userData">
								{foreach $user->getRoles() as $role}
									{$role->name}
								{/foreach}
								{if !count($user->getRoles())}
									<span class="disabled">Disabled</span>
								{/if}
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			<p>These results truncated, <a href="/admin/permissions">click here</a> to search by e-mail.</p>
			<div class="buttons">
				<input type="submit" value="Add User" id="addUser" />
			</div>
		</div>
		<form id="newUserForm" class="hidden validate">
			<h2>Who is this new user?</h2>
			<ul>
				<li>
					<label>First Name</label>
					<input id="firstName" name="first" type="text" title="First name" />
					<span class="inline-error" data-for="firstName"></span>
				</li>
				<li>
					<label>Last Name</label>
					<input id="lastName" name="last" type="text" title="Last name" />
					<span class="inline-error" data-for="lastName"></span>
				</li>
				<li>
					<label>Email</label>
					<input id="email" name="email" type="text" title="Email address" />
					<span class="inline-error" data-for="email"></span>
				</li>
				<li>
					<label>Has Same Roles As Me</label>
					<input id="sameRoles" name="sameRoles" type="checkbox" value="1"/>
				</li>
				<li>
					<div id="userRoles"></div>
				</li>
				<li class="buttons">
					<span class="clickable cancel">Cancel</span>
					<input id="userId" type="hidden" name="userId" value="" />
					<input type="submit" value="Add User" id="form-add-user" />
				</li>
			</ul>
		</form>
	</div>
	<div class=hidden id=roles>
		<h2>Role List</h2>
	</div>
</div>
{capture assign='inlineScripts'}
	window.PF = $.extend( true, window.PF || {}, {
		page: {
			roles: {$roles|json_encode nofilter},
			canCreate: {$canCreate|json_encode nofilter}
		}
	});
{/capture}
{include file='common/adminFooter.tpl'}
