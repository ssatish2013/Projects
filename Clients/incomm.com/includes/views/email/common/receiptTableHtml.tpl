<tr>
  	<td style="font-size: 12px">
  	<table style="width: 650px;border: 1px solid #e3e3e3;background:#f9f9f9;border-collapse: collapse;margin-left: auto;	margin-right: auto;">
        <tr>
          <td valign="top"  style="padding-left:25px;line-height:.5em; height:140px"><h3 style="font-weight: bold;line-height:2.5em;margin-top:-5px;color:#404040 !important;">{include file="lang:emailReceiptBillingInformationText"}:</h3>
			<p>{$transaction->firstName}&nbsp;{$transaction->lastName}</p>
			<p>{$transaction->address}</p>
	        {if $transaction->address2}</p>
	          <p>{$transaction->address2}</p>
	        {/if}
			<p>{$transaction->city}, {$transaction->state} {$transaction->zip}</p>
		  </td>
          <td valign="top" style="padding-left:25px;line-height:.5em;border-left:1px solid #e3e3e3;"><h3 style="font-weight: bold;line-height:2.5em;margin-top:-5px;color:#404040 !important;">
          	{if $transaction->ccType != "Paypal"}
                {include file="lang:emailReceiptEnAuthorizationIDText"}:
            {else}
                 {include file="lang:emailReceiptTransactionIDText"}:
            {/if}</h3>
            <p>
            {if $transaction->ccType != "Paypal"}
                {$transaction->authorizationId}
            {else}
                {$transaction->externalTransactionId}
            {/if}
			</p>
            <p><strong>{include file="lang:emailReceiptOrderDate"}: </strong>{date($settings->ui->dateFormat,strtotime($transaction->created))}</p>
		{if $transaction->ccType == "Paypal"}
			<p>
				<strong>{include file="lang:emailReceiptBilledTo"}:</strong>
				Paypal
        		</p>
		{/if}
	    </td>
        </tr>
    </table>
  	</td>
</tr>
<tr>
    <td style="font-size: 12px;padding-top:20px;">&nbsp;</td>
