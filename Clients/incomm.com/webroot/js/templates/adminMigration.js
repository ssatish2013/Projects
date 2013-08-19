
<% if(res.type=="settings") { %>

<% _.each( res.partner, function(partnerSettings, partnerName ) { %>

<h2><%=partnerName%> Partner Settings</h2>
<table>
	<tbody>
		<tr class='mainHeader'>
			<th></th>
			<th>Source (<%=res.src%>)</th>
			<th></th>
			<th>Destination (<%=res.dst%>)</th>
		</tr>
		<% _.each( partnerSettings, function( keys, category ) { %>
			<tr class='category'>
				<th colspan='4'><%=category%></th>
			</tr>
			<% _.each( keys, function( values, key ) { %>
				<tr>
					<td><%=key%></td>
					<td><% if(_.isNull(values[0])) { %><strong>NULL</strong><% } else { %><%=_.escape(values[0])%><% } %></td>
					<td>
						<% if(_.isNull(values[0])) { %>
							<input type="button" value="DEL" data-mode="settings" data-type="partner" data-partner="<%=partnerName%>" data-src="<%=res.src%>" data-dst="<%=res.dst%>" data-cat="<%=category%>" data-key="<%=key%>" />
						<% } else { %>
							<input type="button" value="&mdash;&gt;" data-mode="settings" data-type="partner" data-partner="<%=partnerName%>" data-src="<%=res.src%>" data-dst="<%=res.dst%>" data-cat="<%=category%>" data-key="<%=key%>" />
						<% } %>
					</td>
					<td><% if(_.isNull(values[1])) { %><strong>NULL</strong><% } else { %><%=_.escape(values[1])%><% } %></td>
				</tr>
			<% }); %>
		<% }); %>
	</tbody>
</table>
	
	
<% }); %>
		
<h2>Default Settings</h2>
<table>
	<tbody>
		<tr class='mainHeader'>
			<th></th>
			<th>Source (<%=res.srcDb%>)</th>
			<th></th>
			<th>Destination (<%=res.dstDb%>)</th>
		</tr>
		<% _.each( res.global, function( keys, category ) { %>
			<tr class='category'>
				<th colspan='4'><%=category%></th>
			</tr>
			<% _.each( keys, function( values, key ) { %>
				<tr>
					<td><%=key%></td>
					<td><% if(_.isNull(values[0])) { %><strong>NULL</strong><% } else { %><%=_.escape(values[0])%><% } %></td>
					<td>
						<% if(_.isNull(values[0])) { %>
							<input type="button" value="DEL" data-mode="settings" data-type="global" data-src="<%=res.src%>" data-dst="<%=res.dst%>" data-cat="<%=category%>" data-key="<%=key%>" />
						<% } else { %>
							<input type="button" value="&mdash;&gt;" data-mode="settings" data-type="global" data-src="<%=res.src%>" data-dst="<%=res.dst%>" data-cat="<%=category%>" data-key="<%=key%>" />
						<% } %>
					</td>
					<td><% if(_.isNull(values[1])) { %><strong>NULL</strong><% } else { %><%=_.escape(values[1])%><% } %></td>
				</tr>
			<% }); %>
		<% }); %>
	</tbody>
</table>


<% } else if (res.type=="language") { %>

<% _.each( res.partner, function(partnerLanguage, partnerName ) { %>

<h2><%=partnerName%> Partner Language</h2>
<table>
	<tbody>
		<tr class='mainHeader'>
			<th></th>
			<th>Source (<%=res.src%>)</th>
			<th></th>
			<th>Destination (<%=res.dst%>)</th>
		</tr>
		<% _.each( partnerLanguage, function( keys, category ) { %>
			<tr class='category'>
				<th colspan='4'><%=category%></th>
			</tr>
			<% _.each( keys, function( values, key ) { %>
				<tr>
					<td><%=key%></td>
					<td><% if(_.isNull(values[0])) { %><strong>NULL</strong><% } else { %><%=_.escape(values[0])%><% } %></td>
					<td>
						<% if(_.isNull(values[0])) { %>
							<input type="button" value="DEL" data-mode="language" data-type="partner" data-partner="<%=partnerName%>" data-src="<%=res.src%>" data-dst="<%=res.dst%>" data-cat="<%=category%>" data-key="<%=key%>" />
						<% } else { %>
							<input type="button" value="&mdash;&gt;" data-mode="language" data-type="partner" data-partner="<%=partnerName%>" data-src="<%=res.src%>" data-dst="<%=res.dst%>" data-cat="<%=category%>" data-key="<%=key%>" />
						<% } %>
					</td>
					<td><% if(_.isNull(values[1])) { %><strong>NULL</strong><% } else { %><%=_.escape(values[1])%><% } %></td>
				</tr>
			<% }); %>
		<% }); %>
	</tbody>
</table>

<% }); %>

<h2>Default Language</h2>
<table>
	<tbody>
		<tr class='mainHeader'>
			<th></th>
			<th>Source (<%=res.srcDb%>)</th>
			<th></th>
			<th>Destination (<%=res.dstDb%>)</th>
		</tr>
		<% _.each( res.global, function( keys, category ) { %>
			<tr class='category'>
				<th colspan='4'><%=category%></th>
			</tr>
			<% _.each( keys, function( values, key ) { %>
				<tr>
					<td><%=key%></td>
					<td><% if(_.isNull(values[0])) { %><strong>NULL</strong><% } else { %><%=_.escape(values[0])%><% } %></td>
					<td>
						<% if(_.isNull(values[0])) { %>
							<input type="button" value="DEL" data-mode="language" data-type="global" data-src="<%=res.src%>" data-dst="<%=res.dst%>" data-cat="<%=category%>" data-key="<%=key%>" />
						<% } else { %>
							<input type="button" value="&mdash;&gt;" data-mode="language" data-type="global" data-src="<%=res.src%>" data-dst="<%=res.dst%>" data-cat="<%=category%>" data-key="<%=key%>" />
						<% } %>
					</td>
					<td><% if(_.isNull(values[1])) { %><strong>NULL</strong><% } else { %><%=_.escape(values[1])%><% } %></td>
				</tr>
			<% }); %>
		<% }); %>
	</tbody>
</table>

<% } %>