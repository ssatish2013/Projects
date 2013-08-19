{* Get default preview amount, 0.00 or the first fixed amount from fixed products array
   Default preview amount is used in gift create page and contribute page *}

{if !isset($defaultAmount)}
	{$defaultAmount = '0.00'}
{/if}

{* if the default amount is one of the fixed products, select it *}
{if isset($fixedProducts) && count($fixedProducts)}
	{foreach from=$fixedProducts item=fixedProduct}
		{if ($defaultAmount|string_format:'%.2f' == $fixedProduct->fixedAmount|string_format:'%.2f')}
			{if ($fixedProduct->currency != 'JPY')}
				{$defaultAmount = $fixedProduct->fixedAmount|string_format:'%.2f'}
			{else}
				{$defaultAmount = $fixedProduct->fixedAmount|string_format:'%d'}
			{/if}
		{/if}
	{/foreach}
{/if}

{* Get values for preview from name, currency and amount *}
{if isset($message)}
	{$previewCurrency = $message->currency}
	{$previewFromName = $message->fromName}
	{if is_null($message->amount)}
		{$previewAmount = $defaultAmount|string_format:'%.2f'}
	{else}
		{$previewAmount = $message->amount|string_format:'%.2f'}
	{/if}
	{if ($giftingMode == giftModel::MODE_VOUCHER) || ($giftingMode == giftModel::MODE_SELF)}
		{$previewFromName = $gift->recipientName}
	{/if}
{elseif isset($gift) && isset($messages) && count($messages) > 0}
	{$message = $messages[0]}
	{$previewCurrency = $message->currency}
	{* Use the first fixed amount as default preview amount in contribute page
	   or use unverified amount on all the other pages when live preview is
	   needed and included on the page *}
	{if isset($ribbonBar) && ($ribbonBar == 'contribute')
		&& ($giftingMode == giftModel::MODE_GROUP)}
		{* For contribute page, use the first fixed amount *}
		{$previewAmount = $defaultAmount}
	{else}
		{* And for all the other pages, use unverifiedAmount from gift object *}
		{if ($previewCurrency != "JPY")}
			{$previewAmount = $gift->unverifiedAmount|string_format:'%.2f'}
		{else}
			{$previewAmount = $gift->unverifiedAmount|string_format:'%d'}
		{/if}
	{/if}
	{$previewFromName = $message->fromName}
	{if $giftingMode == giftModel::MODE_SELF}
		{$previewFromName = $gift->recipientName}
	{/if}
{else}
	{$previewFromName = {$lang->previewName}}
	{$previewCurrency = {$currency}}
	{* 0.00 or the first fixed amount from fixed products array *}
	{$previewAmount = ($isProductPage)?'':$defaultAmount}
{/if}

{* Get values for preview title *}
{if isset($gift) && isset($gift->title)}
	{$previewTitle = $gift->title}
{else}
	{* Change preview title to gift card name in lang string if gifting mode is self or voucher *}
	{if ($giftingMode == giftModel::MODE_SELF) || ($giftingMode == giftModel::MODE_VOUCHER)}
		{* Do nothing *}
	{else}
		{$previewTitle = {$lang->previewGift}}
	{/if}
{/if}

{* Get values for preview image *}
{if isset($gift)}
	{* All pages except products, create gift and voucher pages *}
	{$previewImage = $gift->getDesign()->largeSrc}
{elseif isset($design)}
	{* Create gift page *}
	{$previewImage = $design->largeSrc}
{else}
	{* Products page *}
	{$previewImage = '//gca-common.s3.amazonaws.com/assets/blank.png'}
{/if}
		<div class="column tile" id="design">
			<div id="card">
				<img src="{$previewImage}" />
				<ul>
					<li><span id="cardTitle" data-default="{$previewTitle}">{$previewTitle}</span></li>
					<li>
						<label>{$lang->previewFrom}</label>
						<span id="cardFrom" data-default="{$previewFromName}">{$previewFromName}</span>
					</li>
					{if $previewAmount != ''}
						<li class="amount">
							<span id="symbolAndAmount">
								{if $previewCurrency != "JPY"}
									{currencyToSymbol currency=$previewCurrency}<span id="cardAmount" data-default="{$previewAmount}" data-format="2">{$previewAmount}</span>
								{else}
									{currencyToSymbol currency=$previewCurrency}<span id="cardAmount" data-default="{$previewAmount}" data-format="0">{$previewAmount|string_format:"%d"}</span>
								{/if}
							</span>
						</li>
					{/if}
					{if $showCountDown == 1}
					<li class="countdown">
						{$lang->countdownDesc} {$countdown}
					</li>
					{/if}
				</ul>
			</div>
			{if isset($showCartCount) && $showCartCount && $giftingMode != giftModel::MODE_VOUCHER}
				{include file='gift/_cartCount.tpl'}
			{/if}
		</div>
