<input type="hidden" name="customAmtDenominationError" id="customAmtDenominationError" value="This currency does not have a denomination. Please enter a whole value." />
<input type="hidden" name="customAmtInputError" id="customAmtInputError" value="At the human era, we still measure currency value in numbers." />

<li>
	<span class="required">*</span>
	<label>
		{$lang->chooseAmt}
	</label>
	{if isset($fixedProducts)} 
		<fieldset {if count($fixedProducts)==0}style="height:0"{/if}>
			{foreach $fixedProducts as $fp}
				<input name="messageAmount" id="amount{preg_replace("/[^a-zA-Z0-9\s]/", "", $fp->fixedAmount)}" type="radio" data-target="#cardAmount" data-target-rules="nospaces restoreoldvalue {if $fp->currency == 'JPY'}integer{else}currency{/if}" data-pid="{$fp->id}" value="{$fp->fixedAmount}" {if ((isset($product) && $product->id == $fp->id) || (!isset($product) && $fp@first))}checked="checked"{/if} data-currency="{$fp->currency}" /><label for="amount{preg_replace("/[^a-zA-Z0-9\s]/", "", $fp->fixedAmount)}" class="{if $fp@first}first{/if}{if $fp@last} last{/if}">{$currencySymbol nofilter}{if floatval($fp->fixedAmount) == round(floatval($fp->fixedAmount))}{$fp->fixedAmount|string_format:"%d"}{else}{$fp->fixedAmount|string_format:"%.2f"}{/if}</label>
			{/foreach}
		</fieldset>
	{/if}
	{if isset($openProducts) && count($openProducts)>0}
		<label for="amountCustom" class="custom">
			<input type="text" name="amountCustomText" id="amountCustomText" class="currency xsmall {$currency}" maxlength="10" data-target="#cardAmount" data-target-rules="nospaces restoreoldvalue {if $openProducts[0]->currency == 'JPY'}integer{else}currency{/if}" data-validate-error-target="#cardAmountError" data-min="{$openProducts[0]->minAmount}" data-max="{$openProducts[0]->maxAmount}" data-pid="{$openProducts[0]->id}" {if isset($product) && $product->id == $openProducts[0]->id}value="{$message->amount}"{/if} data-validate-min="{$openProducts[0]->minAmount}" data-validate-max="{$openProducts[0]->maxAmount}" data-currency="{$openProducts[0]->currency}" />
		</label>
		<span class="custom"><input name="messageAmount" id="amountCustom" data-pid="{$openProducts[0]->id}" type="radio" value="custom"{if isset($product) && $product->id == $openProducts[0]->id} checked="checked"{/if}/>{$lang->enterAmt}</span>
	{/if}
</li>
<li id="cardAmountError">
</li>
