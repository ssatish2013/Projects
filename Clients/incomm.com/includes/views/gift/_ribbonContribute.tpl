	<form method="POST" action="/gift/contribute" class="validate" id="contributeform">
		<div class="column invitee" id="status">
			<div>
				<ul>
					<li class="active">
						<label>{$lang->giftRecipientLabel} <a class="help" title="{$lang->giftRecipientTitle}"><img src="//gca-common.s3.amazonaws.com/assets/blank.png" /></a> </label>
						{if isset($fixedProducts)}
						<fieldset>
							{foreach $fixedProducts as $fp}
								<input name="messageAmount" id="amount{preg_replace("/[^a-zA-Z0-9\s]/", "", $fp->fixedAmount)}" type="radio" data-target="#cardAmount" data-pid="{$fp->id}" value="{$fp->fixedAmount}" {if ((isset($product) && $product->id == $fp->id) || (!isset($product) && $fp->fixedAmount == $defaultAmount))}checked="checked"{/if} /><label for="amount{preg_replace("/[^a-zA-Z0-9\s]/", "", $fp->fixedAmount)}" class="{if $fp@first}first{/if}{if $fp@last} last{/if}">{$currencySymbol nofilter}{$fp->fixedAmount|string_format:"%d"}</label>
							{/foreach}
						</fieldset>
						{/if}
						{if isset($openProducts) && count($openProducts)>0}
							<label class='amount' for="amountCustom">{$lang->entercustomAmt} <input type="text" id="amountCustomText" maxlength="10" class="currency small {$currency}{if $defaultAmount == '0.00'} required{/if}" data-target="#cardAmount" data-target-rules="nospaces restoreoldvalue currency" data-min="{$openProducts[0]->minAmount}" data-max="{$openProducts[0]->maxAmount}" data-pid="{$openProducts[0]->id}" {if isset($product) && $product->id == $openProducts[0]->id}value="{$message->amount}"{/if} data-validate-min="{$openProducts[0]->minAmount}" data-validate-max="{$openProducts[0]->maxAmount}" data-validate-error-target="#amounterror" data-default-amount="{$defaultAmount}" autocomplete="off"/></label>
							<span class="hidden"><input name="messageAmount" id="amountCustom" type="radio" value=""/> {$lang->enterAmt} </span>
						{/if}						
					</li>
				</ul>
				<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="left" />
				<img src="//gca-common.s3.amazonaws.com/assets/blank.png" class="right" />
			</div>
		</div>