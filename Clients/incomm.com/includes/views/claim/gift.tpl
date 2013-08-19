{$messages = $gift->getMessages()}

{include file='common/header.tpl' showPreview=true ribbonBar='barcode'}
		<div class="column" id="claim">
			<div>
				<div id="messages">
					<ul>
						{foreach $messages as $key => $message}
							{assign 'user' $message->getUser()}
							<li>	{if ($gift->giftingMode != giftModel::MODE_SELF)}
									{if $message->facebookUserId}
										<img style='vertical-align: bottom' src="/facebook/securePic/uid/{$message->facebookUserId}" class="avatar" alt="{$message->fromName}" />
									{else}
										<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="avatar" alt="{$message->fromName}" />
									{/if}
									<label><strong>{$message->fromName}</strong> {$lang->claimMsgAdd}</label>
								{/if}
								<span>{$message->message|nl2br|stripTagsExcept:'<br>' nofilter}</span>
								{if $message->recordingId}
									<a href="#" class="audioPlay" data-audio-link="{$message->getRecording()->recordingUrl}"
										title="{$lang->claimPlayAudio}">
										<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="audio" />
									</a>
								{/if}
								{if $message->videoLink}
									<a href="#" class="videoPlay" data-video-link="{$message->videoLink}"
										title="{$lang->claimPlayVideo}">
										<img src="//img.youtube.com/vi/{$message->videoLink}/2.jpg" class="video" />
									</a>
								{/if}
							</li>
						{/foreach}
					</ul>
				</div>
				<div id="recipient">
					<h1>{$lang->claimRedeemWays}</h1>
					<ul>
						<li>{$lang->claimPinUse}</li>
						<li>{$lang->claimVoucherPrint}</li>
						<li>{$lang->claimPinSend}</li>
					</ul>
					<a href="/voucher/print/guid/{if isset($recipientGuid)}{$recipientGuid->guid}{/if}" class="button red">
						<span>{$lang->claimDownloadVoucher}</span>
					</a>
					{if $settings->ui->corFire == 1}
					<h2>{$lang->claimPinSend}</h2>
					<form id="sendSms" method="post" class="validate xhr">
						<label>{$lang->claimEnterMobile}</label>
						<input type="text" name="phoneNumber" placeholder="{$lang->phoneMask}" 
							data-validate-required="true"
							data-validate-required-message="{$lang->claimMobileNumberRequired}"
							data-validate-mobile="true"
							data-validate-mobile-message="{$lang->claimMobileNumberInvalid}" />
						<input type="hidden" name="giftGuid" value="{if isset($gift)}{$gift->guid}{/if}" />
						<input type="submit" value="{$lang->send}" />
					</form>
					{/if}
					<hr noshade="noshade" />
					{if isset($gift) && !isset($gift->thanked)}
						<a href="#" class="button" id="openthankyouform">
							<span>{$lang->claimSendThankYou}</span>
						</a>
					{/if}
					<a href="/gift/products" class="button">
						<span>{$lang->claimCreateGift}</span>
					</a>
				</div>
			</div>
			<div>
				<label style="padding-left: 30px;">{$lang->emailReceiptEnAuthorizationIDText}: </label>
				<strong>{if $gift->getCreatorMessage()->getShoppingCart()->getTransaction()->authorizationId}{$gift->getCreatorMessage()->getShoppingCart()->getTransaction()->authorizationId}{else}{$gift->getCreatorMessage()->getShoppingCart()->getTransaction()->externalTransactionId}{/if}</strong>
			</div>
		</div>
		
		<div class="column" id="navigation">
			<div>
				<p><strong>{$lang->tokenRedeemTerms nofilter}</strong></p>
				<p>
					{$gift->getProduct()->getDisplayTerms() nofilter}
				</p>
			</div>
		</div>
		
		{include file='gift/audiovideo.tpl'}
		{if isset($gift) && !isset($gift->thanked)}
			<div id="sendthankyouform" class="dialog" data-title="{$lang->claimSendThankYouTitle}"
				data-okbtntext="Send">
				<form method="post" class="sendThankYouMessage">
					<div class="thankyouform">
						<ul>
							<li>
								<span class="required">*</span><label>{$lang->claimRecipientsMsg}</label>
								<span>*<span> {$lang->required}</span></span>
								<input type="hidden" name="giftGuid" value="{if isset($gift)}{$gift->guid}{/if}" />
								<textarea name="message"></textarea>
							</li>
						</ul>
					</div>
				</form>
			</div>
			<div id="sendthankyouresult" class="dialog" data-title="SUCCESS" data-okbtntext="OK">
				<div class="thankyousent">
					{$lang->claimMsgSuccess}
				</div>
			</div>
		{/if}
{include file='common/footer.tpl'}
