{$messages = $gift->getMessages()}
{if count($messages) > 0}
	{$message = $messages[0]}
{/if}
{include file='mobile/common/mobileHeader.tpl'}
{$lang->claimHeader}
<div id="main">
	<div>
		<img style="float:left; padding: 5px 0 0 5px;" src="{$lang->mobileLogoImage}" height="50" width="50" />
		<span style="float:right; font-size:1.2em; width: 250px;">{$lang->mobileClaimHeader}</span>
	</div>
	<div style="clear:both;"></div>
	<section id="cardDesign">
		<img src="{$gift->getDesign()->largeSrc}" />
	</section>
	<section id="giftDisplay">
		{capture assign = 'currencySymbolAndAmount'}
			{currencyToSymbol currency=$message->currency}{$gift->unverifiedAmount}
		{/capture}
		{if isset($pinDisplay) && !empty($pinDisplay)}
			<div id="pinDisplay">
				{include file="eval:$pinDisplay"}
			</div>
			<ul class="pin">
				<li>
					<label>Gift Amount</label>
					<span class="pin">{$currencySymbolAndAmount nofilter}</span>
				</li>
			</ul>
		{elseif isset($code)}
			<ul class="pin">
				<li>
					<label>{$lang->giftCode}</label>
					<span class="pin">{$code->pan}</span>
				</li>
				<li>
					<label>{$lang->giftPin}</label>
					<span class="pin">{$code->pin}</span>
				</li>
				<li>
					<label>Gift Amount</label>
					<span class="pin">{$currencySymbolAndAmount nofilter}</span>
				</li>
			</ul>
		{/if}
	</section>
	{if !$gift->isThanked()}
		{include file='mobile/claim/_claimedThankYouForm.tpl'}
	{/if}
	<p id="cardTerms">
		<strong>{$lang->tokenRedeemTerms nofilter}</strong><br />
		{$gift->getProduct()->getDisplayTerms() nofilter}
	</p>
</div>

<p id="bottomLink">
	{$lang->mobileBottomLink}
</p>

{$lang->claimFooter}
{include file='mobile/common/mobileFooter.tpl'}
