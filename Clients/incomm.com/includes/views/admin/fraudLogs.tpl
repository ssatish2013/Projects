{include file='common/adminHeader.tpl'}

<form id="fraugLogsForm" method="post">
	Fraud Logs for:
	<select name="searchBy">
		<option value="giftId">Purchased Gift Id</option>
		<option value="shoppingCartId">Shopping Cart Id</option>
		<option value="claimedGiftid">Claimed Gift Id</option>
	</select>
	<input type="search" name="searchParam" value="{$searchParam}"/>
	<div class="buttons">
		<input type="hidden" name="action" value="fraudLog" />
		<input type="submit" value="Search" />
	</div>
</form>
<div id="csResults">
	{if isset($table)}
		{$table nofilter}
	{/if}
</div>
{include file='common/adminFooter.tpl'}
