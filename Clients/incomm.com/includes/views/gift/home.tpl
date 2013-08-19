{capture assign='stylesheets'}
	<link rel="stylesheet" href="/css/home.css" type="text/css" />
{/capture}
{include file="common/header.tpl" showPreview=false isHomepage=true}
{include file="gift/_giftingoption.tpl" embedded=true}
<div class="column" id="content">
	<div id="homecenter">
		{if $settings->ui->noteBand|string_format:"%d" == 1}
		<ul class="homenote">
			<li class="whitenote"><span>{$lang->note}</span> {$lang->noteMsg}</li>
			<li class="blacknote">
				{if $settings->ui->modeVoucher}
					<a href="/gift/products?mode={$voucher}">{$lang->orderGiftMsg} &gt;&gt;</a>
				{/if}
			</li>
		</ul>
		{/if}
	</div>
</div>
{if isset($autoDialog)}
{capture assign='lastMinuteScripts'}
$(document).ready(function() {
	{if $autoDialog=='redemption'}
	var dialog = $("#footer");
	dialog.mdialog({
		title: "{$lang->tokenRedeemTerms nofilter}", 
		url: "/help/redemptionTerms",
		showheader: true,
		showfooter: false,
		ioverflowy: "scroll",
		centre: true,
		width: "700px",
		height: "500px",
		showcancelbtn: false,
		showokbtn: false
	}).mdialog("show");
	{else} 
	$('#{$autoDialog}').click();
	{/if}
});
{/capture}
{/if}
{include file="common/footer.tpl"}
