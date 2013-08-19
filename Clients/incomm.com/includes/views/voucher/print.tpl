{if isset($gift->title)}
	{$voucherTitle = $gift->title}
{else}
	{$voucherTitle = $lang->giftCardName}
{/if}
{$voucherImage = $gift->getDesign()->largeSrc}
{$voucherCurrency = $message->currency}
{if ($gift->unverifiedAmount == 0)}
	{* In-store voucher print *}
	{$voucherFromName = $gift->recipientName}
	{$voucherAmount = $message->amount|string_format:'%.2f'}
{else}
	{* Gift voucher download *}
	{$voucherFromName = $message->fromName}
	{$voucherAmount = $gift->unverifiedAmount|string_format:'%.2f'}
{/if}
<!DOCTYPE html>
<html>
<head>
	<title>{$lang->pageTitle}</title>
	{if $settings->css->customURL}
		<link rel="stylesheet" href="{$settings->css->customURL}" type="text/css" />
	{else}
		<link rel="stylesheet" href="/css/voucher.css" type="text/css" />
	{/if}
	
	{$css = $settings->css}
	
	<style type="text/css">
		/* Printable Voucher banner background */
		
		#banner { 
        	    background: {$css->previewBackgroundColor} url({$css->previewBackgroundImage}) repeat top left;
		}
	</style>
</head>
<body>
<div id="container">
	<div id="content">
		<div id="header" class="block">
			<img id="img-header" src="{$settings->css->headerBackgroundImage}" alt="Header Image" height="115" />
			
			<div id="img-logo">
				<a href="/gift/home">
					<img id="img-logo1" src="{$settings->css->partnerLogo}" alt="Logo" />
				</a>
			</div>
		</div>
		
		<div id="banner" class="block">
			<div class="banner-block">
				<img id="img-banner" src="{$voucherImage}" alt="Card" title="" border="0" />
			</div>
			
			<div class="banner-block">
				<div class="banner-message-block">
					<div class="banner-message block banner-break">{$voucherTitle}</div>
					<div class="banner-from block banner-break"><span class="bold">{$lang->voucherFrom}</span> {$voucherFromName}</div>
					<div class="block banner-break banner-extra-break">
						<span class="banner-amount">
							<span id="currency_symbol">{currencyToSymbol currency=$voucherCurrency}</span><span id="currency_amount">{$voucherAmount}</span>
						</span>
					</div>
				</div>
			</div>
		</div>
		
		<div id="barcode" class="block" data-guid="{$recipientGuid->guid}">
			<div><img src="//gca-common.s3.amazonaws.com/assets/barcode.loader.gif" /></div>
		</div>
		
		<div id="info" class="block">
			<div class="info-header">
				<span id="info-amount"><span id="currency_symbol">{currencyToSymbol currency=$voucherCurrency}</span><span id="currency_amount">{$voucherAmount}</span></span> {$lang->giftCardVoucher}
			</div>
			<div class="info-message info-message-height">
				{$lang->congrats} <span id="info-amount" class="bold"><span id="currency_symbol">{currencyToSymbol currency=$voucherCurrency}</span><span id="currency_amount">{$voucherAmount}</span></span> {$lang->giftCardFrom} <span class="bold">{$lang->partnerDisplayName}</span>!
			</div>
			<div class="info-help bold">
				{$lang->howToRedeem}
			</div>
			
			<div class="info-steps-block">
				<div class="info-steps">
					<div class="info-steps-header bold">
						{$lang->stepOne nofilter}
					</div>
					
					<div class="info-steps-message">
						{$lang->stepOneMsg nofilter}
					</div>
				</div>
				
				<div class="info-steps">
					<div class="info-steps-header bold">
						{$lang->stepTwo nofilter}
					</div>
					
					<div class="info-steps-message">
						{$lang->stepTwoMsg nofilter}
					</div>
				</div>
				
				<div class="info-steps">
					<div class="info-steps-header bold">
						{$lang->stepThree nofilter}
					</div>
					
					<div class="info-steps-message">
						{$lang->stepThreeMsg nofilter}
					</div>
				</div>
			</div>
		</div>
		{if ( intval($gift->giftingMode) != 4 )}
		<div class="confBlock">
			<label>{$lang->emailReceiptEnAuthorizationIDText}: </label>
			<strong>{if $gift->getCreatorMessage()->getShoppingCart()->getTransaction()->authorizationId}{$gift->getCreatorMessage()->getShoppingCart()->getTransaction()->authorizationId}{else}{$gift->getCreatorMessage()->getShoppingCart()->getTransaction()->externalTransactionId}{/if}</strong>
		</div>
		{/if}
		<div class="disclaimer-block block">
			<span id="disclaimer-title" class="bold">{$lang->tokenRedeemTerms nofilter}</span><br />
			<span id="disclaimer-message">{$gift->getProduct()->getDisplayTerms() nofilter}</span>
		</div>
		
		<div class="footer-block block">
			<div class="footer-block-inner" style="float: left; width: 240px;">
				<span class="footer-block-inner-item">
					{$lang->voucherFooterHelp}
				</span>
			</div>
			
			<div class="footer-block-inner">
				{if ( ($settings->contactSupport->recipientPhone != "") && !is_null($settings->contactSupport->recipientPhone) )}
				<span class="footer-block-inner-item">
					{$lang->voucherFooterPhone}
				</span>
				<span class="divider">|</span>
				{/if}
				{if ( ($lang->voucherFooterEmail != "") && !is_null($lang->voucherFooterEmail) )}
				<span class="footer-block-inner-item">
					<a class="sp-link" href="mailto: {$lang->voucherFooterEmail}">{$lang->voucherFooterEmail}</a>
				</span>
				{/if}
				{if ( ($lang->voucherFooterOfficeHours != "") && !is_null($lang->voucherFooterOfficeHours) )}
				<span class="divider">|</span>
				
				<span class="footer-block-inner-item">
					{$lang->voucherFooterOfficeHours}
				</span>
				{/if}
			</div>
		</div>
	</div>
</div>
<div id="voucherprint"></div>
{if isset($scriptTags)}
	{$scriptTags nofilter}
{/if}
</body>
</html>
