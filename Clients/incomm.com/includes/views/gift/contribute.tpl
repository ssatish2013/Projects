{include file='common/header.tpl' showPreview=true ribbonBar="contribute"}
		<input type="hidden" name="giftGuid" value="{$gift->guid}" />
		<input type="hidden" name="emailGuid" value="{$emailGuid}" />
		<input type="hidden" name="messageCurrency" value="{$gift->currency}" />
		<input type="hidden" name="recordClient" id="recordClient" />
		<input type="hidden" name="giftVideoLink" id="giftVideoLink" />
		<div class="column left" id="ribbon">
			{if $gift->allowGuestInvite == 1}
			<div>
				<img src="https://gca-common.s3.amazonaws.com/assets/ribbon.left.png" class="ribbon" />
			</div>
			{/if}
		</div>
		<div class="column" id="invitee">
			<div>
				<div id="invited">
					<ul>
						<li class='header'>{$currencySymbol nofilter}{$contributionAmount|string_format:"%d"} {$lang->tokenFrom} {$contributorCount} {$lang->contributor}</li>
						{foreach $gift->getAllMessages() as $msg}
						<li>
						{if isset($msg->facebookUserId)}
							<img src="/facebook/securePic/uid/{$msg->facebookUserId}" class="avatar" alt="{$msg->fromName}"/>
						{else}
							<img src="https://gca-common.s3.amazonaws.com/assets/blank.png" class="avatar" />
						{/if}
						{$msg->fromName}
						</li>
						{/foreach}
					</ul>
				</div>
				<div id="customize">
					<div id="amounterror"></div>
					<ul>
						<li class='boxed'>
							<h2>{$gift->eventTitle}</h2>
							<p>
								{$gift->eventMessage}
							</p>
							<p>
								<b>From:</b> {$msg->fromName}
							</p>
						</li>
						<li>
							<span class="required">*</span>
							<label>
								{$lang->contributeRecipientMsg}
								<a class="help"><img src="https://gca-common.s3.amazonaws.com/assets/blank.png" /></a>
								<span>
								*
								<span> {$lang->required}</span>
								</span>
							</label>
							<textarea name="messageMessage" id="messageMessage" data-validate-required="true" data-validate-required-message="{$lang->recipientMsgRequired}"></textarea>
						</li>
						<li>
							<span class="required">*</span>
							<label>
								{$lang->contributeFrom}
							</label>
							<input type="text" name="messageFromName" id="messageFromName" data-target="#cardFrom" data-validate-required="true" data-validate-required-message="{$lang->fromRequired}" data-validate-minlength="{$settings->singleCreate->fromMin}" data-validate-minlength-message="{$lang->fromMinMsg}" />
						</li>
						{if $settings->ui->hasCustomRecordingOption}
						<li class="extrawidthli">
							<a href="#" class="button" id="audioUpload"><span>{$lang->addAudioMsg}</span></a><a class="help" title="{$lang->audioHelpTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
							<a href="#" class="play" id="audioPlay"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
						</li>
						{/if}
						{if $settings->ui->hasCustomVideoOption}
						<li class="extrawidthli">
							<a href="#" class="button" id="videoUpload"><span>{$lang->addVideoMsg}</span></a><a class="help" title="{$lang->videoHelpTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
							<a href="#" class="play" id="videoPlay"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
						</li>
						{/if}
						<li class="extrawidthli">
							<input type="submit" value="{$lang->send}" id="btncontributesend"/>
						</li>
					</ul>

				</div>
			</div>
		</div>
		<div class="column" id="navigation">
			<div>

			</div>
		</div>
	</form>

{if $showUnsubscribeInviteReminderDialog}
<div id="unsubscribeInviteReminderDialog">
	<p>You will no longer receive any reminders to contribute to this gift</p>
</div>
{/if}

{include file='gift/audiovideo.tpl'}
{capture assign='inlineScripts'}
{literal}
$('#btncontributesend').click(function(event){
			event.preventDefault();
			$('input[name="messageAmount"]').each(function(idx,item){
				if ($(item).is(":checked")){
					if($(item).attr('id')=='amountCustom'){
						$(item).val($('#amountCustomText').val());
					}
				}
			});

			$('#contributeform').submit();
});
{/literal}
jQuery.extend(jQuery.validator.messages, {
	required: "{$lang->customAmountRequired}",
	min: "{$lang->customAmountRange}",
	max: "{$lang->customAmountRange}"
});
{/capture}
{include file='common/footer.tpl'}