</tr>
<tr>
    <td style="font-size: 12px"><table style="width: 650px;margin-left: auto;border: 1px solid #e3e3e3;margin-right: auto;background: white;border-collapse: collapse; margin-bottom:20px;">
      <tr>
        <td bgcolor="#f9f9f9" style="width:20%;vertical-align:top;border: 1px solid #e3e3e3;padding:8px;"><span style="font-weight: bold;line-height:20px;color:#404040 !important;">{$lang->emailReceiptProductsText}</span></td>
        <td bgcolor="#f9f9f9" style="width:40%;border: 1px solid #e3e3e3;padding-left:10px;"><span style="font-weight: bold;line-height:20px;color:#404040 !important;">{$lang->emailReceiptDescriptionText}</span></td>
	{if $refund}
	{else}
		<td bgcolor="#f9f9f9" {if $settings->ui->deliveryTimeStatus == 1} style="width:20%;border: 1px solid #e3e3e3;padding-left:10px;" {else} style="width:40%;border: 1px solid #e3e3e3;padding-left:10px;" {/if}><span style="font-weight: bold;line-height:20px;color:#404040 !important;">{$lang->emailReceiptDeliveryDate nofilter}</span></td>
		{if $settings->ui->deliveryTimeStatus == 1}
			<td bgcolor="#f9f9f9" style="width:20%;border: 1px solid #e3e3e3;padding-left:10px;"><span style="font-weight: bold;line-height:20px;color:#404040 !important;">{$lang->emailReceiptDeliveryTime nofilter}</span></td>
		{/if}
	{/if}
        <td nowrap bgcolor="#f9f9f9" style="width:40%;border: 1px solid #e3e3e3;padding-left:10px;"><span style="font-weight: bold;line-height:20px;color:#404040 !important;">{include file="lang:emailReceiptUnitPrice"}</span></td>
      </tr>
      {foreach $transaction->getShoppingCart()->getAllMessages() as $message}
      <tr>
        <td style="width:20%;vertical-align:top;border: 1px solid #e3e3e3;padding:8px;"><img src="{$message->getDesign()->smallSrc}" alt="{$message->getDesign()->smallSrc}" style="max-width:115px;max-height:70px;border: 1px solid #CCCCCC;-moz-border-radius: 6px;border-radius: 6px;"/></td>
        <td valign="top" style="width:40%;border: 1px solid #e3e3e3;padding-left:10px;"><p>{productDisplayName productId=$message->_getProductId() default='giftCardName'} {include file="lang:emailReceiptTo"} {$message->getGift()->recipientName} &lt;{$message->getGift()->recipientEmail}&gt;</p></td>
	{if $refund}
        {else}
       		{if $message->getGift()->physicalDelivery}
			<td valign="top" style="width:40%;border: 1px solid #e3e3e3;padding-left:10px;">
				{include file="lang:emailOrderShipmentInProcess"}
			</td>
		{else}
			<td valign="top" {if $settings->ui->deliveryTimeStatus == 1} style="width:20%;border: 1px solid #e3e3e3;padding-left:10px;" {else} style="width:40%;border: 1px solid #e3e3e3;padding-left:10px;" {/if}>
				{date($settings->ui->dateFormat,strtotime($message->getGift()->deliveryDate))}
			</td>
			{if $settings->ui->deliveryTimeStatus == 1}
				<td valign="top" style="width:20%;border: 1px solid #e3e3e3;padding-left:10px;">
					{date("H:i",strtotime($message->getGift()->deliveryDate))} (UTC)
				</td>
			{/if}
		{/if}
	{/if}
        <td valign="top" style="width:40%;border: 1px solid #e3e3e3;padding-left:10px;">{currencyToSymbol currency=$message->currency}{$message->getDiscountedPrice()|string_format:"%.2f"}&nbsp;{$message->currency}</td>
      </tr>
      {/foreach}
      <tr>
        <td style="width:20%;vertical-align:top;border: 1px solid #e3e3e3;padding:8px;">&nbsp;</td>
        <td valign="top" style="width:40%;border: 1px solid #e3e3e3;padding-left:10px;">&nbsp;</td>
	{if $refund}
        {else}
	        <td valign="top" {if $settings->ui->deliveryTimeStatus == 1} style="width:20%;border: 1px solid #e3e3e3;padding-left:10px;" {else} style="width:40%;border: 1px solid #e3e3e3;padding-left:10px;" {/if}>&nbsp;</td>
		{if $settings->ui->deliveryTimeStatus == 1}
		<td valign="top" style="width:20%;border: 1px solid #e3e3e3;padding-left:10px;">&nbsp;</td>
		{/if}
	{/if}
        <td valign="top" style="width:40%;border: 1px solid #e3e3e3;padding-left:10px;">&nbsp;</td>
      </tr>
      
      <tr>
        <td colspan="5" style="width:20%;vertical-align:top;border: 1px solid #e3e3e3;padding:8px;"><div align="right"><strong>{include file="lang:emailReceiptOrderTotal"}: {currencyToSymbol currency=$transaction->getShoppingCart()->currency}{$transaction->getShoppingCart()->getTotal()|string_format:"%.2f"}&nbsp;{$transaction->getShoppingCart()->currency}</strong><br>
          	{if $refund}
			{include file="lang:emailReceiptRefundShowUp"}
		{else}
			{include file="lang:emailReceiptAuthorized"}
		{/if}</div></td>
      </tr>
      <!--
      	<tr>
        	<td colspan="5" style="width:20%;vertical-align:top;border: 1px solid #e3e3e3;padding:8px;">&nbsp;</td>
      	</tr>
      	
      	<tr>
        	<td colspan="5" style="width:20%;vertical-align:top;border: 1px solid #e3e3e3;padding:8px;"><span style="font-weight: bold;padding-left:25px;line-height:2em;color:#404040 !important;">{include file="lang:emailFooterTop"}</span></td>
      	</tr>
      -->
      </table></td>
</tr>
