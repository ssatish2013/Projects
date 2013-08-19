{include file='common/header.tpl' showPreview=true ribbonBar='status' labelText='Claim Expiration'}
<div class="column" id="claim">
	<div>
		<div id="expired">
			{if utilityHelper::isGetRequest()}
				<form action="/claim/expired" method="POST">
					<p>
						{$lang->phraseClaimGuidExpired}
					</p>		
					<input type="hidden" name="recipientGuid" value="{$recipientGuid->guid}" />
					<input type="submit" value="Send new Link" />
				</form>		
			{else}
				<p>
					{$lang->phraseNewLinkSent|strtolower|ucfirst|replace:':':''}
				</p>
			{/if}
		</div>
	</div>
</div>
{include file='common/footer.tpl'}
