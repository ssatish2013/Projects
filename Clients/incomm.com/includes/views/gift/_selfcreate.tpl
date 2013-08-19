<div>
	<ul>
		<li>
			<span class="required">*</span>
			<label>
				{$lang->yourName}
			</label>
			<input type="text" name="giftRecipientName" value="{if isset($gift)}{$gift->recipientName}{/if}" id="giftRecipientName" data-target="#cardFrom" data-validate-required="true" data-validate-required-message="{$lang->recipientNameRequired}" data-validate-minlength="{$settings->singleCreate->toMin}" data-validate-minlength-message="{$lang->toMinMsg}" />
		</li>
		<li>
			<span class="required">*</span>
			<label>
				{$lang->yourEmail}
				<a class="help" title="{$lang->recEmailTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
			</label>
			<input type="text" name="giftRecipientEmail" value="{if isset($gift)}{$gift->recipientEmail}{/if}" id="recipient-email" data-validate-required="true" data-validate-required-message="{$lang->emailRequired}" data-validate-email="true" />
		</li>
		<li>
			<span class="required">*</span>
			<label>
				{$lang->confirmYourEmail}
			</label>
			<input type="text" name="giftRecipientEmailConfirm" value="{if isset($gift)}{$gift->recipientEmail}{/if}" id="giftRecipientEmailConfirm" data-validate-required="true" data-validate-required-message="{$lang->confirmEmail}" data-validate-email="true" data-validate-same-to="#recipient-email" data-validate-same-to-message="{$lang->confirmEmailError}" />
		</li>
		{include file="gift/_deliverymethod.tpl"}
		<input id="date_yyyy" name="date_yyyy" type="hidden" maxlength="4"{if isset($gift)} value="{$gift->deliveryDate|date_format:'%Y'}"{else} value="{date('Y')}"{/if} />
		<input id="date_mm" name="date_mm" type="hidden" maxlength="2"{if isset($gift)} value="{$gift->deliveryDate|date_format:'%m'}"{else} value="{date('m')}"{/if} />
		<input id="date_dd" name="date_dd" type="hidden" maxlength="2"{if isset($gift)} value="{$gift->deliveryDate|date_format:'%d'}"{else} value="{date('d')}"{/if} />
	</ul>
	<ul>
		{include file="gift/_amountselector.tpl"}
		{if $settings->ui->hasPromo}
			<li>
				<label>
					{$lang->promoCode}
					<a class="help" title="{$lang->promoCodeTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
				</label>
				<input type="text" class="medium" name="messagePromoCode"{if isset($message) && isset($message->promoCode) } value="{$message->promoCode}"{/if} data-validate-serverside="true" data-server-method-name="/gift/validatePromocode" data-server-param-one="window.getProductId" data-validate-server-message="{$lang->giftInvalidPromoCodes}"/>
			</li>
		{/if}
	</ul>
</div>
