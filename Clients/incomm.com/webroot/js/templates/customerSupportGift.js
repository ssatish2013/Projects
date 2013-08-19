<ul>
	<li>
		<h2>Gift details</h2>
	</li>
	<li>
		<label>Gift Id</label>
		<span><%=gift.id%></span>
	</li>
	<li>
		<label>Recipient Name</label>
		<span><%=gift.recipientName%></span>
	</li>
	<% if (!gift.physicalDelivery) { %>
		<li>
			<label>Recipient Phone</label>
			<span><%=gift.recipientPhoneNumber%></span>
		</li>
	<% } %>
	<li>
		<label>Recipient Email</label>
		<span><%=gift.recipientEmail%></span>
	</li>
	<li>
		<label>Email VIP Status</label>
		<span class="buttons">
			<select class='emailVipStatus' id="recipientVipStatus" name="recipientVipStatus" data-email="<%=gift.recipientEmail%>" <% if(!canEditVip) { %> disabled <% } %>>
				<option value="">None</option>
				<option value="approve" <%if(recipientVipStatus == "approve") { %>selected<% } %>>Approve</option>
				<option value="review" <%if(recipientVipStatus == "review") { %>selected<% } %>>Review</option>
				<option value="decline" <%if(recipientVipStatus == "decline") { %>selected<% } %>>Decline</option>
			</select>
		</span>
	</li>
	<% if (!gift.physicalDelivery) { %>
		<li>
			<label>Scheduled Delivery</label>
			<span>
				<% if (deliveryDate) { %>
					<%=deliveryDate%>
				<% } else { %>
					Not Delivered
				<% } %>
			</span>
		</li>
		<li>
			<label>Delivered?</label>
			<span>
				<% if ( gift.addedToDeliveryQueue ) { %>
					Yes
				<% } else { %>
					No
				<% } %>
			</span>
		</li>
		<li>
			<label>Delivered On</label>
			<span>
				<% if (gift.delivered) { %>
					<%=gift.delivered%>
				<% } else { %>
					Not Delivered
				<% } %>
			</span>
		</li>
		<li>
			<label>Delivered via</label>
			<span>
				<%
					var types = [];
					if (gift.emailDelivery) {
						types.push("Email");
					}
					if (gift.facebookDelivery) {
						types.push("Facebook");
					}
					if (gift.twitterDelivery) {
						types.push("Twitter");
					}
					types = types.join(", ");
				%>
				<%=types%>
			</span>
		</li>
	<% } else { %>
		<li>
			<label>Delivered via</label>
			<span>
				<%=shippingDetail.shippingOption.carrier%>
				<%=shippingDetail.shippingOption.serviceLevel%>
			</span>
		</li>
		<li>
			<label>Shipped?</label>
			<span><%=(shippingDetail.dateShipped ? "Yes" : "No")%></span>
		</li>
	<% } %>
	<%
		_.each({
			claimed: "Claimed?",
			inScreeningQueue: "In Screening Queue?",
			approved: "Approved?",
			rejected: "Rejected?",
			thanked: "Sent thank you?",
			created: "Created",
			updated: "Updated"
		}, function(value, key) {
			if (gift.physicalDelivery && (key == "claimed" || key == "thanked")) {
				return;
			}
	%>
		<li>
            <label><%=value%></label>
			<% if (gift[key]) { %>
				<% if (gift[key] == 1) { %>
					Yes
				<% } else { %>
					<%=gift[key]%>
				<% } %>
			<% } else { %>
				No
			<% } %>
		</li>
	<% }); %>
	<li>
		<label>Reservation?</label>
		<span>
			<% if ( reservation.reservationTime ) { %>
				<%=reservation.reservationTime%>
			<% } else { %>
				No
			<% } %>
		</span>
	</li>
	<% if (!gift.physicalDelivery) { %>
		<li>
			<label>Activated?</label>
			<span>
				<% if ( inventory.activationTime ) { %>
					<%=inventory.activationTime%>
				<% } else { %>
					No
				<% } %>
			</span>
		</li>
		<li>
			<label>Deactivated?</label>
			<span>
				<% if ( inventory.deactivationTime ) { %>
					<%=inventory.deactivationTime%>
				<% } else { %>
					No
				<% } %>
			</span>
		</li>
		<li>
			<label>Redeemed?</label>
			<span><%=redeemed%></span>
		</li>
	<% } %>
