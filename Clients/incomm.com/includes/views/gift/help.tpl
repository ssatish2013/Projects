<!DOCTYPE html> <html>
	<head>
		<title>
			{$lang->pageTitle}
		</title>

		<link rel="stylesheet" href="/css/jquery-ui-1.8.21.custom.css" type="text/css" />
		<link rel="stylesheet" href="/css/list.css" type="text/css" />
	</head>
	<body>
	<div class="help">
	<img src ="//gca-common.s3.amazonaws.com/assets/loading.gif" alt="Loading ..." class="loader" />
	<div id="accordion">
	{foreach helpController::$helpTopics as $topic}
	{*skip gifting mode topic if that mode is not provided by partner*}
	{if ($topic@key==giftModel::MODE_SINGLE && ($ui['modeSingle']|string_format:"%d" == 1) )
	||  ($topic@key==giftModel::MODE_GROUP && ($ui['modeGroup']|string_format:"%d" == 1) )
	||  ($topic@key==giftModel::MODE_SELF && ($ui['modeSelf']|string_format:"%d" == 1) )
	||  ($topic@key==giftModel::MODE_VOUCHER && ($ui['modeVoucher']|string_format:"%d" == 1) )
	}
	{else}
		{continue}
	{/if}
	<h3>
		<a href="#">
			{assign 'topicTitle' $topic['title']}
			{include file="lang:$topicTitle"}
		</a>
	</h3>
	<div>
		<p>
			{assign 'topicText' $topic['text']}
			{include file="lang:$topicText"}
		</p>
		<dl>
			{$sectionNumber = 1}
			{foreach helpController::$helpContents as $content}
				{*skip a specific content section based on settings*}
				{$disableGiftingOption = 'disable'|cat:ucfirst($content['title'])}
				{if $settings->ui->$disableGiftingOption}
					{continue}
				{/if}
				{*Only group mode has content no. 4*}
				{if $content@iteration==4}
					{if $topic@key!=giftModel::MODE_GROUP}
						{continue}
					{/if}
				{/if}
				<dt>
					<span>{$sectionNumber++}</span>
					{assign 'contentTitle' $content['title']}
					{include file="lang:$contentTitle"}
				</dt>
				<dd>
					{assign 'contentText' $content['text']}
					{include file="lang:$contentText"}
				</dd>
			{/foreach}
		</dl>
	</div>
	{/foreach}
	<h3><a href="#">{$lang->helpCheckout}</a></h3>
	<div>
		<p>
			{include file="lang:helpCheckoutMsg"}
		</p>
	</div>
	{if $settings->ui->hasCustomRecordingOption|string_format:"%d" == 1}
	<h3><a href="#">{$lang->helpAudio}</a></h3>
	<div>
		<p>
			{include file="lang:helpAudioMsg"}
		</p>
	</div>
	{/if}
	{if $settings->ui->hasCustomVideoOption|string_format:"%d" == 1}
	<h3><a href="#">{$lang->helpVideo}</a></h3>
        <div>
                <p>
                        {include file="lang:helpVideoMsg"}
                </p>
        </div>
	{/if}
	</div> </div>

	<script src="/js/jquery-1.7.2.js"></script>
	<script src="/js/jquery-ui-1.8.21.custom.min.js"></script>
	<script src='/js/help.js' type='text/javascript' language='javascript'></script>
	<script src='/js/toggleOptions.js' type='text/javascript' language='javascript'></script>
    </body>
</html>
