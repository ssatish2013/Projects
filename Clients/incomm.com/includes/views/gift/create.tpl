{include file='common/header.tpl'
	showPreview=true showCartCount=true
	ribbonBar='progress' progressAt=2}
<form method="POST" action="create{if isset($message)}/messageGuid/{$message->guid}{/if}" id="createform" class="validate">
<input type='hidden' id='fbAccessToken' name='fbAccessToken' value='{if isset($message)}{$message->facebookAccessToken}{/if}' />
<input type='hidden' id='twitterToken' name='twitterToken' value='{if isset($message)}{$message->twitterToken}{/if}' />
<input type='hidden' id='twitterSecret' name='twitterSecret' value='{if isset($message)}{$message->twitterSecret}{/if}' />
<input type="hidden" name="giftProductId" id="giftProductId" value='{if isset($gift)}{$gift->productId}{/if}'/>
<input type="hidden" name="giftDesignId" id="designId" value="{if isset($gift)}{$gift->designId}{elseif isset($design)}{$design->id}{/if}" />
<input type="hidden" name="messageCurrency" id="currency" value="{$currency}" />
<input type="hidden" name="giftFacebookUID" id="facebookUID" value='{if isset($message)}{$message->facebookUserId}{/if}'/>
<input type="hidden" name="giftDeliveryMethod[]" value="{giftModel::DELIVERY_EMAIL}" />
<input type="hidden" name="shippingDetailState" id="recipientState" {if isset($shippingDetail)}value="{$shippingDetail->state}"{/if} />
<input type="hidden" name="giftGiftingMode" value='{if isset($gift)}{$gift->giftingMode}{elseif isset($mode)}{$mode}{/if}'/>
<input type="hidden" name="giftProductGroupId" value='{if isset($productGroup)}{$productGroup->id}{/if}'/>
<input type="hidden" name="recordClient" id="recordClient"{if isset($message) && $message->recordingId} value="{$message->getRecording()->clientKey}"{/if} />
<input type="hidden" name="giftVideoLink" id="giftVideoLink"{if isset($message)} value="{$message->videoLink}"{/if} />
<input type="hidden" name="giftingmode" id="giftingmode" value="gift/{$giftingmode}.tpl" />
<input type="hidden" name="pastTimeError" id="pastTimeError" value="{$lang->pastTimeError}" />
<input type="hidden" name="currentTimeZone" id="currentTimeZone" value="America/Los_Angeles" />
<input type="hidden" name="timeZoneOffset" id="timeZoneOffset" value="" />
<input type="hidden" name="isToday" id="isToday" value= "1" />
<input type="hidden" name="deliveryTimeStatus" id="deliveryTimeStatus" value="{$settings->ui->deliveryTimeStatus}" />

{if isset($gift) && $gift->facebookDelivery} 
<input type="hidden" name="search" value="{$gift->getRecipientFacebookName()}"/>
{/if}


{if isset($smarty.get.search)}
	<input type="hidden" name="search" value="{$smarty.get.search}" />
{/if}
	<div class="column creategift" id="content">
		{include file="gift/$giftingmode.tpl"}
	</div>
	<div class="column {if $giftingmode != '_groupcreate'}hidden{/if}" id="ribbon">
		<div>
			<img src="//gca-common.s3.amazonaws.com/assets/ribbon.invite.png" class="ribbon" />
		</div>
	</div>
	<div class="column" id="navigation">
		<div>
			<a href="/"><span>{$lang->cancel}</span></a>
			<a href="{if isset($message)}/gift/products/messageGuid/{$message->guid}{else}/gift/products?mode={$mode}{/if}"><span>{$lang->back}</span></a>
			<input id="createsubmit" type="submit" value="{$lang->cartAdd}" />
		</div>
	</div>
	<div class="overlaylit" style="visibility:hidden">
			<div class="calendardiv">
				<div class='calendarheader'><span>Select a Date</span><img src='//gca-common.s3.amazonaws.com/assets/blank.png'/></div>
				<div id="datepicker"></div>
			</div>
			<img class="calendartri" src='//gca-common.s3.amazonaws.com/assets/calendar_tri.png'/>
	</div>
</form>
<div id="dateAlert" class="dialog" data-title="{$lang->dateAlertTitle}">
	<div class="thankyouform">
		<p>{$lang->dateAlertText}</p>
	</div>
</div> 
<div id="datePast" class="dialog" data-title="{$lang->datePastTitle}">
	<div class="thankyouform">
		<p>{$lang->datePastText}</p>
	</div>
</div>
<div id="dateFuture" class="dialog" data-title="{$lang->dateFutureTitle}">
	<div class="thankyouform">
		<p>{$lang->dateFutureText}</p>
	</div>
</div>
{include file='gift/audiovideo.tpl'}
{include file='common/footer.tpl'}
