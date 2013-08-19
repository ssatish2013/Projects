<% var gifts = arguments[0]; %>
<table id="giftList">
	<thead>
		<tr>
			<th>Sender(s)</th>
			<th>Recipient</th>
			<th>Created</th>
			<th>Gift Status</th>
			<th>Card Status</th>
			<th>Payment Status</th>
		</tr>
	</thead>
	<tbody>

		<% _.each( gifts, function( row ) { %>
				<tr class="loadGift" data-gift-id=<%=row.gift.id%>>
					<td>
						<% _.each(row.messages, function(message, i) { %>
							<%if(i > 0) { %><br /><% } %>
							<%=message.fromName%>
						<% }); %>
					</td>
					<td>
						<%=row.gift.recipientName%>
					</td>
					<td>
						<%=row.gift.created%>
					</td>
					<td>
						<%=row.giftStatus%>
					</td>
					<td>
						<%=row.cardStatus%>
					</td>
					<td>
						<%=row.paymentStatus%>
					</td>
				</tr>
		<% }); %>
	</tbody>
</table>
<div id="csOverlay"></div>
