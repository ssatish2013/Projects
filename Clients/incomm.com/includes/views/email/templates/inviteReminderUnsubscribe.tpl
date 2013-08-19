{* email template to be sent to the invitee regarding their unsubscribe *}
{capture name=subject}
{include file="lang:emailInviteReminderUnsubscribeSubject"}
{/capture}

{capture name=title}
{include file="lang:emailInviteReminderUnsubscribeTitle"}
{/capture}

{capture name=textbody}
{include file="lang:emailInviteReminderUnsubscribeTextBody"}
{/capture}

{capture name=html}
<table style="width:660px;">
<tr>
	<td valign="top" style="width:65%"><p style="font-size:1.2em">{include file="lang:emailInviteReminderUnsubscribeBody"}</p></td>
	<td style="width:35%;vertical-align:top;">
		<table width="100%" >
		<tr>
			<td style="width:35%;vertical-align:top;"><div align="left"><img src="{$giftImageSrc}"  alt="{$giftImageSrc}" style="display:block;float:right;width:225px;box-shadow:0 0 6px #343434;-moz-box-shadow:0 0 6px #343434;-webkit-box-shadow:0 0 6px #343434;border-radius:15px;-moz-border-radius:15px;-webkit-border-radius:15px;"></div></td>
		</tr>
		<tr>
			<td valign="top">&nbsp;</td>
		</tr>
		</table>
	</td>
</tr>
</table>
{/capture}