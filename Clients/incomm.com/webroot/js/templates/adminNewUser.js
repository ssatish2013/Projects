	<%
		var roles = _.map(data.roles, function( value ){
			return value.name;	 
		}).join(',');
		var roleIds = _.map(data.roles, function( value ){
			return value.id;	 
		}).join(',');

		if(!data.roles.length) { 
			roles = '<span class="disabled">Disabled</span>';
		}
	%>
<tr class="hidden userRow"
  data-user-id="<%=data.id%>"
  data-user-first-name="<%=data.firstName%>"
  data-user-last-name="<%=data.lastName%>"
  data-user-email="<%=data.email%>"
  data-user-role-ids="<%=roleIds%>"
>
	<td class="userData"><%=data.firstName%> <%=data.lastName%></td>
	<td class="userData"><%=data.email%></td>
	<td class="userData"><%=roles%></td>

</tr>
