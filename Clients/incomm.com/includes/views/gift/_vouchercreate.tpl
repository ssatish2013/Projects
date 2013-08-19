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
	</ul>
	<ul>
		{include file="gift/_amountselector.tpl"}
	</ul>
</div>
