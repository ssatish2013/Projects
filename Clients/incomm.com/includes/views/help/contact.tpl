<!DOCTYPE html>
<html>
	<head>
		<title>{$lang->pageTitle}</title>
		{if $settings->css->customURL}
			<ling rel="stylesheet" href="{$settings->css->customURL}" type="text/css" />
		{else}
			<link rel="stylesheet" href="/css/style.css" type="text/css" />
		{/if}
	</head>
	<body>
		<div class="contact">
			<div id="accordion">
				<div id="opencontactform" class="dialog">
					<div class="thankyouform">
						<p>
							{include file='lang:contactUsCaption'}
						</p>
						{if $settings->ui->contactChat==1}
							<p>								
								<a href="https://livechat.boldchat.com/aid/{$settings->ui->contactChatAid}/bc.chat?cwdid={$settings->ui->contactChatCwdid}" target="_blank" onclick="{literal}window.open((window.pageViewer && pageViewer.link || function(link){return link;})(this.href + (this.href.indexOf('?')>=0 ? '&' : '?') + 'url=' + escape(document.location.href)), 'Chat{/literal}{$settings->ui->contactChatAid}{literal}', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=640,height=480');return false;{/literal}">
									<img src="https://cbi.boldchat.com/aid/{$settings->ui->contactChatAid}/bc.cbi?cbdid={$settings->ui->contactChatCbdid}" border="0" width="100" height="20" />
								</a>
							</p>
						{/if}
						<form id="contactUsForm" class='validate' action="/help/contactconfirm" method="post">
							<input type="hidden" name="minLengthGeneral" id="minLengthGeneral" value="{$lang->minLengthGeneral}" />
							<input type="hidden" name="requiredGeneral" id="requiredGeneral" value="{$lang->requiredGeneral}" />
							<input type="hidden" name="emailGeneral" id="emailGeneral" value="{$lang->emailGeneral}" />
							<ul>
								<li>
									<label>{$lang->yourName}</label>
									<span>*<span> {$lang->required}</span></span>
									<input type="text" name="contactFromName" value="{if isset($message)}{$message->fromName}{/if}" data-validate-error-target="#fromError" data-validate-required="true" data-validate-required-message="{$lang->fromRequired}" data-validate-minlength="{$settings->contactUs->fromMin}" data-validate-minlength-message="{$lang->fromMinMsg}"  />
									<div id="fromError" class="clear">&nbsp;</div>
								</li>
								<li>
									<span class="required">*</span><label>{$lang->email}</label>
									<input type="text" name="contactEmail" value="{if isset($gift)}{$gift->recipientEmail}{/if}" id="contactEmail" data-validate-error-target="#emailError" data-validate-required="true" data-validate-required-message="{$lang->emailRequired}" data-validate-email="true" />
									<div id="emailError" class="clear">&nbsp;</div>
								</li>
								<li>
									<span class="required">*</span><label>{$lang->subject}</label>
									<input type="text" name="contactSubject" id="contactSubject" data-validate-required="true" data-validate-error-target="#subjectError" data-validate-required-message="{$lang->subjectRequired}" data-validate-minlength="{$settings->contactUs->subjectMin}" data-validate-minlength-message="{$lang->subjectMinMsg}" />
									<div id="subjectError" class="clear">&nbsp;</div>
								</li>
								<li>
									<span class="required">*</span><label>{$lang->message}</label>
									<textarea name="contactMessage" id="contactMessage" data-validate-required="true" data-validate-error-target="#messageError" data-validate-required-message="{$lang->messageRequired}" data-validate-minlength="{$settings->contactUs->messageMin}" data-validate-minlength-message="{$lang->messageMinMsg}"></textarea>
									<div id="messageError" class="clear">&nbsp;</div>
								</li>
							</ul>
						</form>
					</div>
				</div>
			</div>
		</div>
		<script src="/js/jquery-1.7.2.js"></script>
		<script src="/js/jquery.validate.js" type="text/javascript"></script>
		<script src="/js/validate.js" type="text/javascript"></script>
		<script type="text/javascript">
			function iframevalidate() { 
				return $("form.validate").first().validate().form();
			}
		</script>
	</body>
</html>
