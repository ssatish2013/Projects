
{capture name=subject}

{* Purchaser Refund *}
{if $purchaserRefund}
{include file="lang:emailRefundPurchaserSubject"}
{else}
{* Recipient Refund *}
{include file="lang:emailRefundRecipientSubject"}
{/if}

{/capture}

{capture name=title}
{include file="lang:emailRefundTitle"}
{/capture}

{capture name=text}

{* Purchaser Refund *}
{if $purchaserRefund}
{include file="lang:emailRefundTextPurchaser"}
{* Recipient Refund *}
{else}
{include file="lang:emailRefundTextRecipient"}

{/if}
{/capture}

{capture name=html}
<table style="width: 698px;">
<tr>
	<td style="font-size: 12px"><h2 style="padding-left:25px;font-weight:500;line-height:1.2em;color:#404040 !important;">
	{* Purchaser Refund *}
	{if $purchaserRefund}
		{include file="lang:emailRefundHtmlPurchaser"}
	{* Recipient Refund *}
	{else}
		{include file="lang:emailRefundHtmlRecipient"}
	{/if}
	</h2>
	</td>
</tr>
{* Purchaser Refund *}
{if $purchaserRefund}
	{include file='email/common/receiptTableHtml.tpl' refund=1}
{/if}
{/capture}