</ul>
<div id="csActions">
	<ul>
		<li>
			<h2>Card Details</h2>
		</li>
		<li>
			<img src="<%=design.smallSrc%>" />
		</li>
		<li>
			<label>Card Status:</label>
			<span><%=cardStatus%></span>
		</li>
		<li>
			<label>Gift Status:</label>
			<span><%=giftStatus%></span>
		</li>
		<li>
			<label>Card Amount:</label>
			<span><%=giftAmount%> <%=gift.currency%></span>
		</li>
		<li>
			<label># of Contributors:</label>
			<span><%=contributors%></span>
		</li>
		<li>
			<label>Payment Status:</label>
			<span><%=paymentStatus%></span>
		</li>
		<% if(screenedBy != '') { %>
			<li>
				<label>Screened By</label>
				<span><%=screenedBy%></span>
			</li>
		<% } %>
		<% if(screenedNotes != '') { %>
			<li>
				<label>Screened Notes</label>
				<span><%=screenedNotes%></span>
			</li>
		<% } %>
		<% if(inventory.activationTime !== null && inventory.deactivationTime === null) { %>
			<li>
				<label>Pan</label>
				<span><%=inventory.pan%></span>
			</li>
			<li>
				<label>Pin</label>
				<span><%=inventory.pin%></span>
			</li>
		<% } %>
	</ul>
	<% if (!gift.physicalDelivery) { %>
		<ul>
			<li id="actionsHeader">
				<h2>Actions</h2>
			</li>
			<li>
				<label>Resend Email</label>
				<span>
					<input id="resendEmail" name="resendEmail" type="text" value="<%=gift.recipientEmail%>" />
				</span>
			</li>
			<% if (inventory.activationTime && !(!isAuthorized || isRefunded || isChargeback) && messages.length > 0) { %>
				<li>
					<div class="buttons">
						<input id="sendGift" class="actionButton" type="submit" value="Resend Gift" />
					</div>
				</li>
			<% } else if (!inventory.deactivationTime && !(!isAuthorized || isRefunded || isChargeback) && messages.length > 0) { %>
				<li>
					<div class="buttons">
						<input id="sendGift" class="actionButton" type="submit" value="ReSend Claim Email" />
					</div>
		    	</li>
			<% } %>
			<% if (!(!isAuthorized || isRefunded || isChargeback) && canRefund && messages.length > 0) { %>
				<li>
					<div class="buttons">
						<input id="refundGift" class="actionButton" type="submit" value="Issue Refund To All" />
					</div>
				</li>
			<% } %>
		</ul>
	<% } else {%>
		<ul id="shippingDetails">
			<li>
				<h2>Shipping Details</h2>
			</li>
			<li>
				<label>Shipped Via</label>
				<span>
					<%=shippingDetail.shippingOption.carrier%>
					<%=shippingDetail.shippingOption.serviceLevel%>
				</span>
			</li>
			<li>
				<label>Ship Date</label>
				<span><% if (deliveryDate) {%>
					<%=deliveryDate%>
					<% } else { %>
					Not Selected
					<% } %>
				</span>
			</li>
			<li>
				<label>Shipped On</label>
				<span><%=shippingDetail.dateShipped%></span>
			</li>
			<li>
				<label>Card Number</label>
				<span><%=shippingDetail.cardNumber%></span>
			</li>
			<li>
				<label>Error Level</label>
				<span><%=shippingDetail.orderException%></span>
			</li>
			<li>
				<label>Order File</label>
				<span>
					<% if (shippingDetail.orderFileKey) { %>
						<%=shippingDetail.orderFileKey%>.xml
					<% } %>
				</span>
			</li>
			<li>
				<label>Address</label>
				<span><%=shippingDetail.address%></span>
			</li>
			<% if (shippingDetail.address2) { %>
				<li>
					<label>Address2</label>
					<span><%=(shippingDetail.address2 ? shippingDetail.address2 : "")%></span>
				</li>
			<% } %>
			<li>
				<label>City</label>
				<span><%=shippingDetail.city%></span>
			</li>
			<li>
				<label>State/Region</label>
				<span><%=shippingDetail.state%></span>
			</li>
			<li>
				<label>Zip/Postal Code</label>
				<span><%=shippingDetail.zip%></span>
			</li>
			<li>
				<label>Country</label>
				<span><%=shippingDetail.country%></span>
			</li>
		</ul>
	<% } %>
