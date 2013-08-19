
{capture name=subject}
{include file="lang:emailThankyouSubject"}
{/capture}

{capture name=text}
{include file="lang:emailThankYouTextGreetings"}

{include file="lang:emailThankYouHeader"}

{$thankYouText}

{/capture}

{capture name=title}
{include file="lang:emailThankYouTitle"}
{/capture}

{capture name=html}
<table style="width:660px;">
  <tr>
	    <td valign="top" style="width:65%">
	    <p>{include file="lang:emailThankYouTop"}</p>
	    <p>&quot;{$thankYouText}&quot;<br>- {$recipientName}</p>
		<p class="red">&nbsp;</p>
    	<p class="grey"><span class="grey2">{$lang->emailThankYouBottom nofilter}</span></p></td>
	    <td style="width:35%;vertical-align:top;"><table width="100%" >
	      <tr>
		    <td style="width:35%;vertical-align:top;"><div align="left"><img src="{$gift->getDesign()->mediumSrc}"  alt="{$gift->getDesign()->mediumSrc}" style="display:block;float:right;width:225px;box-shadow:0 0 6px #343434;-moz-box-shadow:0 0 6px #343434;-webkit-box-shadow:0 0 6px #343434;border-radius:15px;-moz-border-radius:15px;-webkit-border-radius:15px;"></div></td>
	      </tr>
	      <tr>
	        <td valign="top">&nbsp;</td>
          </tr>
	      </table></td>
  </tr>
</table>
{/capture}
