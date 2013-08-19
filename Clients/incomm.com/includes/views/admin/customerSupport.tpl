{include file='common/adminHeader.tpl'}

<form id="customerSupportForm" method="post">
	<div id="searchGroup">
		<span>Find gifts where the</span>
		<select name="searchType">
			<option value="transactionId">{$lang->adminTransactionIDLabel}</option>
			<option value="recipientEmailAddress" selected>{$lang->adminRecipientEmailLabel}</option>
			<option value="senderEmailAddress" selected>{$lang->adminSenderEmailLabel}</option>
			<option value="authorizationId">{$lang->adminConfirmationIDLabel}</option>
			<option value="giftGuid">{$lang->adminGiftGUIDLabel}</option>
			<option value="shoppingCartId">{$lang->adminShoppingCartIDLabel}</option>
			<option value="recentTransactions">{$lang->adminRecentGiftsLabel}</option>
		</select>
		<span>is</span>
		<input type="search" name="searchTerm" value="" />
	</div>
    <div class="buttons">
        <input type="hidden" name="action" value="search" />
        <input type="submit" value="Search" />
    </div>
		<div id="searchShowing" style="float: right;">
			{$lang->adminSearchShowing}
			<select name="searchLimit">
				<option value="0,10">{$lang->adminCSOption10Value}</option>
				<option value="0,25">{$lang->adminCSOption25Value}</option>
				<option value="0,50">{$lang->adminCSOption50Value}</option>
				<option value="">{$lang->adminCSOptionAllValue}</option>
			</select>
		</div>
</form>
<div id="csResults">
</div>
{include file='common/adminFooter.tpl'}
