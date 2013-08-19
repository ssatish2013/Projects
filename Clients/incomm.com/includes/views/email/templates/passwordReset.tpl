{capture name="resetUrl" assign="resetUrl"}
{url controller='login'  method='password' params='guid/'|cat:$user->passwordResetGuid full=true}
{/capture}

{capture name=subject}
{include file="lang:emailPasswordResetSubject"}
{/capture}

{capture name=title}
{include file="lang:emailPasswordResetTitle"}
{/capture}

{capture name=text}
{include file="lang:emailPasswordTextBody"}

{/capture}

{capture name=html}
<table style="width:660px;height:300px;">
  <tr>
	    <td valign="top"><p style="font-size:1.2em">{include file="lang:emailPasswordResetHtmlBody"}</p></td>
  </tr>
</table>
{/capture}
