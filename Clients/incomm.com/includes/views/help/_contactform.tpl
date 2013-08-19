<form class='validate' action="#" method="post">
	<ul>
		<li>
		<label>{$lang->yourName}</label>
		<span>*<span> {$lang->required}</span></span>
		<div id="fromError" class="clear">&nbsp;</div>
		<input type="text" name="contactFromName" value="{if isset($message)}{$message->fromName}{/if}" data-validate-error-target="#fromError" data-validate-required="true" data-validate-required-message="{$lang->fromRequired}" data-validate-minlength="{$settings->contactUs->fromMin}" data-validate-minlength-message="{$lang->fromMinMsg}"  />
		</li>
		<li>
		<span class="required">*</span><label>{$lang->email}</label>
		<div id="emailError" class="clear">&nbsp;</div>
		<input type="text" name="contactEmail" value="{if isset($gift)}{$gift->recipientEmail}{/if}" id="contactEmail" data-validate-error-target="#emailError" data-validate-required="true" data-validate-required-message="{$lang->emailRequired}" data-validate-email="true" />
		</li>
		<li> 
		<span class="required">*</span><label>{$lang->subject}</label>
		<div id="subjectError" class="clear">&nbsp;</div>
		<input type="text" name="contactSubject" id="contactSubject" data-validate-required="true" data-validate-error-target="#subjectError" data-validate-required-message="{$lang->subjectRequired}" data-validate-minlength="{$settings->contactUs->subjectMin}" data-validate-minlength-message="{$lang->subjectMinMsg}" />
		</li>
		<li> 
		<span class="required">*</span><label>{$lang->message}</label>
		<div id="messageError" class="clear">&nbsp;</div>
		<textarea name="contactMessage" id="contactMessage" data-validate-required="true" data-validate-error-target="#messageError" data-validate-required-message="{$lang->messageRequired}" data-validate-minlength="{$settings->contactUs->messageMin}" data-validate-minlength-message="{$lang->messageMinMsg}"></textarea>
		</li>
		<li>
		<input type="submit" name="contactSubmit" id="contactSubmit" value="Send" style="margin-right: 0px; margin-top: 20px;" />
		</li>
	</ul>
</form>