</div>
<div id="csMessages">
	<ul>
		<li>
			<h2>Contributors</h2>
		</li>
		<% _.each(messages, function( message ) { %>
			<li>
				<ol>
					<li>
						<label>Message ID </label>
						<span><%=message.id%></span>
                        </li>
					<li>
						<label>Shopping Cart ID </label>
						<span><%=message.shoppingCart.id%></span>
						<% if(canKount) {  %>
							<span class="cst-kount-link"><a href="<%=kountVars.baseUrl%>/workflow/advancedsearch.html?q=<%=message.shoppingCart.id%>&seach=Search" target="_blank">View in Kount</a></span>
						<% } %>
					</li>
					<li>
						<label>From</label>
						<span><%=message.fromName%></span>
					</li>
					<li>
						<label>Email</label>
						<span><%=message.transaction.fromEmail%></span>
					</li>
					<li>
						<label>Email VIP Status</label>
						<span class="buttons">
							<select class='emailVipStatus' id="<%=message.id%>VipStatus" name="<$=message.id%>VipStatus" data-email="<%=message.transaction.fromEmail%>" <% if(!canEditVip) { %> disabled <% } %>>
								<option value="">None</option>
								<option value="approve" <%if(message.emailVipStatus == "approve") { %>selected<% } %>>Approve</option>
								<option value="review" <%if(message.emailVipStatus == "review") { %>selected<% } %>>Review</option>
								<option value="decline" <%if(message.emailVipStatus == "decline") { %>selected<% } %>>Decline</option>
							</select>
						</span>
					</li>
					<li>
						<label>Amount</label>
						<span><%=message.amount%> <%=message.currency%></span>
					</li>
					<li>
						<label>Message</label>
						<span><%=message.message%></span>
					</li>
					<% if(message.promo !== null) { %>
						<li class="highlight">
                            <label><%=message.promo.title%></label>
                            <span><%=message.promo.description%></span>
						</li>
					<% } %>

					<!-- Payment Info -->
					<ol class="paymentList">
						<li class="paymentListHeader">
							<h3>Payment Information</h3>
						</li>
						<% if(message.transaction.authorizationId === null
							&& message.transaction.externalTransactionId === null
						) { %>
							<li class="highlight">
								<label>Payment Status</label>
								<span>Transaction not authorized or authorization voided</span>
							</li>
						<% } else if (message.transaction.refunded !== null) { %>
							<li class="highlight">
								<label>Payment Status</label>
								<span>This transaction has been refunded</span>
							</li>
						<% } else { %>
						<li>
							<label>Payment Authorized</label>
							<span class="buttons" data-messageid="<%=message.id%>"	>
								<% if (message.isContribution == 1 && gift.claimed == null) { %>
								<input class="refundmsgbtn" type="submit" value="Refund Contributor" />
								<% } else { %>
									Gift Creator
								<% } %>
							</span>
						</li>
						<% } %>
						<li>
							<label>Billing Name</label>
							<span>
								<%=message.transaction.firstName%>
								<%=message.transaction.lastName%>
							</span>
						</li>
						<li>
							<label>Country</label>
							<span><%=message.transaction.country%></span>
						</li>
						<li>
							<label>City</label>
							<span><%=message.transaction.city%></span>
						</li>
						<li>
							<label>State</label>
							<span><%=message.transaction.state%></span>
						</li>
						<li>
							<label>Address</label>
							<span><%=message.transaction.address%></span>
							<% if(message.transaction.address2 !== null && message.transaction.address2) { %>
								<br />
								<span><%=message.transaction.address2%></span>
							<% } %>
						</li>
						<li>
							<label>Zip</label>
							<span><%=message.transaction.zip%></span>
						</li>
							<li>
							<label>Phone</label>
							<span><%=message.transaction.phoneNumber%></span>
						</li>
						<li>
							<label>Authorization ID</label>
							<span><%=message.transaction.authorizationId%></span>
						</li>
						<li>
							<label>Authorization Time</label>
							<span><%=message.transaction.authorizationTime%></span>
						</li>
						<li>
							<label>Transaction ID</label>
							<span><%=message.transaction.externalTransactionId%></span>
						</li>
						<li>
							<label>Payment Type</label>
							<span><%=message.transaction.ccType%></span>
						</li>
						<li>
							<% if(message.transaction.ccType == 'Paypal') { %>
								<label>PayPal ID</label>
								<span><%=message.shoppingCart.paypalExpressPayerId%></span>
							<% } else { %>
								<label>Last Four</label>
								<span><%=message.transaction.ccLastFour%></span>
							<% } %>
						</li>
					</ol>
				</ol>
			</li>
		<% }); %>
	</ul>
</div>
<div id="emailLogs">
	<h2>Event Logs</h2>
	<table>
		<tr>
			<th align="left">Category</th>
			<th align="left">Event</th>
			<th align="left">Timestamp</th>
			<th align="left">Agent</th>
			<th align="left">Description</th>
		</tr>
	<% _.each(cstLogs, function( log ) { %>
		<tr>
			<td><%=log.category%></td>
			<td><%=log.event%></td>
			<td><%=log.timestamp%></td>
			<td><%=log.agent%></td>
			<td class="cstlog-description"><%=log.description%></td>
		</tr>
	<% }); %>
	</table>
</div>

<input id="giftId" name="giftId" type="hidden" value="<%=gift.id%>" />

