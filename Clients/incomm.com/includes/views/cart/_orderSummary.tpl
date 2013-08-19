<h1>{$lang->tokenSummary}</h1>
<hr noshade="noshade" />
{*CONFIRMATION ID*}
{if $isCheckout}
	<label class="caption">{$lang->emailReceiptEnAuthorizationIDText}</label>
	<strong class="caption">{if $transaction->authorizationId}{$transaction->authorizationId}{else}{$transaction->externalTransactionId}{/if}</strong>
	<hr noshade="noshade" />
{/if}
{foreach $shoppingCart->getAllMessages() as $message}
	<label>{currencyToSymbol currency=$message->currency}{if ($message->currency != "JPY")}{($message->amount+$message->bonusAmount)|string_format:'%.2f'}{else}{($message->amount+$message->bonusAmount)|string_format:'%d'}{/if}&nbsp;{$message->currency}&nbsp;{productDisplayName productId=$message->_getProductId() default='giftCardNoun'}</label>
	<strong>{currencyToSymbol currency=$message->currency}{if ($message->currency != "JPY")}{$message->amount}{else}{$message->amount|string_format:"%d"}{/if}</strong>
	{*BONUS*}
		{foreach $message->getMessageItems(messageItemModel::TYPE_BONUS) as $bonus}
			<label>{$bonus->title}</label>
			<strong> </strong>
		{/foreach}
	{*DISCOUNT*}
		{foreach $message->getMessageItems(messageItemModel::TYPE_DISCOUNT) as $disc}
			<label>{$disc->title}</label>
			<strong>{currencyToSymbol currency=$message->currency}{if ($message->currency != "JPY")}({$disc->amount}){else}({$disc->amount|string_format:"%d"}){/if}</strong>
		{/foreach}
	{*EXTRA FEE*}
		{foreach $message->getMessageItems(messageItemModel::TYPE_FEE) as $fee}
			<label>{$fee->title}</label>
			<strong>{currencyToSymbol currency=$message->currency}{if ($message->currency != "JPY")}{$fee->amount}{else}{$fee->amount|string_format:"%d"}{/if}</strong>
		{/foreach}
	<label>{$lang->tokenSubtotal}</label>
	<strong>{currencyToSymbol currency=$message->currency}{if ($message->currency != "JPY")}{$message->getDiscountedPrice()|string_format:'%.2f'}{else}{$message->getDiscountedPrice()|string_format:'%d'}{/if}</strong>
	<hr noshade="noshade" />
{/foreach}
<label class="big">{$lang->tokenOrderTotal}</label>
<strong class="big">{currencyToSymbol currency=$message->currency}{if ($message->currency != "JPY")}{$shoppingCart->getTotal()|string_format:'%.2f'}{else}{$shoppingCart->getTotal()|string_format:'%d'}{/if}</strong>
