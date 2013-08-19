{include file='common/adminHeader.tpl'}
<div>
	<form class='requestActiveCode'>
		<ul>
			<li>
				<label for="method">Method</label>
				<select name='method'>
					<option>requestActiveCode</option>
					<option>returnActiveCode</option>
				</select>
			</li>
			<li>
				<label for="amount">Amount</label>
				<input type='text' name='amount' id='amount' />
			</li>
				<li>
				<label for='upc'>UPC</label>
				<input type='text' name='upc' />
			</li>
			<li>
				<label for='currency'>Currency</label>
				<input type='text' name='currency' value='USD' />
			</li>
			<li>
				<label for='retailerName'>Retailer Name</label>
				<input type='text' name='retailerName' value='{$settings->inCommGateway->retailerName}' />
			</li>
			<li>
				<label for='dateTime'>Date Time</label>
				<input id="dateTime" type='text' name='dateTime' value='{$dateTime}' />
			</li>
			<li>
				<label for='txnId'>Transaction ID</label>
				<input id="txnId" type='text' name='txnId' value='{$txnId}' />
			</li>
			<li class='code'>
				<label for='code'>Code</label>
				<input type='text' name='code' />
			</li>
			<li>
				<label>&nbsp;</label>
				<input type='submit' value='Make Call' />	
			</li>
		</ul>
	</form>
	<section id='console'>
		
	</section>
</div>

<script id="template" type="text/html">
	<div>
		<h2>Request</h2>
		<%=_.escape(theData.request)%>

		<h2>Response</h2>
		<%=_.escape(theData.response)%>
	</div>
</script>
{include file='common/adminFooter.tpl'}
