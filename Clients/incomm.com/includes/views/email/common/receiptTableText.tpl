{include file="lang:emailReceiptBillingInformationText"}
  {$transaction->firstName} {$transaction->lastName}
  {$transaction->address}
  {if $transaction->address2}{$transaction->address2}{/if}
  {$transaction->city}, {$transaction->state} {$transaction->zip}
  {$transaction->ccType} {include file="lang:emailReceiptEndingInText"} {$transaction->ccLastFour}

{if $transaction->ccType != "Paypal"}
{include file="lang:emailReceiptEnAuthorizationIDText"}
  {$transaction->authorizationId}
{else}
{include file="lang:emailReceiptTransactionIDText"}
  {$transaction->externalTransactionId}	
{/if}

{include file="lang:emailReceiptGiftsText"}
{foreach $transaction->getShoppingCart()->getAllMessages() as $message}
   {include file="lang:emailReceiptDeliveryToText"} {$message->getGift()->recipientName} on {date($settings->ui->dateFormat,strtotime($message->getGift()->deliveryDate))} - {currencyToSymbol currency=$message->currency}{$message->getDiscountedPrice()|string_format:"%.2f"} {$message->currency}
{/foreach}
