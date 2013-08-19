{capture name=subject}
	{$subject nofilter}
{/capture}

{capture name=text}
{$message}
{/capture}

{capture name=html}
<tr>
	<td style="padding: 0em 1em 0em 1em">
		{$message}
	</td>
</tr>
{/capture}
