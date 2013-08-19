<table id="actionLogList">
  <thead>
    <tr>
			<th>User</th>
      <th>Area</th>
			<th>Lookup</th>
      <th>Partner</th>
			<th>Type</th>
			<th>Changed</th>
      <th>Timestamp</th>
    </tr>
  </thead>
  <tbody>
    <% _.each( actionLogs, function( actionLog) { %>
        <tr class="loadActionLog summaryRow" data-action-log-id=<%=actionLog.id%>>
          <td>
            <%=actionLog.user.name%>
          </td>
          <td>
            <%=actionLog.area%>
          </td>
          <td>
						<% _.each(actionLog.lookup, function(v,k) { %>
							<div><%=k%>: <%=v%></div>
						<% }); %>
          </td>
          <td>
            <%=actionLog.partner%>
          </td>
          <td>
            <% if(actionLog.oldValue.length == 0) { %>
						Added
						<% } else { %>
						Updated
						<% } %>
          </td>
					<td>
						<% _.each(actionLog.changed, function(v,k) { %>
							<div><%=k%> -> <%=v%></div>
						<% }); %>
					</td>
          <td>
            <%=actionLog.timestamp%>
          </td>
        </tr>
    <% }); %>
  </tbody>
</table>