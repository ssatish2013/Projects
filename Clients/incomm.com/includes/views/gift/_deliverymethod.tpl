<input type="hidden" name="twitterInvalid" id="twitterInvalid" value="{$lang->validateGiftRecipientTwitterInvalid}" />
<input type="hidden" name="twitterRequired" id="twitterRequired" value="{$lang->validateGiftRecipientTwitter}" />
<input type="hidden" name="isPhysicalOnly" id="isPhysicalOnly" value="{$design->isPhysicalOnly}" />

{* determine which delivery methods are available *}
{$noneEnabled 		= (0 == $design->isPhysicalOnly)}
{$facebookEnabled 	= (0 == $design->isPhysicalOnly && $mode != giftModel::MODE_SELF && $settings->ui->facebookDelivery)}
{$twitterEnabled 	= (0 == $design->isPhysicalOnly && $mode != giftModel::MODE_SELF && $settings->ui->twitterDelivery)}
{$mobileEnabled 	= (0 == $design->isPhysicalOnly && $settings->ui->corFire)}
{$physicalEnabled 	= (0 != $design->isPhysicalOnly || $design->isPhysical)}

{* determine how many delivery methods are available *}
{$deliveryMethodCount = 0}
{if $noneEnabled}{$deliveryMethodCount = $deliveryMethodCount + 1}{/if}
{if $facebookEnabled}{$deliveryMethodCount = $deliveryMethodCount + 1}{/if}
{if $twitterEnabled}{$deliveryMethodCount = $deliveryMethodCount + 1}{/if}
{if $mobileEnabled}{$deliveryMethodCount = $deliveryMethodCount + 1}{/if}
{if $physicalEnabled}{$deliveryMethodCount = $deliveryMethodCount + 1}{/if}

{* only show Additional Delivery Options under the following circumstances *}
{if $mode != giftModel::MODE_SELF && $deliveryMethodCount > 1}
<li>
	<label>
		{$lang->additionalDeliveryMethod}
		<a class="help" title="{$lang->additionalDeliveryTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
	</label>

	<fieldset id="deliveryMethodSet">
		{* determine how wide to make the button for each delivery method *}
		{$deliveryMethodContainerWidth = 296}
		{$deliveryMethodButtonWidth = $deliveryMethodContainerWidth / $deliveryMethodCount}

		{* determine the font size for the delivery method buttons *}
		{if $deliveryMethodCount > 4}
			{$deliveryMethodButtonFontSize = '0.8em'}
		{else}
			{$deliveryMethodButtonFontSize = '0.9em'}
		{/if}

		{* determine which delivery method is the last one displayed *}
		{if $physicalEnabled}
			{$lastDeliveryMethod = 'physical'}
		{else if $mobileEnabled}
			{$lastDeliveryMethod = 'mobile'}
		{else if $twitterEnabled}
			{$lastDeliveryMethod = 'twitter'}
		{else if $facebookEnabled}
			{$lastDeliveryMethod = 'facebook'}
		{else}
			{$lastDeliveryMethod = 'none'}
		{/if}

		{* determine which delivery method is selected by default *}
		{if $deliveryMethodCount == 1}
			{$defaultDeliveryMethod = $lastDeliveryMethod}
		{else}
			{$defaultDeliveryMethod = 'none'}
		{/if}

		{* render the delivery methods *}
		{if $noneEnabled}<input name="giftDeliveryMethod[]" id="deliveryMethodNone" type="radio" value="none"{if $defaultDeliveryMethod == 'none'} checked="checked" class="checked"{/if}><label for="deliveryMethodNone" class="first{if $lastDeliveryMethod == 'none'} last{/if}" style="width:{$deliveryMethodButtonWidth}px; font-size:{$deliveryMethodButtonFontSize};">{$lang->selectMethod}</label>{/if}
		{if $facebookEnabled}<input name="giftDeliveryMethod[]" id="deliveryMethodFacebook" type="radio" value="social"{if $defaultDeliveryMethod == 'facebook'} checked="checked" class="checked"{/if}><label for="deliveryMethodFacebook" class="{if $deliveryMethodCount == 1} first{/if}{if $lastDeliveryMethod == 'facebook'} last{/if}" style="width:{$deliveryMethodButtonWidth}px; font-size:{$deliveryMethodButtonFontSize};">{$lang->facebook}</label>{/if}
		{if $twitterEnabled}<input name="giftDeliveryMethod[]" id="deliveryMethodTwitter" type="radio" value="twitter"{if $defaultDeliveryMethod == 'twitter'} checked="checked" class="checked"{/if}><label for="deliveryMethodTwitter" class="{if $deliveryMethodCount == 1} first{/if}{if $lastDeliveryMethod == 'twitter'} last{/if}" style="width:{$deliveryMethodButtonWidth}px; font-size:{$deliveryMethodButtonFontSize};">{$lang->twitter}</label>{/if}
		{if $mobileEnabled}<input name="giftDeliveryMethod[]" id="deliveryMethodMobile" type="radio" value="mobile"{if $defaultDeliveryMethod == 'mobile'} checked="checked" class="checked"{/if}><label for="deliveryMethodMobile" class="{if $deliveryMethodCount == 1} first{/if}{if $lastDeliveryMethod == 'mobile'} last{/if}" style="width:{$deliveryMethodButtonWidth}px; font-size:{$deliveryMethodButtonFontSize};">{$lang->mobile}</label>{/if}
		{if $physicalEnabled}<input name="giftDeliveryMethod[]" id="deliveryMethodPhysical" type="radio" value="physical"{if $defaultDeliveryMethod == 'physical'} checked="checked" class="checked"{/if}><label for="deliveryMethodPhysical" class="{if $deliveryMethodCount == 1} first{/if}{if $lastDeliveryMethod == 'physical'} last{/if}" style="width:{$deliveryMethodButtonWidth}px; font-size:{$deliveryMethodButtonFontSize};">{$lang->postal}</label>{/if}
	</fieldset>

