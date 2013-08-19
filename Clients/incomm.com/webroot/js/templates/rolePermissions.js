<h2>Role Permissions</h2>
<ul>
<% _.each(permissions, function(p) { %>
	<li class="permissionRadio">
		<label for="<%=p.key%>"><%=p.name%></label>
		<div class="permissionsRadio">
			<span>On <input type="radio" name="<%=p.key%>" value="1" <% if(rolePermissions[p.id]) { %> checked <% } %>></span>
			<span>Off <input type="radio" name="<%=p.key%>" value="0" <% if(!rolePermissions[p.id]) { %> checked <% } %>></span>
		</div>
	</li>
<% }); %>
</ul>
