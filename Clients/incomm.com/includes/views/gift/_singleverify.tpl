<div>
	<a href="{$editurl}" class="edit"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" />Edit</a>
	<ul>
		<li>
			<label>
				{$lang->giftTitle}
			</label>
			<span>{$gift->title}</span>
		</li>
		<li>
			<label>
				{$lang->from}
			</label>
			<span>{$message->fromName}</span>
		</li>
		<li>
			<label>
				{$lang->recipientName}
			</label>
			<span>{$gift->recipientName}</span>
		</li>
		<li>
			<label>
				{$lang->recipientEmail}
			</label>
			<span>{$gift->recipientEmail}</span>
		</li>
		<li>
			<label>
				{$lang->additionalDeliveryMethod}
			</label>
			<span>{if $gift->facebookDelivery}
					{$lang->facebook}
				  {elseif $gift->twitterDelivery}
					{$lang->twitter}	  
				  {elseif $gift->physicalDelivery}
				  	{$lang->postal}
				  {elseif $gift->recipientPhoneNumber}
				  	{$lang->mobile}
				  {else}
				  	{$lang->defaultDelivery}
				  {/if}	
			</span>
		</li>
		{if $message->recordingId}
			<li>
				<a href="#" class="playBig" id="audioPlay"><span>{$lang->audioPlay}</span><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
				<input type="hidden" name="recordClient" id="recordClient" value="{$message->getRecording()->clientKey}" />
			</li>
		{/if}
		{if $message->videoLink}
			<li>
				<a href="#" class="playBig" id="videoPlay"><span>{$lang->videoPlay}</span><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a>
				<input type="hidden" name="giftVideoLink" id="giftVideoLink" value="{$message->videoLink}" />
			</li>
		{/if}
	</ul>
	<ul>
		<li>
			<label>
				{$lang->recipientMsg}
			</label>
			<span class="message">{$message->message}</span>
		</li>
		<li>
			<label>
				{$lang->chooseAmt}
			</label>
			<span>{currencyToSymbol currency=$message->currency}{if ($message->currency != "JPY")}{$message->amount}{else}{$message->amount|string_format:"%d"}{/if}</span>
		</li>
		{include file="gift/_promocode.tpl"}
		
		<li>
			<label>
				{if $gift->physicalDelivery}
					{$lang->shipDate}
				{else}
					{$lang->deliveryDate}
				{/if}
			</label>
			<span>{date($settings->ui->dateFormat, strtotime($deliveryDate))}</span>
		</li>
		{if ( ($settings->ui->deliveryTimeStatus == 1) && (!$gift->physicalDelivery) )}
		<li>
                        <label>
                                {$lang->deliveryTime}
                        </label>
                        <span>{if ($isToday != true)}{date("H:i", strtotime($deliveryDate))} {$isToday} ({$timeZoneName}){else}Now{/if}</span>
                </li>
		{/if}
                {if $gift->physicalDelivery}
                        {include file="gift/_postalAddress.tpl"}
                {/if}
	</ul>
</div>
