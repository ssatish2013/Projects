{include file='common/adminHeader.tpl'}
<h1>Terms and Conditions</h1>

<form id="updateTerms" method="POST">		
	<label>Text</label>
	<textarea name="value" id="valueTextarea" style="width: 650px;height: 400px;" >{include file='lang:cardTerms'}</textarea>
	<ul>
		<li class="buttons">
			<span class="clickable cancel">Cancel</span>
			<input type="submit" value="Submit" />
		</li>
	</ul>
</form>					
{include file='common/adminFooter.tpl'}
