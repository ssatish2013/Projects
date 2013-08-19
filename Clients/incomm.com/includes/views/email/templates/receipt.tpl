{capture name=subject}
{include file="lang:receiptEmailSubject"}
{/capture}

{capture name=text}
{include file="lang:emailReceiptTextBody"}

{if $transaction->ccType != "Paypal"}
	{include file="lang:emailReceiptTextCreditAuthorized"}
{/if}
{/capture}

{capture name=title}
{include file="lang:emailReceiptTitle"}
{/capture}

{capture name=html}
<table style="width: 698px;">
<tr>
	<td style="font-size: 12px"><h2 style="padding-left:25px;font-weight:500;line-height:1.2em;color:#404040 !important;">{include file="lang:emailReceiptHtmlDesc"}</h2>
	</td>
</tr>
{include file='email/common/receiptTableHtml.tpl'}
</table>
{/capture}
