<div id="videoForm" class="dialog" data-title="{$lang->uploadVideoTitle}" data-okbtntext="{$lang->uploadVideoAttach}" data-cancelbtntext="{$lang->uploadVideoCancel}">
	<div class="thankyouform">
		<h1>{$lang->uploadVideoTitle}</h1>
		<p>{$lang->uploadVideoInstructions}</p>
		<ul>
			<li>
				<label>{$lang->uploadVideoInputLabel}</label>
				<input type="text" class="videoFormURL" name="videoFormURL"/>
			</li>
		</ul>
	</div>
</div>
<div id="videoView" class="dialog" data-title="{$lang->playVideoTitle}">
	<div class="thankyouform" data-default="{$lang->uploadVideoDefault}">
	</div>
</div> 
<div id="audioForm" class="dialog" data-title="{$lang->recordDialog}" data-okbtntext="{$lang->uploadAudioRecord}" data-cancelbtntext="{$lang->uploadAudioAttach}">
	<div class="thankyouform">
		<p>{$lang->recordInstructions}</p>
		<ul>
			<li>
				<label class="audioStatus" data-button-default="{$lang->uploadAudioRecord}" data-text-default="{$lang->uploadAudioInstructions}" data-button-start="{$lang->uploadAudioStop}" data-text-start="{$lang->uploadAudioStopInstructions}" data-button-stop="{$lang->uploadAudioRetry}" data-text-stop="{$lang->uploadAudioRetryInstructions}">&nbsp;</label>
			</li>
		</ul>
	</div>
</div>
<div id="audioView" class="dialog" data-title="{$lang->playAudioTitle}">
	<div class="thankyouform" data-default="{$lang->uploadAudioDefault}">
	</div>
</div>
