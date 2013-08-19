{include file='common/adminHeader.tpl'}
<form method='post'>
	<ul>
		<li>
			<label for="apiUsername">apiUsername</label>
			<input type="text" name="apiUsername" />
		</li>
		
		<li>
			<label for="apiPassword">apiPassword</label>
			<input type="text" name="apiPassword" />
		</li>

		<li>
			<label for="signature">signature</label>
			<input type="text" name="signature" />
		</li>
	</ul>
	<input type='submit' value='Submit' />
</form>
{include file='common/adminFooter.tpl'}