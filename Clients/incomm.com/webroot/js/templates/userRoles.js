<h2>User Roles (<%=email%>)</h2>
<ul>
<% if(!found) { %>
	<li>No User found!</li>
<% } else { %>
<% _.each(roles, function(r) { %>
	<li class="permissionRadio">
		<label for="role_<%=r.id%>" title="<%=r.description%>"><%=r.name%></label>
		<div class="permissionsRadio">
			<span>On <input type="radio" name="role_<%=r.id%>" value="1" <% if(userRoles[r.id]) { %> checked <% } %>></span>
			<span>Off <input type="radio" name="role_<%=r.id%>" value="0" <% if(!userRoles[r.id]) { %> checked <% } %>></span>
		</div>
	</li>
<% }); %>
<% } %>
</ul>
<input type="hidden" name="userId" value="<%=userId%>" />
