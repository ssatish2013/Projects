{capture name=subject}
	{$lang->emailContactSubject nofilter}
{/capture}

{capture name=text}
	{$lang->from} {$senderName nofilter} \n\n{$lang->message} {$message}
{/capture}

{capture name=title}
        {$lang->emailContactTitle nofilter}
{/capture}

{capture name=html}
<table style="width:660px;">
  <tr>
            <td valign="top" style="width:65%">
		{$lang->emailContactHtmlBody nofilter}
	    </td>
  </tr>
</table>
{/capture}
