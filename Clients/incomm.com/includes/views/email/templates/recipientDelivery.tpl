{$messages = $gift->getMessages()}
{$message = $messages[0]}
{$recipientFirst = $gift->recipientName}

{capture name="claimUrl" assign="claimUrl"}
{url controller='claim' method='gift' full='true' params="guid/"|cat:{$recipientGuid->guid}}
{/capture}

{capture name=subject}
{include file="lang:recipientDeliveryEmailSubject"}
{/capture}

{capture name=text}
{include file="lang:emailDeliveryTextBody"}
{/capture}

{capture name=title}
{include file="lang:emailDeliveryTitle"}
{/capture}

{capture name=html}
<table style="width:660px;">
	<tr>
	    <td valign="top" style="width:65%">
	    	<p style="font-size:1.2em">{if count($messages) > 1}
				{include file='lang:emailDeliveryMessage.multi'}		
			{else}
				{$message = $messages[0]}
				{include file='lang:emailDeliveryMessage.single'}
			{/if}
			</p>
		</td>
	    <td style="width:35%;vertical-align:top;">
	    	<table width="100%" >
		      <tr>
		        <td style="width:35%;vertical-align:top;"><div align="left"><img src="{$gift->getDesign()->mediumSrc}"  alt="{$gift->getDesign()->mediumSrc}" style="display:block;float:right;width:225px;box-shadow:0 0 6px #343434;-moz-box-shadow:0 0 6px #343434;-webkit-box-shadow:0 0 6px #343434;border-radius:15px;-moz-border-radius:15px;-webkit-border-radius:15px;"></div></td>
	          </tr>
		      <tr>
		        <td valign="top">&nbsp;</td>
	          </tr>
	      	</table>
	    </td>
	</tr>
	<tr>
		<td style="padding: 1em 1em 0em 1em" colspan="2">
			{include file='lang:emailDeliveryNote'}
		</td>
	</tr>
</table>
{/capture}

