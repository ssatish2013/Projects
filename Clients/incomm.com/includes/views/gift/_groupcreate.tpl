<div class="hidden">
	<ul>
		<li>
			<span class="required">*</span>
			<label>
				{$lang->giftTitle}
				<a class="help" title="{$lang->giftTitleTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
			</label>
			<input type="text" name="giftTitle" value="{if isset($gift)}{$gift->title}{/if}" id="giftTitle" data-target="#cardTitle" data-validate-required="true" data-validate-required-message="{$lang->giftTitleRequired}" />
		</li>
		<li>
			<span class="required">*</span>
			<label>
				{$lang->from}
			</label>
			<input type="text" name="messageFromName" value="{if isset($message)}{$message->fromName}{/if}" id="messageFromName" data-target="#cardFrom" data-validate-required="true" data-validate-required-message="{$lang->fromRequired}" data-validate-minlength="{$settings->singleCreate->fromMin}" data-validate-minlength-message="{$lang->fromMinMsg}" />
		</li>
		<li>
			<span class="required">*</span>
			<label>
				{$lang->recipientName}
			</label>
			<input type="text" name="giftRecipientName" value="{if isset($gift)}{$gift->recipientName}{/if}" id="giftRecipientName" data-validate-required="true" data-validate-required-message="{$lang->recipientNameRequired}" data-validate-minlength="{$settings->singleCreate->toMin}" data-validate-minlength-message="{$lang->toMinMsg}" />
		</li>
		<li>
			<span class="required">*</span>
			<label>
				{$lang->recipientEmail}
				<a class="help" title="{$lang->recEmailTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
			</label>
			<input type="text" name="giftRecipientEmail" value="{if isset($gift)}{$gift->recipientEmail}{/if}" id="recipient-email" data-validate-required="true" data-validate-required-message="{$lang->recipientEmailRequired}" data-validate-email="true" />
		</li>
		<li>
			<span class="required">*</span>
			<label>
				{$lang->confirmRecipientEmail}
			</label>
			<input type="text" name="giftRecipientEmailConfirm" value="{if isset($gift)}{$gift->recipientEmail}{/if}" data-validate-required="true" data-validate-required-message="{$lang->confirmEmail}" data-validate-email="true" data-validate-same-to="#recipient-email" data-validate-same-to-message="{$lang->confirmEmailError}" />
		</li>
		{include file="gift/_deliverymethod.tpl"}
		{include file="gift/_amountselector.tpl"}
		{include file="gift/_deliverydate.tpl"}
		{if $settings->ui->deliveryTimeStatus}
			{include file="gift/_deliverytime.tpl"}
		{/if}
	</ul>
	<ul>
		<li>
			<span class="required">*</span>
			<label>
				{$lang->recipientMsg}
				<a class="help" title="{$lang->recipientMsgTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
			</label>
			<textarea name="messageMessage" id="messageMessage" data-validate-required="true" data-validate-required-message="{$lang->recipientMsgRequired}">{if isset($message)}{$message->message}{/if}</textarea>
		</li>
		{if $settings->ui->hasCustomRecordingOption}
			<!--[if gte IE 9]>
				<li class="createMultimedia">
					<a href="#" class="button" id="audioUpload"><span>{$lang->addAudioMsg}</span></a><a class="help" title="{$lang->audioHelpTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
					<a href="#" class="play" id="audioPlay"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
				</li>
			<![endif]-->
			<!--[if !IE]>-->
				<li class="createMultimedia">
					<a href="#" class="button" id="audioUpload"><span>{$lang->addAudioMsg}</span></a><a class="help" title="{$lang->audioHelpTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
					<a href="#" class="play" id="audioPlay"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
				</li>
			<!--<![endif]-->
		{/if}
		{if $settings->ui->hasCustomVideoOption}
			<li class="createMultimedia">
				<a href="#" class="button" id="videoUpload"><span>{$lang->addVideoMsg}</span></a><a class="help" title="{$lang->videoHelpTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
				<a href="#" class="play" id="videoPlay"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
			</li>
		{/if}
		<li class="morePadding">
			<span class="required">*</span>
			<label>
				{$lang->groupCreateEventTitle}
				<a class="help" title="{$lang->eventTitleTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
			</label>
			<input type="text" name="giftEventTitle" value="{if isset($gift)}{$gift->eventTitle}{/if}" data-validate-required="true" data-validate-required-message="{$lang->giftEventTitleRequired}" />
		</li>
		<li>
			<span class="required">*</span>
			<label>
				{$lang->groupCreateGuestMsg}
				<a class="help" title="{$lang->guestMsgTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
			</label>
			<textarea name="giftEventMessage" data-validate-required="true" data-validate-required-message="{$lang->giftEventMessageRequired}">{if isset($gift)}{$gift->eventMessage}{/if}</textarea>
		</li>
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
