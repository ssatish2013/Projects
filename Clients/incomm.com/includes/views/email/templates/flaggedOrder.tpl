{$messages = $gift->getMessages()}
{$message = $messages[0]}
{$recipientFirst = $gift->recipientName}

{capture name="claimUrl" assign="claimUrl"}
{url controller='gift' method='claim' full='true' params="guid/"|cat:{$recipientGuid->guid}}
{/capture}

{capture name=subject}
{include file="lang:flaggedOrderEmailSubject"}
{/capture}

{capture name=text}
{include file="lang:flaggedOrderEmailTextBody"}
{/capture}

{capture name=title}
{include file="lang:flaggedOrderEmailTitle"}
{/capture}

{capture name=html}
<table style="width: 698px;">
<tr>
	<td style="font-size: 12px"><h2 style="padding-left:25px;font-weight:500;line-height:1.2em;color:#404040 !important;">{include file="lang:flaggedOrderEmailTop"}</h2>
	</td>
</tr>
{include file='email/common/receiptTableHtml.tpl'}
</table>
{/capture}

