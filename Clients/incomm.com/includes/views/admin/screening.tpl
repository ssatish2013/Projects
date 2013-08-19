{include file='common/adminHeader.tpl'}

<input id="refreshButton" type="button" value="refresh" class="button" />
<form id="screeningForm" method="post" action="/admin/screening">
<!-- Start the screening table -->
<table>
	<tr>
		<th>GiftId</th>
		<th>Amount</th>
		<th>Messages</th>
		<th>Recipient</th>
		<th>Purchaser</th>
		<th>RecipIp</th>
		<th>PurchIp</th>
		<th>Low</th>
		<th>Med</th>
		<th>High</th>
		<th>Created<br />(GMT)</th>
		<th>Claimed<br />(GMT)</th>
		<th>Approve</th>
		<th>Reject</th>
	</tr>

{foreach from=$gifts item=gift}
	<tr>

		<td>{$gift['id']}</td>
		<td>{$gift['amount']} {$gift['currency']}</td>
		<td>{$gift['numMessages']}</td>
		<td>{$gift['recipientName']}</td>
		<td>
		{foreach from=$gift['messages'] item=message}
			{$message->fromName}<br />
		{/foreach}
		</td>
		<td>{$gift['recipient']['ipAddress']}</td>
		<td>
		{foreach from=$gift['purchaser']['ipAddress'] item=ip}
			{$ip} <br />
		{/foreach}
		</td>
		<td>{$gift['low']}</td>
		<td>{$gift['med']}</td>
		<td>{$gift['high']}</td>
		<td>{$gift['createdDate']}<br/>{$gift['createdTime']}</td>
		<td>{$gift['claimedDate']}<br/>{$gift['claimedTime']}</td>
		<td>
			<input type="radio" name="gift_{$gift['id']}" value="approve" />
		</td>
		<td>
			<input type="radio" name="gift_{$gift['id']}" value="reject" />
		</td>
	</tr> 
{/foreach}
</table>

<input type="hidden" name="formGuid" value="{formSignatureModel::createSignature()}" />
<input id="clearButton" type="button" value="clear" class="button" />
<input type="submit" value="Submit" class="button" />

</form>
{include file='common/adminFooter.tpl'}