</li>
{elseif 0 != $design->isPhysicalOnly}
	<input name="giftDeliveryMethod[]" id="deliveryMethodPhysical" type="hidden" value="physical" />
{/if}
<li class="facebookName hidden">
	<span id="deliveryFacebook" class='required' style="display: none">*</span>
	<label for="recipientFacebook">{include file='lang:facebookRecipLabel'}</label>
	<input type="text" name="giftRecipientFacebook" id="recipientFacebook" {if isset($gift)}value="{$gift->getRecipientFacebookName()}" {/if} data-validate-required-conditional="fbrequired" data-validate-required-message="{$lang->validateGiftRecipientFacebookId}"/>
</li>

<li class="twitterSection hidden">
	<span id="deliveryTwitter" class='required' style="display: none">*</span>
	<label for="recipientTwitter">{include file='lang:tokenRecipientTwitter'}</label>
	<input data-skipchangevalidate="true" type="text" name="giftRecipientTwitter" id="recipientTwitter" {if isset($gift)} value="{$gift->recipientTwitter}" {/if}
		data-validate-required-conditional="twitterRequired" data-validate-required-message="{$lang->validateGiftRecipientTwitter}"/>
</li>

{if $settings->ui->corFire}
	<li class="mobileNumber hidden">
		<span id="deliveryMobile" class='required' style="display: none">*</span>
		<label for="recipientPhoneNumber">{include file='lang:tokenPhoneNumberSMS'}</label>
		<input type="text" name="giftRecipientPhoneNumber" id="recipientPhoneNumber" placeholder="{$lang->phoneMask}" {if isset($gift)} value="{$gift->recipientPhoneNumber}" {/if}
			data-validate-required-conditional="validMobileNumber" data-validate-required-message="{$lang->validateGiftRecipientPhoneNumber}"/>
	</li>
{/if}

<li class="shippingInfo hidden clear">
	<span class="required">*</span>
	<label for="recipientAddress1">{include file='lang:tokenRecipientAddress1'}</label>
	<input type="text" name="shippingDetailAddress" id="recipientAddress1" {if isset($shippingDetail)}value="{$shippingDetail->address}" {/if} data-validate-required="true" data-validate-required-message="{$lang->addressRequired}" data-validate-minlength="{$settings->singleCreate->addressMin}" data-validate-minlength-message="{$lang->addressMinMsg}" />
</li>

<li class="shippingInfo hidden">
	<label for="recipientAddress2">{include file='lang:tokenRecipientAddress2'}</label>
	<input type="text" name="shippingDetailAddress2" id="recipientAddress2" {if isset($shippingDetail)}value="{$shippingDetail->address2}" {/if} />
</li>

<li class="shippingInfo hidden">
	<span class="required">*</span>
	<label for="recipientCity">{include file='lang:tokenRecipientCity'}</label>
	<input type="text" name="shippingDetailCity" id="recipientCity" {if isset($shippingDetail)}value="{$shippingDetail->city}" {/if} data-validate-required="true" data-validate-required-message="{$lang->cityRequired}" />
</li>

<li class="shippingInfo hidden" id="stateLi" data-role="region" data-country="US">
	<span class="required">*</span>
	<label for="recipientState">{include file='lang:tokenRecipientState'}</label>
	<div class="select">
	<span></span>
	<select name="shippingDetailState" id="recipientStateList" data-placeholder="{$lang->chooseState}">
		{html_options values=$states|array_keys output=$states|array_keys selected=$shippingDetail->state}
	</select>
	</div>
</li>

<li class="shippingInfo hidden">
	<span class="required">*</span>
	<label for="recipientZip">{include file='lang:tokenRecipientZip'}</label>
	<input type="text" name="shippingDetailZip" id="recipientZip" {if isset($shippingDetail)}value="{$shippingDetail->zip}" {/if} data-validate-required="true" data-validate-required-message="{$lang->zipRequired}" data-validate-number="true" data-validate-number-message="{$lang->zipNumberMsg}" data-validate-min="{$settings->singleCreate->zipUSMin}" data-validate-min-message="{$lang->zipNumberMsg}" data-validate-max="{$settings->singleCreate->zipUSMax}" data-validate-max-message="{$lang->zipNumberMsg}" />
</li>

<li class="shippingInfo hidden">
	<label for="recipientCountry">{include file='lang:tokenRecipientCountry'}</label>
	<div class="clear">
		{$countries['US']}
	</div>
	<input type="hidden" name="shippingDetailCountry" id="recipientCountry" value="US" />
</li>
