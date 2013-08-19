{include file='common/header.tpl' showPreview=false ribbonBar='status' labelText='Order Submitted'}
<div class="column" id="cart">
	<div>
		<div id="messages">
			<h2 style="white-space: nowrap;">{$lang->orderCongrats}</h2>
			{if $transaction->amount>0}
			<p>
				{if $transaction->isPayPalExpress()}
					{$lang->orderCongratsMsg1_paypal_express nofilter}
				{else}
					{$lang->orderCongratsMsg1_paypal_direct nofilter}
				{/if}
			</p>
			{/if}
			<p>
				{$lang->confirmNum nofilter}
				<b>
					{if ($transaction->externalTransactionId)}
						{$transaction->externalTransactionId}
					{else}
						{$transaction->authorizationId}
					{/if}
				</b>
			</p>
			<p>
				{$lang->orderCongratsMsg2 nofilter}
			</p>
			{if $settings->ui->useShareThis}
				<p>
					<span class="st_facebook_large" displayText="Facebook" st_url="{view::GetFullUrl('')}" st_title="{$lang->shareFacebook}"></span>
					<span class="st_twitter_large" displayText="Tweet" st_url="{view::GetFullUrl('')}" st_title="{$lang->shareTwitter}"></span>
					<span class="st_googleplus_large" displayText="Google +" st_url="{view::GetFullUrl('')}" st_title="{$lang->shareGoogle}"></span>
				</p>
			{/if}
			<hr noshade="noshade" />
			{foreach $shoppingCart->getAllGifts() as $gift}
				{if $gift->giftingMode == giftModel::MODE_GROUP && $gift->allowGuestInvite}
					<h2>
						{$lang->inviteGuests}
					</h2>
					<hr noshade="noshade" />
					{break}
				{/if}
			{/foreach}
			<ul>
				{foreach $shoppingCart->getAllGifts() as $gift}
					{foreach $gift->getAllMessages() as $m}
						{if ($m->shoppingCartId == $shoppingCart->id)}
							{$messageGuid = $m->guid}
						{/if}
					{/foreach}
					{*only group mode can invite guest*}
					{if $gift->giftingMode == giftModel::MODE_GROUP}
						{*also check for contributor, if the original gift creator allows guest to invite guest*}
						{if count($gift->getMessages())>1 && !$gift->allowGuestInvite}
						{else}
						<li>
							<h3>{$gift->eventTitle}</h3>
							<img src="{$gift->getDesign()->mediumSrc}" />
							<a href="/gift/invite/guid/{$gift->guid}/messageGuid/{$messageGuid}" class="{if $gift->getInviteSent()==0}red {/if}button"><span>{if $gift->getInviteSent()>0}{$lang->inviteMoreGuests}{else}{$lang->inviteGuestsNow}{/if}</span></a>
						</li>
						{/if}
					{/if}
				{/foreach}
			</ul>
		</div>
		<div id="summary">
			{include file='cart/_orderSummary.tpl' isCheckout=true}
		</div>
	</div>
</div>
{if $settings->ui->useShareThis}
	<script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
	<script type="text/javascript">{literal}stLight.options({publisher: "39b2f5ef-132e-4956-9137-a327b6aef978", onhover: false});{/literal}</script>
{/if}
{capture assign=inlineScripts}
	{literal}
		$(window).on("load", function() {
			PF.xdm.resize(true, {extraHeight: 25});
		});
	{/literal}
{/capture}
{include file='common/footer.tpl'}
